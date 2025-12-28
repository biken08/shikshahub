<?php
session_start();
include 'backend/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($user_query);

// Get user's uploads
$uploads_query = mysqli_query($conn, 
    "SELECT COUNT(*) as total_uploads FROM materials WHERE user_id = '$user_id'"
);
$uploads_data = mysqli_fetch_assoc($uploads_query);

// Get recent uploads
$recent_uploads = mysqli_query($conn,
    "SELECT * FROM materials WHERE user_id = '$user_id' ORDER BY uploaded_at DESC LIMIT 5"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - ShikshaHub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="index.css">
    <style>
        .profile-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .profile-header {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 2rem;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #3498db, #2c3e50);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }
        
        .profile-info h1 {
            margin: 0 0 0.5rem 0;
            color: #2c3e50;
        }
        
        .profile-info .email {
            color: #7f8c8d;
            margin-bottom: 1rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card .number {
            font-size: 2rem;
            font-weight: bold;
            color: #3498db;
            margin-bottom: 0.5rem;
        }
        
        .uploads-section {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .upload-item {
            border-bottom: 1px solid #eee;
            padding: 1rem 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .upload-item:last-child {
            border-bottom: none;
        }
        
        .upload-info h4 {
            margin: 0 0 0.5rem 0;
            color: #2c3e50;
        }
        
        .upload-info .date {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <!-- Include the same header from index.php -->
    <?php include 'header.php'; ?>

    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="profile-info">
                
                <div class="email"><?php echo htmlspecialchars($user['email']); ?></div>
                <div class="username">@<?php echo htmlspecialchars($user['username']); ?></div>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="number"><?php echo $uploads_data['total_uploads']; ?></div>
                <div class="label">Total Uploads</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></div>
                <div class="label">Member Since</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo $user['role'] ?? 'User'; ?></div>
                <div class="label">Role</div>
            </div>
        </div>
        
        <div class="uploads-section">
            <h2>Recent Uploads</h2>
            <?php if(mysqli_num_rows($recent_uploads) > 0): ?>
                <?php while($upload = mysqli_fetch_assoc($recent_uploads)): ?>
                    <div class="upload-item">
                        <div class="upload-info">
                            <h4><?php echo htmlspecialchars($upload['title']); ?></h4>
                            <div class="date">
                                Uploaded on <?php echo date('M d, Y', strtotime($upload['uploaded_at'])); ?>
                            </div>
                        </div>
                        <div class="upload-actions">
                            <a href="<?php echo htmlspecialchars($upload['file_path']); ?>" 
                               target="_blank" 
                               class="view-btn">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No uploads yet. <a href="upload.php">Upload your first material</a></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Include footer -->
    <?php include 'footer.php'; ?>
</body>
</html>