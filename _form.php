<?php
// Shared form used by create.php and update.php.
// Expects: $sale (array), $errors (array), $products, $buttonLabel
?>
<?php if ($errors): ?>
  <div class="error"><?php foreach ($errors as $err): ?><div>⚠️ <?= e($err) ?></div><?php endforeach; ?></div>
<?php endif; ?>

<form method="POST" id="saleForm">
  <label>Customer Name
    <input type="text" name="customer_name" placeholder="e.g. Lucy van Pelt" value="<?= e($sale['customer_name']) ?>" required>
  </label>

  <label>Yarn or Craft Product
    <select name="product" id="product" required>
      <option value="">— Select an item from the shop —</option>
      <?php foreach ($products as $name => $price): ?>
        <option value="<?= e($name) ?>" data-price="<?= e($price) ?>" <?= $sale['product'] === $name ? 'selected' : '' ?>>
          <?= e($name) ?> (<?= peso($price) ?>)
        </option>
      <?php endforeach; ?>
    </select>
  </label>

  <div class="row">
    <label>Quantity
      <input type="number" name="quantity" id="quantity" min="1" value="<?= e($sale['quantity']) ?>" required>
    </label>
    <label>Unit Price (₱)
      <input type="number" name="unit_price" id="unit_price" min="0.01" step="0.01" value="<?= e($sale['unit_price']) ?>" required>
    </label>
  </div>

  <div class="receipt-preview-box">
    <span>Calculated Receipt Total</span>
    <strong id="totalOut">₱0.00</strong>
  </div>

  <div class="row">
    <label>Payment Status
      <select name="payment_status">
        <option value="Paid"    <?= $sale['payment_status'] === 'Paid' ? 'selected' : '' ?>>✅ Paid</option>
        <option value="Pending" <?= $sale['payment_status'] === 'Pending' ? 'selected' : '' ?>>⏳ Pending</option>
      </select>
    </label>
    <label>Sale Date
      <input type="date" name="sale_date" value="<?= e($sale['sale_date']) ?>" required>
    </label>
  </div>

  <button type="submit" class="btn btn-red"><?= e($buttonLabel) ?></button>
</form>
<a href="welcome.php" class="back-btn">← Back to Sales Ledger</a>
