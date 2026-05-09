<?php
// Set session config BEFORE starting session
ini_set('session.cookie_path', '/');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', '0'); // Set to 1 if using HTTPS
session_start();

// Check if user is logged in
if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Load database configuration
require_once 'config.php';

$inquiries = [];
$dbError = null;

// Check for incomplete configuration
if (strpos(DB_HOST, 'your_') !== false || strpos(DB_USER, 'your_') !== false || strpos(DB_NAME, 'your_') !== false) {
    $dbError = "Database is not configured. Please update api/config.php with your TiDB credentials.";
} else {
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
        $conn->set_charset("utf8mb4");
        
        $sql = "SELECT * FROM contact_inquiries ORDER BY created_at DESC";
        $result = $conn->query($sql);

        if ($result) {
            $inquiries = $result->fetch_all(MYSQLI_ASSOC);
            $result->free();
        } else {
            $dbError = "Failed to load inquiries from the database: " . $conn->error;
        }

        $conn->close();
    } else {
        $dbError = "Database connection failed. Please check your TiDB configuration in api/config.php. Error: " . $conn->connect_error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inquiry Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; background: #f4f7f6; color: #333; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .error-message { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb; }
        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 5px 15px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; }
        th, td { padding: 15px; border: 1px solid #eee; text-align: left; }
        th { background: #007bff; color: white; }
        tr:hover { background: #f9f9f9; }
        .logout { 
            color: #fff; 
            background: #dc3545; 
            padding: 8px 15px; 
            text-decoration: none; 
            border-radius: 4px; 
            font-weight: bold; 
            cursor: pointer;
            border: none;
        }
        .logout:hover { background: #c82333; }
        .empty-state { text-align: center; padding: 40px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Client Inquiries</h1>
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <?php if ($dbError): ?>
        <div class="error-message">
            <strong>Error:</strong> <?php echo htmlspecialchars($dbError); ?>
        </div>
    <?php elseif (count($inquiries) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Service</th>
                    <th>Message</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inquiries as $row): ?>
                    <tr>
                        <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['service']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($row['message'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty-state">
            <p>No inquiries found yet.</p>
        </div>
    <?php endif; ?>
</body>
</html>