<?php
session_start();
require '../database/db.php';

// Redirect if not logged in or not a buyer
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'buyer') {
    header("Location: ../login/login.php");
    exit();
}

$buyer_id = $_SESSION['id'];
$username = $_SESSION['username'];

// Fetch stats
$total = $completed = $pending = $disputes = 0;

if ($buyer_id) {
  // Total purchases
  $stmt = $conn->prepare("SELECT COUNT(*) FROM transactions WHERE buyer_id = ?");
  $stmt->bind_param("i", $buyer_id);
  $stmt->execute();
  $stmt->bind_result($total);
  $stmt->fetch();
  $stmt->close();

  // Completed purchases
  $stmt = $conn->prepare("SELECT COUNT(*) FROM transactions WHERE buyer_id = ? AND status = 'completed'");
  $stmt->bind_param("i", $buyer_id);
  $stmt->execute();
  $stmt->bind_result($completed);
  $stmt->fetch();
  $stmt->close();

  // Pending purchases
  $stmt = $conn->prepare("SELECT COUNT(*) FROM transactions WHERE buyer_id = ? AND status = 'pending'");
  $stmt->bind_param("i", $buyer_id);
  $stmt->execute();
  $stmt->bind_result($pending);
  $stmt->fetch();
  $stmt->close();

  // Disputes related to buyer's purchases
  $stmt = $conn->prepare("SELECT COUNT(*) FROM disputes WHERE transaction_id IN (SELECT transaction_id FROM transactions WHERE buyer_id = ?)");
  $stmt->bind_param("i", $buyer_id);
  $stmt->execute();
  $stmt->bind_result($disputes);
  $stmt->fetch();
  $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Buyer Dashboard | BRUY</title>
  <link rel="stylesheet" href="user-style.css">
  <style>
    .dashboard-container {
      margin-left: 260px;
      padding: 40px;
    }
    .welcome {
      font-size: 22px;
      margin-bottom: 20px;
    }
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
    }
    .stat-card {
      background: #fff;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      text-align: center;
    }
    .stat-card h2 {
      font-size: 28px;
      margin: 0;
      color: #0c1f45;
    }
    .stat-card p {
      font-size: 16px;
      color: #555;
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <div class="logo"><img src="logo.png" alt="BRUY Logo" /></div>
    <ul>
      <li class="active"><a href="buyer-dashboard.php">Dashboard</a></li>
      <li><a href="buyer-join-transaction.php">Join Transaction</a></li>
      <li><a href="buyer-my-purchases.php">My Purchases</a></li>
      <li><a href="buyer-disputes.php">Disputes</a></li>
      <li><a href="buyer-profile.php">Profile</a></li>
      <li><a href="../login/login.php">Logout</a></li>
    </ul>
  </div>

  <!-- Main -->
  <div class="dashboard-container fade-in">
    <div class="welcome">👋 Welcome, <strong><?= htmlspecialchars($username) ?></strong></div>

    <div class="stats-grid">
      <div class="stat-card">
        <h2><?= $total ?></h2>
        <p>Total Purchases</p>
      </div>
      <div class="stat-card">
        <h2><?= $completed ?></h2>
        <p>Completed Purchases</p>
      </div>
      <div class="stat-card">
        <h2><?= $pending ?></h2>
        <p>Pending Purchases</p>
      </div>
      <div class="stat-card">
        <h2><?= $disputes ?></h2>
        <p>Disputes</p>
      </div>
    </div>
  </div>
</body>
</html>
