<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

require_login();
$user = current_user();

$receiptId = (int)($_GET['id'] ?? 0);
if ($receiptId <= 0) { http_response_code(400); exit("Bad request"); }

$pdo = db();

$stmt = $pdo->prepare("
  SELECT r.id AS receipt_id, r.token, r.status, r.verified_at,
         p.reference, p.amount, p.payment_date,
         s.matric_no, s.full_name
  FROM receipts r
  JOIN payments p ON p.id = r.payment_id
  JOIN students s ON s.id = p.student_id
  WHERE r.id = :id
  LIMIT 1
");
$stmt->execute([':id' => $receiptId]);
$row = $stmt->fetch();

if (!$row) { http_response_code(404); exit("Receipt not found"); }

$msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $token = $_POST['csrf'] ?? '';
  if (!csrf_validate($token)) {
    $msg = ['type' => 'no', 'text' => 'Invalid CSRF token.'];
  } else {
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'verify') {
      if ($row['status'] !== 'unverified') {
        $msg = ['type' => 'warn', 'text' => 'This receipt is already processed.'];
      } else {
        $upd = $pdo->prepare("
          UPDATE receipts
          SET status='verified', verified_by=:uid, verified_at=NOW()
          WHERE id=:id AND status='unverified'
        ");
        $upd->execute([':uid' => $user['id'], ':id' => $receiptId]);
        log_action($receiptId, $user['id'], 'verify', 'Receipt verified');
        header('Location: ' . APP_URL . '/receipt.php?id=' . $receiptId);
        exit;
      }
    }

    if ($action === 'reject') {
      if ($row['status'] !== 'unverified') {
        $msg = ['type' => 'warn', 'text' => 'This receipt is already processed.'];
      } else {
        $upd = $pdo->prepare("
          UPDATE receipts
          SET status='rejected', verified_by=:uid, verified_at=NOW()
          WHERE id=:id AND status='unverified'
        ");
        $upd->execute([':uid' => $user['id'], ':id' => $receiptId]);
        log_action($receiptId, $user['id'], 'reject', 'Receipt rejected');
        header('Location: ' . APP_URL . '/receipt.php?id=' . $receiptId);
        exit;
      }
    }
  }
}

// Reload
$stmt->execute([':id' => $receiptId]);
$row = $stmt->fetch();

require_once __DIR__ . '/../includes/header.php';
?>
<div class="card">
  <h1 class="h">Receipt Details</h1>

  <?php if ($msg): ?>
    <p class="badge <?= htmlspecialchars($msg['type']) ?>"><?= htmlspecialchars($msg['text']) ?></p>
  <?php endif; ?>

  <div class="row">
    <div><div class="muted">Token</div><b><?= htmlspecialchars($row['token']) ?></b></div>
    <div><div class="muted">Status</div><span class="badge"><?= htmlspecialchars($row['status']) ?></span></div>
    <div><div class="muted">Verified At</div><b><?= htmlspecialchars($row['verified_at'] ?? '—') ?></b></div>
  </div>

  <hr>

  <div class="row">
    <div><div class="muted">Student</div><b><?= htmlspecialchars($row['full_name']) ?></b> (<?= htmlspecialchars($row['matric_no']) ?>)</div>
    <div><div class="muted">Reference</div><b><?= htmlspecialchars($row['reference']) ?></b></div>
    <div><div class="muted">Amount</div><b><?= htmlspecialchars(number_format((float)$row['amount'],2)) ?></b></div>
    <div><div class="muted">Payment Date</div><b><?= htmlspecialchars($row['payment_date']) ?></b></div>
  </div>

  <hr>

  <?php if ($row['status'] === 'unverified' && ($user['role'] === 'bursary' || $user['role'] === 'admin')): ?>
    <form method="post" class="row">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
      <button class="btn" name="action" value="verify" type="submit">Verify (Lock)</button>
      <button class="btn" name="action" value="reject" type="submit">Reject</button>
    </form>
  <?php else: ?>
    <p class="muted">This receipt is locked (already processed).</p>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
