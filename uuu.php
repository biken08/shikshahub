<?php
session_start();
include 'backend/db.php';

$recent = mysqli_query(
  $conn,
  "SELECT * FROM materials ORDER BY uploaded_at DESC LIMIT 6"
);
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

<form action="materials.php" method="GET" class="search-box">
  <input type="text" name="q" placeholder="Search materials..." required>
  <button type="submit">Search</button>
</form>

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
<section class="recent-section">
  <h2>Recently Added Materials</h2>

  <div class="material-grid">
    <?php while($row = mysqli_fetch_assoc($recent)): ?>
      <div class="material-card">
        <h3><?= htmlspecialchars($row['title']) ?></h3>
        <p><?= htmlspecialchars($row['description']) ?></p>
        <span class="tag"><?= $row['type'] ?></span>
        <a href="<?= $row['file_path'] ?>" target="_blank">View</a>
      </div>
    <?php endwhile; ?>
  </div>
</section>




  <footer>
    <p>&copy; <?php echo date("Y"); ?> ShikshaHub. All rights reserved.</p>
  </footer>

</body>
</html>

