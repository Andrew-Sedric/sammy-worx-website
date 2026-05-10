<?php
require_once 'config.php';

// Connect to TiDB
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
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Create sessions table
$sql = "
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

if ($conn->query($sql) === TRUE) {
    echo "Sessions table created successfully!\n";
} else {
    echo "Error creating sessions table: " . $conn->error . "\n";
}

// Create indexes
$index_sql = "
CREATE INDEX IF NOT EXISTS idx_sessions_user_id ON sessions(user_id);
CREATE INDEX IF NOT EXISTS idx_sessions_last_activity ON sessions(last_activity);
";

if ($conn->multi_query($index_sql) === TRUE) {
    echo "Indexes created successfully!\n";
} else {
    echo "Error creating indexes: " . $conn->error . "\n";
}

$conn->close();
echo "Setup complete! You can now delete this file.\n";
?>