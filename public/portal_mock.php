<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/header.php';
?>
<div class="card">
  <h1 class="h">Payment History (Demo)</h1>
  <p class="muted">This page simulates where the “Verify Receipt (PayProof)” button would appear inside the school portal.</p>

  <hr>

  <div class="row">
    <div>
      <div class="muted">Session</div>
      <b>2025/2026</b>
    </div>
    <div>
      <div class="muted">Programme</div>
      <b>B.Sc. Cyber Security</b>
    </div>
    <div>
      <div class="muted">Status</div>
      <span class="badge ok">Payment Complete</span>
    </div>
  </div>

  <div style="margin-top:14px;">
    <a class="btn blue" href="<?= APP_URL ?>/verify.php">Verify Receipt (PayProof)</a>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
