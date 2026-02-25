<?php
include 'auth.php';
include '../backend/db.php';

// Get admin details
$admin_id = $_SESSION['user_id'];
$admin_query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$admin_id'");
$admin_data = mysqli_fetch_assoc($admin_query);

// Main materials query – use users.fullname
$result = mysqli_query(
    $conn,
    "SELECT materials.*, users.fullname
     FROM materials 
     LEFT JOIN users ON materials.user_id = users.id 
     ORDER BY uploaded_at DESC"
);

// Get counts for dashboard
$total_materials = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM materials"))['count'];
$pending_materials = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM materials WHERE status = 'pending'"))['count'];
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'];
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard - ShikshaHub</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* Reset & Base */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Arial, sans-serif;
}
.logo-img {
    width: 40px;
    height: 40px;
    object-fit: contain;
}
body {
    background: #f8fafc;
    color: #1f2937;
    display: flex;
    min-height: 100vh;
}
/* Sidebar (unchanged, keep your existing CSS) */
.sidebar {
    width: 280px;
    background: #1f2937;
    color: white;
    position: fixed;
    height: 100vh;
    overflow-y: auto;
    transition: all 0.3s;
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
}
.sidebar-header {
    padding: 25px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}
.sidebar-header .logo {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 22px;
    font-weight: 700;
    color: white;
}
.admin-profile {
    padding: 25px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}
.profile-image {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #2563eb, #0ea5e9);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 15px;
    font-size: 32px;
    color: white;
}
.profile-info h3 {
    font-size: 18px;
    margin-bottom: 5px;
    font-weight: 600;
}
.profile-info p {
    font-size: 14px;
    color: #9ca3af;
    margin-bottom: 5px;
}
.profile-info .role {
    display: inline-block;
    background: rgba(37, 99, 235, 0.2);
    color: #60a5fa;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}
/* Navigation */
.sidebar-nav {
    padding: 20px 0;
}
.nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 20px;
    color: #d1d5db;
    text-decoration: none;
    transition: all 0.3s;
    border-left: 3px solid transparent;
}
.nav-item:hover,
.nav-item.active {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    border-left-color: #2563eb;
}
.nav-item i {
    width: 20px;
    font-size: 16px;
}
/* Main Content */
.main-content {
    flex: 1;
    margin-left: 280px;
    padding: 30px;
    overflow-x: hidden;
}
.top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e5e7eb;
}
.top-bar h1 {
    font-size: 28px;
    color: #1f2937;
    font-weight: 700;
}
.admin-actions .btn {
    padding: 10px 20px;
    background: #2563eb;
    color: white;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
}
.admin-actions .btn:hover {
    background: #1d4ed8;
    transform: translateY(-2px);
}
/* Stats Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}
.stat-card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    transition: transform 0.3s ease;
}
.stat-card:hover {
    transform: translateY(-5px);
}
.stat-card i {
    font-size: 32px;
    margin-bottom: 15px;
    color: #2563eb;
}
.stat-card .stat-number {
    font-size: 32px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 5px;
}
.stat-card .stat-label {
    font-size: 14px;
    color: #6b7280;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}
/* Materials Table */
.table-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    padding: 25px;
    overflow-x: auto;
}
.table-container h3 {
    font-size: 20px;
    margin-bottom: 20px;
    color: #1f2937;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}
table {
    width: 100%;
    border-collapse: collapse;
}
th {
    background: #f8fafc;
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #1f2937;
    border-bottom: 2px solid #e5e7eb;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
td {
    padding: 15px;
    border-bottom: 1px solid #e5e7eb;
    font-size: 14px;
}
tr:hover {
    background: #f8fafc;
}
.status-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-block;
}
.status-pending {
    background: #fef3c7;
    color: #92400e;
}
.status-approved {
    background: #d1fae5;
    color: #065f46;
}
.action-buttons {
    display: flex;
    gap: 8px;
}
.btn-action {
    padding: 6px 12px;
    border-radius: 5px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.3s;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
}
.btn-approve {
    background: #10b981;
    color: white;
}
.btn-approve:hover {
    background: #059669;
    transform: translateY(-2px);
}
.btn-delete {
    background: #ef4444;
    color: white;
}
.btn-delete:hover {
    background: #dc2626;
    transform: translateY(-2px);
}
.btn-view {
    background: #3b82f6;
    color: white;
}
.btn-view:hover {
    background: #2563eb;
    transform: translateY(-2px);
}
/* Responsive */
@media (max-width: 1024px) {
    .sidebar {
        width: 250px;
    }
    .main-content {
        margin-left: 250px;
    }
}
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        position: fixed;
        z-index: 1000;
    }
    .sidebar.active {
        transform: translateX(0);
    }
    .main-content {
        margin-left: 0;
        padding: 20px;
    }
    .mobile-menu-btn {
        display: block;
        background: #2563eb;
        color: white;
        border: none;
        padding: 10px 15px;
        border-radius: 6px;
        font-size: 20px;
        cursor: pointer;
        margin-bottom: 20px;
    }
    table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
}
.mobile-menu-btn {
    display: none;
}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <img src="../logo.png" class="logo-img" alt="ShikshaHub Logo" height="35">
            <span>ShikshaHub</span>
        </div>
    </div>
    
    <!-- Admin Profile -->
    <div class="admin-profile">
        <div class="profile-image">
            <i class="fas fa-user"></i>
        </div>
        <div class="profile-info">
            <h3><?php echo htmlspecialchars($admin_data['fullname']); ?></h3>
            <p><?php echo htmlspecialchars($admin_data['email']); ?></p>
            <span class="role">Administrator</span>
        </div>
    </div>
    
    <!-- Navigation -->
    <div class="sidebar-nav">
        <a href="dashboard.php" class="nav-item active">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
        <a href="../materials.php" class="nav-item">
            <i class="fas fa-book"></i>
            <span>Materials</span>
        </a>
        <a href="users.php" class="nav-item">
            <i class="fas fa-users"></i>
            <span>Users</span>
        </a>
        <a href="settings.php" class="nav-item">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
        </a>
        <a href="../backend/logout.php" class="nav-item logout">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>

<!-- Main Content -->
<div class="main-content">
    <button class="mobile-menu-btn" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    
    <div class="top-bar">
        <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
        <div class="admin-actions">
            <a href="../index.php" class="btn">
                <i class="fas fa-globe"></i> View Site
            </a>
            <a href="../upload.php" class="btn">
                <i class="fas fa-plus"></i> Add Material
            </a>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <i class="fas fa-book-open"></i>
            <div class="stat-number"><?php echo $total_materials; ?></div>
            <div class="stat-label">Total Materials</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-clock"></i>
            <div class="stat-number"><?php echo $pending_materials; ?></div>
            <div class="stat-label">Pending Approval</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-users"></i>
            <div class="stat-number"><?php echo $total_users; ?></div>
            <div class="stat-label">Registered Users</div>
        </div>
    </div>
    
    <!-- Materials Table -->
    <div class="table-container">
        <h3><i class="fas fa-book"></i> All Materials</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Uploaded By</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td>#<?php echo htmlspecialchars($row['id']); ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($row['title']); ?></strong><br>
                        <small style="color: #6b7280;"><?php echo substr(htmlspecialchars($row['description']), 0, 50); ?>...</small>
                    </td>
                    <td><?php echo htmlspecialchars(strtoupper($row['type'])); ?></td>
                    <td><?php echo htmlspecialchars($row['fullname'] ?? 'Unknown'); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo $row['status']; ?>">
                            <?php echo ucfirst($row['status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($row['uploaded_at'])); ?></td>
                    <td>
                        <div class="action-buttons">
                            <?php if ($row['status'] === 'pending'): ?>
                                <a class="btn-action btn-approve" 
                                   href="approve.php?id=<?php echo $row['id']; ?>">
                                    <i class="fas fa-check"></i> Approve
                                </a>
                            <?php else: ?>
                                <a class="btn-action btn-view" 
                                   href="../uploads/<?php echo htmlspecialchars($row['file_path']); ?>" 
                                   target="_blank">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            <?php endif; ?>
                            
                            <a class="btn-action btn-delete" 
                               href="delete.php?id=<?php echo $row['id']; ?>"
                               onclick="return confirm('Are you sure you want to delete this material?');">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('active');
}
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('sidebar');
    const mobileBtn = document.querySelector('.mobile-menu-btn');
    if (window.innerWidth <= 768) {
        if (!sidebar.contains(event.target) && !mobileBtn.contains(event.target)) {
            sidebar.classList.remove('active');
        }
    }
});
window.addEventListener('resize', function() {
    const sidebar = document.getElementById('sidebar');
    if (window.innerWidth > 768) {
        sidebar.classList.remove('active');
    }
});
</script>

</body>
</html>