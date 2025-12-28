<?php
// process_user.php - Handle all user operations
session_start();

$conn = mysqli_connect('localhost', 'root', '', 'shikshahub');

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = mysqli_real_escape_string($conn, $_POST['password']);
        $role = mysqli_real_escape_string($conn, $_POST['role']);
        
        // Check if user exists
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email' OR username='$username'");
        
        if (mysqli_num_rows($check) > 0) {
            header("Location: users.php?msg=exists");
        } else {
            $sql = "INSERT INTO users (username, email, password, role, created_at) 
                    VALUES ('$username', '$email', '$password', '$role', NOW())";
            
            if (mysqli_query($conn, $sql)) {
                header("Location: users.php?msg=added&id=" . mysqli_insert_id($conn));
            } else {
                header("Location: users.php?msg=error&error=" . urlencode(mysqli_error($conn)));
            }
        }
        break;
        
    case 'edit':
        $id = intval($_POST['id']);
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = mysqli_real_escape_string($conn, $_POST['password']);
        $role = mysqli_real_escape_string($conn, $_POST['role']);
        
        $sql = "UPDATE users SET 
                username = '$username',
                email = '$email',
                password = '$password',
                role = '$role'
                WHERE id = $id";
        
        if (mysqli_query($conn, $sql)) {
            header("Location: users.php?msg=updated&id=$id");
        } else {
            header("Location: edit_user.php?id=$id&error=" . urlencode(mysqli_error($conn)));
        }
        break;
        
    default:
        header("Location: users.php");
}

mysqli_close($conn);
?>