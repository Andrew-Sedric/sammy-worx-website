<?php
require_once 'config.php';

// Configure session for Vercel
if (IS_VERCEL && !file_exists('/tmp')) {
    mkdir('/tmp', 0777, true);
}

session_start();

// Check if logged in
if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php", true, 302);
    exit();
}

$inquiries = [];
$error = null;

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

if ($connected) {
    $conn->set_charset("utf8mb4");
    $result = $conn->query("SELECT * FROM contact_inquiries ORDER BY created_at DESC LIMIT 100");
    
    if ($result) {
        $inquiries = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
    } else {
        $error = "Failed to load inquiries: " . $conn->error;
    }
    $conn->close();
} else {
    $error = "Database connection failed";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Inquiries</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .user-info { color: #666; font-size: 14px; margin-top: 5px; }
        .username { color: #007bff; font-weight: bold; }
        .logout-btn { background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; font-weight: bold; cursor: pointer; border: none; }
        .logout-btn:hover { background: #c82333; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px; border-left: 4px solid #f5c6cb; }
        table { width: 100%; background: white; border-collapse: collapse; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; }
        th { background: #007bff; color: white; padding: 15px; text-align: left; font-weight: bold; }
        td { padding: 12px 15px; border-bottom: 1px solid #eee; }
        tr:hover { background: #f9f9f9; }
        .empty { text-align: center; padding: 40px; color: #999; background: white; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>Client Inquiries</h1>
                <div class="user-info">Welcome, <span class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></span></div>
            </div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>

        <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
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
                    <td><?php echo isset($row['created_at']) ? date('M d, Y H:i', strtotime($row['created_at'])) : 'N/A'; ?></td>
                    <td><?php echo htmlspecialchars($row['name'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['email'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['service'] ?? ''); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($row['message'] ?? '')); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty">
            <p>No inquiries found yet.</p>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>