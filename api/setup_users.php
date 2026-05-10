<?php
// Setup script to create users table and insert default admin user
require_once 'config.php';

// Connect to database
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

// Create users table if not exists
$create_table_sql = "
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
";

if ($conn->query($create_table_sql) === TRUE) {
    echo "Users table created or already exists.<br>";
} else {
    die("Error creating table: " . $conn->error);
}

// Insert default admin user if not exists
$username = 'admin';
$password = 'password123'; // Change this to a secure password

$check_sql = "SELECT id FROM users WHERE username = '$username'";
$result = $conn->query($check_sql);

if ($result->num_rows == 0) {
    $insert_sql = "INSERT INTO users (username, password) VALUES ('$username', '$password')";
    if ($conn->query($insert_sql) === TRUE) {
        echo "Default admin user inserted.<br>";
        echo "Username: $username<br>";
        echo "Password: $password<br>";
        echo "<strong>CHANGE THE PASSWORD AFTER FIRST LOGIN!</strong><br>";
    } else {
        echo "Error inserting user: " . $conn->error . "<br>";
    }
} else {
    echo "Admin user already exists.<br>";
}

$conn->close();
echo "Setup complete.";
?></content>
<parameter name="filePath">c:\Users\SAN i7 core\Documents\GitHub\sammy-worx-website\api\setup_users.php