<?php
// Set session config BEFORE starting session
ini_set('session.cookie_path', '/');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', '0'); // Set to 1 if using HTTPS
session_start();

// Only process POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit();
}

// Load database configuration
require_once 'config.php';

// Get and validate input
$user = isset($_POST['username']) ? trim($_POST['username']) : '';
$pass = isset($_POST['password']) ? trim($_POST['password']) : '';

if (empty($user) || empty($pass)) {
    echo "<script>alert('Username and password are required!'); window.history.back();</script>";
    exit();
}

// Check for incomplete configuration
if (strpos(DB_HOST, 'your_') !== false || strpos(DB_USER, 'your_') !== false || strpos(DB_NAME, 'your_') !== false) {
    echo "<script>alert('Database is not configured. Please update api/config.php with your TiDB credentials.'); window.history.back();</script>";
    exit();
}

// Connect to TiDB with SSL
mysqli_report(MYSQLI_REPORT_OFF);
$conn = mysqli_init();

if (USE_SSL) {
    mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
}

$connection = @$conn->real_connect(
    DB_HOST, 
    DB_USER, 
    DB_PASSWORD, 
    DB_NAME, 
    DB_PORT, 
    NULL, 
    USE_SSL ? MYSQLI_CLIENT_SSL : 0
);

if (!$connection) {
    $error = $conn->connect_error;
    echo "<script>alert('Database connection failed.\\n\\nError: " . addslashes($error) . "\\n\\nPlease check your TiDB configuration in api/config.php'); window.history.back();</script>";
    exit();
}

// Set charset to UTF8
$conn->set_charset("utf8mb4");

// Sanitize input
$user = $conn->real_escape_string($user);
$pass = $conn->real_escape_string($pass);

// Debug: Check if user exists at all (check password field type)
$debug_sql = "SELECT id, username, password FROM users WHERE username = '$user' LIMIT 1";
$debug_result = $conn->query($debug_sql);

// Check database for this user with password hashing support
// Try both plain text and hashed passwords for compatibility
$sql = "SELECT id, username FROM users WHERE username = '$user' AND (password = '$pass' OR password = MD5('$pass')) LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $userData = $result->fetch_assoc();
    
    // SUCCESS: Set session
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['username'] = $userData['username'];
    $_SESSION['user_id'] = $userData['id'];
    $_SESSION['login_time'] = time();
    
    // Close connection
    $conn->close();
    
    // Redirect to admin dashboard
    header("Location: admin.php");
    exit();
} else {
    // FAILURE: Provide more detailed error
    $error_msg = "Wrong username or password!";
    
    // Check if user exists but password is wrong
    if ($debug_result && $debug_result->num_rows > 0) {
        $debug_row = $debug_result->fetch_assoc();
        $error_msg = "User found but password does not match.\\n\\nStored password format: " . substr($debug_row['password'], 0, 10) . "...";
    } else {
        $error_msg = "Username not found in database.";
    }
    
    $conn->close();
    echo "<script>alert('" . addslashes($error_msg) . "'); window.history.back();</script>";
    exit();
}
?>