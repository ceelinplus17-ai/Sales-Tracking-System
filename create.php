<?php
require_once 'config.php';
require_once 'layout.php';
require_login();

$errors = [];
$sale = ['customer_name'=>'','product'=>'','quantity'=>1,'unit_price'=>'','payment_status'=>'Paid','sale_date'=>date('Y-m-d')];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$errors, $sale] = validate_sale($_POST, $products);
    if (!$errors) {
        $stmt = mysqli_prepare(db(), "INSERT INTO sales (customer_name, product, quantity, unit_price, payment_status, sale_date) VALUES (?,?,?,?,?,?)");
        mysqli_stmt_bind_param($stmt, 'ssidss', $sale['customer_name'], $sale['product'], $sale['quantity'], $sale['unit_price'], $sale['payment_status'], $sale['sale_date']);
        if (mysqli_stmt_execute($stmt)) {
            header('Location: welcome.php?msg=added');
            exit;
        }
        $errors[] = 'Could not save: ' . mysqli_error(db());
    }
}
page_top('Record a Sale');
?>
<section class="form-layout">
  <div class="card form-card">
    <span class="comic-tag yellow-tag">🧾 NEW TRANSACTION</span>
    <h1>Record a Yarn Sale</h1>
    <p class="lead">Fill in the customer’s order details below.</p>
    <?php $buttonLabel = 'Save Sale Receipt'; include '_form.php'; ?>
  </div>
  <aside class="card side-mascot-card">
    <img src="assets/snoopy-yarn-love.png" alt="Snoopy with red yarn">
    <h3>Quick Counter Check</h3>
    <p>Make sure to mark whether the customer paid at the counter or placed it on a <strong>Pending</strong> tab.</p>
  </aside>
</section>
<?php page_bottom(); ?>
