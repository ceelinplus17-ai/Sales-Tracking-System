<?php
require_once 'config.php'; // starts the session + provides db()
require_once 'layout.php';
$conn = db();

// Ensure users table exists even if an older database.sql was previously imported
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  full_name     VARCHAR(100) NOT NULL,
  username      VARCHAR(50)  NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL
)");

// Redirect if already logged in
if (!empty($_SESSION['user_id'])) {
    header('Location: welcome.php');
    exit;
}

$activeTab = ($_GET['tab'] ?? '') === 'register' ? 'register' : 'login';
$error = '';
$loginUsername = '';
$regFullName = '';
$regUsername = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'login';

    // --- HANDLE LOGIN ---
    if ($action === 'login') {
        $activeTab = 'login';
        $loginUsername = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($loginUsername === '' || $password === '') {
            $error = 'Please enter both username and password.';
        } else {
            $stmt = $conn->prepare('SELECT id, full_name, username, password_hash FROM users WHERE username = ? LIMIT 1');
            if (!$stmt) {
                $error = 'Database error: ' . $conn->error;
            } else {
                $stmt->bind_param('s', $loginUsername);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                $stmt->close();

                if ($user && password_verify($password, $user['password_hash'])) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];

                    header('Location: welcome.php');
                    exit;
                } else {
                    $error = 'Invalid username or password.';
                }
            }
        }
    }

    // --- HANDLE REGISTRATION ---
    if ($action === 'register') {
        $activeTab = 'register';
        $regFullName = trim($_POST['full_name'] ?? '');
        $regUsername = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($regFullName === '' || $regUsername === '' || $password === '') {
            $error = 'All fields are required to create an account.';
        } elseif (!preg_match('/^[a-zA-Z0-9_.-]{3,50}$/', $regUsername)) {
            $error = 'Username must be 3–50 characters (letters, numbers, dots, underscores, or hyphens).';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } else {
            $checkStmt = $conn->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
            if (!$checkStmt) {
                $error = 'Database error: ' . $conn->error;
            } else {
                $checkStmt->bind_param('s', $regUsername);
                $checkStmt->execute();
                $checkStmt->store_result();

                if ($checkStmt->num_rows > 0) {
                    $error = 'That username is already taken. Try logging in instead.';
                    $checkStmt->close();
                } else {
                    $checkStmt->close();
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                    $insertStmt = $conn->prepare('INSERT INTO users (full_name, username, password_hash) VALUES (?, ?, ?)');
                    $insertStmt->bind_param('sss', $regFullName, $regUsername, $passwordHash);

                    if ($insertStmt->execute()) {
                        session_regenerate_id(true);
                        $_SESSION['user_id'] = $insertStmt->insert_id;
                        $_SESSION['username'] = $regUsername;
                        $_SESSION['full_name'] = $regFullName;
                        $insertStmt->close();

                        header('Location: welcome.php');
                        exit;
                    } else {
                        $error = 'Something went wrong while creating your account.';
                        $insertStmt->close();
                    }
                }
            }
        }
    }
}

page_top('Sign In & Register', false);
?>
<section class="auth-panel">
  <!-- Left Comic Panel: Snoopy & Peanuts Gang Artwork -->
  <div class="auth-art">
    <span class="comic-tag">EST. 2007 · YARN &amp; NOTIONS</span>
    <img src="assets/snoopy-yarn-love.png" alt="Snoopy tangled in red yarn" class="auth-snoopy-img">
    <div class="speech-bubble" id="snoopy-quote">
      <?= $activeTab === 'register'
        ? '“New to the shop? Grab a skein and register your staff badge!”'
        : '“Every great sweater starts with one skein—and a neat sales receipt!”' ?>
    </div>
    <img src="assets/peanuts-stripes.png" alt="Peanuts gang" class="auth-gang-strip">
  </div>

  <!-- Right Comic Panel: Log In / Register Forms -->
  <div class="auth-form-box">
    <div class="doghouse-roof-badge">🏠 SNOOPY'S HOUSE</div>
    <h1 id="form-heading"><?= $activeTab === 'register' ? 'Join the Yarn Crew!' : 'Open the Doghouse' ?></h1>
    <p class="lead" id="form-subtitle">
      <?= $activeTab === 'register'
        ? 'Register a staff account to record yarn sales and print receipts.'
        : 'Log in with your staff account or register a new one below.' ?>
    </p>

    <div class="auth-tabs" role="tablist" aria-label="Account access">
      <button type="button" role="tab" aria-selected="<?= $activeTab === 'login' ? 'true' : 'false' ?>"
              class="auth-tab-btn <?= $activeTab === 'login' ? 'active' : '' ?>"
              onclick="switchAuthTab('login')">
        🔑 Log In
      </button>
      <button type="button" role="tab" aria-selected="<?= $activeTab === 'register' ? 'true' : 'false' ?>"
              class="auth-tab-btn <?= $activeTab === 'register' ? 'active' : '' ?>"
              onclick="switchAuthTab('register')">
        🐾 Register
      </button>
    </div>

    <?php if ($error !== ''): ?>
      <div class="error" id="auth-error" role="alert">⚠️ <?= e($error) ?></div>
    <?php endif; ?>

    <!-- LOGIN FORM -->
    <form id="login-form" method="POST" action="index.php" class="<?= $activeTab === 'login' ? '' : 'is-hidden' ?>">
      <input type="hidden" name="action" value="login">

      <div class="field-group">
        <label for="login-username">Username</label>
        <input type="text" id="login-username" name="username" placeholder="e.g. charlie_brown"
               autocomplete="username" required value="<?= e($loginUsername) ?>">
      </div>

      <div class="field-group">
        <label for="login-password">Password</label>
        <div class="password-wrap">
          <input type="password" id="login-password" name="password" placeholder="Enter your password"
                 autocomplete="current-password" required>
          <button type="button" class="toggle-pw" onclick="togglePassword('login-password', this)" aria-label="Show password">Show</button>
        </div>
      </div>

      <button type="submit" class="btn btn-red">Log In</button>
      <p class="auth-switch-hint">Don’t have a staff account yet? <button type="button" class="link-btn" onclick="switchAuthTab('register')">Register here</button></p>
    </form>

    <!-- REGISTER FORM -->
    <form id="register-form" method="POST" action="index.php" class="<?= $activeTab === 'register' ? '' : 'is-hidden' ?>">
      <input type="hidden" name="action" value="register">

      <div class="field-group">
        <label for="reg-fullname">Full Name</label>
        <input type="text" id="reg-fullname" name="full_name" placeholder="e.g. Charlie Brown"
               autocomplete="name" required value="<?= e($regFullName) ?>">
      </div>

      <div class="field-group">
        <label for="reg-username">Username</label>
        <input type="text" id="reg-username" name="username" placeholder="Choose a unique username"
               autocomplete="username" required value="<?= e($regUsername) ?>">
      </div>

      <div class="field-group">
        <label for="reg-password">Password (min. 6 characters)</label>
        <div class="password-wrap">
          <input type="password" id="reg-password" name="password" placeholder="Create a password"
                 autocomplete="new-password" minlength="6" required>
          <button type="button" class="toggle-pw" onclick="togglePassword('reg-password', this)" aria-label="Show password">Show</button>
        </div>
      </div>

      <div class="field-group">
        <label for="reg-confirm">Confirm Password</label>
        <div class="password-wrap">
          <input type="password" id="reg-confirm" name="confirm_password" placeholder="Re-enter your password"
                 autocomplete="new-password" minlength="6" required>
        </div>
      </div>

      <button type="submit" class="btn btn-yellow btn-full">Create Account</button>
      <p class="auth-switch-hint">Already registered? <button type="button" class="link-btn" onclick="switchAuthTab('login')">Log in instead</button></p>
    </form>
  </div>
</section>

<script>
function switchAuthTab(tab) {
  const loginForm = document.getElementById('login-form');
  const registerForm = document.getElementById('register-form');
  const buttons = document.querySelectorAll('.auth-tab-btn');
  const heading = document.getElementById('form-heading');
  const subtitle = document.getElementById('form-subtitle');
  const quote = document.getElementById('snoopy-quote');
  const errBox = document.getElementById('auth-error');

  if (errBox) errBox.remove();

  if (tab === 'login') {
    loginForm.classList.remove('is-hidden');
    registerForm.classList.add('is-hidden');
    buttons[0].classList.add('active');
    buttons[0].setAttribute('aria-selected', 'true');
    buttons[1].classList.remove('active');
    buttons[1].setAttribute('aria-selected', 'false');
    heading.textContent = 'Open the Doghouse';
    subtitle.textContent = 'Log in with your staff account or register a new one below.';
    if (quote) quote.textContent = '“Every great sweater starts with one skein—and a neat sales receipt!”';
  } else {
    loginForm.classList.add('is-hidden');
    registerForm.classList.remove('is-hidden');
    buttons[0].classList.remove('active');
    buttons[0].setAttribute('aria-selected', 'false');
    buttons[1].classList.add('active');
    buttons[1].setAttribute('aria-selected', 'true');
    heading.textContent = 'Join the Yarn Crew!';
    subtitle.textContent = 'Register a staff account to record yarn sales and print receipts.';
    if (quote) quote.textContent = '“New to the shop? Grab a skein and register your staff badge!”';
  }
}

function togglePassword(inputId, btn) {
  const input = document.getElementById(inputId);
  if (!input) return;
  if (input.type === 'password') {
    input.type = 'text';
    btn.textContent = 'Hide';
    btn.setAttribute('aria-label', 'Hide password');
  } else {
    input.type = 'password';
    btn.textContent = 'Show';
    btn.setAttribute('aria-label', 'Show password');
  }
}
</script>
<?php page_bottom(); ?>
