<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit();
}

$title = $_POST['title'];
$description = $_POST['description'];
$type = $_POST['type'];
$user_id = $_SESSION['user_id'];

$file = $_FILES['file'];
$fileName = time() . "_" . basename($file['name']);
$targetDir = "../uploads/";
$targetFile = $targetDir . $fileName;

$allowed = ["pdf", "ppt", "pptx", "doc", "docx"];
$ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

if (!in_array($ext, $allowed)) {
    die("Invalid file type");
}

if (!is_dir($targetDir)) {
    mkdir($targetDir);
}

if (move_uploaded_file($file['tmp_name'], $targetFile)) {

    $query = "INSERT INTO materials (title, description, file_path, type, user_id)
              VALUES ('$title', '$description', '$fileName', '$type', '$user_id')";

    mysqli_query($conn, $query);

    header("Location: ../materials.php");
} else {
    echo "File upload failed";
}
