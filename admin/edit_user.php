<?php
// edit_user.php - Edit user details
session_start();

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header("Location: users.php");
    exit();
}

$conn = mysqli_connect('localhost', 'root', '', 'shikshahub');

// Get user data
$query = "SELECT * FROM users WHERE id = $id";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    header("Location: users.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 600px; margin: 40px auto; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; margin-bottom: 30px; display: flex; align-items: center; gap: 15px; }
        .form-group { margin-bottom: 25px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #34495e; }
        input, select { width: 100%; padding: 14px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 16px; transition: border-color 0.3s; }
        input:focus, select:focus { outline: none; border-color: #3498db; }
        .btn-group { display: flex; gap: 15px; margin-top: 30px; }
        .btn { padding: 14px 30px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 10px; transition: all 0.3s; }
        .btn-primary { background: #3498db; color: white; }
        .btn-secondary { background: #95a5a6; color: white; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .user-id { background: #f8f9fa; padding: 10px 15px; border-radius: 8px; margin-bottom: 20px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-user-edit"></i> Edit User: <?php echo htmlspecialchars($user['username']); ?></h1>
        
        <div class="user-id">
            <strong>User ID:</strong> <?php echo $user['id']; ?> | 
            <strong>Role:</strong> <?php echo ucfirst($user['role']); ?> | 
            <!-- <strong>Created:</strong> <?php echo $user['created_at'] ? date('M d, Y', strtotime($user['created_at'])) : 'Never'; ?> -->
        </div>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="error">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="process_user.php">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
            
            <div class="form-group">
                <label><i class="fas fa-user"></i> Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-envelope"></i> Email Address</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-key"></i> Password</label>
                <input type="text" name="password" value="<?php echo htmlspecialchars($user['password']); ?>" required>
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-user-tag"></i> User Role</label>
                <select name="role" required>
                    <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>👤 Regular User</option>
                    <option value="moderator" <?php echo $user['role'] == 'moderator' ? 'selected' : ''; ?>>👮 Moderator</option>
                    <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>🛡️ Administrator</option>
                </select>
            </div>
            
            <div class="btn-group">
                <a href="users.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Users
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update User
                </button>
                <a href="users.php?delete=<?php echo $user['id']; ?>" 
                   class="btn btn-danger" 
                   onclick="return confirm('Are you sure you want to delete this user?')"
                   <?php echo $user['id'] == ($_SESSION['user_id'] ?? 0) ? 'disabled' : ''; ?>>
                    <i class="fas fa-trash"></i> Delete User
                </a>
            </div>
        </form>
    </div>
</body>
</html>

<?php mysqli_close($conn); ?>