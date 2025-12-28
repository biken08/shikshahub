<?php
// copy_user.php - Duplicate a user
session_start();

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header("Location: users.php");
    exit();
}

$conn = mysqli_connect('localhost', 'root', '', 'shikshahub');

// Get user to copy
$query = "SELECT * FROM users WHERE id = $id";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

if ($user) {
    // Generate unique username and email
    $new_username = $user['username'] . '_copy';
    $new_email = 'copy_' . $user['email'];
    
    // Make sure they're unique
    $counter = 1;
    while (true) {
        $check = mysqli_query($conn, "SELECT id FROM users WHERE username='$new_username' OR email='$new_email'");
        if (mysqli_num_rows($check) == 0) {
            break;
        }
        $new_username = $user['username'] . '_copy' . $counter;
        $new_email = 'copy' . $counter . '_' . $user['email'];
        $counter++;
    }
    
    // Insert copy
    $sql = "INSERT INTO users (username, email, password, role, created_at) 
            VALUES ('$new_username', '$new_email', '{$user['password']}', '{$user['role']}', NOW())";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: users.php?msg=copied&id=" . mysqli_insert_id($conn));
    } else {
        header("Location: users.php?msg=copy_error");
    }
} else {
    header("Location: users.php");
}

mysqli_close($conn);
?>