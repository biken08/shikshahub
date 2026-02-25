<?php
require_once 'db.php'; // Ensure this file exists with your database connection

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../register.html');
    exit;
}

// Get and sanitize inputs
$fullname = trim($_POST['fullname']);
$email = trim($_POST['email']);
$password = $_POST['password']; // raw password for hashing
$role = $_POST['role'];
$department = null;

// For student/teacher, get department; for admin it remains null
if ($role === 'student' || $role === 'teacher') {
    $department = trim($_POST['department']);
}

// Basic validation
$errors = [];
if (empty($fullname)) $errors[] = "Full name is required.";
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email address.";
if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
if (!in_array($role, ['student', 'teacher', 'admin'])) $errors[] = "Invalid role.";
if (($role === 'student' || $role === 'teacher') && empty($department)) {
    $errors[] = "Department is required.";
}

if (!empty($errors)) {
    // Display errors (you can improve this by storing in session and redirecting)
    echo "<h3>Registration failed:</h3><ul>";
    foreach ($errors as $e) echo "<li>$e</li>";
    echo "</ul><a href='../register.html'>Go back</a>";
    exit;
}

// Check if email already exists using prepared statement
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo "Email already registered. <a href='../register.html'>Try again</a>";
    exit;
}
$stmt->close();

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert user with correct column names: fullname, email, password, role, department
$stmt = $conn->prepare("INSERT INTO users (fullname, email, password, role, department) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $fullname, $email, $hashed_password, $role, $department);

if ($stmt->execute()) {
    // Registration successful – redirect to login page with success message
    header('Location: ../login.html?registered=1');
    exit;
} else {
    echo "Error: " . $stmt->error;
}
$stmt->close();
$conn->close();
?>