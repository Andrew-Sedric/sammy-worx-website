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
    // Use database-backed sessions for Vercel serverless compatibility
    require_once 'DatabaseSessionHandler.php';

    // Connect to database for session handler
    mysqli_report(MYSQLI_REPORT_OFF);
    $session_conn = mysqli_init();

    if (USE_SSL) {
        mysqli_ssl_set($session_conn, NULL, NULL, NULL, NULL, NULL);
    }

    $connected = @$session_conn->real_connect(
        DB_HOST,
        DB_USER,
        DB_PASSWORD,
        DB_NAME,
        DB_PORT,
        NULL,
        USE_SSL ? MYSQLI_CLIENT_SSL : 0
    );

    if (!$connected) {
        $session_conn = mysqli_init();
        $connected = @$session_conn->real_connect(
            DB_HOST,
            DB_USER,
            DB_PASSWORD,
            DB_NAME,
            DB_PORT
        );
    }

    if ($connected) {
        $session_conn->set_charset("utf8mb4");
        $sessionHandler = new DatabaseSessionHandler($session_conn);
        session_set_save_handler($sessionHandler, true);
    } else {
        // Fallback to /tmp if database connection fails
        ini_set('session.save_handler', 'files');
        ini_set('session.save_path', '/tmp');
    }
} else {
    // Local development - use file-based sessions
    ini_set('session.save_handler', 'files');
    ini_set('session.save_path', '/tmp');
}

ini_set('session.cookie_lifetime', '86400');
ini_set('session.cookie_path', '/');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');

?>
