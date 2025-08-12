<?php
session_start();
require_once '../database/db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login/admin-login.php");
    exit();
}

// Search logic
$search = "";
$users = [];

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['search'])) {
    $search = trim($_GET['search']);
    $stmt = $conn->prepare("SELECT * FROM users WHERE username LIKE ? OR email LIKE ?");
    $searchTerm = "%$search%";
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    $stmt->close();
} else {
    $result = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User List | Admin Panel</title>
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
      <li class="active"><a href="admin-users.php">User List</a></li>
      <li><a href="admin-transactions.php">Transactions</a></li>
      <li><a href="admin-completed-transactions.php">Completed</a></li>
      <li><a href="admin-digital-verification.php">Digital Verification</a></li>
      <li><a href="admin-disputes.php">Dispute Center</a></li>
      <li><a href="admin-support.php">Support Requests</a></li>
      <li><a href="admin-dashboard.php?logout=true">Logout</a></li>
    </ul>
  </aside>

  <!-- Main Content -->
  <main class="dashboard">
    <header>
      <h1>User List</h1>
      <form method="GET" class="search-form">
        <input type="text" name="search" placeholder="Search username or email" value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
      </form>
    </header>

    <table class="user-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Username</th>
          <th>Email</th>
          <th>Role</th>
          <th>Created At</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($users) > 0): ?>
          <?php foreach ($users as $user): ?>
            <tr>
              <td><?php echo $user['id']; ?></td>
              <td><?php echo htmlspecialchars($user['username']); ?></td>
              <td><?php echo htmlspecialchars($user['email']); ?></td>
              <td><?php echo ucfirst($user['role']); ?></td>
              <td><?php echo $user['created_at']; ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="5">No users found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </main>
</div>

</body>
</html>
