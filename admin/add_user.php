<?php
// admin/add_user.php
session_start();
require_once '../backend/auth.php'; // ensures user is logged in
require_once '../backend/db.php';

// Only admin allowed
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../login.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize inputs
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password']; // raw password, will hash
    $role = $_POST['role'];
    $department = ($role !== 'admin') ? trim($_POST['department']) : null;

    // Basic validation
    $errors = [];
    if (empty($fullname)) $errors[] = "Full name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email.";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
    if (!in_array($role, ['student', 'teacher', 'admin'])) $errors[] = "Invalid role.";
    if (($role === 'student' || $role === 'teacher') && empty($department)) {
        $errors[] = "Department is required for students and teachers.";
    }

    if (!empty($errors)) {
        // Show errors and redirect back
        $error_string = implode("\\n", $errors);
        echo "<script>alert('$error_string'); window.location.href='users.php';</script>";
        exit;
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo "<script>alert('Email already registered!'); window.location.href='users.php';</script>";
        exit;
    }
    $stmt->close();

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (fullname, email, password, role, department) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $fullname, $email, $hashed_password, $role, $department);

    if ($stmt->execute()) {
        header("Location: users.php?added=1");
        exit;
    } else {
        echo "<script>alert('Error creating user: " . addslashes($stmt->error) . "'); window.location.href='users.php';</script>";
    }
    $stmt->close();
    $conn->close();
} else {
    header("Location: users.php");
    exit;
}
?>