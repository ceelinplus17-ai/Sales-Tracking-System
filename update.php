<?php
require_once 'config.php';
require_once 'layout.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$db = db();
$errors = [];

$stmt = mysqli_prepare($db, "SELECT * FROM sales WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$sale = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$sale) {
    page_top('Update Sale');
    echo '<section class="card narrow"><p class="error">That sale receipt no longer exists.</p><a href="welcome.php" class="back-btn">← Back to Sales Ledger</a></section>';
    page_bottom();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$errors, $sale] = validate_sale($_POST, $products);
    if (!$errors) {
        $stmt = mysqli_prepare($db, "UPDATE sales SET customer_name=?, product=?, quantity=?, unit_price=?, payment_status=?, sale_date=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'ssidssi', $sale['customer_name'], $sale['product'], $sale['quantity'], $sale['unit_price'], $sale['payment_status'], $sale['sale_date'], $id);
        if (mysqli_stmt_execute($stmt)) {
            header('Location: welcome.php?msg=updated');
            exit;
        }
        $errors[] = 'Could not update: ' . mysqli_error($db);
    }
}
page_top('Update Sale');
?>
<section class="form-layout">
  <div class="card form-card">
    <span class="comic-tag">✏️ EDIT RECEIPT #<?= str_pad($id, 3, '0', STR_PAD_LEFT) ?></span>
    <h1>Update Sale Record</h1>
    <p class="lead">Modify quantities, prices, or settle a pending payment.</p>
    <?php $buttonLabel = 'Save Updated Receipt'; include '_form.php'; ?>
  </div>
  <aside class="card side-mascot-card">
    <img src="assets/snoopy-sewing.png" alt="Snoopy on sewing machine">
    <h3>Editing Receipt #<?= str_pad($id, 3, '0', STR_PAD_LEFT) ?></h3>
    <p>Need to mark a pending customer order as <strong>Paid</strong>? Switch the payment dropdown and save!</p>
  </aside>
</section>
<?php page_bottom(); ?>
