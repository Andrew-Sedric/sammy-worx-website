<?php
// Set session config BEFORE starting session
ini_set('session.cookie_path', '/');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', '0');
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
    $_SESSION['login_error'] = 'Username and password are required!';
    header("Location: login.php");
    exit();
}

// Check for incomplete configuration
if (strpos(DB_HOST, 'your_') !== false) {
    $_SESSION['login_error'] = 'Database is not configured properly.';
    header("Location: login.php");
    exit();
}

// Connect to TiDB - try without SSL first
mysqli_report(MYSQLI_REPORT_OFF);
$conn = mysqli_init();

$connection = @$conn->real_connect(
    DB_HOST, 
    DB_USER, 
    DB_PASSWORD, 
    DB_NAME, 
    DB_PORT
);

if (!$connection) {
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
        $_SESSION['login_error'] = 'Database connection failed: ' . $conn->connect_error;
        header("Location: login.php");
        exit();
    }
}

// Set charset to UTF8
$conn->set_charset("utf8mb4");

// Sanitize input
$user = $conn->real_escape_string($user);
$pass = $conn->real_escape_string($pass);

// Check database for this user
$sql = "SELECT id, username FROM users WHERE username = '$user' AND password = '$pass' LIMIT 1";
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
    // FAILURE
    $_SESSION['login_error'] = 'Wrong username or password!';
    $conn->close();
    header("Location: login.php");
    exit();
}
?>