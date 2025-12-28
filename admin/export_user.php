<?php
// export_users.php - Export all users to CSV
session_start();

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="shikshahub_users_' . date('Y-m-d_H-i') . '.csv"');

$conn = mysqli_connect('localhost', 'root', '', 'shikshahub');

// Get all users
$result = mysqli_query($conn, "SELECT * FROM users ORDER BY id");

// Create CSV output
$output = fopen('php://output', 'w');

// Add headers
fputcsv($output, ['ID', 'Username', 'Email', 'Password', 'Role', 'Created At']);

// Add data
while ($user = mysqli_fetch_assoc($result)) {
    fputcsv($output, [
        $user['id'],
        $user['username'],
        $user['email'],
        $user['password'],
        ucfirst($user['role']),
        $user['created_at'] ? date('Y-m-d H:i:s', strtotime($user['created_at'])) : ''
    ]);
}

fclose($output);
mysqli_close($conn);
?>