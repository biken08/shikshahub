<?php
// header.php - Complete header with absolute paths and correct session variables

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define base URL (change 'shikshahub' to your actual project folder if needed)
define('BASE_URL', '/shikshahub');

// Check if user is logged in and get correct session data
$is_logged_in = isset($_SESSION['user_id']);
$user_id      = $_SESSION['user_id'] ?? null;
$fullname     = $_SESSION['fullname'] ?? 'Guest';
$user_role    = $_SESSION['role'] ?? 'guest';
$department   = $_SESSION['department'] ?? '';

// Set page title if not already set
if (!isset($page_title)) {
    $page_title = 'ShikshaHub';
}

// Get current page filename (for active link highlighting)
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - ShikshaHub</title>
    
    <!-- Favicon -->
    <link rel="icon" href="<?php echo BASE_URL; ?>/logo.png" type="image/x-icon">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Embedded CSS (your existing styles, unchanged) -->
    <style>
        /* ====== GLOBAL STYLES ====== */
        :root {
            --primary: #3498db;
            --secondary: #2c3e50;
            --accent: #e74c3c;
            --light: #ecf0f1;
            --dark: #34495e;
            --success: #27ae60;
            --warning: #f39c12;
            --danger: #e74c3c;
            --gray: #95a5a6;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .content-wrapper {
            flex: 1;
            padding: 20px;
        }
        
        /* ====== NAVBAR STYLES ====== */
        .navbar {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--dark) 100%);
            color: white;
            padding: 0;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .nav-container {
            max-width: 1300px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }
        
        /* Logo */
        .logo-link {
            text-decoration: none;
            color: white;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 24px;
            font-weight: 700;
        }
        
        .logo i {
            color: var(--primary);
            font-size: 28px;
        }
        
        /* Navigation Links */
        .nav-links {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            transform: translateY(-2px);
        }
        
        .nav-link.active {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
        }
        
        /* Dropdown Menus */
        .nav-dropdown {
            position: relative;
        }
        
        .dropdown-toggle {
            position: relative;
        }
        
        .dropdown-icon {
            font-size: 12px;
            transition: transform 0.3s ease;
        }
        
        .nav-dropdown:hover .dropdown-icon {
            transform: rotate(180deg);
        }
        
        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            min-width: 220px;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.3s ease;
            z-index: 1001;
            overflow: hidden;
        }
        
        .nav-dropdown:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 20px;
            color: #333;
            text-decoration: none;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }
        
        .dropdown-item:hover {
            background: #f8f9fa;
            color: var(--primary);
            border-left-color: var(--primary);
            padding-left: 25px;
        }
        
        .dropdown-item i {
            width: 20px;
            text-align: center;
            color: #6c757d;
        }
        
        .dropdown-item:hover i {
            color: var(--primary);
        }
        
        .dropdown-divider {
            height: 1px;
            background: #e9ecef;
            margin: 5px 0;
        }
        
        /* User Avatar */
        .user-avatar-link {
            padding: 5px 15px;
            gap: 12px;
        }
        
        .user-avatar-small {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, #9b59b6 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
        }
        
        .username {
            max-width: 120px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .logout-item {
            color: var(--danger) !important;
        }
        
        .logout-item:hover {
            color: white !important;
            background: var(--danger) !important;
        }
        
        .logout-item:hover i {
            color: white !important;
        }
        
        .admin-link {
            color: var(--success) !important;
        }
        
        .admin-link:hover {
            color: white !important;
            background: var(--success) !important;
        }
        
        /* Mobile Menu */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 10px;
        }
        
        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, var(--primary) 0%, #2980b9 100%);
            color: white;
            padding: 60px 0;
            margin-bottom: 30px;
        }
        
        .page-header .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            text-align: center;
        }
        
        .page-header h1 {
            font-size: 42px;
            margin-bottom: 15px;
        }
        
        .page-subtitle {
            font-size: 18px;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }
        
        /* Container for page content */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .nav-links {
                position: fixed;
                top: 70px;
                left: 0;
                width: 100%;
                background: var(--secondary);
                flex-direction: column;
                padding: 20px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                display: none;
                z-index: 999;
            }
            
            .nav-links.active {
                display: flex;
            }
            
            .nav-link {
                width: 100%;
                justify-content: flex-start;
                padding: 15px 20px;
            }
            
            .nav-dropdown {
                width: 100%;
            }
            
            .dropdown-menu {
                position: static;
                width: 100%;
                box-shadow: none;
                background: rgba(0,0,0,0.1);
                opacity: 1;
                visibility: visible;
                transform: none;
                display: none;
            }
            
            .nav-dropdown:hover .dropdown-menu {
                display: block;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .page-header h1 {
                font-size: 32px;
            }
            
            .content-wrapper {
                padding: 15px;
            }
        }
        
        @media (max-width: 576px) {
            .nav-container {
                padding: 0 15px;
            }
            
            .logo span {
                font-size: 20px;
            }
            
            .logo i {
                font-size: 24px;
            }
            
            .page-header {
                padding: 40px 0;
            }
            
            .page-header h1 {
                font-size: 28px;
            }
            
            .page-subtitle {
                font-size: 16px;
            }
            
            .container {
                padding: 0 15px;
            }
        }
    </style>
    
    <!-- Additional Page-Specific CSS -->
    <?php if (isset($additional_css)): ?>
        <style><?php echo $additional_css; ?></style>
    <?php endif; ?>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-container">
            <!-- Logo (absolute path) -->
            <div class="nav-brand">
                <a href="<?php echo BASE_URL; ?>/index.php" class="logo-link">
                    <div class="logo">
                        <i class="fas fa-graduation-cap"></i>
                        <span>ShikshaHub</span>
                    </div>
                </a>
            </div>
            
            <!-- Mobile Menu Toggle -->
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>
            
            <!-- Navigation Links (all absolute) -->
            <div class="nav-links" id="navLinks">
                <!-- Home -->
                <a href="<?php echo BASE_URL; ?>/index.php" class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
                
                <!-- Study Materials -->
                <a href="<?php echo BASE_URL; ?>/materials.php" class="nav-link <?php echo $current_page == 'materials.php' ? 'active' : ''; ?>">
                    <i class="fas fa-book"></i>
                    <span>Materials</span>
                </a>
                
                <!-- Upload (only for logged in users) – teachers & admins -->
                <?php if ($is_logged_in && in_array($user_role, ['teacher', 'admin'])): ?>
                <a href="<?php echo BASE_URL; ?>/teacher/upload.php" class="nav-link <?php echo $current_page == 'upload.php' ? 'active' : ''; ?>">
                    <i class="fas fa-upload"></i>
                    <span>Upload</span>
                </a>
                <?php endif; ?>
                
                <!-- Admin Panel (only for admins) -->
                <?php if ($user_role === 'admin'): ?>
                <div class="nav-dropdown">
                    <a href="#" class="nav-link dropdown-toggle">
                        <i class="fas fa-user-shield"></i>
                        <span>Admin Panel</span>
                        <i class="fas fa-chevron-down dropdown-icon"></i>
                    </a>
                    <div class="dropdown-menu">
                        <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="dropdown-item">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a href="<?php echo BASE_URL; ?>/admin/users.php" class="dropdown-item">
                            <i class="fas fa-users"></i> User Management
                        </a>
                        <a href="<?php echo BASE_URL; ?>/admin/approve.php" class="dropdown-item">
                            <i class="fas fa-check-circle"></i> Approve Content
                        </a>
                        <a href="<?php echo BASE_URL; ?>/admin/delete.php" class="dropdown-item">
                            <i class="fas fa-trash"></i> Delete Content
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- User Dropdown -->
                <div class="nav-dropdown user-dropdown">
                    <a href="#" class="nav-link user-avatar-link">
                        <div class="user-avatar-small">
                            <?php echo strtoupper(substr($fullname, 0, 1)); ?>
                        </div>
                        <span class="username"><?php echo htmlspecialchars($fullname); ?></span>
                        <i class="fas fa-chevron-down dropdown-icon"></i>
                    </a>
                    <div class="dropdown-menu user-menu">
                        <?php if ($is_logged_in): ?>
                            <a href="<?php echo BASE_URL; ?>/profile.php" class="dropdown-item">
                                <i class="fas fa-user"></i> My Profile
                            </a>
                            <a href="<?php echo BASE_URL; ?>/settings.php" class="dropdown-item">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                            <div class="dropdown-divider"></div>
                            <?php if ($user_role === 'admin'): ?>
                                <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="dropdown-item admin-link">
                                    <i class="fas fa-shield-alt"></i> Admin Dashboard
                                </a>
                                <div class="dropdown-divider"></div>
                            <?php endif; ?>
                            <a href="<?php echo BASE_URL; ?>/backend/logout.php" class="dropdown-item logout-item">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        <?php else: ?>
                            <a href="<?php echo BASE_URL; ?>/login.html" class="dropdown-item">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                            <a href="<?php echo BASE_URL; ?>/register.html" class="dropdown-item">
                                <i class="fas fa-user-plus"></i> Register
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Mobile Menu Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const navLinks = document.getElementById('navLinks');
            
            if (mobileMenuBtn && navLinks) {
                mobileMenuBtn.addEventListener('click', function() {
                    navLinks.classList.toggle('active');
                    mobileMenuBtn.innerHTML = navLinks.classList.contains('active') 
                        ? '<i class="fas fa-times"></i>' 
                        : '<i class="fas fa-bars"></i>';
                });
                
                // Close mobile menu when clicking outside
                document.addEventListener('click', function(event) {
                    if (!event.target.closest('.nav-container') && navLinks.classList.contains('active')) {
                        navLinks.classList.remove('active');
                        mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
                    }
                });
            }
        });
    </script>
    
    <!-- Main Content Container -->
    <div class="content-wrapper">
        <!-- Page Header (optional) -->
        <?php if (isset($show_page_header) && $show_page_header): ?>
        <div class="page-header">
            <div class="container">
                <h1><?php echo htmlspecialchars($page_title); ?></h1>
                <?php if (isset($page_subtitle)): ?>
                    <p class="page-subtitle"><?php echo htmlspecialchars($page_subtitle); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>