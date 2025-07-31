<?php
session_start();
require '../database/db.php';

// Redirect if not a logged-in seller
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../login/login.php");
    exit();
}

$seller_id = $_SESSION['id'];

// Fetch seller's transactions
$stmt = $conn->prepare("SELECT * FROM transactions WHERE seller_id = ? ORDER BY created_at DESC");
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
    .transaction-card {
      background: #fff;
      padding: 20px;
      margin-bottom: 20px;
      border-radius: 12px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .transaction-card h3 {
      margin: 0 0 10px;
    }
    .shipping-form input, .shipping-form button {
      display: block;
      margin: 8px 0;
      padding: 10px;
      width: 100%;
    }
    .shipping-form button,
    .view-btn {
      background: #0c1f45;
      color: white;
      border: none;
      cursor: pointer;
      border-radius: 6px;
      padding: 10px 15px;
      text-align: center;
      text-decoration: none;
      display: inline-block;
      margin-top: 10px;
    }
    .shipping-form button:hover,
    .view-btn:hover {
      background: #00bcd4;
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

<div class="main fade-in">
  <h1>My Transactions</h1>

  <?php while ($row = $result->fetch_assoc()): ?>
    <div class="transaction-card">
      <h3><?= htmlspecialchars($row['item_name']) ?> (<?= $row['transaction_id'] ?>)</h3>
      <p><strong>Amount:</strong> RM <?= number_format($row['amount'], 2) ?></p>
      <p><strong>Type:</strong> <?= ucfirst($row['product_type']) ?></p>
      <p><strong>Status:</strong> <?= ucfirst($row['status']) ?></p>

      <!-- View Product Button -->
      <?php if (!empty($row['marketplace_link'])): ?>
        <a href="<?= htmlspecialchars($row['marketplace_link']) ?>" target="_blank" class="view-btn">🔗 View Product</a>
      <?php endif; ?>

      <!-- If digital product and paid, seller doesn’t need shipping -->
      <?php if ($row['status'] === 'paid' && $row['product_type'] === 'physical'): ?>
        <form class="shipping-form" action="upload-shipping.php" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="transaction_id" value="<?= $row['transaction_id'] ?>">
          <input type="text" name="tracking_number" placeholder="Enter Tracking Number" required>
          <input type="file" name="shipping_proof" accept="image/*" required>
          <button type="submit">Submit Shipping Details</button>
        </form>

      <?php elseif ($row['status'] === 'shipped'): ?>
        <p>📦 <strong>Tracking Number:</strong> <?= htmlspecialchars($row['tracking_number']) ?></p>
        <p>🖼️ <strong>Proof:</strong> <a href="../<?= $row['shipping_proof'] ?>" target="_blank">View Image</a></p>
      <?php endif; ?>
    </div>
  <?php endwhile; ?>
</div>

</body>
</html>
