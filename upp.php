<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Upload Material - ShikshaHub</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="home.css">
  <link rel="stylesheet" href="upload.css">
</head>
<body>

<nav class="navbar">
  <div class="logo-box">
    <img src="logo.png" class="logo-img">
    <h2 class="logo-text">ShikshaHub</h2>
  </div>
  <ul class="nav-links">
    <li><a href="index.php">Home</a></li>
    <li><a href="materials.php">Materials</a></li>
    <li><a href="upload.php" class="active">Upload</a></li>
  </ul>
  <?php if(isset($_SESSION['user_id'])): ?>
    <a href="backend/logout.php" class="login-btn">Logout</a>
  <?php else: ?>
    <a href="login.html" class="login-btn">Login</a>
  <?php endif; ?>
</nav>

<!-- UPLOAD SECTION -->
<section class="upload-section">
  <h2>Upload Study Material</h2>

  <form class="upload-form" action="backend/upload.php" method="POST" enctype="multipart/form-data">

    <input type="text" name="title" placeholder="Material Title" required>

    <textarea name="description" placeholder="Short description" rows="4" required></textarea>

    <select name="type" required>
      <option value="">Select File Type</option>
      <option value="PDF">PDF</option>
      <option value="PPT">PPT</option>
      <option value="DOC">DOC</option>
    </select>

    <input type="file" name="file" required>

    <button type="submit">Upload</button>

  </form>
</section>

</body>
</html>
