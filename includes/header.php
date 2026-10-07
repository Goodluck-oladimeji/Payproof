<?php
declare(strict_types=1);
require_once __DIR__ . '/security.php';
start_secure_session();
$user = $_SESSION['user'] ?? null;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title><?= htmlspecialchars(APP_NAME) ?></title>
  <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
  <nav class="nav">
    <div class="nav-left">
      <a class="brand" href="<?= APP_URL ?>/index.php"><?= htmlspecialchars(APP_NAME) ?></a>
    </div>
    <div class="nav-right">
      <?php if ($user): ?>
        <span class="pill"><?= htmlspecialchars($user['username']) ?> (<?= htmlspecialchars($user['role']) ?>)</span>
        <a class="link" href="<?= APP_URL ?>/verify.php">Verify</a>
        <a class="link" href="<?= APP_URL ?>/logs.php">Logs</a>
        <?php if (($user['role'] ?? '') === 'admin'): ?>
          <a class="link" href="<?= APP_URL ?>/admin_users.php">Users</a>
        <?php endif; ?>
        <a class="btn" href="<?= APP_URL ?>/logout.php">Logout</a>
      <?php else: ?>
        <a class="btn" href="<?= APP_URL ?>/login.php">Login</a>
      <?php endif; ?>
    </div>
  </nav>
  <main class="container">
