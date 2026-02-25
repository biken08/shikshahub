<?php
session_start();
if (!isset($_SESSION['user_id'])) header("Location: login.html");

$is_admin = ($_SESSION['role'] ?? '') == 'admin';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Settings</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Settings</h1>
        
        <div class="card">
            <h2>Change Password</h2>
            <form action="change_password.php" method="POST">
                <input type="password" name="current" placeholder="Current Password" required>
                <input type="password" name="new" placeholder="New Password (min 6 chars)" required minlength="6">
                <button type="submit">Update Password</button>
            </form>
        </div>
        
       
</body>
</html>