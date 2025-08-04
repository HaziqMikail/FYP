<?php
session_start();
require '../database/db.php';

// Redirect if not buyer
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'buyer') {
    header("Location: ../login/login.php");
    exit();
}

$message = "";
$transaction = null;
$buyer_id = $_SESSION['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $transaction_id = $_POST['transaction_id'];

    // Check if still pending
    $stmt = $conn->prepare("SELECT * FROM transactions WHERE transaction_id = ? AND status = 'pending'");
    $stmt->bind_param("s", $transaction_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $transaction = $result->fetch_assoc();

        // Update transaction -> paid + assign buyer
        $update = $conn->prepare("UPDATE transactions 
                                  SET status = 'paid', buyer_id = ? 
                                  WHERE transaction_id = ?");
        $update->bind_param("is", $buyer_id, $transaction_id);
        $update->execute();
        $update->close();

        $message = "✅ Payment successful!";
    } else {
        $message = "❌ Transaction not found or already paid.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Payment Confirmation | BRUY</title>
  <link rel="stylesheet" href="user-style.css">
</head>
<body>
<div class="sidebar">
  <div class="logo"><img src="logo.png" alt="BRUY Logo"></div>
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
    <p><?= $message ?></p>
    <?php if ($transaction): ?>
      <h2><?= htmlspecialchars($transaction['item_name']) ?></h2>
      <p><strong>Amount Paid:</strong> RM <?= number_format($transaction['amount'], 2) ?></p>
      <p><strong>Transaction ID:</strong> <?= htmlspecialchars($transaction['transaction_id']) ?></p>
    <?php endif; ?>
    <a href="buyer-my-purchases.php"><button class="btn">View My Purchases</button></a>
  </div>
</div>
</body>
</html>
