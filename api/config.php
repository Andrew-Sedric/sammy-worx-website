<?php
// TiDB Database Configuration
// ============================
// IMPORTANT: Fill in your TiDB credentials below

define('DB_HOST', 'your_tidb_host'); // e.g., gateway01.us-west-2.prod.aws.tidbcloud.com
define('DB_PORT', 4000);
define('DB_USER', 'your_tidb_user'); // e.g., root or your custom user
define('DB_PASSWORD', 'your_tidb_password');
define('DB_NAME', 'your_database_name');

// SSL Settings for TiDB Cloud
define('USE_SSL', true);
define('SSL_VERIFY', false); // Set to true if you have proper SSL certificates

?>
