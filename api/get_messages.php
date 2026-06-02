<?php
// api/get_messages.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allows your desktop app to connect

// Include your existing configuration file that has the DB credentials
require_once 'config.php';

try {
    // Connect to TiDB using your existing settings
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, $options);
    
    // Fetch all inquiries
    $stmt = $pdo->query("SELECT id, name, email, phone, message, created_at FROM contact_inquiries ORDER BY id DESC");
    $messages = $stmt->fetchAll();
    
    // Output them as clean JSON text
    echo json_encode($messages);

} catch (PDOException $e) {
    echo json_encode(["error" => "Database connection failed: " . $e->getMessage()]);
}
?>