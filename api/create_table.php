<?php
// Simple sessions table creation script
$host = 'gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com';
$user = '21cRDQ1yQsS5317.root';
$pass = 'LZhyUl0yo2bHuMBE';
$db = 'sammyworx_db';
$port = 4000;

mysqli_report(MYSQLI_REPORT_OFF);
$conn = mysqli_init();

if ($conn) {
    mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
    $connected = @$conn->real_connect($host, $user, $pass, $db, $port, NULL, MYSQLI_CLIENT_SSL);

    if (!$connected) {
        $conn = mysqli_init();
        $connected = @$conn->real_connect($host, $user, $pass, $db, $port);
    }

    if ($connected) {
        $conn->set_charset("utf8mb4");

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
            echo "Sessions table created successfully!\n";

            $index_sql = "
            CREATE INDEX IF NOT EXISTS idx_sessions_user_id ON sessions(user_id);
            CREATE INDEX IF NOT EXISTS idx_sessions_last_activity ON sessions(last_activity);
            ";

            if ($conn->multi_query($index_sql) === TRUE) {
                echo "Indexes created successfully!\n";
            } else {
                echo "Error creating indexes: " . $conn->error . "\n";
            }
        } else {
            echo "Error creating sessions table: " . $conn->error . "\n";
        }

        $conn->close();
    } else {
        echo "Database connection failed\n";
    }
} else {
    echo "Failed to initialize connection\n";
}
?>