<?php
include 'auth.php';
include '../backend/db.php';

if (!isset($_GET['id'])) {
    die('Invalid request');
}

$id = (int)$_GET['id'];

/* get file path */
$result = mysqli_query($conn, "SELECT file_path FROM materials WHERE id = $id");
$data = mysqli_fetch_assoc($result);

if ($data) {
    $file = '../uploads/' . $data['file_path'];

    if (file_exists($file)) {
        unlink($file);
    }
}

/* delete record */
mysqli_query($conn, "DELETE FROM materials WHERE id = $id");

header("Location: dashboard.php");
exit;
