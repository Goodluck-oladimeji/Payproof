<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/security.php';

start_secure_session();
$_SESSION = [];
session_destroy();

header('Location: ' . APP_URL . '/login.php');
exit;
