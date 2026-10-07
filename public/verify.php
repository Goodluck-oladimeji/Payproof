<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../config/config.php';

require_login();
$user = current_user();

$result = null;
$receiptRow = null;

function compute_receipt_hash(string $token, string $reference, string $amount, string $matric): string {
  // amount string must be consistent
  $payload = $token . '|' . $reference . '|' . $amount . '|' . $matric . '|' . APP_SECRET_KEY;
  return hash('sha256', $payload);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $token = $_POST['csrf'] ?? '';
  if (!csrf_validate($token)) {
    $result = ['type' => 'no', 'msg' => 'Invalid CSRF token. Refresh and try again.'];
  } else {
    $receiptToken = trim((string)($_POST['receipt_token'] ?? ''));
    if ($receiptToken === '') {
      $result = ['type' => 'no', 'msg' => 'Enter a receipt token.'];
    } else {
      $pdo = db();
      $stmt = $pdo->prepare("
        SELECT r.id AS receipt_id, r.token, r.receipt_hash, r.status, r.verified_at,
               p.reference, p.amount, p.payment_date,
               s.matric_no, s.full_name
        FROM receipts r
        JOIN payments p ON p.id = r.payment_id
        JOIN students s ON s.id = p.student_id
        WHERE r.token = :t
        LIMIT 1
      ");
      $stmt->execute([':t' => $receiptToken]);
      $receiptRow = $stmt->fetch();

      if (!$receiptRow) {
        log_action(null, $user['id'], 'check_failed', 'Token not found');
        $result = ['type' => 'no', 'msg' => 'Receipt token not found (Invalid).'];
      } else {
        // Recompute hash from DB fields
        $amountStr = number_format((float)$receiptRow['amount'], 2, '.', '');
        $computed = compute_receipt_hash($receiptRow['token'], $receiptRow['reference'], $amountStr, $receiptRow['matric_no']);

        if (!hash_equals($receiptRow['receipt_hash'], $computed)) {
          log_action((int)$receiptRow['receipt_id'], $user['id'], 'check_failed', 'Hash mismatch (tampered)');
          $result = ['type' => 'no', 'msg' => 'Hash mismatch: receipt may be tampered / fake.'];
        } else {
          // Status check
          if ($receiptRow['status'] === 'verified') {
            log_action((int)$receiptRow['receipt_id'], $user['id'], 'flag_reuse', 'Receipt already verified');
            $result = ['type' => 'warn', 'msg' => 'Valid but already VERIFIED earlier (possible reuse).'];
          } elseif ($receiptRow['status'] === 'reused') {
            log_action((int)$receiptRow['receipt_id'], $user['id'], 'flag_reuse', 'Receipt marked as reused');
            $result = ['type' => 'warn', 'msg' => 'Receipt is marked as REUSED.'];
          } elseif ($receiptRow['status'] === 'rejected') {
            log_action((int)$receiptRow['receipt_id'], $user['id'], 'check_ok', 'Receipt rejected previously');
            $result = ['type' => 'warn', 'msg' => 'Receipt hash is valid but status is REJECTED already.'];
          } else {
            log_action((int)$receiptRow['receipt_id'], $user['id'], 'check_ok', 'Receipt valid & unverified');
            $result = ['type' => 'ok', 'msg' => 'Receipt is VALID and UNVERIFIED. You can open details and verify.' ];
          }
        }
      }
    }
  }
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="card">
  <h1 class="h">Verify Receipt</h1>
  <p class="muted">Enter a receipt token. System checks Token + SHA-256 hash and returns status.</p>

  <?php if ($result): ?>
    <p class="badge <?= htmlspecialchars($result['type']) ?>"><?= htmlspecialchars($result['msg']) ?></p>
  <?php endif; ?>

  <form method="post" class="grid" style="margin-top:10px;">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
    <div>
      <label>Receipt Token</label>
      <input name="receipt_token" placeholder="e.g. PP-8F3A... " required>
    </div>
    <div style="align-self:end;">
      <button class="btn" type="submit">Check</button>
    </div>
  </form>

  <?php if ($receiptRow): ?>
    <hr>
    <div class="row">
      <div>
        <div class="muted">Student</div>
        <div><b><?= htmlspecialchars($receiptRow['full_name']) ?></b> (<?= htmlspecialchars($receiptRow['matric_no']) ?>)</div>
      </div>
      <div>
        <div class="muted">Reference</div>
        <div><b><?= htmlspecialchars($receiptRow['reference']) ?></b></div>
      </div>
      <div>
        <div class="muted">Amount</div>
        <div><b><?= htmlspecialchars(number_format((float)$receiptRow['amount'], 2)) ?></b></div>
      </div>
      <div>
        <div class="muted">Status</div>
        <div><span class="badge"><?= htmlspecialchars($receiptRow['status']) ?></span></div>
      </div>
    </div>

    <div style="margin-top:14px;">
      <a class="btn" href="<?= APP_URL ?>/receipt.php?id=<?= (int)$receiptRow['receipt_id'] ?>">Open Receipt Details</a>
    </div>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
