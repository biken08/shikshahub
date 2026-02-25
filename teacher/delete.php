<?php
session_start();
require_once '../backend/auth.php';
require_once '../backend/db.php';

if ($_SESSION['role'] !== 'teacher') {
    header('Location: ../login.html');
    exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id) {
    $stmt = $conn->prepare("SELECT file_path FROM materials WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $file = '../uploads/' . $row['file_path'];
        if (file_exists($file)) {
            unlink($file);
        }
        $del = $conn->prepare("DELETE FROM materials WHERE id = ?");
        $del->bind_param("i", $id);
        $del->execute();
        $del->close();
    }
    $stmt->close();
}
header("Location: dashboard.php");
exit;
?>