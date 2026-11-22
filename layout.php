<?php
// Shared comic-strip page frame. Usage: page_top('Title'); ... page_bottom();
function page_top($title, $showNav = true) {
  $current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title) ?> · Woodstock Yarn Shop Sales Tracker</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@600;700&family=Patrick+Hand&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="peanuts-top-ribbon" aria-hidden="true"></div>
<header class="topbar">
  <div class="topbar-inner">
    <a class="brand" href="welcome.php">
      <div class="brand-avatar">
        <img src="assets/snoopy-holding-star.png" alt="Snoopy Mascot">
      </div>
      <div class="brand-text">
        <span class="brand-title">Woodstock Yarn Shop</span>
        <span class="brand-sub">Peanuts Sales Tracking System</span>
      </div>
    </a>

    <?php if ($showNav && !empty($_SESSION['full_name'])): ?>
    <nav class="main-nav" aria-label="Primary">
      <a href="welcome.php" class="<?= $current === 'welcome.php' ? 'active' : '' ?>">Sales Ledger</a>
      <a href="create.php" class="<?= $current === 'create.php' ? 'active' : '' ?>">+ Record Sale</a>
      <span class="user-badge" title="Signed in cashier">🐾 <?= e($_SESSION['first_name'] ?? $_SESSION['full_name']) ?></span>
      <a href="welcome.php?logout=1" class="nav-out">Log out</a>
    </nav>
    <?php endif; ?>
  </div>
</header>
<main class="wrap">
<?php }

function page_bottom() { ?>
</main>
<footer class="foot">
  <div class="foot-inner">
    <div class="foot-zig-zag" aria-hidden="true"></div>
    <p>🐾 <strong>Snoopy’s Yarn Ledger</strong> · Counting every skein, stitch &amp; sale with Woodstock</p>
  </div>
</footer>
<script src="app.js"></script>
</body>
</html>
<?php }
