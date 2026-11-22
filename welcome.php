<?php
require_once 'config.php';
require_once 'layout.php';

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}
require_login();

$db = db();

// READ: summary numbers & sales metrics
$sum = mysqli_fetch_assoc(mysqli_query($db,
    "SELECT COUNT(*) AS orders,
            COALESCE(SUM(quantity), 0) AS units_sold,
            COALESCE(SUM(quantity * unit_price), 0) AS revenue,
            COALESCE(SUM(CASE WHEN payment_status='Paid' THEN quantity * unit_price END), 0) AS collected,
            COALESCE(SUM(CASE WHEN payment_status='Pending' THEN quantity * unit_price END), 0) AS pending
     FROM sales"));

// READ: top selling product
$topProdRow = mysqli_fetch_assoc(mysqli_query($db,
    "SELECT product, SUM(quantity) AS total_qty, SUM(quantity * unit_price) AS total_sales
     FROM sales GROUP BY product ORDER BY total_qty DESC, total_sales DESC LIMIT 1"));

// READ: all records
$result = mysqli_query($db, "SELECT * FROM sales ORDER BY sale_date DESC, id DESC");
$rows = [];
while ($r = mysqli_fetch_assoc($result)) {
    $rows[] = $r;
}

$messages = [
    'added'   => 'Woof! New yarn sale recorded in the ledger.',
    'updated' => 'Receipt updated! Woodstock double-checked the numbers.',
    'deleted' => 'Sale record removed from the ledger.',
];
$flash = $messages[$_GET['msg'] ?? ''] ?? '';

page_top('Sales Dashboard');
?>
<section class="hero-strip">
  <div class="hero-copy">
    <span class="comic-tag yellow-tag">🐾 DAILY SALES TRACKING SYSTEM</span>
    <h1>Welcome back, <?= e($_SESSION['full_name']) ?>!</h1>
    <p>Track every skein sold, monitor collected revenue, and follow up on pending customer tabs.</p>
    <div class="hero-actions">
      <a href="create.php" class="btn btn-yellow">+ Record New Sale</a>
      <button type="button" id="exportCsvBtn" class="btn btn-white">⬇ Export CSV</button>
    </div>
  </div>
  <div class="hero-illustration">
    <img src="assets/peanuts-gang-banner.png" alt="Snoopy on his red doghouse with the Peanuts gang">
  </div>
</section>

<?php if ($flash): ?><div class="flash">⭐ <?= e($flash) ?></div><?php endif; ?>

<section class="stats-grid" aria-label="Sales Key Performance Indicators">
  <article class="stat-card">
    <div class="stat-head">
      <span class="stat-label">Total Orders</span>
      <span class="stat-icon">🧾</span>
    </div>
    <div class="stat-num"><?= (int)$sum['orders'] ?></div>
    <div class="stat-foot"><?= (int)$sum['units_sold'] ?> total items sold</div>
  </article>

  <article class="stat-card accent-blue">
    <div class="stat-head">
      <span class="stat-label">Gross Revenue</span>
      <span class="stat-icon">💰</span>
    </div>
    <div class="stat-num"><?= peso($sum['revenue']) ?></div>
    <div class="stat-foot">All recorded yarn sales</div>
  </article>

  <article class="stat-card accent-green">
    <div class="stat-head">
      <span class="stat-label">Collected (Paid)</span>
      <span class="stat-icon">✅</span>
    </div>
    <div class="stat-num"><?= peso($sum['collected']) ?></div>
    <div class="stat-foot">Settled in cash / register</div>
  </article>

  <article class="stat-card accent-yellow">
    <div class="stat-head">
      <span class="stat-label">Unpaid Tabs (Pending)</span>
      <span class="stat-icon">⏳</span>
    </div>
    <div class="stat-num"><?= peso($sum['pending']) ?></div>
    <div class="stat-foot">Awaiting customer payment</div>
  </article>
</section>

<section class="card ledger-card">
  <div class="card-head">
    <div>
      <h2>Sales Ledger</h2>
      <p class="subtext">Filter by customer, item, or payment status in real time.</p>
    </div>
    <div class="toolbar">
      <div class="filter-pills" role="group" aria-label="Filter by status">
        <button type="button" class="filter-btn active" data-filter="all">All (<?= count($rows) ?>)</button>
        <button type="button" class="filter-btn" data-filter="paid">Paid</button>
        <button type="button" class="filter-btn" data-filter="pending">Pending</button>
      </div>
      <input type="search" id="search" placeholder="🔍 Search customer or yarn..." aria-label="Search sales">
    </div>
  </div>

  <div class="table-scroll">
    <table id="salesTable">
      <thead>
        <tr>
          <th>Receipt #</th>
          <th>Sale Date</th>
          <th>Customer</th>
          <th>Yarn / Product</th>
          <th class="num">Qty</th>
          <th class="num">Unit Price</th>
          <th class="num">Total</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $row):
          $total = $row['quantity'] * $row['unit_price'];
          $statusLower = strtolower(remark($row['payment_status']));
      ?>
        <tr data-status="<?= $statusLower ?>">
          <td><span class="receipt-id">#<?= str_pad((int)$row['id'], 3, '0', STR_PAD_LEFT) ?></span></td>
          <td><?= e($row['sale_date']) ?></td>
          <td><strong><?= e($row['customer_name']) ?></strong></td>
          <td><?= e($row['product']) ?></td>
          <td class="num"><?= (int)$row['quantity'] ?></td>
          <td class="num"><?= peso($row['unit_price']) ?></td>
          <td class="num"><strong><?= peso($total) ?></strong></td>
          <td><span class="pill <?= $statusLower ?>"><?= e(remark($row['payment_status'])) ?></span></td>
          <td class="actions">
            <a class="action-btn" href="view.php?id=<?= (int)$row['id'] ?>" title="View Receipt">View</a>
            <a class="action-btn edit" href="update.php?id=<?= (int)$row['id'] ?>" title="Edit Sale">Edit</a>
            <form method="POST" action="delete.php" class="inline" data-confirm="Delete receipt #<?= (int)$row['id'] ?> for <?= e($row['customer_name']) ?>?">
              <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
              <button type="submit" class="action-btn danger" title="Delete Sale">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
      <?php if (count($rows) > 0): ?>
      <tfoot>
        <tr>
          <td colspan="4"><strong>Visible Ledger Subtotal</strong></td>
          <td class="num"><strong id="visibleQty"><?= (int)$sum['units_sold'] ?></strong></td>
          <td></td>
          <td class="num"><strong id="visibleTotal"><?= peso($sum['revenue']) ?></strong></td>
          <td colspan="2"></td>
        </tr>
      </tfoot>
      <?php endif; ?>
    </table>
  </div>

  <p id="noResults" class="empty" hidden>🐾 Snoopy sniffed everywhere, but no sales match that filter.</p>
  <?php if (count($rows) === 0): ?>
    <p class="empty">No sales recorded yet. Click “+ Record New Sale” to log your first order!</p>
  <?php endif; ?>
</section>

<section class="bottom-grid">
  <div class="card catalog-card">
    <div class="card-head">
      <div>
        <h2>Shop Price Catalogue</h2>
        <p class="subtext">Standard retail rates for quick counter reference.</p>
      </div>
      <?php if ($topProdRow): ?>
      <div class="star-seller-badge">
        <img src="assets/snoopy-star-burst.png" alt="Top Seller Star">
        <div>
          <small>TOP SELLER</small>
          <strong><?= e($topProdRow['product']) ?> (<?= (int)$topProdRow['total_qty'] ?> sold)</strong>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <div class="catalog-grid">
    <?php
      $names = array_keys($products);
      $i = 0;
      while ($i < count($names)):
        $pName = $names[$i];
        $pPrice = $products[$pName];
    ?>
      <div class="catalog-item">
        <span class="item-name">🧶 <?= e($pName) ?></span>
        <span class="item-price"><?= peso($pPrice) ?></span>
      </div>
    <?php
        $i++;
      endwhile;
    ?>
    </div>
  </div>

  <aside class="card mascot-card">
    <img src="assets/snoopy-sewing.png" alt="Snoopy sitting on a vintage sewing machine">
    <h3>Craft Counter Tip</h3>
    <p>Select any product on the <strong>Record Sale</strong> page and Snoopy will automatically fill in the unit price and calculate the receipt total!</p>
    <a href="create.php" class="btn btn-red">Log a Sale Now</a>
  </aside>
</section>
<?php page_bottom(); ?>
