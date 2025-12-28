<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Get user info
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';
$profile_pic = $_SESSION['profile_pic'] ?? '';
$user_type = $_SESSION['user_type'] ?? 'User';
$email = $_SESSION['email'] ?? '';

// Extract first name from username
$first_name = explode(' ', $username)[0];

// Define sectors
$sectors = [
    'BCA' => 'Bachelor of Computer Applications',
    'BBM' => 'Bachelor of Business Management',
    'B.Tech' => 'Bachelor of Technology',
    'BBA' => 'Bachelor of Business Administration',
    'MBA' => 'Master of Business Administration',
    'MCA' => 'Master of Computer Applications',
    'M.Tech' => 'Master of Technology',
    'B.Com' => 'Bachelor of Commerce',
    'M.Com' => 'Master of Commerce',
    'BA' => 'Bachelor of Arts',
    'MA' => 'Master of Arts',
    'B.Sc' => 'Bachelor of Science',
    'M.Sc' => 'Master of Science',
    'LLB' => 'Bachelor of Laws',
    'LLM' => 'Master of Laws',
    'Medical' => 'Medical Sciences',
    'Engineering' => 'Engineering',
    'Law' => 'Law',
    'Arts' => 'Arts & Humanities',
    'Science' => 'Science',
    'Commerce' => 'Commerce',
    'Other' => 'Other Sector'
];

// Function to get user initials
function getInitials($name) {
    $initials = '';
    $words = explode(' ', $name);
    foreach ($words as $word) {
        if (!empty($word)) {
            $initials .= strtoupper($word[0]);
        }
    }
    return substr($initials, 0, 2);
}

$user_initials = getInitials($first_name);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Upload Material - ShikshaHub</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="home.css">
  <link rel="stylesheet" href="up.css">
  <style>
    /* Navbar Styles */
    .navbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 40px;
      background: white;
      box-shadow: 0 2px 15px rgba(0,0,0,0.1);
      position: sticky;
      top: 0;
      z-index: 1000;
    }
    
    .nav-left {
      display: flex;
      align-items: center;
      gap: 40px;
    }
    
    .logo-box {
      display: flex;
      align-items: center;
      gap: 12px;
      text-decoration: none;
    }
    
    .logo-img {
      height: 45px;
      width: 45px;
      border-radius: 8px;
    }
    
    .logo-text {
      font-size: 26px;
      color: #3366ff;
      margin: 0;
      font-weight: 700;
    }
    
    .nav-links {
      display: flex;
      list-style: none;
      gap: 30px;
      margin: 0;
      padding: 0;
    }
    
    .nav-links a {
      text-decoration: none;
      color: #555;
      font-weight: 500;
      font-size: 16px;
      padding: 8px 15px;
      border-radius: 6px;
      transition: all 0.3s;
    }
    
    .nav-links a:hover {
      color: #3366ff;
      background: #f5f7ff;
    }
    
    .nav-links a.active {
      color: #3366ff;
      background: #e8eeff;
      font-weight: 600;
    }
    
    .nav-right {
      display: flex;
      align-items: center;
      gap: 20px;
    }
    
    /* User Profile Styles */
    .user-profile {
      position: relative;
    }
    
    .profile-trigger {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 8px 15px;
      background: #f8f9fa;
      border: none;
      border-radius: 25px;
      cursor: pointer;
      transition: all 0.3s;
    }
    
    .profile-trigger:hover {
      background: #e9ecef;
    }
    
    .avatar {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: bold;
      font-size: 16px;
    }
    
    .avatar img {
      width: 100%;
      height: 100%;
      border-radius: 50%;
      object-fit: cover;
    }
    
    .user-name {
      font-weight: 600;
      color: #333;
      font-size: 15px;
    }
    
    .dropdown-arrow {
      font-size: 12px;
      color: #666;
    }
    
    .profile-dropdown {
      position: absolute;
      top: 100%;
      right: 0;
      background: white;
      border-radius: 10px;
      box-shadow: 0 8px 30px rgba(0,0,0,0.15);
      min-width: 250px;
      display: none;
      margin-top: 10px;
      overflow: hidden;
      z-index: 1000;
      border: 1px solid #e9ecef;
    }
    
    .profile-dropdown.active {
      display: block;
      animation: fadeIn 0.3s ease;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .dropdown-header {
      padding: 20px;
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      border-bottom: 1px solid #dee2e6;
    }
    
    .dropdown-avatar {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      margin: 0 auto 15px;
      background: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      font-weight: bold;
      color: #667eea;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    
    .dropdown-avatar img {
      width: 100%;
      height: 100%;
      border-radius: 50%;
      object-fit: cover;
    }
    
    .dropdown-user-info {
      text-align: center;
    }
    
    .dropdown-user-info h4 {
      margin: 0 0 5px 0;
      color: #333;
      font-size: 18px;
    }
    
    .dropdown-user-info p {
      margin: 0;
      color: #666;
      font-size: 14px;
    }
    
    .user-badge {
      display: inline-block;
      background: #3366ff;
      color: white;
      padding: 4px 12px;
      border-radius: 12px;
      font-size: 12px;
      margin-top: 8px;
      text-transform: capitalize;
    }
    
    .dropdown-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 20px;
      color: #333;
      text-decoration: none;
      transition: all 0.3s;
      border-bottom: 1px solid #f8f9fa;
    }
    
    .dropdown-item:hover {
      background: #f8f9ff;
      color: #3366ff;
      padding-left: 25px;
    }
    
    .dropdown-item i {
      width: 20px;
      color: #666;
      font-size: 16px;
    }
    
    .dropdown-item:hover i {
      color: #3366ff;
    }
    
    .dropdown-item.logout {
      color: #e74c3c;
    }
    
    .dropdown-item.logout:hover {
      background: #fff5f5;
      color: #c0392b;
    }
    
    .dropdown-item.logout i {
      color: #e74c3c;
    }
    
    .login-btn {
      padding: 10px 25px;
      background: #3366ff;
      color: white;
      text-decoration: none;
      border-radius: 6px;
      font-weight: 500;
      transition: background 0.3s;
    }
    
    .login-btn:hover {
      background: #3b57a5;
    }
  </style>
</head>
<body>

<nav class="navbar">
  <div class="nav-left">
    <a href="index.php" class="logo-box">
      <img src="logo.png" class="logo-img">
      <h2 class="logo-text">ShikshaHub</h2>
    </a>
    <ul class="nav-links">
      <li><a href="index.php">Home</a></li>
      <li><a href="materials.php">Materials</a></li>
      <li><a href="upload.php" class="active">Upload</a></li>
    </ul>
  </div>
  
  <div class="nav-right">
    <?php if(isset($_SESSION['user_id'])): ?>
      <div class="user-profile">
        <button class="profile-trigger" id="profileBtn">
          <?php if(!empty($profile_pic)): ?>
            <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile" class="avatar">
          <?php else: ?>
            <div class="avatar"><?php echo $user_initials; ?></div>
          <?php endif; ?>
          <span class="user-name"><?php echo htmlspecialchars($first_name); ?></span>
          <i class="fas fa-chevron-down dropdown-arrow"></i>
        </button>
        <div class="profile-dropdown" id="profileDropdown">
          <div class="dropdown-header">
            <?php if(!empty($profile_pic)): ?>
              <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile" class="dropdown-avatar">
            <?php else: ?>
              <div class="dropdown-avatar"><?php echo $user_initials; ?></div>
            <?php endif; ?>
            <div class="dropdown-user-info">
              <h4><?php echo htmlspecialchars($first_name); ?></h4>
              <p><?php echo htmlspecialchars($email); ?></p>
              <span class="user-badge"><?php echo htmlspecialchars($user_type); ?></span>
            </div>
          </div>
          <a href="profile.php" class="dropdown-item">
            <i class="fas fa-user"></i> My Profile
          </a>
          <a href="my_uploads.php" class="dropdown-item">
            <i class="fas fa-cloud-upload-alt"></i> My Uploads
          </a>
          <a href="settings.php" class="dropdown-item">
            <i class="fas fa-cog"></i> Settings
          </a>
          <a href="backend/logout.php" class="dropdown-item logout">
            <i class="fas fa-sign-out-alt"></i> Logout
          </a>
        </div>
      </div>
    <?php else: ?>
      <a href="login.html" class="login-btn">Login</a>
    <?php endif; ?>
  </div>
</nav>

<!-- UPLOAD SECTION -->
<section class="upload-section">
  <div class="upload-header">
    <h2><i class="fas fa-cloud-upload-alt"></i> Upload Study Material</h2>
    <p class="upload-subtitle">Share your knowledge with students across different sectors</p>
  </div>

  <form class="upload-form" action="backend/upload.php" method="POST" enctype="multipart/form-data" id="uploadForm">
    <div class="form-step active" id="step1">
      <h3><i class="fas fa-info-circle"></i> Material Details</h3>
      
      <div class="form-group">
        <label for="title">
          <i class="fas fa-heading"></i> Material Title *
        </label>
        <input type="text" id="title" name="title" placeholder="Enter a clear, descriptive title" required>
        <div class="char-counter"><span id="titleChars">0</span>/100 characters</div>
      </div>

      <div class="form-group">
        <label for="sector">
          <i class="fas fa-graduation-cap"></i> Sector *
        </label>
        <div class="sector-selector">
          <select id="sector" name="sector" required>
            <option value="">Select a sector</option>
            <?php foreach($sectors as $key => $value): ?>
              <option value="<?php echo htmlspecialchars($key); ?>">
                <?php echo htmlspecialchars($key); ?> - <?php echo htmlspecialchars($value); ?>
              </option>
            <?php endforeach; ?>
          </select>
          <div class="select-arrow">
            <i class="fas fa-chevron-down"></i>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label for="semester">
          <i class="fas fa-calendar-alt"></i> Semester (Optional)
        </label>
        <select id="semester" name="semester">
          <option value="">Select semester</option>
          <option value="1">Semester 1</option>
          <option value="2">Semester 2</option>
          <option value="3">Semester 3</option>
          <option value="4">Semester 4</option>
          <option value="5">Semester 5</option>
          <option value="6">Semester 6</option>
          <option value="7">Semester 7</option>
          <option value="8">Semester 8</option>
        </select>
      </div>

      <div class="form-group">
        <label for="description">
          <i class="fas fa-align-left"></i> Description *
        </label>
        <textarea id="description" name="description" rows="5" placeholder="Describe the content, topics covered, and any important notes" required></textarea>
        <div class="char-counter"><span id="descChars">0</span>/500 characters</div>
      </div>

      <div class="form-navigation">
        <button type="button" class="next-btn" onclick="nextStep(2)">
          Next <i class="fas fa-arrow-right"></i>
        </button>
      </div>
    </div>

    <div class="form-step" id="step2">
      <h3><i class="fas fa-file-alt"></i> Content Details</h3>
      
      <div class="form-group">
        <label><i class="fas fa-file-code"></i> File Type *</label>
        <div class="file-type-selector">
          <div class="type-options">
            <div class="type-option" data-type="PDF">
              <i class="fas fa-file-pdf"></i>
              <span>PDF</span>
              <small>Documents</small>
            </div>
            <div class="type-option" data-type="PPT">
              <i class="fas fa-file-powerpoint"></i>
              <span>PPT</span>
              <small>Presentations</small>
            </div>
            <div class="type-option" data-type="DOC">
              <i class="fas fa-file-word"></i>
              <span>DOC</span>
              <small>Word Docs</small>
            </div>
            <div class="type-option" data-type="ZIP">
              <i class="fas fa-file-archive"></i>
              <span>ZIP</span>
              <small>Archives</small>
            </div>
            <div class="type-option" data-type="IMAGE">
              <i class="fas fa-file-image"></i>
              <span>Image</span>
              <small>JPG, PNG</small>
            </div>
            <div class="type-option" data-type="VIDEO">
              <i class="fas fa-file-video"></i>
              <span>Video</span>
              <small>MP4, AVI</small>
            </div>
          </div>
          <input type="hidden" name="type" id="fileType" required>
        </div>
      </div>

      <div class="form-group">
        <label for="file"><i class="fas fa-paperclip"></i> File Upload *</label>
        <div class="file-upload-wrapper">
          <div class="file-upload-area" id="dropArea">
            <i class="fas fa-cloud-upload-alt upload-icon"></i>
            <h4>Drag & Drop your file here</h4>
            <p>or click to browse</p>
            <p class="file-limit">Maximum file size: 50MB</p>
            <input type="file" name="file" id="fileInput" required>
            <button type="button" class="browse-btn">Browse Files</button>
          </div>
          <div class="file-preview" id="filePreview">
            <div class="preview-placeholder">
              <i class="fas fa-file"></i>
              <p>No file selected</p>
            </div>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label for="keywords">
          <i class="fas fa-tags"></i> Keywords (Optional)
        </label>
        <input type="text" id="keywords" name="keywords" placeholder="Enter relevant keywords separated by commas">
        <small>Helps others find your material easily</small>
      </div>

      <div class="form-navigation">
        <button type="button" class="prev-btn" onclick="prevStep(1)">
          <i class="fas fa-arrow-left"></i> Back
        </button>
        <button type="button" class="next-btn" onclick="nextStep(3)">
          Next <i class="fas fa-arrow-right"></i>
        </button>
      </div>
    </div>

    <div class="form-step" id="step3">
      <h3><i class="fas fa-check-circle"></i> Review & Submit</h3>
      
      <div class="review-section">
        <div class="review-card">
          <h4><i class="fas fa-eye"></i> Preview</h4>
          <div class="review-content">
            <div class="review-item">
              <span class="review-label">Title:</span>
              <span id="reviewTitle" class="review-value">-</span>
            </div>
            <div class="review-item">
              <span class="review-label">Sector:</span>
              <span id="reviewSector" class="review-value">-</span>
            </div>
            <div class="review-item">
              <span class="review-label">Semester:</span>
              <span id="reviewSemester" class="review-value">-</span>
            </div>
            <div class="review-item">
              <span class="review-label">Description:</span>
              <span id="reviewDesc" class="review-value">-</span>
            </div>
            <div class="review-item">
              <span class="review-label">File Type:</span>
              <span id="reviewFileType" class="review-value">-</span>
            </div>
            <div class="review-item">
              <span class="review-label">File:</span>
              <span id="reviewFileName" class="review-value">-</span>
            </div>
          </div>
        </div>

        <div class="settings-section">
          <h4><i class="fas fa-cog"></i> Sharing Settings</h4>
          <div class="settings-options">
            <div class="setting-option">
              <input type="radio" id="visibility_public" name="visibility" value="public" checked>
              <label for="visibility_public">
                <i class="fas fa-globe"></i>
                <div>
                  <strong>Public</strong>
                  <p>Anyone can view and download</p>
                </div>
              </label>
            </div>
            <div class="setting-option">
              <input type="radio" id="visibility_link" name="visibility" value="link">
              <label for="visibility_link">
                <i class="fas fa-link"></i>
                <div>
                  <strong>Link Sharing</strong>
                  <p>Only people with the link can access</p>
                </div>
              </label>
            </div>
            <div class="setting-option">
              <input type="radio" id="visibility_private" name="visibility" value="private">
              <label for="visibility_private">
                <i class="fas fa-lock"></i>
                <div>
                  <strong>Private</strong>
                  <p>Only you can access</p>
                </div>
              </label>
            </div>
          </div>

          <div class="additional-settings">
            <label class="checkbox-label">
              <input type="checkbox" name="allow_comments" checked>
              <span>Allow comments and ratings</span>
            </label>
            <label class="checkbox-label">
              <input type="checkbox" name="allow_download" checked>
              <span>Allow file downloads</span>
            </label>
          </div>
        </div>
      </div>

      <div class="form-navigation">
        <button type="button" class="prev-btn" onclick="prevStep(2)">
          <i class="fas fa-arrow-left"></i> Back
        </button>
        <button type="submit" class="submit-btn">
          <i class="fas fa-upload"></i> Upload Material
        </button>
      </div>
    </div>

    <div class="progress-indicator">
      <div class="progress-step active" data-step="1">
        <div class="step-number">1</div>
        <div class="step-label">Details</div>
      </div>
      <div class="progress-line"></div>
      <div class="progress-step" data-step="2">
        <div class="step-number">2</div>
        <div class="step-label">Content</div>
      </div>
      <div class="progress-line"></div>
      <div class="progress-step" data-step="3">
        <div class="step-number">3</div>
        <div class="step-label">Review</div>
      </div>
    </div>
  </form>

  <div class="upload-guidelines">
    <h4><i class="fas fa-info-circle"></i> Upload Guidelines</h4>
    <div class="guidelines-grid">
      <div class="guideline-item">
        <i class="fas fa-check-circle"></i>
        <div>
          <strong>Choose Correct Sector</strong>
          <p>Select the appropriate sector (BCA, BBM, etc.) for better organization</p>
        </div>
      </div>
      <div class="guideline-item">
        <i class="fas fa-check-circle"></i>
        <div>
          <strong>Clear Descriptions</strong>
          <p>Provide detailed descriptions with topics covered</p>
        </div>
      </div>
      <div class="guideline-item">
        <i class="fas fa-check-circle"></i>
        <div>
          <strong>File Size Limit</strong>
          <p>Maximum file size: 50MB per upload</p>
        </div>
      </div>
      <div class="guideline-item">
        <i class="fas fa-check-circle"></i>
        <div>
          <strong>Copyright Respect</strong>
          <p>Ensure you have rights to share the material</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER (Same as materials.php) -->
<footer class="footer">
  <div class="footer-container">
    <div class="footer-section">
      <div class="footer-logo">
        <img src="logo.png" alt="ShikshaHub Logo" class="footer-logo-img">
        <h3 class="footer-logo-text">ShikshaHub</h3>
      </div>
      <p class="footer-description">
        Empowering education through knowledge sharing. Join our community of learners and educators.
      </p>
      <div class="social-links">
        <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
        <a href="#" class="social-link"><i class="fab fa-linkedin"></i></a>
      </div>
    </div>

    <div class="footer-section">
      <h4 class="footer-heading">Quick Links</h4>
      <ul class="footer-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="materials.php">Browse Materials</a></li>
        <li><a href="upload.php">Upload Material</a></li>
        <li><a href="about.php">About Us</a></li>
        <li><a href="contact.php">Contact Us</a></li>
      </ul>
    </div>

    <div class="footer-section">
      <h4 class="footer-heading">Popular Sectors</h4>
      <ul class="footer-links">
        <li><a href="materials.php?sector=BCA">BCA</a></li>
        <li><a href="materials.php?sector=BBM">BBM</a></li>
        <li><a href="materials.php?sector=B.Tech">B.Tech</a></li>
        <li><a href="materials.php?sector=BBA">BBA</a></li>
        <li><a href="materials.php?sector=MBA">MBA</a></li>
      </ul>
    </div>

    <div class="footer-section">
      <h4 class="footer-heading">Contact Info</h4>
      <div class="contact-info">
        <div class="contact-item">
          <i class="fas fa-map-marker-alt"></i>
          <span>shiksha cityy</span>
        </div>
        <div class="contact-item">
          <i class="fas fa-phone"></i>
          <span>+977 9841434743</span>
        </div>
        <div class="contact-item">
          <i class="fas fa-envelope"></i>
          <span>support@shikshahub.com</span>
        </div>
      </div>
    </div>
  </div>

  <div class="footer-bottom">
    <div class="footer-bottom-content">
      <p>&copy; <?php echo date('Y'); ?> ShikshaHub. All rights reserved.</p>
      <div class="footer-bottom-links">
        <a href="privacy.php">Privacy Policy</a>
        <a href="terms.php">Terms of Service</a>
        <a href="faq.php">FAQ</a>
      </div>
    </div>
  </div>
</footer>

<script>
// Profile Dropdown
document.addEventListener('DOMContentLoaded', function() {
  const profileBtn = document.getElementById('profileBtn');
  const profileDropdown = document.getElementById('profileDropdown');
  
  if (profileBtn) {
    profileBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      this.classList.toggle('active');
      profileDropdown.classList.toggle('active');
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
      if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
        profileBtn.classList.remove('active');
        profileDropdown.classList.remove('active');
      }
    });
  }

  // Step Navigation
  let currentStep = 1;
  const steps = document.querySelectorAll('.form-step');
  const progressSteps = document.querySelectorAll('.progress-step');

  window.nextStep = function(step) {
    if (validateStep(currentStep)) {
      currentStep = step;
      updateSteps();
      updateReview();
    }
  };

  window.prevStep = function(step) {
    currentStep = step;
    updateSteps();
  };

  function validateStep(step) {
    switch(step) {
      case 1:
        const title = document.getElementById('title').value.trim();
        const sector = document.getElementById('sector').value;
        const description = document.getElementById('description').value.trim();
        
        if (!title) {
          alert('Please enter a title for your material');
          document.getElementById('title').focus();
          return false;
        }
        if (!sector) {
          alert('Please select a sector');
          document.getElementById('sector').focus();
          return false;
        }
        if (!description) {
          alert('Please enter a description');
          document.getElementById('description').focus();
          return false;
        }
        return true;
        
      case 2:
        const fileType = document.getElementById('fileType').value;
        const fileInput = document.getElementById('fileInput');
        
        if (!fileType) {
          alert('Please select a file type');
          return false;
        }
        if (!fileInput.files.length) {
          alert('Please select a file to upload');
          return false;
        }
        
        // Check file size (50MB limit)
        const maxSize = 50 * 1024 * 1024;
        if (fileInput.files[0].size > maxSize) {
          alert('File size exceeds 50MB limit. Please choose a smaller file.');
          return false;
        }
        return true;
    }
    return true;
  }

  function updateSteps() {
    // Update form steps
    steps.forEach(step => {
      step.classList.remove('active');
    });
    document.getElementById(`step${currentStep}`).classList.add('active');
    
    // Update progress steps
    progressSteps.forEach((step, index) => {
      if (index + 1 <= currentStep) {
        step.classList.add('active');
      } else {
        step.classList.remove('active');
      }
    });
  }

  function updateReview() {
    // Update review section
    const title = document.getElementById('title').value;
    const sector = document.getElementById('sector');
    const sectorText = sector.options[sector.selectedIndex]?.text || '-';
    const semester = document.getElementById('semester');
    const semesterText = semester.value ? `Semester ${semester.value}` : 'Not specified';
    const desc = document.getElementById('description').value;
    const fileType = document.getElementById('fileType').value;
    const fileInput = document.getElementById('fileInput');
    
    document.getElementById('reviewTitle').textContent = title || '-';
    document.getElementById('reviewSector').textContent = sectorText.split(' - ')[0] || '-';
    document.getElementById('reviewSemester').textContent = semesterText;
    document.getElementById('reviewDesc').textContent = 
      desc.substring(0, 100) + (desc.length > 100 ? '...' : '');
    document.getElementById('reviewFileType').textContent = fileType || '-';
    document.getElementById('reviewFileName').textContent = 
      fileInput.files[0]?.name || '-';
  }

  // Character Counters
  const titleInput = document.getElementById('title');
  const descInput = document.getElementById('description');
  const titleChars = document.getElementById('titleChars');
  const descChars = document.getElementById('descChars');

  titleInput.addEventListener('input', function() {
    const count = this.value.length;
    titleChars.textContent = count;
    if (count > 100) {
      this.value = this.value.substring(0, 100);
      titleChars.textContent = 100;
    }
  });

  descInput.addEventListener('input', function() {
    const count = this.value.length;
    descChars.textContent = count;
    if (count > 500) {
      this.value = this.value.substring(0, 500);
      descChars.textContent = 500;
    }
  });

  // File Type Selection
  const typeOptions = document.querySelectorAll('.type-option');
  const fileTypeInput = document.getElementById('fileType');

  typeOptions.forEach(option => {
    option.addEventListener('click', function() {
      typeOptions.forEach(opt => opt.classList.remove('active'));
      this.classList.add('active');
      fileTypeInput.value = this.dataset.type;
    });
  });

  // Set default file type
  if (typeOptions.length > 0 && !fileTypeInput.value) {
    typeOptions[0].click();
  }

  // File Upload Drag & Drop
  const dropArea = document.getElementById('dropArea');
  const fileInput = document.getElementById('fileInput');
  const filePreview = document.getElementById('filePreview');
  const browseBtn = document.querySelector('.browse-btn');

  ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropArea.addEventListener(eventName, preventDefaults, false);
  });

  function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
  }

  ['dragenter', 'dragover'].forEach(eventName => {
    dropArea.addEventListener(eventName, highlight, false);
  });

  ['dragleave', 'drop'].forEach(eventName => {
    dropArea.addEventListener(eventName, unhighlight, false);
  });

  function highlight() {
    dropArea.classList.add('dragover');
  }

  function unhighlight() {
    dropArea.classList.remove('dragover');
  }

  dropArea.addEventListener('drop', handleDrop, false);
  fileInput.addEventListener('change', handleFileSelect);

  function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    handleFiles(files);
  }

  function handleFileSelect(e) {
    const files = e.target.files;
    handleFiles(files);
  }

  function handleFiles(files) {
    if (files.length > 0) {
      const file = files[0];
      updateFilePreview(file);
      
      // Auto-detect file type
      const extension = file.name.split('.').pop().toLowerCase();
      detectFileType(extension);
    }
  }

  function updateFilePreview(file) {
    const fileSize = formatFileSize(file.size);
    
    filePreview.innerHTML = `
      <div class="file-preview-item">
        <i class="${getFileIcon(file.name)}"></i>
        <div class="file-info">
          <span class="file-name">${file.name}</span>
          <span class="file-size">${fileSize}</span>
        </div>
        <button type="button" class="remove-file" onclick="removeFile()">
          <i class="fas fa-times"></i>
        </button>
      </div>
    `;
  }

  function getFileIcon(filename) {
    const ext = filename.split('.').pop().toLowerCase();
    const iconMap = {
      'pdf': 'fas fa-file-pdf',
      'ppt': 'fas fa-file-powerpoint',
      'pptx': 'fas fa-file-powerpoint',
      'doc': 'fas fa-file-word',
      'docx': 'fas fa-file-word',
      'jpg': 'fas fa-file-image',
      'jpeg': 'fas fa-file-image',
      'png': 'fas fa-file-image',
      'gif': 'fas fa-file-image',
      'mp4': 'fas fa-file-video',
      'avi': 'fas fa-file-video',
      'mov': 'fas fa-file-video',
      'zip': 'fas fa-file-archive',
      'rar': 'fas fa-file-archive',
      '7z': 'fas fa-file-archive'
    };
    return iconMap[ext] || 'fas fa-file';
  }

  function detectFileType(extension) {
    const typeMap = {
      'pdf': 'PDF',
      'ppt': 'PPT',
      'pptx': 'PPT',
      'doc': 'DOC',
      'docx': 'DOC',
      'jpg': 'IMAGE',
      'jpeg': 'IMAGE',
      'png': 'IMAGE',
      'gif': 'IMAGE',
      'mp4': 'VIDEO',
      'avi': 'VIDEO',
      'mov': 'VIDEO',
      'zip': 'ZIP',
      'rar': 'ZIP',
      '7z': 'ZIP'
    };
    
    if (typeMap[extension]) {
      fileTypeInput.value = typeMap[extension];
      // Highlight the corresponding type option
      typeOptions.forEach(option => {
        if (option.dataset.type === typeMap[extension]) {
          option.click();
        }
      });
    }
  }

  function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  }

  // Remove file function
  window.removeFile = function() {
    fileInput.value = '';
    filePreview.innerHTML = `
      <div class="preview-placeholder">
        <i class="fas fa-file"></i>
        <p>No file selected</p>
      </div>
    `;
    fileTypeInput.value = '';
    typeOptions.forEach(opt => opt.classList.remove('active'));
  };

  // Browse button
  browseBtn.addEventListener('click', function() {
    fileInput.click();
  });

  // Click to browse on drop area
  dropArea.addEventListener('click', function(e) {
    if (e.target !== browseBtn && !e.target.closest('.browse-btn')) {
      fileInput.click();
    }
  });

  // Form submission
  const uploadForm = document.getElementById('uploadForm');
  uploadForm.addEventListener('submit', function(e) {
    if (!validateStep(currentStep)) {
      e.preventDefault();
      return;
    }
    
    // Show loading state
    const submitBtn = this.querySelector('.submit-btn');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
    submitBtn.disabled = true;
  });

  // Initialize counters
  titleChars.textContent = titleInput.value.length;
  descChars.textContent = descInput.value.length;
});

</script>
</body>
</html>