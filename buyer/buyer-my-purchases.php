<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'buyer') {
    header("Location: ../login/login.php");
    exit();
}

$buyer_id = $_SESSION['id'];

// Handle "Item Received" confirmation for physical products
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transaction_id'])) {
    $transaction_id = $_POST['transaction_id'];

    $stmtCheck = $conn->prepare("SELECT product_type FROM transactions WHERE transaction_id = ? AND buyer_id = ? AND status IN ('shipped','paid')");
    $stmtCheck->bind_param("si", $transaction_id, $buyer_id);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result();
    if ($resCheck->num_rows === 1) {
        $rowCheck = $resCheck->fetch_assoc();
        if ($rowCheck['product_type'] === 'physical') {
            $update = $conn->prepare("UPDATE transactions 
                                      SET status = 'completed' 
                                      WHERE transaction_id = ? AND buyer_id = ? AND status IN ('shipped','paid')");
            $update->bind_param("si", $transaction_id, $buyer_id);
            $update->execute();
            $update->close();
        }
    }
    $stmtCheck->close();
}

// Fetch buyer’s purchases
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
  <meta charset="UTF-8" />
  <title>My Purchases | BRUY</title>
  <link rel="stylesheet" href="user-style.css" />
  <style>
    .transaction-card {
      background: #fff;
      padding: 20px;
      margin-bottom: 20px;
      border-radius: 12px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .btn {
      background: #0c1f45;
      color: #fff;
      padding: 8px 14px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      margin-top: 10px;
      font-weight: 600;
      transition: background-color 0.3s ease;
    }
    .btn:hover {
      background: #00bcd4;
    }
    .status {
      padding: 6px 10px;
      border-radius: 8px;
      font-weight: bold;
      display: inline-block;
    }
    .pending {
      background: #ffeeba;
      color: #856404;
    }
    .paid {
      background: #b8daff;
      color: #004085;
    }
    .pending_verification {
      background: #fff3cd;
      color: #856404;
    }
    .verified {
      background: #c3e6cb;
      color: #155724;
    }
    .shipped {
      background: #d4edda;
      color: #155724;
    }
    .completed {
      background: #c3e6cb;
      color: #155724;
    }
    .credential-box {
      background: #eef9f9;
      border: 1px solid #0c1f45;
      padding: 12px;
      margin-top: 10px;
      border-radius: 8px;
      font-family: monospace;
      white-space: pre-wrap;
      color: #0c1f45;
    }
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
    <li><a href="../login/logout.php">Logout</a></li>
  </ul>
</div>

<div class="main fade-in">
  <h1>My Purchases</h1>

  <?php if ($result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
      <div class="transaction-card">
        <h3><?= htmlspecialchars($row['item_name']) ?> (<?= htmlspecialchars($row['transaction_id']) ?>)</h3>
        <p><strong>Amount:</strong> RM <?= number_format($row['amount'], 2) ?></p>
        <p><strong>Seller:</strong> <?= htmlspecialchars($row['seller_name']) ?></p>
        <p><span class="status <?= htmlspecialchars($row['status']) ?>"><?= ucfirst(str_replace('_', ' ', htmlspecialchars($row['status']))) ?></span></p>

        <?php if ($row['status'] === 'shipped'): ?>
          <p>📦 Tracking: <?= htmlspecialchars($row['tracking_number']) ?></p>
          <p>🖼 Proof: <a href="../<?= htmlspecialchars($row['shipping_proof']) ?>" target="_blank">View</a></p>
        <?php endif; ?>

        <?php if ($row['product_type'] === 'digital'): ?>
          <?php if ($row['status'] === 'verified'): ?>
            <?php 
              $creds = json_decode($row['digital_credentials'], true);
              if (json_last_error() === JSON_ERROR_NONE && is_array($creds)): ?>
                <div class="credential-box">
                  <strong>Digital Credentials:</strong><br>
                  Email: <?= htmlspecialchars($creds['email'] ?? 'N/A') ?><br>
                  Password: <?= htmlspecialchars($creds['password'] ?? 'N/A') ?>
                </div>
              <?php else: ?>
                <div class="credential-box">
                  <?= nl2br(htmlspecialchars($row['digital_credentials'])) ?>
                </div>
              <?php endif; ?>
          <?php elseif ($row['status'] === 'pending_verification'): ?>
            <p>⌛ Waiting for seller to verify your payment and provide credentials.</p>
          <?php else: ?>
            <p>Payment processing or other status: <?= ucfirst(htmlspecialchars($row['status'])) ?></p>
          <?php endif; ?>
          <!-- No confirm receipt button for digital -->
        <?php else: ?>
          <?php if ($row['status'] === 'shipped' || $row['status'] === 'paid'): ?>
            <form method="post" style="margin-top:10px;">
              <input type="hidden" name="transaction_id" value="<?= htmlspecialchars($row['transaction_id']) ?>">
              <button type="submit" class="btn">Confirm Receipt</button>
            </form>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p>No purchases found.</p>
  <?php endif; ?>
</div>

</body>
</html>
