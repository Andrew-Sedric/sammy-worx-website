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

$servername = getenv('DB_HOST');
$username   = getenv('DB_USER');
$password   = getenv('DB_PASSWORD');
$dbname     = getenv('DB_NAME');

$dbAvailable = !empty($servername) && !empty($username) && !empty($dbname);
$inquiries = [];
$dbError = null;

if ($dbAvailable) {
    mysqli_report(MYSQLI_REPORT_OFF);
    $conn = mysqli_init();
    mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);

    if ($conn->real_connect($servername, $username, $password, $dbname, 3306, NULL, MYSQLI_CLIENT_SSL)) {
        $sql = "SELECT * FROM contact_inquiries ORDER BY created_at DESC";
        $result = $conn->query($sql);

        if ($result) {
            $inquiries = $result->fetch_all(MYSQLI_ASSOC);
            $result->free();
        } else {
            $dbError = "Failed to load inquiries from the database.";
        }

        $conn->close();
    } else {
        $dbError = "Database connection failed: " . $conn->connect_error;
    }
} else {
    $dbError = "Database is not configured locally. Please configure DB credentials or use demo mode.";
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
        <a href="logout.php" class="logout">Logout</a>
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