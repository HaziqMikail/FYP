<?php
session_start();
require '../database/db.php';

// Redirect if not logged in or not a seller
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../login/login.php");
    exit();
}

$seller_id = $_SESSION['id'];
$username = $_SESSION['username'];

// Fetch stats
$total = $completed = $pending = $disputes = 0;

if ($seller_id) {
  // Total transactions
  $stmt = $conn->prepare("SELECT COUNT(*) FROM transactions WHERE seller_id = ?");
  $stmt->bind_param("i", $seller_id);
  $stmt->execute();
  $stmt->bind_result($total);
  $stmt->fetch();
  $stmt->close();

  // Completed transactions
  $stmt = $conn->prepare("SELECT COUNT(*) FROM transactions WHERE seller_id = ? AND status = 'completed'");
  $stmt->bind_param("i", $seller_id);
  $stmt->execute();
  $stmt->bind_result($completed);
  $stmt->fetch();
  $stmt->close();

  // Pending transactions
  $stmt = $conn->prepare("SELECT COUNT(*) FROM transactions WHERE seller_id = ? AND status = 'pending'");
  $stmt->bind_param("i", $seller_id);
  $stmt->execute();
  $stmt->bind_result($pending);
  $stmt->fetch();
  $stmt->close();

  // Disputes
  $stmt = $conn->prepare("SELECT COUNT(*) FROM disputes WHERE transaction_id IN (SELECT transaction_id FROM transactions WHERE seller_id = ?)");
  $stmt->bind_param("i", $seller_id);
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
  <title>Seller Dashboard | BRUY</title>
  <link rel="stylesheet" href="user-style.css">
  <style>
    .dashboard-container {
      margin-left: 260px;
      padding: 40px;
    }
    .welcome-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
    }
    .welcome {
      font-size: 22px;
      font-weight: 500;
      color: #0c1f45;
    }
    .switch-btn {
      background: #0c1f45;
      color: #fff;
      padding: 8px 16px;
      border-radius: 8px;
      text-decoration: none;
      font-size: 14px;
      font-weight: bold;
      transition: 0.3s ease;
    }
    .switch-btn:hover {
      background: #00bcd4;
    }
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
    }
    .stat-card {
      background: #fff;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      text-align: center;
      transition: transform 0.2s;
    }
    .stat-card:hover {
      transform: translateY(-5px);
    }
    .stat-card h2 {
      font-size: 32px;
      margin: 0;
      color: #0c1f45;
    }
    .stat-card p {
      font-size: 16px;
      color: #555;
      margin-top: 8px;
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <div class="logo"><img src="logo.png" alt="BRUY Logo" /></div>
    <ul>
      <li class="active"><a href="seller-dashboard.php">Dashboard</a></li>
      <li><a href="create-product.php">Create Product</a></li>
      <li><a href="seller-my-transactions.php">My Transactions</a></li>
      <li><a href="seller-disputes.php">Disputes</a></li>
      <li><a href="seller-profile.php">Profile</a></li>
      <li><a href="../login/logout.php">Logout</a></li>
    </ul>
  </div>

  <!-- Main -->
  <div class="dashboard-container fade-in">
    <div class="welcome-bar">
      <div class="welcome">👋 Welcome, <strong><?= htmlspecialchars($username) ?></strong></div>
      <a href="../login/logout.php" class="switch-btn">🔄 Switch Role</a>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <h2><?= $total ?></h2>
        <p>Total Transactions</p>
      </div>
      <div class="stat-card">
        <h2><?= $completed ?></h2>
        <p>Completed Transactions</p>
      </div>
      <div class="stat-card">
        <h2><?= $pending ?></h2>
        <p>Pending Transactions</p>
      </div>
      <div class="stat-card">
        <h2><?= $disputes ?></h2>
        <p>Total Disputes</p>
      </div>
    </div>
  </div>
</body>
</html>
