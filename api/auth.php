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
    header("Location: /api/login.php", true, 302);
    exit();
}

// Get form input
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

// Validate input
if (empty($username) || empty($password)) {
    $_SESSION['login_error'] = 'Username and password are required';
    header("Location: /api/login.php", true, 302);
    exit();
}

// Connect to TiDB - Attempt with SSL first
mysqli_report(MYSQLI_REPORT_OFF);
$conn = mysqli_init();

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

// If SSL connection failed, try without SSL
if (!$connected) {
    $conn = mysqli_init();
    $connected = @$conn->real_connect(
        DB_HOST,
        DB_USER,
        DB_PASSWORD,
        DB_NAME,
        DB_PORT
    );
}

// If still not connected, show error
if (!$connected) {
    $_SESSION['login_error'] = 'Database connection failed: ' . $conn->connect_error;
    header("Location: /api/login.php", true, 302);
    exit();
}

// Set charset
$conn->set_charset("utf8mb4");

// Escape input for security
$username_safe = $conn->real_escape_string($username);
$password_safe = $conn->real_escape_string($password);

// Query database for matching user
$sql = "SELECT id, username FROM users WHERE username = '$username_safe' AND password = '$password_safe' LIMIT 1";
$result = $conn->query($sql);

// Handle query error
if (!$result) {
    $_SESSION['login_error'] = 'Database error: ' . $conn->error;
    $conn->close();
    header("Location: /api/login.php", true, 302);
    exit();
}

// Check if user found
if ($result->num_rows > 0) {
    // LOGIN SUCCESS
    $user = $result->fetch_assoc();

    $_SESSION['admin_logged_in'] = true;
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['login_time'] = time();

    // Regenerate session ID for security
    session_regenerate_id(true);

    $result->free();
    $conn->close();

    // Ensure session is written before redirect
    session_write_close();

    header("Location: /api/admin.php", true, 302);
    exit();
} else {
    // LOGIN FAILED
    $_SESSION['login_error'] = 'Wrong username or password';
    $result->free();
    $conn->close();
    header("Location: /api/login.php", true, 302);
    exit();
}
?>