<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.html');
    exit;
}

$email = trim($_POST['email']);
$password = $_POST['password'];

// Use prepared statement
$stmt = $conn->prepare("SELECT id, fullname, password, role, department FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    // Verify password against hash
    if (password_verify($password, $user['password'])) {
        // Password correct – set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['department'] = $user['department'];

        // Redirect based on role
        switch ($user['role']) {
            case 'admin':
                header('Location: ../admin/dashboard.php');
                break;
            case 'teacher':
                header('Location: ../teacher/dashboard.php');
                break;
            case 'student':
                header('Location: ../student/dashboard.php');
                break;
            default:
                header('Location: ../index.php');
        }
        exit;
    }
}

header('Location: ../login.html?error=1');
exit;
?>