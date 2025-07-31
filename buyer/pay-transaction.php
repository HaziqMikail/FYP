<?php
session_start();
require '../database/db.php';

// Redirect if not a logged-in buyer
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'buyer') {
    header("Location: ../login/login.php");
    exit();
}

$message = "";
$transaction = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $transaction_id = $_POST['transaction_id'];

    // Check if pending
    $stmt = $conn->prepare("SELECT * FROM transactions WHERE transaction_id = ? AND status = 'pending'");
    $stmt->bind_param("s", $transaction_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Update status
        $update = $conn->prepare("UPDATE transactions SET status = 'paid' WHERE transaction_id = ?");
        $update->bind_param("s", $transaction_id);
        $update->execute();
        $update->close();

        $transaction = $result->fetch_assoc();
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
  <style>
    .card {
      background: #fff;
      padding: 20px;
      margin-top: 20px;
      border-radius: 12px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      text-align: center;
    }
    .btn {
      background: #0c1f45;
      color: #fff;
      padding: 10px 16px;
      border-radius: 8px;
      border: none;
      cursor: pointer;
      margin-top: 15px;
      transition: 0.3s;
    }
    .btn:hover {
      background: #00bcd4;
    }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
  </style>
</head>
<body>

<div class="sidebar">
  <div class="logo"><img src="logo.png" alt="BRUY Logo" /></div>
  <ul>
    <li><a href="buyer-dashboard.php">Dashboard</a></li>
    <li><a href="buyer-join-transaction.php">Join Transaction</a></li>
    <li><a href="buyer-disputes.php">Disputes</a></li>
    <li><a href="buyer-profile.php">Profile</a></li>
    <li><a href="../login/login.php">Logout</a></li>
  </ul>
</div>

<div class="main fade-in">
  <h1>Payment Confirmation</h1>

  <div class="card">
    <p class="<?= strpos($message, '✅') !== false ? 'success' : 'error' ?>"><?= $message ?></p>

    <?php if ($transaction): ?>
      <h2><?= htmlspecialchars($transaction['item_name']) ?></h2>
      <p><strong>Amount Paid:</strong> RM <?= number_format($transaction['amount'], 2) ?></p>
      <p><strong>Transaction ID:</strong> <?= htmlspecialchars($transaction['transaction_id']) ?></p>
    <?php endif; ?>

    <a href="buyer-dashboard.php"><button class="btn">Go to Dashboard</button></a>
  </div>
</div>

</body>
</html>
