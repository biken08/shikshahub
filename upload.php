<?php
session_start();
require_once 'backend/auth.php'; // ensures logged in

$role = $_SESSION['role'] ?? '';

if ($role === 'teacher') {
    header('Location: /shikshahub/teacher/upload.php');
    exit;
} elseif ($role === 'admin') {
    // If you have an admin upload page, redirect there; otherwise allow teacher upload or show a message
    // For now, we'll also allow admin to use the teacher upload page
    header('Location: /shikshahub/teacher/upload.php');
    exit;
} else {
    // Students or others are not allowed
    header('Location: /shikshahub/index.php');
    exit;
}
?>