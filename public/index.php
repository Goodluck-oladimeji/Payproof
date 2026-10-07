<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

require_login();
require_once __DIR__ . '/../includes/header.php';
?>
<div class="card">
  <h1 class="h">Dashboard</h1>
  <p class="muted">Use PayProof to verify receipts using Token + SHA-256 hash, then lock the receipt and log all actions.</p>
  <div class="row">
    <a class="btn" href="<?= APP_URL ?>/verify.php">Verify a Receipt</a>
    <a class="btn" href="<?= APP_URL ?>/logs.php">View Audit Logs</a>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
