<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'buyer') {
    header("Location: ../login/login.php");
    exit();
}

$message = "";
$transaction = null;
$buyer_id = $_SESSION['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $transaction_id = $_POST['transaction_id'];
    $payment_method = $_POST['payment_method'] ?? 'Unknown';

    // Get transaction with status 'pending' (not yet paid)
    $stmt = $conn->prepare("SELECT * FROM transactions WHERE transaction_id = ? AND status = 'pending'");
    $stmt->bind_param("s", $transaction_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $transaction = $result->fetch_assoc();

        if ($transaction['product_type'] === 'digital') {
            // For digital products, set status to pending_verification
            $new_status = 'pending_verification';
        } else {
            // For physical products, set status directly to paid
            $new_status = 'paid';
        }

        $update = $conn->prepare("UPDATE transactions SET status = ?, buyer_id = ? WHERE transaction_id = ?");
        $update->bind_param("sis", $new_status, $buyer_id, $transaction_id);
        $update->execute();
        $update->close();

        $pretty_method = str_replace('_', ' ', strtolower($payment_method));
        $message = "✅ Payment successful via $pretty_method!";
    } else {
        $message = "❌ Transaction not found or already paid.";
    }
    $stmt->close();

    // Fetch updated transaction details for display
    $stmt2 = $conn->prepare("SELECT t.*, u.username AS seller_name FROM transactions t JOIN users u ON t.seller_id = u.id WHERE t.transaction_id = ?");
    $stmt2->bind_param("s", $transaction_id);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    if ($result2->num_rows === 1) {
        $transaction = $result2->fetch_assoc();
    }
    $stmt2->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Payment Confirmation | BRUY</title>
  <link rel="stylesheet" href="user-style.css" />
  <style>
    .card p {
      margin: 8px 0;
      font-size: 16px;
      color: #222;
    }
    .btn {
      background: #0c1f45;
      color: #fff;
      border: none;
      padding: 12px 24px;
      border-radius: 8px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: background-color 0.3s ease;
      margin-top: 20px;
      display: inline-block;
      text-align: center;
      width: 100%;
    }
    .btn:hover {
      background: #00bcd4;
    }
    .transaction-details {
      border-top: 1px solid #ddd;
      margin-top: 20px;
      padding-top: 15px;
      color: #444;
    }
    .transaction-details strong {
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
    <li><a href="buyer-my-purchases.php">My Purchases</a></li>
    <li><a href="buyer-disputes.php">Disputes</a></li>
    <li><a href="buyer-profile.php">Profile</a></li>
    <li><a href="../login/logout.php">Logout</a></li>
  </ul>
</div>

<div class="main fade-in">
  <h1>Payment Confirmation</h1>
  <div class="card">
    <p><?= htmlspecialchars($message) ?></p>

    <?php if ($transaction): ?>
      <div class="transaction-details">
        <h2><?= htmlspecialchars($transaction['item_name']) ?></h2>
        <p><strong>Amount Paid:</strong> RM <?= number_format($transaction['amount'], 2) ?></p>
        <p><strong>Transaction ID:</strong> <?= htmlspecialchars($transaction['transaction_id']) ?></p>
        <p><strong>Payment Method:</strong> <?= htmlspecialchars($payment_method) ?></p>
        <p><strong>Product Type:</strong> <?= ucfirst(htmlspecialchars($transaction['product_type'])) ?></p>
        <p><strong>Seller:</strong> <?= htmlspecialchars($transaction['seller_name']) ?></p>
        <p><strong>Status:</strong> <?= ucfirst(htmlspecialchars($transaction['status'])) ?></p>
        <p><strong>Date:</strong> <?= htmlspecialchars(date("d M Y, H:i", strtotime($transaction['created_at'] ?? $transaction['updated_at'] ?? ''))) ?></p>
      </div>
    <?php endif; ?>

    <a href="buyer-my-purchases.php"><button class="btn">View My Purchases</button></a>
  </div>
</div>

</body>
</html>
