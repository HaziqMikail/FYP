<?php
session_start();
require '../database/db.php';

// Redirect if not a logged-in buyer
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'buyer') {
    header("Location: ../login/login.php");
    exit();
}

$buyer_id = $_SESSION['id'];

// Handle "Item Received" action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transaction_id'])) {
    $transaction_id = $_POST['transaction_id'];

    // Update status to completed
    $update = $conn->prepare("UPDATE transactions SET status = 'completed' WHERE transaction_id = ? AND buyer_id = ? AND status IN ('shipped','paid')");
    $update->bind_param("si", $transaction_id, $buyer_id);
    $update->execute();
    $update->close();
}

// Fetch buyer's purchases
$stmt = $conn->prepare("SELECT t.*, u.username AS seller_name 
                        FROM transactions t 
                        JOIN users u ON t.seller_id = u.id 
                        WHERE t.buyer_id = ? 
                        ORDER BY t.created_at DESC");
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Purchases | BRUY</title>
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
    .btn {
      background: #0c1f45;
      color: #fff;
      padding: 8px 14px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: 0.3s;
      margin-top: 10px;
    }
    .btn:hover {
      background: #00bcd4;
    }
    .status {
      padding: 6px 10px;
      border-radius: 8px;
      font-weight: bold;
      display: inline-block;
      margin-top: 5px;
    }
    .pending { background: #ffeeba; color: #856404; }
    .paid { background: #b8daff; color: #004085; }
    .shipped { background: #d4edda; color: #155724; }
    .completed { background: #c3e6cb; color: #155724; }
  </style>
</head>
<body>

<div class="sidebar">
  <div class="logo"><img src="logo.png" alt="BRUY Logo" /></div>
  <ul>
    <li><a href="buyer-dashboard.php">Dashboard</a></li>
    <li><a href="buyer-join-transaction.php">Join Transaction</a></li>
    <li class="active"><a href="buyer-my-purchases.php">My Purchases</a></li>
    <li><a href="buyer-disputes.php">Disputes</a></li>
    <li><a href="buyer-profile.php">Profile</a></li>
    <li><a href="../login/login.php">Logout</a></li>
  </ul>
</div>

<div class="main fade-in">
  <h1>My Purchases</h1>

  <?php if ($result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
      <div class="transaction-card">
        <h3><?= htmlspecialchars($row['item_name']) ?> (<?= $row['transaction_id'] ?>)</h3>
        <p><strong>Amount:</strong> RM <?= number_format($row['amount'], 2) ?></p>
        <p><strong>Type:</strong> <?= ucfirst($row['product_type']) ?></p>
        <p><strong>Seller:</strong> <?= htmlspecialchars($row['seller_name']) ?></p>
        <p><span class="status <?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></p>

        <?php if ($row['status'] === 'shipped'): ?>
          <p>📦 <strong>Tracking Number:</strong> <?= htmlspecialchars($row['tracking_number']) ?></p>
          <p>🖼️ <strong>Proof:</strong> <a href="../<?= $row['shipping_proof'] ?>" target="_blank">View Image</a></p>
        <?php elseif ($row['status'] === 'paid' && $row['product_type'] === 'digital'): ?>
          <p>✅ Your digital product is available. (Seller will provide delivery details.)</p>
        <?php endif; ?>

        <?php if (in_array($row['status'], ['paid','shipped'])): ?>
          <form method="POST">
            <input type="hidden" name="transaction_id" value="<?= $row['transaction_id'] ?>">
            <button type="submit" class="btn">✔ Item Received</button>
          </form>
        <?php elseif ($row['status'] === 'completed'): ?>
          <p>🎉 You confirmed receipt. Waiting for admin to release payment.</p>
        <?php endif; ?>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p>You have not purchased anything yet.</p>
  <?php endif; ?>
</div>

</body>
</html>
