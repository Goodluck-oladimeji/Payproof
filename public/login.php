<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../config/db.php';

start_secure_session();

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $token = $_POST['csrf'] ?? '';
  if (!csrf_validate($token)) {
    $error = "Invalid CSRF token. Refresh and try again.";
  } else {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    $stmt = db()->prepare("SELECT id, username, password_hash, role, is_active FROM users WHERE username = :u LIMIT 1");
    $stmt->execute([':u' => $username]);
    $user = $stmt->fetch();

    if (!$user || (int)$user['is_active'] !== 1 || !password_verify($password, $user['password_hash'])) {
      $error = "Invalid login.";
    } else {
      session_regenerate_id(true); // prevent session fixation after login
      $_SESSION['user'] = [
        'id' => (int)$user['id'],
        'username' => $user['username'],
        'role' => $user['role'],
      ];
      header('Location: ' . APP_URL . '/index.php');
      exit;
    }
  }
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="card" style="max-width:520px;margin:0 auto;">
  <h1 class="h">Login</h1>
  <p class="muted">Sign in with your bursary or admin account.</p>
  <?php if ($error): ?>
    <p class="badge no"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
    <label>Username</label>
    <input name="username" autocomplete="username" required>
    <label>Password</label>
    <input name="password" type="password" autocomplete="current-password" required>
    <div style="margin-top:14px;">
      <button class="btn" type="submit">Sign in</button>
    </div>
  </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
