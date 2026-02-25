<?php
session_start();
require_once '../backend/auth.php';
require_once '../backend/db.php';

if ($_SESSION['role'] !== 'teacher' && $_SESSION['role'] !== 'admin') {
    header('Location: /shikshahub/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: upload.php');
    exit;
}

$title = trim($_POST['title']);
$description = trim($_POST['description']);
$subject = trim($_POST['subject'] ?? '');
$role = $_SESSION['role'];

// Determine department
if ($role === 'admin' && isset($_POST['department'])) {
    $department = $_POST['department'];
} else {
    $department = $_SESSION['department'] ?? '';
}

if (empty($department)) {
    die("Department is required.");
}

// File upload handling
$target_dir = "../uploads/";
$file_extension = strtolower(pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION));
$allowed = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt'];
$max_size = 20 * 1024 * 1024; // 20MB

if (!in_array($file_extension, $allowed)) {
    die("File type not allowed. Only PDF, DOC, DOCX, PPT, PPTX, TXT are permitted.");
}

if ($_FILES["file"]["size"] > $max_size) {
    die("File size exceeds 20MB limit.");
}

// Generate unique filename
$unique_name = time() . '_' . bin2hex(random_bytes(8)) . '.' . $file_extension;
$target_file = $target_dir . $unique_name;

if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
    // Insert into database
    $stmt = $conn->prepare("INSERT INTO materials (user_id, title, description, subject, file_path, type, status, department) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)");
    $file_type = strtoupper($file_extension);
    $user_id = $_SESSION['user_id'];
    $stmt->bind_param("issssss", $user_id, $title, $description, $subject, $unique_name, $file_type, $department);

    if ($stmt->execute()) {
        // Redirect back to dashboard with success message
        header("Location: dashboard.php?upload=success");
        exit;
    } else {
        // Delete uploaded file if DB insert fails
        unlink($target_file);
        die("Database error: " . $stmt->error);
    }
    $stmt->close();
} else {
    die("Sorry, there was an error uploading your file.");
}
$conn->close();
?>