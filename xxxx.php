<?php
session_start();
include 'backend/db.php';

// DEBUG: Always show errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get recent materials from database
$recentMaterials = mysqli_query(
    $conn,
    "SELECT materials.*, users.username 
     FROM materials 
     LEFT JOIN users ON materials.user_id = users.id 
     ORDER BY uploaded_at DESC LIMIT 6"
);

// If no results, create dummy data for testing
if (!$recentMaterials || mysqli_num_rows($recentMaterials) === 0) {
    echo "<!-- DEBUG: No materials found in database, using test data -->";
    // Create test data
    $testData = [
        [
            'title' => 'Calculus I Complete Notes',
            'description' => 'Complete notes for Calculus I covering limits, derivatives, and integrals.',
            'type' => 'PPT',
            'username' => 'Ram kc',
            'file_path' => '#',
            'uploaded_at' => date('Y-m-d H:i:s')
        ],
        [
            'title' => 'Web Development Guide',
            'description' => 'Complete guide to modern web development covering HTML, CSS, JavaScript, and React.',
            'type' => 'PDF',
            'username' => 'Biken Shrestha',
            'file_path' => '#',
            'uploaded_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
        ],
        [
            'title' => 'Physics Lab Experiments',
            'description' => 'Complete lab manual for Physics practicals with detailed procedures.',
            'type' => 'DOC',
            'username' => 'Anjali Sharma',
            'file_path' => '#',
            'uploaded_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
        ]
    ];
    
    // Convert to result-like array
    $recentArray = [];
    foreach ($testData as $item) {
        $recentArray[] = (object) $item;
    }
    $recentMaterials = $recentArray;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShikshaHub - Free Educational Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="index.css">
    <!-- EMERGENCY CSS FIX -->
    <style>
        /* FORCE STYLES TO WORK */
        .recent-section {
            background: #f8fafc !important;
            padding: 80px 40px !important;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        
        .material-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)) !important;
            gap: 30px !important;
            max-width: 1200px !important;
            margin: 0 auto !important;
        }
        
        .material-card {
            background: white !important;
            padding: 25px !important;
            border-radius: 12px !important;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08) !important;
            border-left: 4px solid #2563eb !important;
            display: block !important;
        }
        
        .recent-section h2 {
            text-align: center !important;
            font-size: 36px !important;
            color: #1f2937 !important;
            margin-bottom: 50px !important;
        }
        
        /* Make sure everything is visible */
        * {
            box-sizing: border-box !important;
        }
        
        body {
            overflow-x: hidden !important;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="logo">
            <img src="logo.png" alt="ShikshaHub Logo" height="40">
            <strong>ShikshaHub</strong>
        </div>

        <nav>
            <a href="index.php" class="active"><i class="fas fa-home"></i> Home</a>
            <a href="materials.php"><i class="fas fa-book"></i> Materials</a>
            <a href="upload.php"><i class="fas fa-cloud-upload-alt"></i> Upload</a>
        </nav>

        <form class="search-box" action="materials.php" method="GET">
            <input type="text" name="q" placeholder="Search for study materials, notes, textbooks...">
            <button type="submit"><i class="fas fa-search"></i> Search</button>
        </form>

        <div class="auth-btn">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="backend/logout.php" class="btn-login"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php else: ?>
                <a href="login.html" class="btn-login"><i class="fas fa-sign-in-alt"></i> Login</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <div class="hero-text">
                <h1>Welcome to ShikshaHub</h1>
                <p>A free platform to share and access educational materials.</p>
                
                <!-- Statistics -->
                <div class="stats-section">
                    <div class="stat-item">
                        <div class="stat-number"><?php 
                            $count = mysqli_query($conn, "SELECT COUNT(*) as count FROM materials");
                            $row = mysqli_fetch_assoc($count);
                            echo $row['count'] ?? '0';
                        ?></div>
                        <div class="stat-label">Study Materials</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php 
                            $count = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
                            $row = mysqli_fetch_assoc($count);
                            echo $row['count'] ?? '0';
                        ?></div>
                        <div class="stat-label">Active Users</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">6</div>
                        <div class="stat-label">Recent Uploads</div>
                    </div>
                </div>

                <div class="hero-buttons">
                    <a href="materials.php" class="btn primary">
                        <i class="fas fa-search"></i> Browse Materials
                    </a>
                    <a href="upload.php" class="btn outline">
                        <i class="fas fa-cloud-upload-alt"></i> Upload Material
                    </a>
                </div>
            </div>

            <div class="hero-image">
                <img src="2.png" alt="Study Illustration">
            </div>
        </div>
    </section>

    <!-- Recent Materials Section - FIXED VERSION -->
    <section class="recent-section">
        <h2><i class="fas fa-history"></i> Recently Added Materials</h2>
        
        <div class="material-grid">
            <?php 
            if (is_array($recentMaterials)) {
                // Using test array
                foreach($recentMaterials as $row):
                    $upload_date = date("M d, Y", strtotime($row['uploaded_at']));
            ?>
            <div class="material-card">
                <div class="material-meta">
                    <span class="material-type"><?php echo htmlspecialchars($row['type']); ?></span>
                    <span class="material-author">
                        <i class="fas fa-user"></i> 
                        <?php echo htmlspecialchars($row['username']); ?>
                    </span>
                </div>
                
                <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                <p><?php echo htmlspecialchars($row['description']); ?></p>
                
                <div class="material-actions">
                    <a href="<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank" class="view-btn">
                        <i class="fas fa-eye"></i> View Material
                    </a>
                    <div class="material-info">
                        <span class="material-date">
                            <i class="far fa-calendar"></i> <?php echo $upload_date; ?>
                        </span>
                        <span class="material-size">
                            <i class="fas fa-file"></i> N/A
                        </span>
                    </div>
                </div>
            </div>
            <?php endforeach;
            } else {
                // Using mysqli result
                if(mysqli_num_rows($recentMaterials) > 0):
                    while($row = mysqli_fetch_assoc($recentMaterials)): 
                        $upload_date = date("M d, Y", strtotime($row['uploaded_at']));
                        $file_type = isset($row['type']) ? strtoupper($row['type']) : 'DOC';
            ?>
            <div class="material-card">
                <div class="material-meta">
                    <span class="material-type"><?php echo htmlspecialchars($file_type); ?></span>
                    <span class="material-author">
                        <i class="fas fa-user"></i> 
                        <?php echo htmlspecialchars($row['username'] ?? 'Anonymous'); ?>
                    </span>
                </div>
                
                <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                <p><?php echo htmlspecialchars(substr($row['description'] ?? 'No description available', 0, 120)) . '...'; ?></p>
                
                <div class="material-actions">
                    <a href="<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank" class="view-btn">
                        <i class="fas fa-eye"></i> View Material
                    </a>
                    <div class="material-info">
                        <span class="material-date">
                            <i class="far fa-calendar"></i> <?php echo $upload_date; ?>
                        </span>
                        <span class="material-size">
                            <i class="fas fa-file"></i> N/A
                        </span>
                    </div>
                </div>
            </div>
            <?php 
                    endwhile;
                else: 
            ?>
            <div class="empty-state">
                <i class="fas fa-book-open"></i>
                <h3>No materials uploaded yet</h3>
                <p>Be the first to share educational materials!</p>
                <a href="upload.php" class="view-btn" style="margin-top: 20px;">
                    <i class="fas fa-cloud-upload-alt"></i> Upload First Material
                </a>
            </div>
            <?php endif; } ?>
        </div>
        
        <div class="view-all-btn">
            <a href="materials.php">
                <i class="fas fa-arrow-right"></i> View All Materials
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <div class="logo">
                    <img src="logo.png" alt="ShikshaHub Logo" height="40">
                    <strong>ShikshaHub</strong>
                </div>
                <p>Empowering education through shared knowledge</p>
            </div>
            
            <div class="footer-section">
                <h3>Quick Links</h3>
                <a href="index.php"><i class="fas fa-home"></i> Home</a>
                <a href="materials.php"><i class="fas fa-book"></i> Browse Materials</a>
                <a href="upload.php"><i class="fas fa-cloud-upload-alt"></i> Upload Material</a>
                <a href="about.php"><i class="fas fa-info-circle"></i> About Us</a>
            </div>
            
            <div class="footer-section">
                <h3>Contact</h3>
                <p><i class="fas fa-envelope"></i> contact@shikshahub.com</p>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date("Y"); ?> ShikshaHub. All rights reserved.</p>
            <div>
                <a href="privacy.php">Privacy Policy</a> | 
                <a href="terms.php">Terms of Service</a> | 
                <a href="faq.php">FAQ</a>
            </div>
        </div>
    </footer>
</body>
</html>  