<?php
// Direct TiDB Connection Test
echo "<h1>TiDB Connection Test</h1>";
echo "<hr>";

// Hardcoded credentials
$host = 'gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com';
$user = '21cRDQ1yQsS5317.root';
$pass = 'LZhyUl0yo2bHuMBE';
$db = 'sammyworx_db';
$port = 4000;

echo "<h2>1. Connection Test</h2>";
echo "Host: $host<br>";
echo "User: $user<br>";
echo "Database: $db<br>";
echo "Port: $port<br><br>";

// Try connection
mysqli_report(MYSQLI_REPORT_OFF);
$conn = mysqli_init();

if ($conn) {
    mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
    $connected = @$conn->real_connect($host, $user, $pass, $db, $port, NULL, MYSQLI_CLIENT_SSL);

    if (!$connected) {
        echo "<p style='color: red;'>SSL connection failed, trying without SSL...</p>";
        $conn = mysqli_init();
        $connected = @$conn->real_connect($host, $user, $pass, $db, $port);
    }

    if ($connected) {
        echo "<p style='color: green;'><b>✓ Database Connected Successfully!</b></p>";
        $conn->set_charset("utf8mb4");

        echo "<h2>2. Users Table Check</h2>";
        $result = $conn->query("SELECT id, username, password FROM users");

        if ($result) {
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>ID</th><th>Username</th><th>Password</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                echo "<td>" . htmlspecialchars($row['password']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";

            echo "<h2>3. Create Sessions Table</h2>";
            $create_sql = "
            CREATE TABLE IF NOT EXISTS sessions (
                session_id VARCHAR(255) PRIMARY KEY,
                user_id INT NOT NULL,
                username VARCHAR(255) NOT NULL,
                login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                ip_address VARCHAR(45),
                user_agent TEXT,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            );
            ";

            if ($conn->query($create_sql) === TRUE) {
                echo "<p style='color: green;'><b>✓ Sessions table created successfully!</b></p>";

                // Create indexes
                $index_sql = "
                CREATE INDEX IF NOT EXISTS idx_sessions_user_id ON sessions(user_id);
                CREATE INDEX IF NOT EXISTS idx_sessions_last_activity ON sessions(last_activity);
                ";

                if ($conn->multi_query($index_sql) === TRUE) {
                    echo "<p style='color: green;'><b>✓ Indexes created successfully!</b></p>";
                } else {
                    echo "<p style='color: red;'>✗ Error creating indexes: " . $conn->error . "</p>";
                }
            } else {
                echo "<p style='color: red;'>✗ Error creating sessions table: " . $conn->error . "</p>";
            }

            echo "<h2>4. Test Login Query</h2>";
            $test_username = 'sammyworx';
            $test_password = '0844sammy';

            $stmt = $conn->prepare("SELECT id, username FROM users WHERE username = ? AND password = ? LIMIT 1");
            $stmt->bind_param("ss", $test_username, $test_password);
            $stmt->execute();
            $login_result = $stmt->get_result();

            if ($login_result->num_rows > 0) {
                $user = $login_result->fetch_assoc();
                echo "<p style='color: green;'><b>✓ Login test successful!</b></p>";
                echo "User ID: " . $user['id'] . "<br>";
                echo "Username: " . $user['username'] . "<br>";
            } else {
                echo "<p style='color: red;'><b>✗ Login test failed - no matching user found</b></p>";
                echo "Tested with: username='$test_username', password='$test_password'<br>";
            }
            $stmt->close();
            echo "<p><b>Total users: " . $result->num_rows . "</b></p>";
        } else {
            echo "<p style='color: red;'>Failed to query users table: " . $conn->error . "</p>";
        }

        echo "<h2>3. Test Login Queries</h2>";

        // Test sammyworx login
        $test_user = 'sammyworx';
        $test_pass = '0844sammy';
        $test_user_safe = $conn->real_escape_string($test_user);
        $test_pass_safe = $conn->real_escape_string($test_pass);

        $sql = "SELECT id, username FROM users WHERE username = '$test_user_safe' AND password = '$test_pass_safe' LIMIT 1";
        $result = $conn->query($sql);

        echo "<p><b>Testing: Username='$test_user', Password='$test_pass'</b></p>";
        echo "<p>Query: <code>$sql</code></p>";

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            echo "<p style='color: green;'><b>✓ Login should work! User found: " . $user['username'] . "</b></p>";
        } else {
            echo "<p style='color: red;'><b>✗ Login will fail - user not found or password mismatch</b></p>";
        }

        // Test staff login
        $test_user2 = 'staff';
        $test_pass2 = 'staff123';
        $test_user_safe2 = $conn->real_escape_string($test_user2);
        $test_pass_safe2 = $conn->real_escape_string($test_pass2);

        $sql2 = "SELECT id, username FROM users WHERE username = '$test_user_safe2' AND password = '$test_pass_safe2' LIMIT 1";
        $result2 = $conn->query($sql2);

        echo "<p><b>Testing: Username='$test_user2', Password='$test_pass2'</b></p>";
        echo "<p>Query: <code>$sql2</code></p>";

        if ($result2 && $result2->num_rows > 0) {
            $user2 = $result2->fetch_assoc();
            echo "<p style='color: green;'><b>✓ Login should work! User found: " . $user2['username'] . "</b></p>";
        } else {
            echo "<p style='color: red;'><b>✗ Login will fail - user not found or password mismatch</b></p>";
        }

        $conn->close();
    } else {
        echo "<p style='color: red;'><b>✗ Database Connection Failed!</b></p>";
        echo "<p>Error: " . $conn->connect_error . "</p>";
    }
} else {
    echo "<p style='color: red;'><b>✗ mysqli_init() failed</b></p>";
}

echo "<hr>";
echo "<p><a href='login.php'>Back to Login</a></p>";
?>