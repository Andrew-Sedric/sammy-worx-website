<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$servername = getenv('DB_HOST');
$username   = getenv('DB_USER');
$password   = getenv('DB_PASSWORD');
$dbname     = getenv('DB_NAME');

$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL); 
$conn->real_connect($servername, $username, $password, $dbname, 3306, NULL, MYSQLI_CLIENT_SSL);

$sql = "SELECT * FROM contact_inquiries ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Inquiry Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; background: #f4f7f6; }
        .header { display: flex; justify-content: space-between; align-items: center; }
        table { width: 100%; border-collapse: collapse; background: white; margin-top: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        th, td { padding: 15px; border: 1px solid #eee; text-align: left; }
        th { background: #007bff; color: white; }
        tr:hover { background: #f9f9f9; }
        .logout { color: red; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Client Inquiries</h1>
        <a href="api/logout.php" class="logout">Logout</a>
    </div>
    <table>
        <tr>
            <th>Date</th>
            <th>Name</th>
            <th>Email</th>
            <th>Service</th>
            <th>Message</th>
        </tr>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['created_at']; ?></td>
            <td><?php echo $row['name']; ?></td>
            <td><?php echo $row['email']; ?></td>
            <td><?php echo $row['service']; ?></td>
            <td><?php echo $row['message']; ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>