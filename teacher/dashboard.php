<?php
// teacher/dashboard.php
session_start();
require_once '../backend/auth.php';
require_once '../backend/db.php';

if ($_SESSION['role'] !== 'teacher') {
    header('Location: ../login.html');
    exit;
}

$teacher_id = $_SESSION['user_id'];

// Fetch teacher's materials
$stmt = $conn->prepare("SELECT * FROM materials WHERE user_id = ? ORDER BY uploaded_at DESC");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$materials = $stmt->get_result();

include '../header.php';
?>

<div class="container" style="margin-top: 30px;">
    <div class="row">
        <div class="col-md-12">
            <div class="welcome-box">
                <h2>Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?>!</h2>
                <p>Your department: <strong><?php echo htmlspecialchars($_SESSION['department']); ?></strong></p>
                <a href="../backend/logout.php" class="btn btn-danger">Logout</a>
                <a href="upload.php" class="btn btn-success">Upload New Material</a>
            </div>
            <hr>

            <?php if (isset($_GET['upload']) && $_GET['upload'] == 'success'): ?>
                <div class="alert alert-success">Material uploaded successfully! It is now pending approval.</div>
            <?php endif; ?>

            <h3>Your Uploaded Materials</h3>

            <?php if ($materials->num_rows == 0): ?>
                <div class="alert alert-info">You haven't uploaded any materials yet.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $materials->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo htmlspecialchars(substr($row['description'], 0, 50)) . '...'; ?></td>
                                <td><?php echo htmlspecialchars($row['type']); ?></td>
                                <td>
                                    <?php if ($row['status'] == 'approved'): ?>
                                        <span class="badge badge-success">Approved</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d M Y', strtotime($row['uploaded_at'])); ?></td>
                                <td>
                                    <a class="btn btn-primary btn-sm" href="../uploads/<?php echo urlencode($row['file_path']); ?>" target="_blank">View</a>
                                    <a class="btn btn-danger btn-sm" href="delete.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Delete this material?')">Delete</a>
                                </td>
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