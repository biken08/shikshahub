<?php
session_start();
require_once 'backend/auth.php'; // ensures user is logged in
require_once 'backend/db.php';

$user_id = $_SESSION['user_id'];

// Get user data with prepared statement
$stmt = $conn->prepare("SELECT fullname, email, role, department, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Get user's upload statistics
$upload_stats = $conn->prepare("SELECT COUNT(*) as total_uploads FROM materials WHERE user_id = ?");
$upload_stats->bind_param("i", $user_id);
$upload_stats->execute();
$stats_result = $upload_stats->get_result();
$stats = $stats_result->fetch_assoc();
$upload_stats->close();

// Get recent uploads
$recent = $conn->prepare("SELECT id, title, file_path, uploaded_at FROM materials WHERE user_id = ? ORDER BY uploaded_at DESC LIMIT 5");
$recent->bind_param("i", $user_id);
$recent->execute();
$recent_uploads = $recent->get_result();
$recent->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - ShikshaHub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }
        body {
            background: linear-gradient(135deg, #f6f9fc 0%, #f1f5f9 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        /* Navbar (can be in header.php, but included here for completeness) */
        .navbar {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0,0,0,0.08);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo-box {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .logo-img {
            width: 40px;
            height: 40px;
            object-fit: contain;
        }
        .logo-text {
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(135deg, #4361ee, #7209b7);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }
        .nav-links a {
            text-decoration: none;
            color: #1e293b;
            font-weight: 500;
            transition: 0.3s;
        }
        .nav-links a:hover {
            color: #4361ee;
        }
        .login-btn {
            background: linear-gradient(135deg, #4361ee, #7209b7);
            color: white;
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
        }
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(67,97,238,0.3);
        }
        /* Profile container */
        .profile-container {
            max-width: 1000px;
            margin: 3rem auto;
            padding: 0 2rem;
            flex: 1;
        }
        .profile-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .profile-cover {
            height: 120px;
            background: linear-gradient(135deg, #4361ee, #7209b7);
        }
        .profile-header {
            padding: 0 2rem 2rem;
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
            position: relative;
            margin-top: -60px;
        }
        .avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4361ee, #7209b7);
            border: 4px solid white;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3.5rem;
        }
        .profile-info {
            padding-top: 1.5rem;
        }
        .profile-info h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.3rem;
        }
        .profile-info .email {
            color: #64748b;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .profile-info .role-badge {
            display: inline-block;
            padding: 0.3rem 1rem;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            background: #e2e8f0;
            color: #334155;
        }
        .role-badge.teacher { background: #fef3c7; color: #92400e; }
        .role-badge.student { background: #dbeafe; color: #1e40af; }
        .role-badge.admin { background: #fee2e2; color: #991b1b; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            padding: 2rem;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
        }
        .stat-card {
            text-align: center;
        }
        .stat-number {
            font-size: 2.2rem;
            font-weight: 700;
            color: #4361ee;
        }
        .stat-label {
            color: #64748b;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .uploads-section {
            padding: 2rem;
        }
        .uploads-section h2 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: #1e293b;
        }
        .upload-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            transition: 0.2s;
        }
        .upload-item:hover {
            background: #f8fafc;
        }
        .upload-info h4 {
            font-size: 1.1rem;
            margin-bottom: 0.3rem;
            color: #1e293b;
        }
        .upload-info .date {
            font-size: 0.85rem;
            color: #64748b;
        }
        .upload-actions a {
            background: #e2e8f0;
            color: #334155;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: 0.2s;
        }
        .upload-actions a:hover {
            background: #4361ee;
            color: white;
        }
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #64748b;
        }
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        footer {
            background: #1e293b;
            color: white;
            text-align: center;
            padding: 2rem;
            margin-top: auto;
        }
    </style>
</head>
<body>
    <!-- Header (reuse your header.php) -->
    <?php include 'header.php'; ?>

    <div class="profile-container">
        <div class="profile-card">
            <div class="profile-cover"></div>
            <div class="profile-header">
                <div class="avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($user['fullname'] ?? 'User'); ?></h1>
                    <div class="email"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></div>
                    <div>
                        <span class="role-badge <?php echo $user['role']; ?>">
                            <?php echo ucfirst($user['role']); ?>
                        </span>
                        <?php if (!empty($user['department'])): ?>
                            <span style="margin-left: 1rem; color: #64748b;">
                                <i class="fas fa-building"></i> <?php echo htmlspecialchars($user['department']); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_uploads']; ?></div>
                    <div class="stat-label">Total Uploads</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></div>
                    <div class="stat-label">Member Since</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">
                        <?php
                        // You can add more stats like downloads if you have them
                        echo '0';
                        ?>
                    </div>
                    <div class="stat-label">Downloads</div>
                </div>
            </div>

            <div class="uploads-section">
                <h2>Recent Uploads</h2>
                <?php if ($recent_uploads->num_rows > 0): ?>
                    <?php while ($upload = $recent_uploads->fetch_assoc()): ?>
                        <div class="upload-item">
                            <div class="upload-info">
                                <h4><?php echo htmlspecialchars($upload['title']); ?></h4>
                                <div class="date">
                                    <i class="far fa-calendar-alt"></i>
                                    <?php echo date('M d, Y', strtotime($upload['uploaded_at'])); ?>
                                </div>
                            </div>
                            <div class="upload-actions">
                                <a href="/shikshahub/uploads/<?php echo urlencode($upload['file_path']); ?>" target="_blank">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>You haven't uploaded any materials yet.</p>
                        <a href="/shikshahub/upload.php" class="login-btn" style="display: inline-block; margin-top: 1rem;">Upload Now</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'footer.php'; ?>
</body>
</html>