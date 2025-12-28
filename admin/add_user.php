<?php
// add_user.php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = mysqli_connect('localhost', 'root', '', 'shikshahub');
    
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    
    // Check if user exists
    $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email' OR username='$username'");
    
    if (mysqli_num_rows($check) > 0) {
        echo "<script>
            alert('User already exists with this email or username!');
            window.location.href = 'users.php';
        </script>";
    } else {
        $sql = "INSERT INTO users (username, email, password, role) 
                VALUES ('$username', '$email', '$password', '$role')";
        
        if (mysqli_query($conn, $sql)) {
            header("Location: users.php");
        } else {
            echo "<script>
                alert('Error creating user!');
                window.location.href = 'users.php';
            </script>";
        }
    }
    
    mysqli_close($conn);
} else {
    header("Location: users.php");
}
?>