<?php
session_start();
include 'backend/db.php';

// Initialize variables
$search = $_GET['search'] ?? '';
$type   = $_GET['type'] ?? '';
$sort   = $_GET['sort'] ?? 'newest';
$subject = $_GET['subject'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12; // Materials per page
$offset = ($page - 1) * $limit;

// Escape inputs for security
$searchEscaped = mysqli_real_escape_string($conn, $search);
$typeEscaped   = mysqli_real_escape_string($conn, $type);
$subjectEscaped = mysqli_real_escape_string($conn, $subject);

// Initialize where conditions array
$conditions = [];

// Add search condition
if (!empty($search)) {
    $conditions[] = "(m.title LIKE '%$searchEscaped%' 
                     OR m.description LIKE '%$searchEscaped%' 
                     OR m.subject LIKE '%$searchEscaped%')";
}

// Add file type condition
if (!empty($type)) {
    $conditions[] = "m.file_path LIKE '%.$typeEscaped%'";
}

// Add subject filter
if (!empty($subject)) {
    $conditions[] = "m.subject = '$subjectEscaped'";
}

// Filter only approved materials
$conditions[] = "m.status = 'approved'";

// Build WHERE clause
$where = '';
if (!empty($conditions)) {
    $where = " WHERE " . implode(" AND ", $conditions);
}

// ===== FIXED COUNT QUERY =====
// Create a separate count query
$countSql = "SELECT COUNT(DISTINCT m.id) as total 
             FROM materials m 
             JOIN users u ON m.user_id = u.id
             $where";

$countResult = mysqli_query($conn, $countSql);
if (!$countResult) {
    die("Count query error: " . mysqli_error($conn));
}
$totalRows = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalRows / $limit);

// ===== MAIN QUERY =====
$sql = "SELECT m.*, u.username, u.profile_image, u.email,
               (SELECT COUNT(*) FROM downloads WHERE material_id = m.id) as download_count,
               (SELECT COUNT(*) FROM views WHERE material_id = m.id) as view_count
        FROM materials m 
        JOIN users u ON m.user_id = u.id
        $where";

// Add group by (to prevent duplicates if needed)
$sql .= " GROUP BY m.id";

// Add sorting - FIXED: Check if date column exists, use id if not
// First check what columns exist in materials table
$columns_result = mysqli_query($conn, "SHOW COLUMNS FROM materials");
$date_columns = ['uploaded_at', 'upload_date', 'date'];
$date_column_found = null;

while ($col = mysqli_fetch_assoc($columns_result)) {
    if (in_array($col['Field'], $date_columns)) {
        $date_column_found = $col['Field'];
        break;
    }
}

// Use found date column or default to id
$order_column = $date_column_found ? "m.$date_column_found" : "m.id";

// Add sorting
switch ($sort) {
    case 'popular':
        $sql .= " ORDER BY download_count DESC";
        break;
    case 'views':
        $sql .= " ORDER BY view_count DESC";
        break;
    case 'oldest':
        $sql .= " ORDER BY $order_column ASC";
        break;
    default: // newest
        $sql .= " ORDER BY $order_column DESC";
        break;
}

// Add pagination
$sql .= " LIMIT $limit OFFSET $offset";

// Execute main query
$result = mysqli_query($conn, $sql);
if (!$result) {
    die("Main query error: " . mysqli_error($conn));
}

// Get popular subjects for filter
$subjectsQuery = "SELECT subject, COUNT(*) as count FROM materials 
                  WHERE status='approved' 
                  GROUP BY subject 
                  HAVING subject IS NOT NULL AND subject != ''
                  ORDER BY count DESC 
                  LIMIT 10";
$subjectsResult = mysqli_query($conn, $subjectsQuery);
$popularSubjects = [];
if ($subjectsResult) {
    while ($subjectRow = mysqli_fetch_assoc($subjectsResult)) {
        $popularSubjects[] = $subjectRow;
    }
}

// Get stats
$statsQuery = "SELECT 
    (SELECT COUNT(*) FROM materials WHERE status='approved') as total_materials,
    (SELECT COUNT(DISTINCT user_id) FROM materials WHERE status='approved') as total_contributors,
    (SELECT COUNT(*) FROM downloads) as total_downloads";
$statsResult = mysqli_query($conn, $statsQuery);
$stats = $statsResult ? mysqli_fetch_assoc($statsResult) : [
    'total_materials' => 0,
    'total_contributors' => 0,
    'total_downloads' => 0
];

// Format file size
function formatSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}

// Function to get file preview URL
function getPreviewUrl($file_path, $file_name) {
    $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
    
    // For PDF files, use Google Docs Viewer
    if ($ext === 'pdf') {
        $encoded_url = urlencode("http://" . $_SERVER['HTTP_HOST'] . '/' . $file_path);
        return "https://docs.google.com/viewer?url=$encoded_url&embedded=true";
    }
    
    // For Office files, use Microsoft Office Online Viewer
    elseif (in_array($ext, ['doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx'])) {
        $encoded_url = urlencode("http://" . $_SERVER['HTTP_HOST'] . '/' . $file_path);
        return "https://view.officeapps.live.com/op/view.aspx?src=$encoded_url";
    }
    
    // For images, show directly
    elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
        return $file_path;
    }
    
    // For text files
    elseif ($ext === 'txt') {
        return $file_path;
    }
    
    // Default to direct file
    return $file_path;
}

// Function to get file icon
function getFileIcon($file_path) {
    $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
    
    $icons = [
        'pdf' => ['fas fa-file-pdf', '#f40f02', 'PDF'],
        'doc' => ['fas fa-file-word', '#2b579a', 'DOC'],
        'docx' => ['fas fa-file-word', '#2b579a', 'DOCX'],
        'ppt' => ['fas fa-file-powerpoint', '#d24726', 'PPT'],
        'pptx' => ['fas fa-file-powerpoint', '#d24726', 'PPTX'],
        'xls' => ['fas fa-file-excel', '#217346', 'XLS'],
        'xlsx' => ['fas fa-file-excel', '#217346', 'XLSX'],
        'txt' => ['fas fa-file-alt', '#3c3c3c', 'TXT'],
        'zip' => ['fas fa-file-archive', '#8a2be2', 'ZIP'],
        'rar' => ['fas fa-file-archive', '#8a2be2', 'RAR'],
        'jpg' => ['fas fa-file-image', '#4caf50', 'JPG'],
        'jpeg' => ['fas fa-file-image', '#4caf50', 'JPEG'],
        'png' => ['fas fa-file-image', '#4caf50', 'PNG'],
        'gif' => ['fas fa-file-image', '#4caf50', 'GIF']
    ];
    
    return $icons[$ext] ?? ['fas fa-file', '#6b7280', strtoupper($ext)];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Materials - ShikshaHub</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Browse thousands of free study materials, notes, and resources shared by students and educators on ShikshaHub.">
    <meta name="keywords" content="study materials, notes, pdf, ppt, doc, education, learning, free resources">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="https://cdn-icons-png.flaticon.com/512/2237/2237283.png">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* ===== VARIABLES & RESET ===== */
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #7209b7;
            --accent: #4cc9f0;
            --success: #4ade80;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #64748b;
            --border-radius: 12px;
            --shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f6f9fc 0%, #f1f5f9 100%);
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
        }

        /* ===== NAVBAR ===== */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
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
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            line-height: 1;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--dark);
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: var(--transition);
            position: relative;
        }

        .nav-links a:hover {
            color: var(--primary);
            background: rgba(67, 97, 238, 0.05);
        }

        .nav-links a.active {
            color: var(--primary);
            background: rgba(67, 97, 238, 0.1);
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: var(--transition);
        }

        .nav-links a:hover::after,
        .nav-links a.active::after {
            width: 80%;
        }

        /* User Profile Dropdown */
        .user-menu {
            position: relative;
        }

        .user-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 8px;
            transition: var(--transition);
        }

        .user-btn:hover {
            background: rgba(0, 0, 0, 0.05);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary);
        }

        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
            min-width: 200px;
            display: none;
            z-index: 1000;
        }

        .user-menu:hover .user-dropdown {
            display: block;
        }

        .user-dropdown a {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            text-decoration: none;
            color: var(--dark);
            transition: var(--transition);
        }

        .user-dropdown a:hover {
            background: var(--light);
        }

        .login-btn {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.3);
        }

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--dark);
            cursor: pointer;
        }

        /* ===== HERO SECTION ===== */
        .materials-hero {
            padding: 4rem 0;
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.1), rgba(114, 9, 183, 0.1));
            text-align: center;
        }

        .hero-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .materials-hero h1 {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 1rem;
        }

        .materials-hero p {
            font-size: 1.2rem;
            color: var(--gray);
            max-width: 600px;
            margin: 0 auto 2rem;
        }

        /* ===== STATS ===== */
        .stats-section {
            padding: 2rem 0;
            background: white;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .stat-card {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            text-align: center;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            line-height: 1;
        }

        .stat-label {
            color: var(--gray);
            margin-top: 0.5rem;
            font-size: 0.9rem;
        }

        /* ===== SEARCH & FILTERS ===== */
        .filters-section {
            padding: 2rem 0;
            background: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .filters-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .search-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .search-bar input[type="text"] {
            flex: 1;
            padding: 0.8rem 1.2rem;
            border: 2px solid rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
        }

        .search-bar input[type="text"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .search-bar button {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .search-bar button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(67, 97, 238, 0.3);
        }

        .filter-row {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .filter-group label {
            font-weight: 500;
            color: var(--dark);
        }

        .filter-group select {
            padding: 0.5rem 1rem;
            border: 2px solid rgba(0, 0, 0, 0.1);
            border-radius: 6px;
            background: white;
            color: var(--dark);
            font-size: 0.9rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .filter-group select:focus {
            outline: none;
            border-color: var(--primary);
        }

        .subject-chips {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        .subject-chip {
            background: white;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            color: var(--gray);
            cursor: pointer;
            transition: var(--transition);
            border: 1px solid rgba(0, 0, 0, 0.1);
            text-decoration: none;
        }

        .subject-chip:hover,
        .subject-chip.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        /* ===== MATERIALS GRID ===== */
        .materials-section {
            padding: 3rem 0;
        }

        .section-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .materials-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .materials-header h2 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
        }

        .results-info {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .materials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }

        .material-card {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: 1px solid rgba(0, 0, 0, 0.05);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .material-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            padding: 1.5rem 1.5rem 0.5rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .material-category {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .file-type {
            display: flex;
            align-items: center;
            gap: 5px;
            color: var(--gray);
            font-size: 0.9rem;
        }

        .file-type i {
            font-size: 1.2rem;
        }

        .card-body {
            padding: 1rem 1.5rem;
            flex-grow: 1;
        }

        .material-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.8rem;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .material-description {
            color: var(--gray);
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .material-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid rgba(0, 0, 0, 0.08);
            font-size: 0.85rem;
        }

        .uploader-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .uploader-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary);
        }

        .uploader-name {
            font-weight: 500;
            color: var(--dark);
        }

        .material-stats {
            display: flex;
            gap: 1rem;
            color: var(--gray);
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .card-footer {
            padding: 1rem 1.5rem;
            background: rgba(0, 0, 0, 0.02);
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            gap: 0.8rem;
        }

        .action-btn {
            flex: 1;
            padding: 0.6rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            text-align: center;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .download-btn {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }

        .download-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(67, 97, 238, 0.3);
        }

        .preview-btn {
            background: white;
            color: var(--primary);
            border: 2px solid rgba(67, 97, 238, 0.2);
        }

        .preview-btn:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        /* Preview Modal */
        .preview-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 2000;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }

        .modal-content {
            background: white;
            border-radius: var(--border-radius);
            width: 90%;
            max-width: 1200px;
            max-height: 90vh;
            overflow: hidden;
            position: relative;
        }

        .modal-header {
            padding: 1.5rem;
            background: var(--dark);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            font-size: 1.2rem;
            font-weight: 600;
        }

        .close-modal {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
            transition: var(--transition);
        }

        .close-modal:hover {
            color: var(--accent);
        }

        .modal-body {
            padding: 0;
            height: calc(90vh - 120px);
            overflow: hidden;
        }

        .preview-iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .preview-unavailable {
            padding: 3rem;
            text-align: center;
            color: var(--gray);
        }

        .preview-unavailable i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: var(--warning);
        }

        /* ===== PAGINATION ===== */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 3rem;
            flex-wrap: wrap;
        }

        .pagination-btn {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            background: white;
            color: var(--dark);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .pagination-btn:hover:not(:disabled) {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .pagination-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* ===== FOOTER ===== */
        footer {
            background: var(--dark);
            color: white;
            padding: 3rem 0 2rem;
            margin-top: 3rem;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-brand h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .footer-brand p {
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.8;
        }

        .footer-links h4,
        .footer-contact h4 {
            color: white;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .footer-links ul {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 0.5rem;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .footer-links a:hover {
            color: var(--accent);
            padding-left: 5px;
        }

        .footer-contact p {
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 0.8rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .social-link {
            width: 36px;
            height: 36px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: var(--transition);
        }

        .social-link:hover {
            background: var(--primary);
            transform: translateY(-3px);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.5);
        }

        .footer-bottom-links {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }

        .footer-bottom-links a {
            color: rgba(255, 255, 255, 0.5);
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-bottom-links a:hover {
            color: var(--accent);
        }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 3rem 1rem;
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--gray);
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-state h3 {
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: var(--gray);
            max-width: 400px;
            margin: 0 auto;
        }

        /* ===== RESPONSIVE DESIGN ===== */
        @media (max-width: 768px) {
            .menu-toggle {
                display: block;
            }
            
            .nav-links {
                position: fixed;
                top: 70px;
                left: -100%;
                width: 100%;
                height: calc(100vh - 70px);
                background: white;
                flex-direction: column;
                padding: 2rem;
                transition: var(--transition);
                box-shadow: var(--shadow);
            }
            
            .nav-links.active {
                left: 0;
            }
            
            .nav-container {
                padding: 0 1rem;
            }
            
            .materials-hero h1 {
                font-size: 2.2rem;
            }
            
            .search-bar {
                flex-direction: column;
            }
            
            .filter-row {
                flex-direction: column;
                gap: 0.8rem;
            }
            
            .materials-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .materials-grid {
                grid-template-columns: 1fr;
            }
            
            .footer-grid {
                grid-template-columns: 1fr;
            }
            
            .user-dropdown {
                position: fixed;
                top: auto;
                bottom: 0;
                left: 0;
                right: 0;
                border-radius: 20px 20px 0 0;
                display: none;
                animation: slideUp 0.3s ease;
            }
            
            .user-menu:hover .user-dropdown {
                display: block;
                animation: slideUp 0.3s ease;
            }
        }

        @media (max-width: 480px) {
            .hero-container,
            .section-container,
            .filters-container {
                padding: 0 1rem;
            }
            
            .card-footer {
                flex-direction: column;
            }
            
            .material-stats {
                flex-wrap: wrap;
                justify-content: center;
            }
        }

        /* ===== ANIMATIONS ===== */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideUp {
            from { transform: translateY(100%); }
            to { transform: translateY(0); }
        }

        .material-card {
            animation: fadeIn 0.6s ease-out;
        }

        /* ===== CUSTOM SCROLLBAR ===== */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--secondary));
        }
    </style>
</head>
<body>
    <!-- Preview Modal -->
    <div class="preview-modal" id="previewModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">File Preview</h3>
                <button class="close-modal" id="closeModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Preview content will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <!-- Logo -->
            <a href="index.php" class="logo-box">
                <img src="logo.png" class="logo-img" alt="ShikshaHub Logo">
                <div class="logo-text">ShikshaHub</div>
            </a>
            
            <!-- Mobile Menu Toggle -->
            <button class="menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <!-- Navigation Links -->
            <ul class="nav-links" id="navLinks">
                <li><a href="index.php">Home</a></li>
                <li><a href="materials.php" class="active">Materials</a></li>
                <li><a href="upload.php">Upload</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="user-menu">
                        <button class="user-btn">
                            <?php if(isset($_SESSION['profile_image']) && !empty($_SESSION['profile_image'])): ?>
                                <img src="<?php echo htmlspecialchars($_SESSION['profile_image']); ?>" 
                                     alt="Profile" 
                                     class="user-avatar">
                            <?php else: ?>
                                <div class="user-avatar" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                                    <?php echo strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            <span><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="user-dropdown">
                            <a href="profile.php">
                                <i class="fas fa-user"></i> My Profile
                            </a>
                            <a href="my_uploads.php">
                                <i class="fas fa-upload"></i> My Uploads
                            </a>
                            <a href="settings.php">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                            <a href="backend/logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </li>
                <?php else: ?>
                    <li><a href="login.html" class="login-btn">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="materials-hero">
        <div class="hero-container">
            <h1>Study Materials Library</h1>
            <p>Browse thousands of free educational resources shared by students and educators worldwide.</p>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['total_materials']); ?></div>
                <div class="stat-label">Study Materials</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['total_contributors']); ?></div>
                <div class="stat-label">Contributors</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['total_downloads']); ?>+</div>
                <div class="stat-label">Total Downloads</div>
            </div>
        </div>
    </section>

    <!-- Search & Filters -->
    <section class="filters-section">
        <div class="filters-container">
            <!-- Search Form -->
            <form method="GET" class="search-bar">
                <input type="text" 
                       name="search" 
                       placeholder="Search by title, description, or subject..." 
                       value="<?php echo htmlspecialchars($search, ENT_QUOTES); ?>">
                <button type="submit">
                    <i class="fas fa-search"></i> Search
                </button>
            </form>
            
            <!-- Filters -->
            <div class="filter-row">
                <div class="filter-group">
                    <label for="type"><i class="fas fa-file"></i> File Type:</label>
                    <select name="type" id="type" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        <option value="pdf" <?php if($type=='pdf') echo 'selected'; ?>>PDF</option>
                        <option value="ppt" <?php if($type=='ppt') echo 'selected'; ?>>PPT</option>
                        <option value="doc" <?php if($type=='doc') echo 'selected'; ?>>DOC</option>
                        <option value="docx" <?php if($type=='docx') echo 'selected'; ?>>DOCX</option>
                        <option value="pptx" <?php if($type=='pptx') echo 'selected'; ?>>PPTX</option>
                        <option value="txt" <?php if($type=='txt') echo 'selected'; ?>>TXT</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="sort"><i class="fas fa-sort"></i> Sort By:</label>
                    <select name="sort" id="sort" onchange="this.form.submit()">
                        <option value="newest" <?php if($sort=='newest') echo 'selected'; ?>>Newest First</option>
                        <option value="oldest" <?php if($sort=='oldest') echo 'selected'; ?>>Oldest First</option>
                        <option value="popular" <?php if($sort=='popular') echo 'selected'; ?>>Most Popular</option>
                        <option value="views" <?php if($sort=='views') echo 'selected'; ?>>Most Viewed</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="subject"><i class="fas fa-book"></i> Subject:</label>
                    <select name="subject" id="subject" onchange="this.form.submit()">
                        <option value="">All Subjects</option>
                        <?php foreach ($popularSubjects as $subj): ?>
                            <option value="<?php echo htmlspecialchars($subj['subject']); ?>" 
                                <?php if($subject==$subj['subject']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($subj['subject']); ?> (<?php echo $subj['count']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <!-- Quick Subject Chips -->
            <div class="subject-chips">
                <strong>Popular:</strong>
                <?php foreach ($popularSubjects as $subj): ?>
                    <a href="?subject=<?php echo urlencode($subj['subject']); ?>" 
                       class="subject-chip <?php if($subject==$subj['subject']) echo 'active'; ?>">
                        <?php echo htmlspecialchars($subj['subject']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Materials Grid -->
    <section class="materials-section">
        <div class="section-container">
            <div class="materials-header">
                <h2>Available Materials</h2>
                <div class="results-info">
                    Showing <?php echo min($limit, mysqli_num_rows($result)); ?> of <?php echo number_format($totalRows); ?> materials
                </div>
            </div>
            
            <div class="materials-grid">
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): 
                        $file = $row['file_path'];
                        $file_icon = getFileIcon($file);
                        $preview_url = getPreviewUrl($file, $row['title']);
                        $file_ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        $can_preview = in_array($file_ext, ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt', 'jpg', 'jpeg', 'png', 'gif']);
                    ?>
                        <div class="material-card">
                            <div class="card-header">
                                <span class="material-category"><?php echo htmlspecialchars($row['subject'] ?? 'General'); ?></span>
                                <div class="file-type" style="color: <?php echo $file_icon[1]; ?>">
                                    <i class="<?php echo $file_icon[0]; ?>"></i>
                                    <span><?php echo $file_icon[2]; ?></span>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <h3 class="material-title">
                                    <?php echo htmlspecialchars($row['title'], ENT_QUOTES); ?>
                                </h3>
                                
                                <?php if (!empty($row['description'])): ?>
                                    <p class="material-description">
                                        <?php echo htmlspecialchars(substr($row['description'], 0, 150), ENT_QUOTES); ?>
                                        <?php if (strlen($row['description']) > 150): ?>...<?php endif; ?>
                                    </p>
                                <?php endif; ?>
                                
                                <div class="material-meta">
                                    <div class="uploader-info">
                                        <?php if (!empty($row['profile_image'])): ?>
                                            <img src="<?php echo htmlspecialchars($row['profile_image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($row['username']); ?>" 
                                                 class="uploader-avatar"
                                                 onerror="this.src='data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 100 100\"><rect width=\"100\" height=\"100\" fill=\"%234361ee\"/><text x=\"50\" y=\"50\" font-family=\"Arial\" font-size=\"50\" fill=\"white\" text-anchor=\"middle\" dy=\".3em\"><?php echo strtoupper(substr($row['username'], 0, 1)); ?></text></svg>'">
                                        <?php else: ?>
                                            <div class="uploader-avatar" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                                                <?php echo strtoupper(substr($row['username'], 0, 1)); ?>
                                            </div>
                                        <?php endif; ?>
                                        <span class="uploader-name">
                                            <?php echo htmlspecialchars($row['username'], ENT_QUOTES); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="material-stats">
                                        <div class="stat-item">
                                            <i class="fas fa-eye"></i>
                                            <span><?php echo number_format($row['view_count'] ?? 0); ?></span>
                                        </div>
                                        <div class="stat-item">
                                            <i class="fas fa-download"></i>
                                            <span><?php echo number_format($row['download_count'] ?? 0); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-footer">
                                <a href="download.php?id=<?php echo $row['id']; ?>" class="action-btn download-btn">
                                    <i class="fas fa-download"></i> Download
                                </a>
                                <?php if ($can_preview): ?>
                                    <button class="action-btn preview-btn preview-trigger" 
                                            data-title="<?php echo htmlspecialchars($row['title'], ENT_QUOTES); ?>"
                                            data-url="<?php echo htmlspecialchars($preview_url); ?>"
                                            data-ext="<?php echo $file_ext; ?>">
                                        <i class="fas fa-eye"></i> Preview
                                    </button>
                                <?php else: ?>
                                    <a href="<?php echo $file; ?>" target="_blank" class="action-btn preview-btn">
                                        <i class="fas fa-external-link-alt"></i> View
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>No Materials Found</h3>
                        <p><?php echo !empty($search) ? 'Try adjusting your search terms or filters.' : 'Be the first to upload study materials!'; ?></p>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <a href="upload.php" class="login-btn" style="margin-top: 1rem; display: inline-block;">
                                <i class="fas fa-upload"></i> Upload Material
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo $type; ?>&sort=<?php echo $sort; ?>&subject=<?php echo urlencode($subject); ?>" 
                           class="pagination-btn">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php 
                    $start_page = max(1, $page - 2);
                    $end_page = min($totalPages, $start_page + 4);
                    
                    for ($i = $start_page; $i <= $end_page; $i++): 
                    ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo $type; ?>&sort=<?php echo $sort; ?>&subject=<?php echo urlencode($subject); ?>" 
                           class="pagination-btn <?php echo $i == $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo $type; ?>&sort=<?php echo $sort; ?>&subject=<?php echo urlencode($subject); ?>" 
                           class="pagination-btn">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <h3><i class="fas fa-graduation-cap"></i> ShikshaHub</h3>
                    <p>Empowering education through shared knowledge. Join our community to access and contribute to educational resources.</p>
                </div>
                
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
                        <li><a href="materials.php"><i class="fas fa-book"></i> Materials</a></li>
                        <li><a href="upload.php"><i class="fas fa-upload"></i> Upload</a></li>
                        <li><a href="#"><i class="fas fa-star"></i> Featured</a></li>
                    </ul>
                </div>
                
           
                
                <div class="footer-contact">
                    <h4>Contact Us</h4>
                    <p><i class="fas fa-envelope"></i> contact@shikshahub.com</p>
                    <p><i class="fas fa-phone"></i>(+977) 9841234343</p>
                    
                    <div class="social-links">
                        <a href="#" class="social-link" title="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-link" title="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="social-link" title="LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="#" class="social-link" title="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>© <?php echo date('Y'); ?> ShikshaHub. All rights reserved.</p>
                <div class="footer-bottom-links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="#">Cookie Policy</a>
                    <a href="#">FAQ</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // Mobile Menu Toggle
        const menuToggle = document.getElementById('menuToggle');
        const navLinks = document.getElementById('navLinks');
        
        if (menuToggle) {
            menuToggle.addEventListener('click', () => {
                navLinks.classList.toggle('active');
                menuToggle.innerHTML = navLinks.classList.contains('active') 
                    ? '<i class="fas fa-times"></i>' 
                    : '<i class="fas fa-bars"></i>';
            });
            
            // Close menu when clicking outside
            document.addEventListener('click', (e) => {
                if (!navLinks.contains(e.target) && !menuToggle.contains(e.target)) {
                    navLinks.classList.remove('active');
                    menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
                }
            });
        }
        
        // Preview Modal Functionality
        const previewModal = document.getElementById('previewModal');
        const closeModal = document.getElementById('closeModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalBody = document.getElementById('modalBody');
        const previewTriggers = document.querySelectorAll('.preview-trigger');
        
        // Open preview modal
        previewTriggers.forEach(trigger => {
            trigger.addEventListener('click', function() {
                const title = this.getAttribute('data-title');
                const url = this.getAttribute('data-url');
                const ext = this.getAttribute('data-ext');
                
                modalTitle.textContent = title;
                
                // Clear previous content
                modalBody.innerHTML = '';
                
                // Check file type and create appropriate preview
                if (['pdf', 'doc', 'docx', 'ppt', 'pptx'].includes(ext)) {
                    // Use iframe for documents
                    const iframe = document.createElement('iframe');
                    iframe.className = 'preview-iframe';
                    iframe.src = url;
                    iframe.title = title;
                    modalBody.appendChild(iframe);
                } else if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) {
                    // Use image tag for images
                    const img = document.createElement('img');
                    img.src = url;
                    img.alt = title;
                    img.style.width = '100%';
                    img.style.height = '100%';
                    img.style.objectFit = 'contain';
                    modalBody.appendChild(img);
                } else if (ext === 'txt') {
                    // Use fetch to load text file
                    fetch(url)
                        .then(response => response.text())
                        .then(text => {
                            const pre = document.createElement('pre');
                            pre.style.padding = '2rem';
                            pre.style.whiteSpace = 'pre-wrap';
                            pre.textContent = text.substring(0, 5000) + (text.length > 5000 ? '\n\n... (truncated)' : '');
                            modalBody.appendChild(pre);
                        })
                        .catch(() => {
                            modalBody.innerHTML = `
                                <div class="preview-unavailable">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <h3>Unable to load preview</h3>
                                    <p>Please download the file to view its contents.</p>
                                </div>
                            `;
                        });
                }
                
                // Show modal
                previewModal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            });
        });
        
        // Close preview modal
        closeModal.addEventListener('click', () => {
            previewModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        });
        
        // Close modal when clicking outside
        previewModal.addEventListener('click', (e) => {
            if (e.target === previewModal) {
                previewModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && previewModal.style.display === 'flex') {
                previewModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });
        
        // Auto-submit filter changes
        document.querySelectorAll('select').forEach(select => {
            select.addEventListener('change', function() {
                // Preserve all GET parameters
                const url = new URL(window.location);
                url.searchParams.set(this.name, this.value);
                
                // Remove page parameter when changing filters
                if (this.name !== 'page') {
                    url.searchParams.delete('page');
                }
                
                window.location.href = url.toString();
            });
        });
        
        // Track downloads
        document.querySelectorAll('.download-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const materialId = this.href.split('id=')[1];
                if (materialId) {
                    // Send analytics (in a real app, this would be an API call)
                    console.log('Download tracked:', materialId);
                    
                    // Show loading state
                    const originalHTML = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Downloading...';
                    this.style.pointerEvents = 'none';
                    
                    // Reset after 3 seconds if still on page
                    setTimeout(() => {
                        this.innerHTML = originalHTML;
                        this.style.pointerEvents = 'auto';
                    }, 3000);
                }
            });
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Ctrl + F to focus search
            if (e.ctrlKey && e.key === 'f') {
                e.preventDefault();
                document.querySelector('input[name="search"]').focus();
            }
            
            // Esc to close menu
            if (e.key === 'Escape' && navLinks.classList.contains('active')) {
                navLinks.classList.remove('active');
                menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
            }
        });
        
        // Load user data from session
        <?php if(isset($_SESSION['user_id'])): ?>
            const userMenu = document.querySelector('.user-menu');
            if (userMenu) {
                userMenu.addEventListener('mouseenter', () => {
                    userMenu.querySelector('.user-dropdown').style.display = 'block';
                });
                
                userMenu.addEventListener('mouseleave', () => {
                    userMenu.querySelector('.user-dropdown').style.display = 'none';
                });
            }
        <?php endif; ?>
        
        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Add loading animation
        const style = document.createElement('style');
        style.textContent = `
            .fa-spin {
                animation: fa-spin 1s linear infinite;
            }
            
            @keyframes fa-spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            
            @media (max-width: 768px) {
                .user-dropdown {
                    animation: slideUp 0.3s ease;
                }
            }
        `;
        document.head.appendChild(style);
        
        // Show notifications from URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('uploaded') && urlParams.get('uploaded') === 'success') {
            showNotification('success', 'Material uploaded successfully!');
        }
        if (urlParams.has('error')) {
            showNotification('error', urlParams.get('error'));
        }
        
        // Notification function
        function showNotification(type, message) {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 100px;
                right: 20px;
                padding: 1rem 1.5rem;
                border-radius: 10px;
                color: white;
                font-weight: 500;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
                z-index: 2000;
                animation: slideIn 0.3s ease-out;
                display: flex;
                align-items: center;
                gap: 10px;
                max-width: 400px;
                background: ${type === 'success' ? 'linear-gradient(135deg, #4ade80, #22c55e)' : 
                          'linear-gradient(135deg, #ef4444, #dc2626)'};
            `;
            
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            notification.innerHTML = `<i class="fas ${icon}"></i><span>${message}</span>`;
            
            document.body.appendChild(notification);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                notification.style.animation = 'slideIn 0.3s ease-out reverse';
                setTimeout(() => notification.remove(), 300);
            }, 5000);
        }
        
        // Add slideIn animation
        const animationStyle = document.createElement('style');
        animationStyle.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(animationStyle);
    </script>
</body>
</html>

<?php
// Close database connection
mysqli_close($conn);
?>