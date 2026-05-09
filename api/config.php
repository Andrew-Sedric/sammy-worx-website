<?php
// TiDB Database Configuration - DIRECT CREDENTIALS
// ==================================================

// Direct database credentials (hardcoded for reliability)
define('DB_HOST', 'gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com');
define('DB_PORT', 4000);
define('DB_USER', '21cRDQ1yQsS5317.root');
define('DB_PASSWORD', 'LZhyUl0yo2bHuMBE');
define('DB_NAME', 'sammyworx_db');

// Vercel environment detection
define('IS_VERCEL', !empty(getenv('VERCEL')));

// SSL Settings for TiDB Cloud
define('USE_SSL', true);

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
