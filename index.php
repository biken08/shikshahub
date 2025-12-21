<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ShikshaHub</title>
  <link rel="stylesheet" href="index.css">
</head>
<body>


  <header>
    <div class="logo">
      <img src="logo.png" alt="ShikshaHub Logo" height="40">
      <strong>ShikshaHub</strong>
    </div>

    <nav>
      <a href="index.php">Home</a>
      <a href="materials.php">Materials</a>
      <a href="upload.php">Upload</a>
    </nav>

    <div class="auth-btn">
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="backend/logout.php" class="btn-login">Logout</a>
      <?php else: ?>
        <a href="login.html" class="btn-login">Login</a>
      <?php endif; ?>
    </div>
  </header>

  
  <section class="hero">
  <div class="hero-content">

    <div class="hero-text">
      <h1>Welcome to ShikshaHub</h1>
      <p>A free platform to share and access educational materials.</p>

      <div class="hero-buttons">
        <a href="materials.php" class="btn primary">Browse Materials</a>
        <a href="upload.php" class="btn outline">Upload Material</a>
      </div>
    </div>

    <div class="hero-image">
      <img src="2.png" alt="Study Illustration">
    </div>

  </div>
</section>



  <footer>
    <p>&copy; <?php echo date("Y"); ?> ShikshaHub. All rights reserved.</p>
  </footer>

</body>
</html>

