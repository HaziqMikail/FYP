<?php
session_start();
require_once '../database/db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login/admin-login.php");
    exit();
}

$message = "";

// Handle admin action (update status/notes)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['request_id'])) {
    $id = intval($_POST['request_id']);
    $status = $_POST['status'];
    $admin_notes = trim($_POST['admin_notes']);

    $stmt = $conn->prepare("UPDATE support_requests SET status=?, admin_notes=? WHERE id=?");
    $stmt->bind_param("ssi", $status, $admin_notes, $id);

    if ($stmt->execute()) {
        // Fetch user email for notification
        $resultUser = $conn->query("SELECT email, issue FROM support_requests WHERE id=$id");
        if ($resultUser && $resultUser->num_rows > 0) {
            $userData = $resultUser->fetch_assoc();
            $to = $userData['email'];
            $subject = "Update on Your Support Request (ID: $id)";
            $body = "Hello,\n\nYour support request has been updated.\n\n".
                    "Issue: ".$userData['issue']."\n".
                    "New Status: ".ucfirst($status)."\n".
                    "Admin Notes: ".$admin_notes."\n\n".
                    "Thank you,\nBRUY Support Team";

            // Send notification email (only works if mail is configured)
            @mail($to, $subject, $body, "From: no-reply@bruy.com");
        }

        $message = "<span style='color:green;'>✅ Request updated successfully & notification sent.</span>";
    } else {
        $message = "<span style='color:red;'>❌ Failed to update request.</span>";
    }
    $stmt->close();
}

// Fetch support requests
$sql = "SELECT id, email, issue, status, submitted_at, admin_notes 
        FROM support_requests 
        ORDER BY submitted_at DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Support Requests | BRUY</title>
  <link rel="stylesheet" href="admin-style.css">
  <style>
    .content-section { padding: 20px; }
    .banner { margin-bottom: 10px; font-weight: bold; }
    table {
      width: 100%; border-collapse: collapse; margin-top: 15px; background: #fff;
      border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    table th, table td { padding: 12px 15px; border-bottom: 1px solid #ddd; text-align: left; vertical-align: top; }
    table th { background: #0c1f45; color: #fff; }
    .status-badge { padding: 5px 10px; border-radius: 5px; font-size: 0.85em; font-weight: bold; }
    .open { background: #ff9800; color: white; }
    .resolved { background: #4caf50; color: white; }
    .in_progress { background: #2196f3; color: white; }
    .admin-form textarea { width: 100%; padding: 5px; border-radius: 5px; border: 1px solid #ccc; }
    .admin-form select, .admin-form button { margin-top: 5px; padding: 6px 10px; }
    .admin-form button { background: #0c1f45; color: #fff; border: none; border-radius: 6px; cursor: pointer; }
    .admin-form button:hover { background: #00bcd4; }
  </style>
</head>
<body>

<div class="admin-container">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="logo">
      <img src="logo.png" alt="BRUY Logo">
      <h3>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></h3>
    </div>
    <ul>
      <li><a href="admin-dashboard.php">Dashboard</a></li>
      <li><a href="admin-users.php">User List</a></li>
      <li><a href="admin-transactions.php">Transactions</a></li>
      <li><a href="admin-completed-transactions.php">Completed</a></li>
      <li><a href="admin-disputes.php">Dispute Center</a></li>
      <li class="active"><a href="admin-support.php">Support Requests</a></li>
      <li><a href="admin-dashboard.php?logout=true">Logout</a></li>
    </ul>
  </aside>

  <!-- Main Content -->
  <main class="dashboard">
    <header>
      <h1>Support Requests</h1>
    </header>

    <section class="content-section">
      <?php if (!empty($message)): ?>
        <div class="banner"><?= $message ?></div>
      <?php endif; ?>

      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Email</th>
            <th>Issue</th>
            <th>Status</th>
            <th>Submitted</th>
            <th>Admin Notes / Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= nl2br(htmlspecialchars($row['issue'])) ?></td>
                <td><span class="status-badge <?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
                <td><?= $row['submitted_at'] ?></td>
                <td>
                  <form method="POST" class="admin-form">
                    <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
                    <textarea name="admin_notes" rows="2" placeholder="Add notes..."><?= htmlspecialchars($row['admin_notes']) ?></textarea><br>
                    <select name="status">
                      <option value="open" <?= $row['status']=="open"?"selected":"" ?>>Open</option>
                      <option value="in_progress" <?= $row['status']=="in_progress"?"selected":"" ?>>In Progress</option>
                      <option value="resolved" <?= $row['status']=="resolved"?"selected":"" ?>>Resolved</option>
                    </select>
                    <button type="submit">Update</button>
                  </form>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="6" style="text-align:center;">No support requests found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </section>
  </main>
</div>

</body>
</html>
