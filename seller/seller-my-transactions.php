<?php
session_start();
require '../database/db.php';

// Redirect if not logged in or not a seller
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../login/login.php");
    exit();
}

$seller_id = $_SESSION['id'];

// Fetch transactions for this seller
$stmt = $conn->prepare("SELECT transaction_id, item_name, amount, status, product_type, created_at FROM transactions WHERE seller_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Transactions | BRUY</title>
  <link rel="stylesheet" href="user-style.css">
  <style>
    .main-content {
      margin-left: 260px;
      padding: 40px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    th, td {
      padding: 12px;
      border-bottom: 1px solid #ddd;
      text-align: left;
    }
    th {
      background-color: #cdcbffff;
    }
    .status {
      text-transform: capitalize;
      font-weight: bold;
    }
  </style>
</head>
<body>

<div class="sidebar">
  <div class="logo"><img src="logo.png" alt="BRUY Logo" /></div>
  <ul>
    <li><a href="seller-dashboard.php">Dashboard</a></li>
    <li><a href="create-product.php">Create Product</a></li>
    <li class="active"><a href="seller-my-transactions.php">My Transactions</a></li>
    <li><a href="seller-disputes.php">Disputes</a></li>
    <li><a href="seller-profile.php">Profile</a></li>
    <li><a href="../login/login.php">Logout</a></li>
  </ul>
</div>

<div class="main-content fade-in">
  <h1>My Transactions</h1>

  <?php if ($result->num_rows > 0): ?>
    <table>
      <thead>
        <tr>
          <th>Code</th>
          <th>Item Name</th>
          <th>Price (MYR)</th>
          <th>Product Type</th>
          <th>Status</th>
          <th>Date Created</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><strong><?= htmlspecialchars($row['transaction_id']) ?></strong></td>
            <td><?= htmlspecialchars($row['item_name']) ?></td>
            <td><?= number_format($row['amount'], 2) ?></td>
            <td><?= ucfirst($row['product_type']) ?></td>
            <td class="status"><?= ucfirst($row['status']) ?></td>
            <td><?= date("Y-m-d H:i", strtotime($row['created_at'])) ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>No transactions found.</p>
  <?php endif; ?>

</div>

</body>
</html>
