<?php
session_start();
require '../database/db.php';

// Redirect if not buyer
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'buyer') {
    header("Location: ../login/login.php");
    exit();
}

if (!isset($_POST['transaction_id'])) {
    header("Location: buyer-join-transaction.php");
    exit();
}

$transaction_id = $_POST['transaction_id'];

// Fetch transaction details
$stmt = $conn->prepare("SELECT * FROM transactions WHERE transaction_id = ? AND status = 'pending'");
$stmt->bind_param("s", $transaction_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $stmt->close();
    header("Location: buyer-join-transaction.php?error=Transaction+not+found+or+already+paid");
    exit();
}

$transaction = $result->fetch_assoc();
$stmt->close();

// Calculate service charge (12% max RM12)
$service_charge = min($transaction['amount'] * 0.12, 12);
$total_to_pay = $transaction['amount'] + $service_charge;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Payment | BRUY</title>
  <link rel="stylesheet" href="user-style.css" />
  <style>
    .main {
      max-width: 420px;
      margin: 60px auto;
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      text-align: center;
    }
    h1 {
      margin-bottom: 25px;
      color: #0c1f45;
    }
    .amount-box {
      font-size: 18px;
      margin-bottom: 5px;
      color: #333;
    }
    .amount-label {
      font-weight: 600;
      color: #0c1f45;
    }
    .payment-methods {
      margin: 25px 0;
      text-align: left;
    }
    .payment-methods label {
      display: block;
      margin-bottom: 12px;
      font-weight: 500;
      cursor: pointer;
      user-select: none;
      font-size: 15px;
      color: #0c1f45;
    }
    input[type="radio"] {
      margin-right: 10px;
      transform: scale(1.2);
      cursor: pointer;
    }
    button.btn {
      width: 100%;
      background-color: #0c1f45;
      color: #fff;
      padding: 14px 0;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      font-weight: 700;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    button.btn:hover {
      background-color: #00bcd4;
    }
  </style>
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
  <h1>Complete Your Payment</h1>
  
  <div class="amount-box">
    <p><span class="amount-label">Original Amount:</span> RM <?= number_format($transaction['amount'], 2) ?></p>
    <p><span class="amount-label">Service Charge (12% max RM12):</span> RM <?= number_format($service_charge, 2) ?></p>
    <p><strong>Total to Pay:</strong> RM <?= number_format($total_to_pay, 2) ?></p>
  </div>

  <form method="POST" action="payment-confirmation.php">
    <input type="hidden" name="transaction_id" value="<?= htmlspecialchars($transaction['transaction_id']) ?>">
    
    <div class="payment-methods">
      <label>
        <input type="radio" name="payment_method" value="card" required>
        Credit / Debit Card
      </label>
      <label>
        <input type="radio" name="payment_method" value="bank_transfer" required>
        Bank Transfer
      </label>
    </div>
    
    <button type="submit" class="btn">Confirm Payment</button>
  </form>
</div>

</body>
</html>
