<?php
declare(strict_types=1);

/*
 * Reset a user's password (CLI only):  php database/reset_password.php <username>
 * Generates a random password and prints it once.
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }
if ($argc < 2) { exit("Usage: php database/reset_password.php <username>\n"); }

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

$pw = bin2hex(random_bytes(6));
$stmt = db()->prepare("UPDATE users SET password_hash = :h, is_active = 1 WHERE username = :u");
$stmt->execute([':h' => password_hash($pw, PASSWORD_DEFAULT), ':u' => $argv[1]]);

echo $stmt->rowCount() ? "New password for {$argv[1]}: $pw\n" : "User not found.\n";
