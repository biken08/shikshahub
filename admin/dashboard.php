<?php
include 'auth.php';
include '../backend/db.php';

$result = mysqli_query(
    $conn,
    "SELECT * FROM materials ORDER BY uploaded_at DESC"
);
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>
<style>
table { width:100%; border-collapse:collapse; }
th, td { padding:10px; border:1px solid #ccc; text-align:left; }
a.btn { padding:6px 10px; text-decoration:none; border-radius:4px; }

.approve { background:green; color:white; }
.delete { background:red; color:white; }

.pending { color:orange; font-weight:bold; }
.approved { color:green; font-weight:bold; }
</style>
</head>
<body>

<?php include 'navbar.php'; ?>

<h2>📚 Manage Materials</h2>

<table>
<tr>
  <th>Title</th>
  <th>Type</th>
  <th>Status</th>
  <th>Action</th>
</tr>

<?php while ($row = mysqli_fetch_assoc($result)): ?>
<tr>
  <td><?= htmlspecialchars($row['title']); ?></td>
  <td><?= htmlspecialchars($row['type']); ?></td>
  <td class="<?= $row['status']; ?>">
      <?= ucfirst($row['status']); ?>
  </td>
  <td>
    <?php if ($row['status'] === 'pending'): ?>
      <a class="btn approve"
         href="approve.php?id=<?= $row['id']; ?>">
         Approve
      </a>
    <?php endif; ?>

    <a class="btn delete"
       href="delete.php?id=<?= $row['id']; ?>"
       onclick="return confirm('Delete this material?');">
       Delete
    </a>
  </td>
</tr>
<?php endwhile; ?>

</table>

</body>
</html>
