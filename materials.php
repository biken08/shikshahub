<?php
session_start();
include 'backend/db.php';

$search = $_GET['search'] ?? '';
$type   = $_GET['type'] ?? '';

$searchEscaped = mysqli_real_escape_string($conn, $search);
$typeEscaped   = mysqli_real_escape_string($conn, $type);


$sql = "SELECT m.*, u.username 
        FROM materials m 
        JOIN users u ON m.user_id = u.id";

if (!empty($search)) {
    $sql .= " WHERE m.title LIKE '%$searchEscaped%'";
}

if (!empty($type)) {

    $sql .= (strpos($sql, 'WHERE') !== false ? " AND" : " WHERE");
    $sql .= " m.file_path LIKE '%.$typeEscaped%'";
}

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Materials - ShikshaHub</title>
  <link rel="stylesheet" href="home.css">
  <link rel="stylesheet" href="materials.css">
</head>
<body>

<nav class="navbar">
  <div class="logo-box">
    <img src="logo.png" class="logo-img">
    <h2 class="logo-text">ShikshaHub</h2>
  </div>
  <ul class="nav-links">
    <li><a href="index.php">Home</a></li>
    <li><a href="materials.php" class="active">Materials</a></li>
    <li><a href="upload.php">Upload</a></li>
  </ul>
  <?php if(isset($_SESSION['user_id'])): ?>
    <a href="backend/logout.php" class="login-btn">Logout</a>
  <?php else: ?>
    <a href="login.html" class="login-btn">Login</a>
  <?php endif; ?>
</nav>

<section class="search-bar">
  <form method="GET">
    <input type="text" name="search" placeholder="Search materials" value="<?php echo htmlspecialchars($search, ENT_QUOTES); ?>">
    <select name="type">
      <option value="">All Types</option>
      <option value="pdf" <?php if($type=='pdf') echo 'selected'; ?>>PDF</option>
      <option value="ppt" <?php if($type=='ppt') echo 'selected'; ?>>PPT</option>
      <option value="doc" <?php if($type=='doc') echo 'selected'; ?>>DOC</option>
    </select>
    <button type="submit">Search</button>
  </form>
</section>

<section class="materials-section">
  <h2>Study Materials</h2>
  <div class="materials-grid">

<?php
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $file = $row['file_path'];
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        switch ($ext) {
            case 'pdf': $icon = 'pdf.png'; break;
            case 'ppt': case 'pptx': $icon = 'ppt.png'; break;
            case 'doc': case 'docx': $icon = 'doc.png'; break;
            default: $icon = 'file.png';
        }

        echo "
        <div class='material-card'>
          <img src='$icon' class='file-icon'>
          <h3>".htmlspecialchars($row['title'], ENT_QUOTES)."</h3>
          <p>Uploaded by: ".htmlspecialchars($row['username'], ENT_QUOTES)."</p>
          <a href='uploads/".htmlspecialchars($file, ENT_QUOTES)."' class='btn primary' download>Download</a>
        </div>";
    }
} else {
    echo '<p>No materials found.</p>';
}
$result = mysqli_query($conn, "SELECT * FROM materials WHERE status='approved'");

?>

  </div>
</section>

</body>
</html>
