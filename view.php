<?php
require_once 'config.php';
require_once 'layout.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$stmt = mysqli_prepare(db(), "SELECT * FROM sales WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$sale = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

page_top('Sale Receipt');
?>
<section class="card narrow receipt-card">
  <?php if (!$sale): ?>
    <h1>Receipt Not Found</h1>
    <p class="error">That sale record no longer exists.</p>
  <?php else: ?>
    <div class="receipt-header">
      <img src="assets/snoopy-holding-star.png" alt="Snoopy Star Stamp" class="receipt-stamp">
      <span class="comic-tag yellow-tag">OFFICIAL SHOP RECEIPT</span>
      <h1>Receipt #<?= str_pad((int)$sale['id'], 3, '0', STR_PAD_LEFT) ?></h1>
      <p class="receipt-date">Issued on <?= e($sale['sale_date']) ?></p>
    </div>

    <div class="receipt-divider"></div>

    <dl class="receipt-details">
      <div class="receipt-row">
        <dt>Customer</dt>
        <dd><?= e($sale['customer_name']) ?></dd>
      </div>
      <div class="receipt-row">
        <dt>Product</dt>
        <dd><?= e($sale['product']) ?></dd>
      </div>
      <div class="receipt-row">
        <dt>Quantity</dt>
        <dd><?= (int)$sale['quantity'] ?> pcs</dd>
      </div>
      <div class="receipt-row">
        <dt>Unit Price</dt>
        <dd><?= peso($sale['unit_price']) ?></dd>
      </div>
      <div class="receipt-row">
        <dt>Payment Status</dt>
        <dd><span class="pill <?= strtolower(remark($sale['payment_status'])) ?>"><?= e(remark($sale['payment_status'])) ?></span></dd>
      </div>
    </dl>

    <div class="receipt-divider"></div>

    <div class="receipt-grand-total">
      <span>Total Amount</span>
      <strong><?= peso($sale['quantity'] * $sale['unit_price']) ?></strong>
    </div>

    <div class="receipt-actions">
      <a href="update.php?id=<?= (int)$sale['id'] ?>" class="btn btn-yellow">Edit Receipt</a>
      <button type="button" onclick="window.print()" class="btn btn-white">🖨 Print</button>
    </div>
  <?php endif; ?>
  <a href="welcome.php" class="back-btn">← Back to Sales Ledger</a>
</section>
<?php page_bottom(); ?>
