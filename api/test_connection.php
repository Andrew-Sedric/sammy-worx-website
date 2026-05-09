<?php
// TiDB Connection Diagnostic Test
// ================================

require_once 'config.php';

echo "<h1>TiDB Connection Diagnostic Test</h1>";
echo "<hr>";

// Test 1: Configuration Check
echo "<h2>1. Configuration Check</h2>";
echo "DB_HOST: " . DB_HOST . "<br>";
echo "DB_PORT: " . DB_PORT . "<br>";
echo "DB_USER: " . DB_USER . "<br>";
echo "DB_NAME: " . DB_NAME . "<br>";
echo "USE_SSL: " . (USE_SSL ? "Yes" : "No") . "<br>";
echo "<hr>";

// Test 2: Connection Test
echo "<h2>2. Database Connection Test</h2>";

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

if ($connection) {
    echo "<p style='color: green;'><b>✓ Connection Successful!</b></p>";
    
    // Test 3: Check Users Table
    echo "<h2>3. Users Table Check</h2>";
    $sql = "SELECT id, username, password FROM users";
    $result = $conn->query($sql);
    
    if ($result) {
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>ID</th><th>Username</th><th>Password (First 20 chars)</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
            echo "<td>" . htmlspecialchars(substr($row['password'], 0, 20)) . "...</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<p><b>Users found in database: " . $result->num_rows . "</b></p>";
    } else {
        echo "<p style='color: red;'><b>✗ Error querying users table: " . $conn->error . "</b></p>";
    }
    
    // Test 4: Test Login Query
    echo "<h2>4. Test Login Query</h2>";
    $test_user = "staff";
    $test_pass = "staff123";
    echo "Testing login with: Username=<b>" . $test_user . "</b>, Password=<b>" . $test_pass . "</b><br><br>";
    
    $test_user_escaped = $conn->real_escape_string($test_user);
    $test_pass_escaped = $conn->real_escape_string($test_pass);
    
    $login_sql = "SELECT id, username FROM users WHERE username = '$test_user_escaped' AND (password = '$test_pass_escaped' OR password = MD5('$test_pass_escaped')) LIMIT 1";
    echo "Query: <code>" . htmlspecialchars($login_sql) . "</code><br><br>";
    
    $login_result = $conn->query($login_sql);
    
    if ($login_result && $login_result->num_rows > 0) {
        echo "<p style='color: green;'><b>✓ Login Query Successful!</b></p>";
        $user = $login_result->fetch_assoc();
        echo "User ID: " . $user['id'] . "<br>";
        echo "Username: " . $user['username'] . "<br>";
    } else {
        echo "<p style='color: red;'><b>✗ Login Query Failed</b></p>";
        
        // Try to see what's in the database
        $check_user_sql = "SELECT * FROM users WHERE username = '$test_user_escaped'";
        $check_user_result = $conn->query($check_user_sql);
        
        if ($check_user_result && $check_user_result->num_rows > 0) {
            $stored = $check_user_result->fetch_assoc();
            echo "<p style='color: orange;'><b>User exists but password doesn't match!</b></p>";
            echo "Stored Password: " . htmlspecialchars($stored['password']) . "<br>";
            echo "Entered Password: " . $test_pass_escaped . "<br>";
            echo "MD5 Hash: " . md5($test_pass_escaped) . "<br>";
        } else {
            echo "<p style='color: red;'><b>User not found in database</b></p>";
        }
    }
    
    $conn->close();
} else {
    echo "<p style='color: red;'><b>✗ Connection Failed!</b></p>";
    echo "Error: " . $conn->connect_error . "<br>";
    echo "<p>Please check:</p>";
    echo "<ul>";
    echo "<li>DB_HOST is correct</li>";
    echo "<li>DB_USER and DB_PASSWORD are correct</li>";
    echo "<li>DB_NAME exists in TiDB</li>";
    echo "<li>TiDB cluster is running and accessible</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p><a href='login.php'>Back to Login</a></p>";
?>
