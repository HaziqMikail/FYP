<?php
session_start();
require_once '../database/db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login/admin-login.php");
    exit();
}

// Logout handler
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../login/admin-login.php");
    exit();
}

// Fetch summary data
$user_count = $txn_count = $pending_txn = $total_earnings = 0;

// Count total users
$result = $conn->query("SELECT COUNT(*) as total FROM users");
if ($result && $row = $result->fetch_assoc()) {
    $user_count = $row['total'];
}

// Count transactions
$result = $conn->query("SELECT COUNT(*) as total FROM transactions");
if ($result && $row = $result->fetch_assoc()) {
    $txn_count = $row['total'];
}

// Count pending transactions
$result = $conn->query("SELECT COUNT(*) as total FROM transactions WHERE status='pending'");
if ($result && $row = $result->fetch_assoc()) {
    $pending_txn = $row['total'];
}

// Total earnings
$result = $conn->query("SELECT SUM(amount) as total FROM transactions WHERE status IN ('completed', 'released')");
if ($result && $row = $result->fetch_assoc()) {
    $total_earnings = $row['total'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard | BRUY</title>
  <link rel="stylesheet" href="admin-style.css">
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
      <li class="active"><a href="admin-dashboard.php">Dashboard</a></li>
      <li><a href="admin-users.php">User List</a></li>
      <li><a href="admin-transactions.php">Transactions</a></li>
      <li><a href="admin-completed-transactions.php">Completed</a></li>
      <li><a href="admin-digital-verification.php">Digital Verification</a></li>
      <li><a href="admin-disputes.php">Dispute Center</a></li>
      <li><a href="admin-support.php">Support Requests</a></li>
      <li><a href="admin-dashboard.php?logout=true">Logout</a></li>
    </ul>
  </aside>

  <!-- Dashboard Content -->
  <main class="dashboard">
    <header>
      <h1>Admin Dashboard</h1>
    </header>

    <section class="content-section">
      <br><p>Welcome to the Admin Dashboard. You can manage transactions, disputes, and monitor earnings here.</p>
    </section>

    <section class="cards">
      <div class="card"><h3><?php echo $user_count; ?></h3><p>Total Users</p></div>
      <div class="card"><h3><?php echo $txn_count; ?></h3><p>Total Transactions</p></div>
      <div class="card"><h3><?php echo number_format($total_earnings, 2); ?></h3><p>Total Earnings</p></div>
      <div class="card"><h3><?php echo $pending_txn; ?></h3><p>Pending Transactions</p></div>
    </section>

  </main>
</div>

</body>
</html>
