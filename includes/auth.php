<?php
declare(strict_types=1);

require_once __DIR__ . '/security.php';
require_once __DIR__ . '/../config/db.php';

function require_login(): void {
  start_secure_session();
  if (empty($_SESSION['user'])) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
  }
}

function require_role(string $role): void {
  require_login();
  if (($_SESSION['user']['role'] ?? '') !== $role) {
    http_response_code(403);
    echo "403 Forbidden";
    exit;
  }
}

function current_user(): ?array {
  start_secure_session();
  return $_SESSION['user'] ?? null;
}

function log_action(?int $receiptId, ?int $actorId, string $action, ?string $details = null): void {
  $pdo = db();
  $stmt = $pdo->prepare("
    INSERT INTO verification_logs (receipt_id, actor_id, action, details, ip_address, user_agent)
    VALUES (:rid, :aid, :act, :det, :ip, :ua)
  ");
  $stmt->execute([
    ':rid' => $receiptId,
    ':aid' => $actorId,
    ':act' => $action,
    ':det' => $details,
    ':ip'  => $_SERVER['REMOTE_ADDR'] ?? null,
    ':ua'  => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
  ]);
}
