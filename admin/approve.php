<?php
// admin/approve.php
session_start();
require_once '../backend/auth.php';
require_once '../backend/db.php';

// Only admin allowed
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../login.html');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid request");
}

$id = intval($_GET['id']);

// Use prepared statement
$stmt = $conn->prepare("UPDATE materials SET status = 'approved' WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

header("Location: dashboard.php");
exit;
?>