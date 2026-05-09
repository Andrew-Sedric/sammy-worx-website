<?php
require_once 'config.php';

// Configure session for Vercel
if (IS_VERCEL && !file_exists('/tmp')) {
    mkdir('/tmp', 0777, true);
}

// Start session
session_start();

// Verify POST request
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php", true, 302);
    exit();
}

// Get form input
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

// Validate input
if (empty($username) || empty($password)) {
    $_SESSION['login_error'] = 'Username and password are required';
    header("Location: login.php", true, 302);
    exit();
}

// Connect to TiDB
mysqli_report(MYSQLI_REPORT_OFF);
$conn = mysqli_init();

// Set connection options
if (USE_SSL) {
    mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
}

$connected = @$conn->real_connect(
    DB_HOST,
    DB_USER,
    DB_PASSWORD,
    DB_NAME,
    DB_PORT,
    NULL,
    USE_SSL ? MYSQLI_CLIENT_SSL : 0
);

if (!$connected) {
    // Try without SSL as fallback
    $conn = mysqli_init();
    $connected = @$conn->real_connect(
        DB_HOST,
        DB_USER,
        DB_PASSWORD,
        DB_NAME,
        DB_PORT
    );
    
    if (!$connected) {
        $_SESSION['login_error'] = 'Database connection failed';
        header("Location: login.php", true, 302);
        exit();
    }
}

$conn->set_charset("utf8mb4");

// Escape input
$username = $conn->real_escape_string($username);
$password = $conn->real_escape_string($password);

// Query database
$sql = "SELECT id, username FROM users WHERE username = '$username' AND password = '$password' LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    // Login successful
    $user = $result->fetch_assoc();
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['login_time'] = time();
    
    $conn->close();
    header("Location: admin.php", true, 302);
    exit();
} else {
    // Login failed
    $_SESSION['login_error'] = 'Wrong username or password';
    $conn->close();
    header("Location: login.php", true, 302);
    exit();
}
?>