<?php
ini_set('session.cookie_path', '/');
session_start();

// Final Fix: Use empty() to check session status for better compatibility
if (empty($_SESSION['admin_logged_in'])) {
    header("Location: /login");
    exit();
}

$servername = getenv('DB_HOST');
$username   = getenv('DB_USER');
$password   = getenv('DB_PASSWORD');
$dbname     = getenv('DB_NAME');

// Database Connection
$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL); 
$conn->real_connect($servername, $username, $password, $dbname, 3306, NULL, MYSQLI_CLIENT_SSL);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM contact_inquiries ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inquiry Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; background: #f4f7f6; color: #333; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
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
        }
        .logout:hover { background: #c82333; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Client Inquiries</h1>
        <a href="/api/logout" class="logout">Logout</a>
    </div>
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
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['service']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($row['message'])); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center;">No inquiries found yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>