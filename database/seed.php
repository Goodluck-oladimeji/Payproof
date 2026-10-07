<?php
declare(strict_types=1);

/*
 * Demo data seeder (CLI only):  php database/seed.php
 * Creates admin + bursary demo users with RANDOM passwords (printed once),
 * one valid demo receipt and one deliberately tampered receipt.
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

function compute_receipt_hash(string $token, string $reference, string $amount, string $matric): string {
  // Must match compute_receipt_hash() in public/verify.php
  return hash('sha256', $token . '|' . $reference . '|' . $amount . '|' . $matric . '|' . APP_SECRET_KEY);
}

$pdo = db();

echo "Demo accounts (save now, shown only once):\n";
foreach (['admin', 'bursary'] as $role) {
  $pw = bin2hex(random_bytes(6));
  $pdo->prepare("INSERT INTO users (username, password_hash, role, is_active) VALUES (:u,:h,:r,1)
                 ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), is_active = 1")
      ->execute([':u' => $role, ':h' => password_hash($pw, PASSWORD_DEFAULT), ':r' => $role]);
  echo "  $role / $pw\n";
}

$demos = [
  ['EU/CSC/25/001', 'Demo Student',    'Cybersecurity', 25000.00, false],
  ['EU/CSC/25/002', 'Tampered Sample', 'Cybersecurity', 25000.00, true],
];

echo "\nSample receipts:\n";
foreach ($demos as [$matric, $name, $dept, $amount, $tamper]) {
  $pdo->prepare("INSERT IGNORE INTO students (matric_no, full_name, department) VALUES (:m,:n,:d)")
      ->execute([':m' => $matric, ':n' => $name, ':d' => $dept]);
  $studentId = (int)$pdo->query("SELECT id FROM students WHERE matric_no=" . $pdo->quote($matric))->fetchColumn();

  $reference = 'REF-' . strtoupper(bin2hex(random_bytes(4)));
  $pdo->prepare("INSERT INTO payments (student_id, amount, reference, payment_date, channel, status)
                 VALUES (:sid,:amt,:ref,:dt,'portal','paid')")
      ->execute([':sid' => $studentId, ':amt' => $amount, ':ref' => $reference, ':dt' => date('Y-m-d')]);
  $paymentId = (int)$pdo->lastInsertId();

  $token = 'PP-' . strtoupper(bin2hex(random_bytes(10)));
  $hash  = compute_receipt_hash($token, $reference, number_format($amount, 2, '.', ''), $matric);
  if ($tamper) { $hash = str_repeat('0', 64); }

  $pdo->prepare("INSERT INTO receipts (payment_id, token, receipt_hash) VALUES (:pid,:t,:h)")
      ->execute([':pid' => $paymentId, ':t' => $token, ':h' => $hash]);
  echo "  $token  ($name)" . ($tamper ? '  <- tampered (hash mismatch demo)' : '') . "\n";
}
echo "\nDone. Verify at " . APP_URL . "/verify.php\n";
