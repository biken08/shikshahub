<?php
session_start();
include 'backend/db.php';

if (!isset($_GET['id'])) {
    header("Location: materials.php");
    exit;
}

$id = intval($_GET['id']);

$sql = "SELECT * FROM materials WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result->num_rows === 0) {
    header("Location: materials.php?error=File not found");
    exit;
}

$material = mysqli_fetch_assoc($result);
$file_path = $material['file_path'];   // uploads/filename.ext
$file_name = basename($file_path);

if (!file_exists($file_path)) {
    header("Location: materials.php?error=File missing");
    exit;
}

/* ===== LOG DOWNLOAD ===== */
$user_id = $_SESSION['user_id'] ?? null;
$ip = $_SERVER['REMOTE_ADDR'];
$agent = $_SERVER['HTTP_USER_AGENT'];

mysqli_query(
    $conn,
    "UPDATE materials SET download_count = download_count + 1 WHERE id = $id"
);

/* ===== FILE EXTENSION ===== */
$ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

/* ===== VIEWABLE TYPES ===== */
$direct_view = ['pdf', 'jpg', 'jpeg', 'png', 'gif'];

if (in_array($ext, $direct_view)) {

    // Direct browser view
    header("Content-Type: application/$ext");
    header("Content-Disposition: inline; filename=\"$file_name\"");
    readfile($file_path);
    exit;

} else {

    // DOC, DOCX, PPT, PPTX → Google Docs Viewer
    $full_url = "http://localhost/shikshahub/" . $file_path;

    $viewer = "https://docs.google.com/gview?url=" . urlencode($full_url) . "&embedded=true";

    header("Location: $viewer");
    exit;
}
