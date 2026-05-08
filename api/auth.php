<?php
ini_set('session.cookie_path', '/');
session_start();

$servername = getenv('DB_HOST');
$username   = getenv('DB_USER');
$password   = getenv('DB_PASSWORD');
$dbname     = getenv('DB_NAME');

// 1. Database Connection
$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL); 
$conn->real_connect($servername, $username, $password, $dbname, 3306, NULL, MYSQLI_CLIENT_SSL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 2. Secure Input
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = mysqli_real_escape_string($conn, $_POST['password']);

    // 3. Check database for this user
    $sql = "SELECT * FROM users WHERE username = '$user' AND password = '$pass' LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // SUCCESS: Set session and save it
        $_SESSION['admin_logged_in'] = true;
        session_write_close(); 
        
        // Redirect to the clean route defined in vercel.json
        header("Location: /admin"); 
        exit(); 
    } else {
        // FAILURE: Show alert and return to login
        echo "<script>alert('Wrong username or password!'); window.location.href='/login';</script>";
        exit();
    }
}
?>