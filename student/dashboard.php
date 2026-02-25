<?php
// student/dashboard.php
session_start();
require_once '../backend/auth.php';
require_once '../backend/db.php';

if ($_SESSION['role'] !== 'student') {
    header('Location: ../login.html');
    exit;
}

$department = $_SESSION['department'];

// Fetch approved materials for this department
$stmt = $conn->prepare("SELECT m.*, u.fullname AS teacher_name FROM materials m 
                        JOIN users u ON m.user_id = u.id 
                        WHERE m.department = ? AND m.status = 'approved' 
                        ORDER BY m.uploaded_at DESC");
$stmt->bind_param("s", $department);
$stmt->execute();
$materials = $stmt->get_result();

// Include header (adjust path as needed)
include '../header.php';
?>

<div class="container" style="margin-top: 30px;">
    <div class="row">
        <div class="col-md-12">
            <div class="welcome-box">
                <h2>Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?>!</h2>
                <p>Your department: <strong><?php echo htmlspecialchars($department); ?></strong></p>
                <a href="../backend/logout.php" class="btn btn-danger">Logout</a>
            </div>
            <hr>
            <h3>Study Materials for <?php echo htmlspecialchars($department); ?></h3>

            <?php if ($materials->num_rows == 0): ?>
                <div class="alert alert-info">No materials available for your department yet.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Type</th>
                                <th>Uploaded By</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $materials->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo htmlspecialchars(substr($row['description'], 0, 80)) . '...'; ?></td>
                                <td><?php echo htmlspecialchars($row['type']); ?></td>
                                <td><?php echo htmlspecialchars($row['teacher_name']); ?></td>
                                <td><?php echo date('d M Y', strtotime($row['uploaded_at'])); ?></td>
                                <td><a class="btn btn-primary btn-sm" href="../uploads/<?php echo urlencode($row['file_path']); ?>" target="_blank">View/Download</a></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>