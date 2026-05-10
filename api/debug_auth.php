<?php
require_once 'config.php';

// Configure session for Vercel
if (IS_VERCEL && !file_exists('/tmp')) {
    mkdir('/tmp', 0777, true);
}

session_start();

// Debug output
echo "<h1>Login Debug</h1>";
echo "<pre>";

// Check if POST request
echo "Request Method: " . $_SERVER["REQUEST_METHOD"] . "\n";

// Get form input
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

echo "Username: '$username'\n";
echo "Password length: " . strlen($password) . "\n";

// Validate input
if (empty($username) || empty($password)) {
    echo "ERROR: Empty username or password\n";
    echo "</pre><p><a href='login.php'>Back to Login</a></p>";
    exit();
}

// Connect to TiDB
echo "Connecting to database...\n";
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

if (!$connected) {
    echo "SSL connection failed, trying without SSL...\n";
    $conn = mysqli_init();
    $connected = @$conn->real_connect(
        DB_HOST,
        DB_USER,
        DB_PASSWORD,
        DB_NAME,
        DB_PORT
    );
}

if (!$connected) {
    echo "ERROR: Database connection failed: " . $conn->connect_error . "\n";
    echo "</pre><p><a href='login.php'>Back to Login</a></p>";
    exit();
}

echo "Database connected successfully!\n";
$conn->set_charset("utf8mb4");

// Escape input
$username_safe = $conn->real_escape_string($username);
$password_safe = $conn->real_escape_string($password);

echo "Escaped username: '$username_safe'\n";
echo "Escaped password: '$password_safe'\n";

// Query database
$sql = "SELECT id, username FROM users WHERE username = '$username_safe' AND password = '$password_safe' LIMIT 1";
echo "Query: $sql\n";

$result = $conn->query($sql);

if (!$result) {
    echo "ERROR: Query failed: " . $conn->error . "\n";
    $conn->close();
    echo "</pre><p><a href='login.php'>Back to Login</a></p>";
    exit();
}

echo "Query executed, rows found: " . $result->num_rows . "\n";

if ($result->num_rows > 0) {
    echo "SUCCESS: User found!\n";
    $user = $result->fetch_assoc();
    echo "User ID: " . $user['id'] . "\n";
    echo "Username: " . $user['username'] . "\n";

    // Set session
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['login_time'] = time();

    echo "Session set, redirecting to admin.php...\n";
    $result->free();
    $conn->close();

    echo "</pre>";
    echo "<p style='color: green;'><b>Login successful! Redirecting...</b></p>";
    echo "<script>setTimeout(function(){ window.location.href = 'admin.php'; }, 2000);</script>";
    exit();
} else {
    echo "FAILURE: No matching user found\n";

    // Check if user exists at all
    $check_sql = "SELECT id, username, password FROM users WHERE username = '$username_safe'";
    $check_result = $conn->query($check_sql);

    if ($check_result && $check_result->num_rows > 0) {
        $db_user = $check_result->fetch_assoc();
        echo "User exists in database:\n";
        echo "  ID: " . $db_user['id'] . "\n";
        echo "  Username: " . $db_user['username'] . "\n";
        echo "  Stored password: " . $db_user['password'] . "\n";
        echo "  Entered password: $password_safe\n";
        echo "  Password match: " . ($db_user['password'] === $password_safe ? "YES" : "NO") . "\n";
    } else {
        echo "User does not exist in database at all\n";
    }

    $result->free();
    $conn->close();
    echo "</pre><p><a href='login.php'>Back to Login</a></p>";
    exit();
}
?>