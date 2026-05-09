<?php
// Set session config BEFORE starting session
ini_set('session.cookie_path', '/');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', '0');
session_start();

// Debug logging
$log_file = __DIR__ . '/auth_debug.log';

function log_debug($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}

log_debug("=== LOGIN ATTEMPT ===");

// Only process POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    log_debug("Not a POST request, redirecting to login");
    header("Location: login.php");
    exit();
}

// Load database configuration
require_once 'config.php';
log_debug("Config loaded. Host: " . DB_HOST . ", User: " . DB_USER . ", DB: " . DB_NAME);

// Get and validate input
$user = isset($_POST['username']) ? trim($_POST['username']) : '';
$pass = isset($_POST['password']) ? trim($_POST['password']) : '';

log_debug("Username: $user, Password length: " . strlen($pass));

if (empty($user) || empty($pass)) {
    log_debug("Empty username or password");
    echo "<script>alert('Username and password are required!'); window.history.back();</script>";
    exit();
}

// Check for incomplete configuration
if (strpos(DB_HOST, 'your_') !== false) {
    log_debug("Configuration incomplete - placeholder values found");
    echo "<script>alert('Database is not configured properly.'); window.history.back();</script>";
    exit();
}

// Connect to TiDB WITHOUT SSL first (to test basic connectivity)
log_debug("Attempting database connection to " . DB_HOST . ":" . DB_PORT);
mysqli_report(MYSQLI_REPORT_OFF);
$conn = mysqli_init();

// Try without SSL first
$connection = @$conn->real_connect(
    DB_HOST, 
    DB_USER, 
    DB_PASSWORD, 
    DB_NAME, 
    DB_PORT
);

if (!$connection) {
    log_debug("Connection failed without SSL. Error: " . $conn->connect_error . ". Trying with SSL...");
    
    // Try with SSL
    $conn = mysqli_init();
    mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
    $connection = @$conn->real_connect(
        DB_HOST, 
        DB_USER, 
        DB_PASSWORD, 
        DB_NAME, 
        DB_PORT, 
        NULL, 
        MYSQLI_CLIENT_SSL
    );
    
    if (!$connection) {
        $error = $conn->connect_error;
        log_debug("Connection also failed with SSL. Error: " . $error);
        echo "<script>alert('Database connection failed: " . addslashes($error) . "'); window.history.back();</script>";
        exit();
    } else {
        log_debug("Connection successful WITH SSL");
    }
} else {
    log_debug("Connection successful WITHOUT SSL");
}

// Set charset to UTF8
$conn->set_charset("utf8mb4");
log_debug("Charset set to utf8mb4");

// Sanitize input
$user = $conn->real_escape_string($user);
$pass = $conn->real_escape_string($pass);
log_debug("Input sanitized");

// Check database for this user - try multiple password formats
$sql = "SELECT id, username FROM users WHERE username = '$user' AND password = '$pass' LIMIT 1";
log_debug("Executing query: $sql");
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    log_debug("Login successful! User found with matching password.");
    $userData = $result->fetch_assoc();
    
    // SUCCESS: Set session
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['username'] = $userData['username'];
    $_SESSION['user_id'] = $userData['id'];
    $_SESSION['login_time'] = time();
    
    log_debug("Session variables set. Redirecting to admin.php");
    
    // Close connection
    $conn->close();
    
    // Redirect to admin dashboard
    header("Location: admin.php");
    exit();
} else {
    log_debug("Login failed - user not found or password doesn't match");
    
    // Check what's actually in the database
    $check_sql = "SELECT id, username, password FROM users WHERE username = '$user' LIMIT 1";
    $check_result = $conn->query($check_sql);
    
    if ($check_result && $check_result->num_rows > 0) {
        $db_user = $check_result->fetch_assoc();
        log_debug("User exists! ID: " . $db_user['id']);
        log_debug("Stored password: " . substr($db_user['password'], 0, 20) . "...");
        log_debug("Entered password: " . $pass);
        log_debug("Password match: " . ($db_user['password'] === $pass ? "YES" : "NO"));
    } else {
        log_debug("User not found in database at all");
    }
    
    $conn->close();
    echo "<script>alert('Wrong username or password!'); window.history.back();</script>";
    exit();
}
?>