<?php
include 'auth.php';
include '../backend/db.php';

if (!isset($_GET['id'])) {
    die("Invalid request");
}

$id = (int) $_GET['id'];

mysqli_query(
    $conn,
    "UPDATE materials SET status = 'approved' WHERE id = $id"
);

header("Location: dashboard.php");
exit;
