<?php
require_once 'config.php';

// Configure session for Vercel
if (IS_VERCEL && !file_exists('/tmp')) {
    mkdir('/tmp', 0777, true);
}

session_start();

$error = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : '';
if (isset($_SESSION['login_error'])) unset($_SESSION['login_error']);

// If already logged in, redirect to admin
if (!empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin.php", true, 302);
    exit();
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sammy Worx Staff Login</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: url('../images/N3.jpeg') no-repeat center center fixed; background-size: cover; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 15px 35px rgba(0,0,0,0.5); width: 320px; text-align: center; }
        h2 { color: #0a192f; margin-bottom: 25px; }
        input { width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; font-size: 14px; }
        button { width: 100%; padding: 12px; background: #007bff; color: white; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; font-size: 14px; }
        button:hover { background: #0056b3; }
        .error-message { color: #d32f2f; background: #ffebee; padding: 12px; border-radius: 5px; margin-bottom: 15px; font-size: 13px; border-left: 4px solid #d32f2f; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Staff Login</h2>
        <?php if ($error): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form action="auth.php" method="POST">
            <input type="text" name="username" placeholder="Username" required autofocus>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>