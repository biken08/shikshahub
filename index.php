<?php
session_start();
include 'backend/db.php';

// DEBUG: Always show errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch user details if logged in
$user_data = null;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_query = mysqli_query($conn, "SELECT id, username, email, role, profile_image FROM users WHERE id = '$user_id'");
    if ($user_query && mysqli_num_rows($user_query) > 0) {
        $user_data = mysqli_fetch_assoc($user_query);
    }
}

// Get recent materials from database
$recentMaterials = mysqli_query(
    $conn,
    "SELECT materials.*, users.username 
     FROM materials 
     LEFT JOIN users ON materials.user_id = users.id 
     ORDER BY uploaded_at DESC LIMIT 6"
);

// Get total materials count
$total_materials = 0;
$materials_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM materials");
if ($materials_count) {
    $row = mysqli_fetch_assoc($materials_count);
    $total_materials = $row['count'] ?? '0';
}

// Get categories for quick links
$popular_subjects = [
    'BHM' => ['icon' => 'fas fa-hotel', 'name' => 'Hotel Management'],
    'BCA' => ['icon' => 'fas fa-laptop-code', 'name' => 'Computer Applications'],
    'Engineering' => ['icon' => 'fas fa-cogs', 'name' => 'Engineering'],
    'Business' => ['icon' => 'fas fa-chart-line', 'name' => 'Business Studies']
];

// If no recent materials, create dummy data for testing
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
        $recentArray[] = $item;
    }
    $recentMaterials = $recentArray;
    $is_test_data = true;
} else {
    $is_test_data = false;
    // Convert mysqli result to array
    $materials_array = [];
    while($row = mysqli_fetch_assoc($recentMaterials)) {
        $materials_array[] = $row;
    }
    $recentMaterials = $materials_array;
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
    <style>
        /* Additional CSS for profile dropdown and new hero elements */
        .user-dropdown {
            position: relative;
            display: inline-block;
        }
        
        .user-profile-btn {
            background: #ffffff;
            color: #2563eb;
            border: none;
            padding: 10px 15px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        
        .user-profile-btn:hover {
            background: #f0f9ff;
            transform: translateY(-2px);
        }
        
        .user-profile-btn i.fa-chevron-down {
            font-size: 12px;
            transition: transform 0.3s ease;
        }
        
        .user-dropdown:hover .user-profile-btn i.fa-chevron-down {
            transform: rotate(180deg);
        }
        
        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            min-width: 200px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            border-radius: 8px;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            margin-top: 10px;
            overflow: hidden;
        }
        
        .user-dropdown:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            color: #1f2937;
            text-decoration: none;
            transition: background 0.3s ease;
            border-bottom: 1px solid #f3f4f6;
            font-size: 14px;
        }
        
        .dropdown-item:last-child {
            border-bottom: none;
        }
        
        .dropdown-item:hover {
            background: #f8f9fa;
        }
        
        .dropdown-item i {
            width: 20px;
            color: #6b7280;
        }
        
        .dropdown-item.logout {
            color: #dc2626;
        }
        
        .dropdown-item.logout:hover {
            background: #fef2f2;
        }
        
        .dropdown-item.logout i {
            color: #dc2626;
        }
        
        /* New Hero Elements - Replacing Statistics */
        .hero-highlights {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin: 30px 0;
            max-width: 600px;
        }
        
        .highlight-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        
        .highlight-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.15);
        }
        
        .highlight-title {
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .highlight-title i {
            font-size: 20px;
            color: #93c5fd;
        }
        
        .highlight-desc {
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
            line-height: 1.5;
        }
        
        /* Quick Subjects Grid */
        .quick-subjects {
            margin-top: 30px;
            max-width: 600px;
        }
        
        .quick-subjects h3 {
            color: white;
            font-size: 18px;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .subject-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        
        .subject-item {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 12px 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .subject-item:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }
        
        .subject-item i {
            font-size: 16px;
            color: #93c5fd;
        }
        
        .subject-item span {
            font-size: 14px;
            font-weight: 500;
        }
        
        /* Features Section */
        .features-section {
            padding: 80px 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .features-container {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }
        
        .features-section h2 {
            font-size: 42px;
            margin-bottom: 15px;
            font-weight: 700;
        }
        
        .features-section > .features-container > p {
            font-size: 18px;
            opacity: 0.9;
            margin-bottom: 50px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }
        
        .feature-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 40px 30px;
            border-radius: 16px;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }
        
        .feature-icon {
            font-size: 50px;
            margin-bottom: 25px;
            color: white;
            transition: transform 0.3s ease;
        }
        
        .feature-card:hover .feature-icon {
            transform: scale(1.1);
        }
        
        .feature-card h3 {
            font-size: 24px;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .feature-card p {
            font-size: 16px;
            opacity: 0.8;
            line-height: 1.6;
        }
        
        /* Feature card specific colors */
        .feature-card:nth-child(1)::before { background: linear-gradient(90deg, #3b82f6, #06b6d4); }
        .feature-card:nth-child(2)::before { background: linear-gradient(90deg, #10b981, #3b82f6); }
        .feature-card:nth-child(3)::before { background: linear-gradient(90deg, #8b5cf6, #3b82f6); }
        .feature-card:nth-child(4)::before { background: linear-gradient(90deg, #ef4444, #f97316); }
        
        /* Call to Action */
        .cta-section {
            padding: 80px 40px;
            background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .cta-container {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }
        
        .cta-section h2 {
            font-size: 42px;
            margin-bottom: 20px;
            font-weight: 700;
        }
        
        .cta-section p {
            font-size: 18px;
            opacity: 0.9;
            margin-bottom: 40px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }
        
        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .cta-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 16px 32px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 16px;
            cursor: pointer;
            border: 2px solid transparent;
            min-width: 180px;
        }
        
        .cta-btn.primary {
            background: #2563eb;
            color: white;
        }
        
        .cta-btn.primary:hover {
            background: #1d4ed8;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
        }
        
        .cta-btn.outline {
            border: 2px solid white;
            color: white;
            background: transparent;
        }
        
        .cta-btn.outline:hover {
            background: white;
            color: #1f2937;
            transform: translateY(-3px);
        }
        
        /* Platform Stats Bar - New Section */
        .stats-bar {
            background: #f8fafc;
            padding: 40px 20px;
            text-align: center;
        }
        
        .stats-container {
            max-width: 800px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }
        
        .stat-box {
            padding: 25px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
        }
        
        .stat-box:hover {
            transform: translateY(-5px);
        }
        
        .stat-box i {
            font-size: 40px;
            color: #2563eb;
            margin-bottom: 15px;
        }
        
        .stat-box .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 5px;
        }
        
        .stat-box .stat-label {
            font-size: 14px;
            color: #6b7280;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        @media (max-width: 768px) {
            .features-section h2,
            .cta-section h2 {
                font-size: 32px;
            }
            
            .features-section,
            .cta-section {
                padding: 60px 20px;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .user-profile-btn span {
                display: none;
            }
            
            .dropdown-menu {
                position: fixed;
                top: auto;
                bottom: 70px;
                left: 20px;
                right: 20px;
                margin-top: 0;
            }
            
            .subject-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .hero-highlights {
                gap: 15px;
            }
            
            .highlight-card {
                padding: 15px;
            }
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
            <?php if ($user_data): ?>
                <div class="user-dropdown">
                    <button class="user-profile-btn">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo htmlspecialchars($user_data['username']); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu">
                        <a href="profile.php" class="dropdown-item">
                            <i class="fas fa-user"></i> My Profile
                        </a>
                        <a href="my-uploads.php" class="dropdown-item">
                            <i class="fas fa-cloud-upload-alt"></i> My Uploads
                        </a>
                        <?php if (isset($user_data['role']) && $user_data['role'] == 'admin'): ?>
                        <a href="admin/dashboard.php" class="dropdown-item">
                            <i class="fas fa-crown"></i> Admin Panel
                        </a>
                        <?php endif; ?>
                        <a href="settings.php" class="dropdown-item">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                        <a href="backend/logout.php" class="dropdown-item logout">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="login.html" class="btn-login"><i class="fas fa-sign-in-alt"></i> Login</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Hero Section with New Elements -->
    <section class="hero">
        <div class="hero-content">
            <div class="hero-text">
                <h1>Welcome to ShikshaHub</h1>
                <p>A free platform to share and access educational materials.</p>
                
                <!-- New Highlights Section (Replacing Statistics) -->
                <div class="hero-highlights">
                    <div class="highlight-card">
                        <div class="highlight-title">
                            <i class="fas fa-rocket"></i>
                            <h4>Quick & Easy Sharing</h4>
                        </div>
                        <p class="highlight-desc">
                            Upload your study materials in seconds. No complex forms, just share and help others learn.
                        </p>
                    </div>
                    
                    <div class="highlight-card">
                        <div class="highlight-title">
                            <i class="fas fa-search"></i>
                            <h4>Smart Discovery</h4>
                        </div>
                        <p class="highlight-desc">
                            Find exactly what you need with our powerful search and filtering system.
                        </p>
                    </div>
                    
                    <div class="highlight-card">
                        <div class="highlight-title">
                            <i class="fas fa-users"></i>
                            <h4>Community Driven</h4>
                        </div>
                        <p class="highlight-desc">
                            Join thousands of students and educators building the largest educational resource library.
                        </p>
                    </div>
                </div>

                <!-- Quick Subjects Access -->
                <div class="quick-subjects">
                    <h3>Quick Access to Popular Subjects:</h3>
                    <div class="subject-grid">
                        <?php foreach($popular_subjects as $code => $subject): ?>
                        <a href="materials.php?q=<?php echo urlencode($subject['name']); ?>" class="subject-item">
                            <i class="<?php echo $subject['icon']; ?>"></i>
                            <span><?php echo $subject['name']; ?></span>
                        </a>
                        <?php endforeach; ?>
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

    <!-- Platform Stats Bar -->
    <section class="stats-bar">
        <div class="stats-container">
            <div class="stat-box">
                <i class="fas fa-book-open"></i>
                <div class="stat-number"><?php echo $total_materials; ?>+</div>
                <div class="stat-label">Study Resources</div>
            </div>
            
            <div class="stat-box">
                <i class="fas fa-clock"></i>
                <div class="stat-number">24/7</div>
                <div class="stat-label">Access Available</div>
            </div>
            
            <div class="stat-box">
                <i class="fas fa-lock-open"></i>
                <div class="stat-number">100% Free</div>
                <div class="stat-label">No Hidden Charges</div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="features-container">
            <h2>Why Choose ShikshaHub?</h2>
            <p>Experience the best platform for educational resource sharing</p>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <h3>Easy Upload</h3>
                    <p>Upload your study materials in seconds with our simple interface</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Smart Search</h3>
                    <p>Find exactly what you need with our powerful search filters</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>Mobile Friendly</h3>
                    <p>Access materials anytime, anywhere on any device</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Safe & Secure</h3>
                    <p>Your data and privacy are our top priority</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Recent Materials Section -->
    <section class="recent-section">
        <h2><i class="fas fa-history"></i> Recently Added Materials</h2>
        
        <div class="material-grid">
            <?php 
            if (count($recentMaterials) > 0):
                foreach($recentMaterials as $row): 
                    $upload_date = isset($row['uploaded_at']) ? date("M d, Y", strtotime($row['uploaded_at'])) : date("M d, Y");
                    $file_type = isset($row['type']) ? strtoupper($row['type']) : 'DOC';
                    $display_name = isset($row['username']) ? htmlspecialchars($row['username']) : 'Anonymous';
            ?>
            <div class="material-card">
                <div class="material-meta">
                    <span class="material-type"><?php echo htmlspecialchars($file_type); ?></span>
                    <span class="material-author">
                        <i class="fas fa-user"></i> 
                        <?php echo $display_name; ?>
                    </span>
                </div>
                
                <h3><?php echo htmlspecialchars($row['title'] ?? 'Untitled'); ?></h3>
                <p><?php 
                    $description = $row['description'] ?? 'No description available';
                    echo htmlspecialchars(substr($description, 0, 120));
                    if (strlen($description) > 120) echo '...';
                ?></p>
                
                <div class="material-actions">
                 <a href="download.php?id=<?= $row['id'] ?>" target="_blank" class="view-btn">
    <i class="fas fa-eye"></i> View
</a>

                    <div class="material-info">
                        <span class="material-date">
                            <i class="far fa-calendar"></i> <?php echo $upload_date; ?>
                        </span>
                        <span class="material-size">
                            <i class="fas fa-file"></i> <?php echo $file_type; ?>
                        </span>
                    </div>
                </div>
            </div>
            <?php 
                endforeach;
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
            <?php endif; ?>
        </div>
        
        <div class="view-all-btn">
            <a href="materials.php">
                <i class="fas fa-arrow-right"></i> View All Materials
            </a>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section">
        <div class="cta-container">
            <h2>Start Sharing Knowledge Today</h2>
            <p>Join thousands of students and educators sharing valuable educational resources.</p>
            <div class="cta-buttons">
                <?php if ($user_data): ?>
                    <a href="upload.php" class="cta-btn primary">
                        <i class="fas fa-cloud-upload-alt"></i> Upload Material
                    </a>
                    <a href="materials.php" class="cta-btn outline">
                        <i class="fas fa-search"></i> Browse Resources
                    </a>
                <?php else: ?>
                    <a href="register.html" class="cta-btn primary">
                        <i class="fas fa-user-plus"></i> Join Free
                    </a>
                    <a href="login.html" class="cta-btn outline">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                <?php endif; ?>
            </div>
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

    <script>
        // Scroll to Top Button
        const scrollBtn = document.createElement('button');
        scrollBtn.className = 'scroll-top';
        scrollBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
        document.body.appendChild(scrollBtn);
        
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                scrollBtn.classList.add('visible');
            } else {
                scrollBtn.classList.remove('visible');
            }
        });
        
        scrollBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
        
        // User dropdown hover effect
        const userDropdown = document.querySelector('.user-dropdown');
        if (userDropdown) {
            userDropdown.addEventListener('mouseenter', () => {
                userDropdown.querySelector('.dropdown-menu').style.display = 'block';
            });
            
            userDropdown.addEventListener('mouseleave', () => {
                userDropdown.querySelector('.dropdown-menu').style.display = 'none';
            });
        }
        
        // Make scroll top button visible
        window.addEventListener('DOMContentLoaded', () => {
            const scrollTopBtn = document.querySelector('.scroll-top');
            if (scrollTopBtn) {
                scrollTopBtn.style.opacity = '0';
                scrollTopBtn.style.visibility = 'hidden';
                scrollTopBtn.style.transition = 'all 0.3s';
            }
        });
        
        // Animate stats on scroll
        const observerOptions = {
            threshold: 0.5
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const statBoxes = document.querySelectorAll('.stat-box');
                    statBoxes.forEach((box, index) => {
                        setTimeout(() => {
                            box.style.opacity = '1';
                            box.style.transform = 'translateY(0)';
                        }, index * 200);
                    });
                }
            });
        }, observerOptions);
        
        const statsBar = document.querySelector('.stats-bar');
        if (statsBar) {
            observer.observe(statsBar);
        }
    </script>
</body>
</html>