<?php
session_start();
require_once '../database/db.php';

// Handle status update
if (isset($_POST['update_status'])) {
  $disputeId = $_POST['dispute_id'];
  $newStatus = $_POST['status'];
  $adminNotes = $_POST['admin_notes'];

  $stmt = $conn->prepare("UPDATE disputes SET status = ?, admin_notes = ? WHERE id = ?");
  $stmt->bind_param("ssi", $newStatus, $adminNotes, $disputeId);
  $stmt->execute();
}

// Fetch all disputes with user info (including role)
$sql = "SELECT d.*, u.username, u.email, u.role 
        FROM disputes d
        JOIN users u ON d.user_id = u.id
        ORDER BY d.submitted_at DESC";
$result = $conn->query($sql);
$disputes = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Disputes | Admin Panel</title>
  <link rel="stylesheet" href="admin-style.css">
</head>
<body>

<div class="admin-container">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="logo">
      <img src="logo.png" alt="BRUY Logo">
      <h3>Welcome, <?php echo $_SESSION['admin_name']; ?></h3>
    </div>
    <ul>
      <li><a href="admin-dashboard.php">Dashboard</a></li>
      <li><a href="admin-transactions.php">Transactions</a></li>
      <li><a href="admin-users.php">User List</a></li>
      <li class="active"><a href="admin-disputes.php">Dispute Center</a></li>
      <li><a href="admin-dashboard.php?logout=true">Logout</a></li>
    </ul>
  </aside>

  <!-- Main Content -->
  <main class="dashboard">
    <header><h1>Dispute Center</h1></header>

    <table class="user-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Transaction ID</th>
          <th>User</th>
          <th>Email</th>
          <th>Role</th> <!-- ✅ New column for role -->
          <th>Reason</th>
          <th>Status</th>
          <th>Admin Notes</th>
          <th>Submitted</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($disputes) > 0): ?>
          <?php foreach ($disputes as $d): ?>
            <tr>
              <td><?php echo $d['id']; ?></td>
              <td><?php echo htmlspecialchars($d['transaction_id']); ?></td>
              <td><?php echo htmlspecialchars($d['username']); ?></td>
              <td><?php echo htmlspecialchars($d['email']); ?></td>
              <td><?php echo ucfirst($d['role']); ?></td> <!-- ✅ Display role -->
              <td><?php echo nl2br(htmlspecialchars($d['reason'])); ?></td>
              <td><?php echo ucfirst($d['status']); ?></td>
              <td><?php echo nl2br(htmlspecialchars($d['admin_notes'])); ?></td>
              <td><?php echo $d['submitted_at']; ?></td>
              <td>
                <form method="POST" style="display: flex; flex-direction: column; gap: 6px;">
                  <input type="hidden" name="dispute_id" value="<?php echo $d['id']; ?>">
                  <select name="status">
                    <option value="open" <?= $d['status'] == 'open' ? 'selected' : '' ?>>Open</option>
                    <option value="in_progress" <?= $d['status'] == 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                    <option value="resolved" <?= $d['status'] == 'resolved' ? 'selected' : '' ?>>Resolved</option>
                    <option value="rejected" <?= $d['status'] == 'rejected' ? 'selected' : '' ?>>Rejected</option>
                  </select>
                  <textarea name="admin_notes" placeholder="Admin notes..." rows="2"><?php echo htmlspecialchars($d['admin_notes']); ?></textarea>
                  <button type="submit" name="update_status">Update</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="10">No disputes found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </main>
</div>

</body>
</html>
