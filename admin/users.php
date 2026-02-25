<?php
// users.php - Admin User Management
session_start();
require_once '../backend/auth.php'; // ensures only admin can access
require_once '../backend/db.php';

// Only admin allowed
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../login.html');
    exit;
}

// Handle actions
$action = $_GET['action'] ?? '';
$id = intval($_GET['id'] ?? 0);

if ($action && $id) {
    switch ($action) {
        case 'delete':
            // Prevent self-deletion
            if ($id != $_SESSION['user_id']) {
                // Optionally delete user's materials first (or set FK to cascade)
                $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();
                $message = '<div class="alert success">User deleted successfully</div>';
            } else {
                $message = '<div class="alert error">Cannot delete your own account</div>';
            }
            break;
    }
}

// Handle role update via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $user_id = intval($_POST['user_id']);
    $new_role = $_POST['role']; // already validated via select options
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $new_role, $user_id);
    if ($stmt->execute()) {
        $message = '<div class="alert success">User role updated successfully</div>';
    } else {
        $message = '<div class="alert error">Failed to update role</div>';
    }
    $stmt->close();
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $user_id = intval($_POST['user_id']);
    $new_password = $_POST['new_password'];
    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed, $user_id);
    if ($stmt->execute()) {
        $message = '<div class="alert success">Password reset successfully</div>';
    } else {
        $message = '<div class="alert error">Failed to reset password</div>';
    }
    $stmt->close();
}

// Fetch all users with correct columns
$query = "SELECT id, fullname, email, role, department, created_at FROM users ORDER BY id DESC";
$result = mysqli_query($conn, $query);
$users = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
}

// Get statistics
$total_users = count($users);
$admin_count = 0;
$teacher_count = 0;
$student_count = 0;
foreach ($users as $user) {
    switch ($user['role']) {
        case 'admin': $admin_count++; break;
        case 'teacher': $teacher_count++; break;
        case 'student': $student_count++; break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* (keep your existing CSS, it's fine) */
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI',sans-serif; }
        body { background:#f8f9fa; padding:20px; }
        .admin-container { max-width:1300px; margin:0 auto; background:white; border-radius:12px; box-shadow:0 5px 20px rgba(0,0,0,0.08); overflow:hidden; }
        .admin-header { background:linear-gradient(135deg,#2c3e50 0%,#34495e 100%); color:white; padding:25px 30px; display:flex; justify-content:space-between; align-items:center; border-bottom:3px solid #3498db; }
        .admin-header h1 { font-size:26px; display:flex; align-items:center; gap:12px; }
        .header-badge { background:rgba(52,152,219,0.2); padding:4px 12px; border-radius:20px; font-size:14px; border:1px solid rgba(52,152,219,0.3); }
        .nav-buttons { display:flex; gap:10px; }
        .nav-btn { padding:10px 20px; background:rgba(255,255,255,0.1); color:white; border:1px solid rgba(255,255,255,0.2); border-radius:6px; text-decoration:none; display:flex; align-items:center; gap:8px; transition:all 0.3s; }
        .nav-btn:hover { background:#3498db; transform:translateY(-2px); }
        .admin-content { padding:30px; }
        .alert { padding:15px; border-radius:8px; margin-bottom:25px; display:flex; align-items:center; gap:12px; animation:slideIn 0.3s ease; }
        .success { background:#d4edda; color:#155724; border-left:4px solid #28a745; }
        .error { background:#f8d7da; color:#721c24; border-left:4px solid #dc3545; }
        .warning { background:#fff3cd; color:#856404; border-left:4px solid #ffc107; }
        .stats-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(220px,1fr)); gap:20px; margin-bottom:30px; }
        .stat-card { background:white; padding:25px; border-radius:10px; box-shadow:0 3px 10px rgba(0,0,0,0.08); text-align:center; border-top:4px solid #3498db; transition:transform 0.3s; }
        .stat-card:hover { transform:translateY(-5px); }
        .stat-card i { font-size:32px; color:#3498db; margin-bottom:15px; }
        .stat-number { font-size:36px; font-weight:bold; color:#2c3e50; margin:10px 0; }
        .stat-label { color:#7f8c8d; font-size:15px; }
        .action-bar { display:flex; gap:15px; margin-bottom:25px; flex-wrap:wrap; }
        .btn { padding:12px 24px; border:none; border-radius:6px; cursor:pointer; font-weight:600; display:inline-flex; align-items:center; gap:10px; transition:all 0.3s; text-decoration:none; font-size:15px; }
        .btn-primary { background:#3498db; color:white; }
        .btn-success { background:#28a745; color:white; }
        .btn-secondary { background:#6c757d; color:white; }
        .btn-danger { background:#dc3545; color:white; }
        .btn:hover { transform:translateY(-2px); box-shadow:0 5px 15px rgba(0,0,0,0.1); }
        .search-box { margin-bottom:25px; position:relative; }
        .search-input { width:100%; padding:14px 20px 14px 45px; border:2px solid #e0e0e0; border-radius:8px; font-size:16px; transition:border-color 0.3s; }
        .search-input:focus { outline:none; border-color:#3498db; }
        .search-icon { position:absolute; left:15px; top:50%; transform:translateY(-50%); color:#95a5a6; }
        .users-table-wrapper { overflow-x:auto; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.05); }
        .users-table { width:100%; border-collapse:collapse; background:white; }
        .users-table thead { background:#f8f9fa; }
        .users-table th { padding:16px 15px; text-align:left; font-weight:600; color:#2c3e50; border-bottom:2px solid #dee2e6; white-space:nowrap; }
        .users-table td { padding:16px 15px; border-bottom:1px solid #eee; vertical-align:middle; }
        .users-table tr:hover { background:#f8f9fa; }
        .user-avatar { width:40px; height:40px; border-radius:50%; background:linear-gradient(135deg,#3498db 0%,#2c3e50 100%); color:white; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:16px; }
        .role-badge { display:inline-block; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600; text-transform:uppercase; }
        .role-admin { background:#e74c3c; color:white; }
        .role-teacher { background:#f39c12; color:white; }
        .role-student { background:#2ecc71; color:white; }
        .role-select { padding:8px 12px; border:2px solid #e0e0e0; border-radius:6px; background:white; cursor:pointer; font-size:14px; min-width:120px; }
        .role-select:focus { outline:none; border-color:#3498db; }
        .action-buttons { display:flex; gap:8px; }
        .btn-icon { width:36px; height:36px; border:none; border-radius:6px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all 0.2s; }
        .btn-icon:hover { transform:scale(1.1); }
        .icon-edit { background:#ffc107; color:white; }
        .icon-delete { background:#dc3545; color:white; }
        .icon-reset { background:#28a745; color:white; }
        .icon-view { background:#17a2b8; color:white; }
        .no-data { text-align:center; padding:50px; color:#6c757d; }
        .no-data i { font-size:48px; margin-bottom:15px; color:#dee2e6; }
        .modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center; animation:fadeIn 0.3s; }
        .modal-content { background:white; padding:30px; border-radius:10px; max-width:500px; width:90%; animation:slideUp 0.3s; }
        .modal-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; padding-bottom:15px; border-bottom:2px solid #f1f1f1; }
        .modal-title { color:#2c3e50; font-size:22px; display:flex; align-items:center; gap:10px; }
        .close-btn { background:none; border:none; font-size:24px; cursor:pointer; color:#6c757d; }
        .form-group { margin-bottom:20px; }
        .form-label { display:block; margin-bottom:8px; font-weight:600; color:#495057; }
        .form-control { width:100%; padding:12px; border:2px solid #e9ecef; border-radius:6px; font-size:15px; transition:border-color 0.3s; }
        .form-control:focus { outline:none; border-color:#3498db; }
        .modal-actions { display:flex; gap:10px; justify-content:flex-end; margin-top:25px; }
        @keyframes slideIn { from{transform:translateY(-20px); opacity:0;} to{transform:translateY(0); opacity:1;} }
        @keyframes fadeIn { from{opacity:0;} to{opacity:1;} }
        @keyframes slideUp { from{transform:translateY(50px); opacity:0;} to{transform:translateY(0); opacity:1;} }
        @media (max-width:768px) { .admin-header{flex-direction:column; gap:15px; text-align:center;} .nav-buttons{width:100%; justify-content:center;} .action-bar{justify-content:center;} .users-table th,.users-table td{padding:12px 8px; font-size:14px;} .action-buttons{flex-direction:column;} }
    </style>
</head>
<body>
<div class="admin-container">
    <!-- Header -->
    <div class="admin-header">
        <div>
            <h1><i class="fas fa-users-cog"></i> User Management <span class="header-badge">Admin Panel</span></h1>
            <p style="opacity:0.8; margin-top:5px; font-size:14px;">Manage all registered users and their permissions</p>
        </div>
        <div class="nav-buttons">
            <a href="dashboard.php" class="nav-btn"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="../index.php" class="nav-btn"><i class="fas fa-home"></i> View Site</a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="admin-content">
        <!-- Messages -->
        <?php if (isset($message)) echo $message; ?>
        
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card"><i class="fas fa-users"></i><div class="stat-number"><?php echo $total_users; ?></div><div class="stat-label">Total Users</div></div>
            <div class="stat-card"><i class="fas fa-user-shield"></i><div class="stat-number"><?php echo $admin_count; ?></div><div class="stat-label">Admins</div></div>
            <div class="stat-card"><i class="fas fa-chalkboard-teacher"></i><div class="stat-number"><?php echo $teacher_count; ?></div><div class="stat-label">Teachers</div></div>
            <div class="stat-card"><i class="fas fa-user-graduate"></i><div class="stat-number"><?php echo $student_count; ?></div><div class="stat-label">Students</div></div>
        </div>
        
        <!-- Action Buttons -->
        <div class="action-bar">
            <button class="btn btn-primary" onclick="showAddUserModal()"><i class="fas fa-user-plus"></i> Add New User</button>
            <a href="users.php" class="btn btn-secondary"><i class="fas fa-sync-alt"></i> Refresh</a>
        </div>
        
        <!-- Search -->
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="searchInput" class="search-input" placeholder="Search users by name, email, role, department..." onkeyup="filterUsers()">
        </div>
        
        <!-- Users Table -->
        <div class="users-table-wrapper">
            <table class="users-table" id="usersTable">
                <thead>
                    <tr><th>ID</th><th>Full Name</th><th>Email</th><th>Role</th><th>Department</th><th>Joined</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                    <tr><td colspan="7"><div class="no-data"><i class="fas fa-user-slash"></i><h3>No Users Found</h3><p>No users are registered in the system yet.</p><button class="btn btn-primary" onclick="showAddUserModal()" style="margin-top:15px;"><i class="fas fa-user-plus"></i> Add First User</button></div></td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                        <tr class="user-row">
                            <td><strong>#<?php echo $user['id']; ?></strong></td>
                            <td>
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div class="user-avatar"><?php echo strtoupper(substr($user['fullname'], 0, 1)); ?></div>
                                    <div><strong><?php echo htmlspecialchars($user['fullname']); ?></strong></div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <form method="POST" action="users.php" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <select name="role" class="role-select" onchange="this.form.submit()">
                                        <option value="student" <?php if($user['role']=='student') echo 'selected'; ?>>👨‍🎓 Student</option>
                                        <option value="teacher" <?php if($user['role']=='teacher') echo 'selected'; ?>>👩‍🏫 Teacher</option>
                                        <option value="admin" <?php if($user['role']=='admin') echo 'selected'; ?>>🛡️ Admin</option>
                                    </select>
                                    <input type="hidden" name="update_role" value="1">
                                </form>
                            </td>
                            <td><?php echo htmlspecialchars($user['department'] ?? 'N/A'); ?></td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon icon-reset" onclick="showResetPasswordModal(<?php echo $user['id']; ?>)" title="Reset Password"><i class="fas fa-key"></i></button>
                                    <button class="btn-icon icon-delete" onclick="confirmDelete(<?php echo $user['id']; ?>, '<?php echo addslashes($user['fullname']); ?>')" <?php echo $user['id'] == $_SESSION['user_id'] ? 'disabled' : ''; ?> title="Delete User"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div id="addUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title"><i class="fas fa-user-plus"></i> Add New User</h2>
            <button class="close-btn" onclick="hideModal('addUserModal')">&times;</button>
        </div>
        <form method="POST" action="add_user.php" id="addUserForm">
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="fullname" class="form-control" required placeholder="Enter full name">
            </div>
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required placeholder="Enter email address">
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="text" name="password" class="form-control" required placeholder="Enter password" id="newPassword">
                <small style="color:#6c757d; display:block; margin-top:5px;"><button type="button" onclick="generatePassword()" style="background:none; border:none; color:#3498db; cursor:pointer; padding:0;"><i class="fas fa-random"></i> Generate strong password</button></small>
            </div>
            <div class="form-group">
                <label class="form-label">Role</label>
                <select name="role" class="form-control" required>
                    <option value="student">👨‍🎓 Student</option>
                    <option value="teacher">👩‍🏫 Teacher</option>
                    <option value="admin">🛡️ Admin</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Department (for Student/Teacher)</label>
                <input type="text" name="department" class="form-control" placeholder="e.g., Engineering, IT, Business Studies">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="hideModal('addUserModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Create User</button>
            </div>
        </form>
    </div>
</div>

<!-- Reset Password Modal -->
<div id="resetPasswordModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title"><i class="fas fa-key"></i> Reset Password</h2>
            <button class="close-btn" onclick="hideModal('resetPasswordModal')">&times;</button>
        </div>
        <form method="POST" action="users.php" id="resetPasswordForm">
            <input type="hidden" name="user_id" id="resetUserId">
            <input type="hidden" name="reset_password" value="1">
            <div class="form-group">
                <label class="form-label">New Password</label>
                <input type="text" name="new_password" class="form-control" required id="resetPasswordField">
                <small style="color:#6c757d; display:block; margin-top:5px;"><button type="button" onclick="generateResetPassword()" style="background:none; border:none; color:#3498db; cursor:pointer; padding:0;"><i class="fas fa-random"></i> Generate password</button></small>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="hideModal('resetPasswordModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Reset Password</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title" style="color:#dc3545;"><i class="fas fa-exclamation-triangle"></i> Confirm Delete</h2>
            <button class="close-btn" onclick="hideModal('deleteModal')">&times;</button>
        </div>
        <div style="padding:20px 0;">
            <p>Are you sure you want to delete user <strong id="deleteUserName"></strong>?</p>
            <div style="background:#f8d7da; color:#721c24; padding:15px; border-radius:6px; margin-top:15px;"><i class="fas fa-warning"></i> <strong>Warning:</strong> This action cannot be undone. All user data will be permanently deleted.</div>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" onclick="hideModal('deleteModal')">Cancel</button>
            <a href="#" id="confirmDeleteBtn" class="btn btn-danger"><i class="fas fa-trash"></i> Delete User</a>
        </div>
    </div>
</div>

<script>
// Modal functions
function showModal(modalId) { document.getElementById(modalId).style.display='flex'; document.body.style.overflow='hidden'; }
function hideModal(modalId) { document.getElementById(modalId).style.display='none'; document.body.style.overflow='auto'; }
function showAddUserModal() { document.getElementById('addUserForm').reset(); showModal('addUserModal'); }
function showResetPasswordModal(userId) { document.getElementById('resetUserId').value=userId; showModal('resetPasswordModal'); }
function confirmDelete(userId, userName) { document.getElementById('deleteUserName').textContent=userName; document.getElementById('confirmDeleteBtn').href=`users.php?action=delete&id=${userId}`; showModal('deleteModal'); }
function generatePassword() { const chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*'; let p=''; for(let i=0;i<12;i++) p+=chars.charAt(Math.floor(Math.random()*chars.length)); document.getElementById('newPassword').value=p; }
function generateResetPassword() { const chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*'; let p=''; for(let i=0;i<12;i++) p+=chars.charAt(Math.floor(Math.random()*chars.length)); document.getElementById('resetPasswordField').value=p; }
function filterUsers() { const filter=document.getElementById('searchInput').value.toLowerCase(); document.querySelectorAll('.user-row').forEach(row => { row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none'; }); }
window.onclick = function(e) { if(e.target.className==='modal') { e.target.style.display='none'; document.body.style.overflow='auto'; } }
setTimeout(()=>{ document.querySelectorAll('.alert').forEach(alert => { alert.style.opacity='0'; alert.style.transition='opacity 0.5s'; setTimeout(()=>alert.remove(),500); }); },5000);
</script>
</body>
</html>
<?php mysqli_close($conn); ?>