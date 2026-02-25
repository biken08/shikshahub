<?php
session_start();
require_once '../backend/auth.php';
require_once '../backend/db.php';

if ($_SESSION['role'] !== 'teacher' && $_SESSION['role'] !== 'admin') {
    header('Location: /shikshahub/index.php');
    exit;
}

$department = $_SESSION['department'] ?? '';
$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Material - ShikshaHub</title>
    <!-- Font Awesome & Google Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
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

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f6f9fc 0%, #f1f5f9 100%);
            color: var(--dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navbar */
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
            gap: 1.5rem;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--dark);
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: var(--transition);
        }

        .nav-links a:hover {
            color: var(--primary);
            background: rgba(67, 97, 238, 0.05);
        }

        .nav-links a.active {
            color: var(--primary);
            background: rgba(67, 97, 238, 0.1);
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

        /* Main container */
        .upload-container {
            max-width: 800px;
            margin: 3rem auto;
            padding: 0 2rem;
            flex: 1;
        }

        .upload-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 2.5rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .upload-card h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            display: inline-block;
        }

        .upload-card .subtitle {
            color: var(--gray);
            margin-bottom: 2rem;
            font-size: 1rem;
        }

        .info-note {
            background: #fef9c3;
            border-left: 4px solid var(--warning);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            color: #854d0e;
        }

        .info-note i {
            font-size: 1.2rem;
        }

        .form-group {
            margin-bottom: 1.8rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.6rem;
            font-weight: 600;
            color: var(--dark);
            font-size: 0.95rem;
            letter-spacing: 0.3px;
        }

        .form-group label i {
            color: var(--primary);
            margin-right: 0.5rem;
            width: 1.2rem;
        }

        .form-control {
            width: 100%;
            padding: 0.9rem 1.2rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        /* Department display for teachers */
        .dept-display {
            background: #f1f5f9;
            padding: 0.9rem 1.2rem;
            border-radius: 10px;
            color: var(--dark);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border: 2px solid #e2e8f0;
        }

        .dept-display i {
            color: var(--primary);
        }

        /* File input styling */
        .file-input-wrapper {
            position: relative;
            border: 2px dashed #e2e8f0;
            border-radius: 12px;
            padding: 2rem 1.5rem;
            text-align: center;
            transition: var(--transition);
            background: #f8fafc;
            cursor: pointer;
        }

        .file-input-wrapper:hover {
            border-color: var(--primary);
            background: #fff;
        }

        .file-input-wrapper input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .file-input-icon {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .file-input-text {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }

        .file-input-hint {
            font-size: 0.85rem;
            color: var(--gray);
        }

        .allowed-types {
            display: flex;
            flex-wrap: wrap;
            gap: 0.8rem;
            margin-top: 1rem;
            justify-content: center;
        }

        .type-badge {
            background: white;
            border: 1px solid #e2e8f0;
            padding: 0.4rem 0.8rem;
            border-radius: 30px;
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            color: var(--gray);
            transition: var(--transition);
        }

        .type-badge i {
            color: var(--primary);
        }

        .type-badge:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .file-info {
            margin-top: 1rem;
            padding: 0.8rem;
            background: #e6f7ff;
            border-radius: 8px;
            display: none;
            align-items: center;
            gap: 0.8rem;
            color: #0050b3;
            border-left: 4px solid var(--accent);
        }

        .file-info i {
            font-size: 1.2rem;
        }

        /* Action buttons */
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            padding: 0.9rem 2rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
            flex: 1;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(67, 97, 238, 0.3);
        }

        .btn-secondary {
            background: white;
            color: var(--dark);
            border: 2px solid #e2e8f0;
        }

        .btn-secondary:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Footer */
        footer {
            background: var(--dark);
            color: white;
            text-align: center;
            padding: 1.5rem;
            margin-top: auto;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .menu-toggle {
                display: block;
            }
            .nav-links {
                position: fixed;
                top: 70px;
                left: -100%;
                width: 100%;
                background: white;
                flex-direction: column;
                padding: 2rem;
                box-shadow: var(--shadow);
                transition: left 0.3s ease;
            }
            .nav-links.active {
                left: 0;
            }
            .upload-card {
                padding: 1.5rem;
            }
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar (same as materials.php) -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="/shikshahub/index.php" class="logo-box">
                <img src="/shikshahub/logo.png" class="logo-img" alt="ShikshaHub Logo">
                <div class="logo-text">ShikshaHub</div>
            </a>
            <button class="menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="nav-links" id="navLinks">
                <a href="/shikshahub/index.php">Home</a>
                <a href="/shikshahub/materials.php">Materials</a>
                <?php if ($role === 'teacher'): ?>
                    <a href="/shikshahub/teacher/dashboard.php">Dashboard</a>
                    <a href="/shikshahub/teacher/upload.php" class="active">Upload</a>
                <?php elseif ($role === 'admin'): ?>
                    <a href="/shikshahub/admin/dashboard.php">Admin</a>
                    <a href="/shikshahub/teacher/upload.php" class="active">Upload</a>
                <?php endif; ?>
                <a href="/shikshahub/backend/logout.php" class="login-btn">Logout</a>
            </div>
        </div>
    </nav>

    <main class="upload-container">
        <div class="upload-card">
            <h2>Upload Study Material</h2>
            <p class="subtitle">Share your knowledge with the community. All uploads will be reviewed before publication.</p>

            <?php if ($role === 'admin' && empty($department)): ?>
                <div class="info-note">
                    <i class="fas fa-info-circle"></i>
                    <span>You are an admin. You can upload materials for any department. Please select a department below.</span>
                </div>
            <?php endif; ?>

            <form id="uploadForm" action="upload_process.php" method="post" enctype="multipart/form-data">
                <!-- Title -->
                <div class="form-group">
                    <label for="title"><i class="fas fa-heading"></i> Title</label>
                    <input type="text" class="form-control" id="title" name="title" placeholder="e.g., Introduction to Algorithms" required>
                </div>

                <!-- Description -->
                <div class="form-group">
                    <label for="description"><i class="fas fa-align-left"></i> Description</label>
                    <textarea class="form-control" id="description" name="description" placeholder="Briefly describe the material..."></textarea>
                </div>

                <!-- Subject -->
                <div class="form-group">
                    <label for="subject"><i class="fas fa-book"></i> Subject (optional)</label>
                    <input type="text" class="form-control" id="subject" name="subject" placeholder="e.g., Computer Science, Mathematics">
                </div>

                <!-- Department -->
                <?php if ($role === 'admin'): ?>
                    <div class="form-group">
                        <label for="department"><i class="fas fa-building"></i> Department</label>
                        <select class="form-control" id="department" name="department" required>
                            <option value="">-- Select Department --</option>
                            <option value="Engineering">Engineering</option>
                            <option value="Hotel Management">Hotel Management</option>
                            <option value="IT">IT</option>
                            <option value="Business Studies">Business Studies</option>
                        </select>
                    </div>
                <?php else: ?>
                    <input type="hidden" name="department" value="<?php echo htmlspecialchars($department); ?>">
                    <div class="form-group">
                        <label><i class="fas fa-building"></i> Department</label>
                        <div class="dept-display">
                            <i class="fas fa-check-circle" style="color: #27ae60;"></i> <?php echo htmlspecialchars($department); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- File Upload -->
                <div class="form-group">
                    <label><i class="fas fa-file"></i> File</label>
                    <div class="file-input-wrapper" id="fileDropZone">
                        <div class="file-input-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <div class="file-input-text">Drag & drop or click to select</div>
                        <div class="file-input-hint">Supported: PDF, DOC, DOCX, PPT, PPTX, TXT (max 20MB)</div>
                        <input type="file" name="file" id="fileInput" accept=".pdf,.doc,.docx,.ppt,.pptx,.txt" required>
                    </div>
                    <div class="allowed-types">
                        <span class="type-badge"><i class="fas fa-file-pdf"></i> PDF</span>
                        <span class="type-badge"><i class="fas fa-file-word"></i> DOC/DOCX</span>
                        <span class="type-badge"><i class="fas fa-file-powerpoint"></i> PPT/PPTX</span>
                        <span class="type-badge"><i class="fas fa-file-alt"></i> TXT</span>
                    </div>
                    <div class="file-info" id="fileInfo">
                        <i class="fas fa-check-circle"></i>
                        <span id="fileName"></span> (<span id="fileSize"></span>)
                    </div>
                </div>

                <!-- Buttons -->
                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-upload"></i> Upload Material
                    </button>
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </main>

    <footer>
        &copy; <?php echo date('Y'); ?> ShikshaHub. All rights reserved.
    </footer>

    <!-- JavaScript for interactivity and validation -->
    <script>
        // Mobile menu toggle
        const menuToggle = document.getElementById('menuToggle');
        const navLinks = document.getElementById('navLinks');
        if (menuToggle) {
            menuToggle.addEventListener('click', () => {
                navLinks.classList.toggle('active');
                menuToggle.innerHTML = navLinks.classList.contains('active') 
                    ? '<i class="fas fa-times"></i>' 
                    : '<i class="fas fa-bars"></i>';
            });
            document.addEventListener('click', (e) => {
                if (!navLinks.contains(e.target) && !menuToggle.contains(e.target)) {
                    navLinks.classList.remove('active');
                    menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
                }
            });
        }

        // File input handling
        const fileInput = document.getElementById('fileInput');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const dropZone = document.getElementById('fileDropZone');
        const form = document.getElementById('uploadForm');
        const submitBtn = document.getElementById('submitBtn');

        // Allowed file types and max size
        const allowedExtensions = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt'];
        const maxSize = 20 * 1024 * 1024; // 20MB

        // Update file info on selection
        fileInput.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const ext = file.name.split('.').pop().toLowerCase();
                if (!allowedExtensions.includes(ext)) {
                    alert('File type not allowed. Only ' + allowedExtensions.join(', ').toUpperCase() + ' files are permitted.');
                    fileInput.value = '';
                    fileInfo.style.display = 'none';
                    return;
                }
                if (file.size > maxSize) {
                    alert('File size exceeds 20MB limit.');
                    fileInput.value = '';
                    fileInfo.style.display = 'none';
                    return;
                }
                // Display file info
                fileName.textContent = file.name;
                const sizeKB = (file.size / 1024).toFixed(2);
                const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                fileSize.textContent = file.size < 1024 * 1024 ? sizeKB + ' KB' : sizeMB + ' MB';
                fileInfo.style.display = 'flex';
            } else {
                fileInfo.style.display = 'none';
            }
        });

        // Drag & drop visual feedback
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });
        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });
        function highlight() {
            dropZone.style.borderColor = '#4361ee';
            dropZone.style.backgroundColor = '#e8f0fe';
        }
        function unhighlight() {
            dropZone.style.borderColor = '#e2e8f0';
            dropZone.style.backgroundColor = '#f8fafc';
        }
        dropZone.addEventListener('drop', handleDrop, false);
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files.length) {
                fileInput.files = files;
                // Trigger change event manually
                const event = new Event('change', { bubbles: true });
                fileInput.dispatchEvent(event);
            }
        }

        // Form submit loading state
        form.addEventListener('submit', function(e) {
            if (!fileInput.files || !fileInput.files[0]) {
                e.preventDefault();
                alert('Please select a file.');
                return;
            }
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
        });
    </script>
</body>
</html>