<?php
require_once 'config.php';
require_once 'layout.php';

if (!empty($_SESSION['full_name'])) { header('Location: welcome.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $last_name   = trim($_POST['Last_name'] ?? '');
    $first_name  = trim($_POST['First_name'] ?? '');
    $middle_name = trim($_POST['Middle_name'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $password    = $_POST['password'] ?? '';

    if ($last_name === '' || $first_name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
        $error = 'Please fill all required fields correctly (password min 6 characters).';
    } else {
        $_SESSION['last_name']   = $last_name;
        $_SESSION['first_name']  = $first_name;
        $_SESSION['middle_name'] = $middle_name;
        $_SESSION['full_name']   = trim("$first_name $middle_name $last_name");
        header('Location: welcome.php');
        exit;
    }
}
page_top('Sign in', false);
?>
<section class="auth-panel">
  <div class="auth-art">
    <span class="comic-tag">EST. 1950 · YARN &amp; NOTIONS</span>
    <img src="assets/snoopy-yarn-love.png" alt="Snoopy tangled in red yarn" class="auth-snoopy-img">
    <div class="speech-bubble">
      “Every great sweater starts with one skein—and a neat sales receipt!”
    </div>
    <img src="assets/peanuts-stripes.png" alt="Peanuts gang" class="auth-gang-strip">
  </div>

  <div class="auth-form-box">
    <div class="doghouse-roof-badge">🏠 DOGHOUSE POS</div>
    <h1>Open the Doghouse</h1>
    <p class="lead">Sign in to manage yarn orders, track revenue, and check unpaid tabs.</p>

    <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>

    <form action="index.php" method="POST">
      <div class="row">
        <label>First name<input type="text" name="First_name" placeholder="Charlie" required></label>
        <label>Last name<input type="text" name="Last_name" placeholder="Brown" required></label>
      </div>
      <label>Middle name (optional)<input type="text" name="Middle_name" placeholder="Optional"></label>
      <label>Email address<input type="email" name="email" placeholder="charlie@peanuts.com" required></label>
      <label>Password<input type="password" name="password" placeholder="Min. 6 characters" required minlength="6"></label>
      <button type="submit" class="btn btn-red">Enter Sales Tracker →</button>
    </form>
  </div>
</section>
<?php page_bottom(); ?>
