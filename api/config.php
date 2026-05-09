<?php
// TiDB Database Configuration for Vercel
// ========================================
// Uses environment variables when available, falls back to direct config

$db_host = getenv('DB_HOST') ?: 'gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com';
$db_user = getenv('DB_USER') ?: '21cRDQ1yQsS5317.root';
$db_pass = getenv('DB_PASSWORD') ?: 'LZhyUl0yo2bHuMBE';
$db_name = getenv('DB_NAME') ?: 'sammyworx_db';

define('DB_HOST', $db_host);
define('DB_PORT', 4000);
define('DB_USER', $db_user);
define('DB_PASSWORD', $db_pass);
define('DB_NAME', $db_name);

// Vercel environment detection
define('IS_VERCEL', !empty(getenv('VERCEL')));

// SSL Settings for TiDB Cloud
define('USE_SSL', true);
define('SSL_VERIFY', false);

// Session configuration for Vercel
if (IS_VERCEL) {
    ini_set('session.save_handler', 'files');
    ini_set('session.save_path', '/tmp');
    ini_set('session.gc_maxlifetime', '86400');
}

ini_set('session.cookie_lifetime', '86400');
ini_set('session.cookie_path', '/');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');

?>
