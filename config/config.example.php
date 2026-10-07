<?php
declare(strict_types=1);

/*
 * Copy this file to config/config.php and fill in your own values.
 * config/config.php is git-ignored and must NEVER be committed.
 */

define('APP_NAME', 'PayProof');
define('APP_URL', 'http://localhost/payproof/public');

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'payproof_db');
define('DB_USER', 'payproof_user');   // use a dedicated MySQL user, not root
define('DB_PASS', 'change_me');

// Used inside the receipt hash. Generate one with:
//   php -r "echo bin2hex(random_bytes(32));"
// If this changes, every existing receipt hash becomes invalid.
define('APP_SECRET_KEY', 'REPLACE_WITH_A_LONG_RANDOM_SECRET');

// Session security
define('SESSION_NAME', 'payproof_session');
