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

// Get database credentials
$servername = getenv('DB_HOST');
$dbuser     = getenv('DB_USER');
$dbpass     = getenv('DB_PASSWORD');
$dbname     = getenv('DB_NAME');

// Check if credentials exist
if (!$servername || !$dbuser || !$dbname) {
    // If database not configured, use demo credentials
    $_POST['username'] = isset($_POST['username']) ? trim($_POST['username']) : '';
    $_POST['password'] = isset($_POST['password']) ? trim($_POST['password']) : '';
    
    // Demo login for testing
    if ($_POST['username'] === 'staff' && $_POST['password'] === 'staff123') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['username'] = $_POST['username'];
        header("Location: admin.php");
        exit();
    } else {
        echo "<script>alert('Wrong username or password!'); window.history.back();</script>";
        exit();
    }
}

// Database Connection
$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL); 
$conn->real_connect($servername, $dbuser, $dbpass, $dbname, 3306, NULL, MYSQLI_CLIENT_SSL);

if ($conn->connect_error) {
    echo "<script>alert('Database connection error. Please try again later.'); window.history.back();</script>";
    exit();
}

// Get and validate input
$user = isset($_POST['username']) ? trim($_POST['username']) : '';
$pass = isset($_POST['password']) ? trim($_POST['password']) : '';

if (empty($user) || empty($pass)) {
    echo "<script>alert('Username and password are required!'); window.history.back();</script>";
    exit();
}

// Secure the input
$user = mysqli_real_escape_string($conn, $user);
$pass = mysqli_real_escape_string($conn, $pass);

// Check database for this user
$sql = "SELECT * FROM users WHERE username = '$user' AND password = '$pass' LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    // SUCCESS: Set session
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['username'] = $user;
    
    // Close connection
    $conn->close();
    
    // Redirect to admin dashboard
    header("Location: admin.php");
    exit();
} else {
    // FAILURE: Redirect back with error
    $conn->close();
    echo "<script>alert('Wrong username or password!'); window.history.back();</script>";
    exit();
}
?>