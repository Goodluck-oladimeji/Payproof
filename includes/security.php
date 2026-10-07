<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

function start_secure_session(): void {
  if (session_status() === PHP_SESSION_ACTIVE) return;

  session_name(SESSION_NAME);

  $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

  session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'secure' => $isHttps,
    'samesite' => 'Lax',
  ]);

  ini_set('session.use_strict_mode', '1');
  ini_set('session.cookie_httponly', '1');

  session_start();

  // Basic fixation protection: regenerate once per session
  if (empty($_SESSION['_regen'])) {
    session_regenerate_id(true);
    $_SESSION['_regen'] = 1;
  }
}
