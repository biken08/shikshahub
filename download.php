<?php
session_start();
include 'backend/db.php';

// DEBUG: Show errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_GET['id'])) {
    die("Invalid request. No file ID provided.");
}

$id = (int) $_GET['id'];

// Validate ID
if ($id <= 0) {
    die("Invalid file ID.");
}

// Get file information from database - ONLY EXISTING COLUMNS
$stmt = mysqli_prepare($conn, "SELECT id, file_path, title, type FROM materials WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    die("File not found or has been removed.");
}

$row = mysqli_fetch_assoc($result);
$db_path = $row['file_path'];
$file_title = $row['title'] ?? 'Untitled Document';
$file_type = $row['type'] ?? 'Unknown';

// Log for debugging
error_log("Download Request - ID: $id, DB Path: $db_path");

/* -------- NORMALIZE FILE PATH -------- */

// Remove any leading slash or dots for security
$db_path = ltrim($db_path, './\\');

// If path doesn't start with 'uploads/', add it
if (strpos($db_path, 'uploads/') !== 0 && strpos($db_path, 'uploads\\') !== 0) {
    // Check if it's just a filename without path
    if (strpos($db_path, '/') === false && strpos($db_path, '\\') === false) {
        $db_path = 'uploads/' . $db_path;
    }
}

// Get absolute path - normalize slashes
$server_path = __DIR__ . '/' . str_replace('\\', '/', $db_path);

// Log the server path for debugging
error_log("Server Path: $server_path");

/* -------- SECURITY CHECK -------- */

// Prevent directory traversal
$allowed_base = str_replace('\\', '/', __DIR__ . '/uploads/');
$normalized_path = str_replace('\\', '/', $server_path);
if (strpos($normalized_path, $allowed_base) !== 0) {
    die("Access denied: Invalid file path.<br>
         Path: " . htmlspecialchars($server_path) . "<br>
         Allowed: " . htmlspecialchars($allowed_base));
}

/* -------- FILE EXISTENCE CHECK -------- */

if (!file_exists($server_path)) {
    // Try alternative paths
    $alternative_paths = [
        __DIR__ . '/uploads/' . basename($db_path),
        __DIR__ . '/' . basename($db_path),
    ];
    
    foreach ($alternative_paths as $alt_path) {
        if (file_exists($alt_path)) {
            $server_path = $alt_path;
            error_log("Found file at alternative path: $alt_path");
            break;
        }
    }
    
    if (!file_exists($server_path)) {
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>File Not Found</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 40px; text-align: center; }
                .error-box { 
                    background: #fff3f3; 
                    border: 2px solid #ff6b6b; 
                    border-radius: 10px; 
                    padding: 30px; 
                    max-width: 600px; 
                    margin: 0 auto;
                    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                }
                h1 { color: #dc3545; }
                .file-info { 
                    background: #f8f9fa; 
                    padding: 15px; 
                    border-radius: 5px; 
                    margin: 20px 0;
                    text-align: left;
                }
                .btn { 
                    display: inline-block; 
                    padding: 10px 20px; 
                    background: #2563eb; 
                    color: white; 
                    text-decoration: none; 
                    border-radius: 5px; 
                    margin: 10px;
                }
            </style>
        </head>
        <body>
            <div class='error-box'>
                <h1>⚠️ File Not Found</h1>
                <p>The requested file could not be found on the server.</p>
                
                <div class='file-info'>
                    <strong>File Details:</strong><br>
                    File ID: $id<br>
                    Title: " . htmlspecialchars($file_title) . "<br>
                    Expected Path: " . htmlspecialchars($server_path) . "<br>
                    Database Path: " . htmlspecialchars($db_path) . "
                </div>
                
                <p>Please contact the administrator or upload the file again.</p>
                
                <div>
                    <a href='javascript:history.back()' class='btn'>← Go Back</a>
                    <a href='index.php' class='btn'>🏠 Return Home</a>
                </div>
            </div>
        </body>
        </html>";
        exit;
    }
}

/* -------- GET FILE EXTENSION -------- */

$ext = strtolower(pathinfo($server_path, PATHINFO_EXTENSION));

/* -------- DETERMINE VIEW OR DOWNLOAD -------- */

// Check if user wants to download (has ?download=true parameter)
$force_download = isset($_GET['download']) && $_GET['download'] === 'true';

// Use title as filename, or generate from path
$download_filename = $file_title . '.' . $ext;
// Clean filename for download
$download_filename = preg_replace('/[^\w\s.-]/', '', $download_filename);

// Viewable file types
$viewable_images = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
$viewable_docs = ['pdf', 'txt'];
$google_docs = ['doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx'];

if (!$force_download) {
    // Handle viewing
    if (in_array($ext, $viewable_images)) {
        // Display image directly
        $mime_types = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'webp' => 'image/webp'
        ];
        
        header('Content-Type: ' . ($mime_types[$ext] ?? 'image/jpeg'));
        header('Content-Length: ' . filesize($server_path));
        header('Cache-Control: public, max-age=3600');
        readfile($server_path);
        exit;
    }
    
    elseif (in_array($ext, $viewable_docs)) {
        // Display PDF/text in browser
        if ($ext === 'pdf') {
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . htmlspecialchars($download_filename) . '"');
        } elseif ($ext === 'txt') {
            header('Content-Type: text/plain; charset=utf-8');
            header('Content-Disposition: inline; filename="' . htmlspecialchars($download_filename) . '"');
        }
        
        header('Content-Length: ' . filesize($server_path));
        header('Cache-Control: public, max-age=3600');
        readfile($server_path);
        exit;
    }
    
    elseif (in_array($ext, $google_docs)) {
        // Use Google Docs Viewer
        $public_url = "http://" . $_SERVER['HTTP_HOST'] . '/shikshahub/' . $db_path;
        $viewer_url = "https://docs.google.com/gview?url=" . urlencode($public_url) . "&embedded=true";
        
        // Display Google Viewer in an iframe
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>View: ' . htmlspecialchars($file_title) . '</title>
            <style>
                body { margin: 0; padding: 20px; background: #f5f5f5; font-family: Arial, sans-serif; }
                .header { 
                    background: white; 
                    padding: 15px 20px; 
                    border-radius: 8px; 
                    margin-bottom: 15px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                .header h2 { margin: 0; color: #333; font-size: 18px; }
                .container { 
                    background: white; 
                    border-radius: 8px; 
                    padding: 0;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                    overflow: hidden;
                }
                iframe { 
                    width: 100%; 
                    height: 80vh; 
                    border: none; 
                }
                .buttons { display: flex; gap: 10px; flex-wrap: wrap; }
                .btn {
                    padding: 8px 16px;
                    background: #4CAF50;
                    color: white;
                    text-decoration: none;
                    border-radius: 4px;
                    font-size: 14px;
                    display: inline-flex;
                    align-items: center;
                    gap: 5px;
                }
                .btn.download { background: #2196F3; }
                .btn:hover { opacity: 0.9; transform: translateY(-2px); }
                @media (max-width: 600px) {
                    .header { flex-direction: column; align-items: flex-start; gap: 10px; }
                    .buttons { width: 100%; }
                    .btn { flex: 1; text-align: center; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h2>📄 ' . htmlspecialchars($file_title) . '</h2>
                <div class="buttons">
                    <a href="?id=' . $id . '&download=true" class="btn download">
                        <i class="fas fa-download"></i> Download
                    </a>
                    <a href="javascript:history.back()" class="btn">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <a href="index.php" class="btn">
                        <i class="fas fa-home"></i> Home
                    </a>
                </div>
            </div>
            <div class="container">
                <iframe src="' . htmlspecialchars($viewer_url) . '"></iframe>
            </div>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
        </body>
        </html>';
        exit;
    }
}

/* -------- FORCE DOWNLOAD (for all other files or when ?download=true) -------- */

// Get file size
$filesize = filesize($server_path);

// Set headers for download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . htmlspecialchars($download_filename) . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . $filesize);

// Clean output buffer
if (ob_get_level()) {
    ob_end_clean();
}

// Read file in chunks to handle large files
$chunkSize = 1024 * 1024; // 1MB chunks
$handle = fopen($server_path, 'rb');
if ($handle === false) {
    die("Cannot open file for reading.");
}

while (!feof($handle)) {
    echo fread($handle, $chunkSize);
    flush();
}

fclose($handle);
exit;