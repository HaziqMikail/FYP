<?php
session_start();
require '../database/db.php';

// Check seller login
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../login/login.php");
    exit();
}

$user_id = $_SESSION['id'];
$username = $_SESSION['username'];
$email = '';

// Get seller's email from DB
$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($email);
$stmt->fetch();
$stmt->close();

$message = "";

// Handle dispute form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $transaction_id = $_POST['transaction_id'];
    $reason = $_POST['reason'];

    $stmt = $conn->prepare("INSERT INTO disputes (transaction_id, user_id, email, reason) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siss", $transaction_id, $user_id, $email, $reason);

    if ($stmt->execute()) {
        $message = "✅ Dispute submitted successfully. Admin will review it.";
    } else {
        $message = "❌ Error submitting dispute: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Submit Dispute | BRUY</title>
  <link rel="stylesheet" href="user-style.css">
  <style>
    .main-content {
      margin-left: 260px;
      padding: 40px;
    }
    .form-container {
      max-width: 600px;
    }
    .form-group {
      margin-bottom: 20px;
    }
    .form-group input, .form-group textarea {
      width: 100%;
      padding: 12px;
      border: 1px solid #ccc;
      border-radius: 8px;
    }
    .form-group textarea {
      resize: vertical;
      height: 120px;
    }
    .submit-btn {
      background-color: #0c1f45;
      color: #fff;
      border: none;
      padding: 12px 25px;
      border-radius: 8px;
      cursor: pointer;
    }
    .submit-btn:hover {
      background-color: #00bcd4;
    }
    .message {
      margin-bottom: 20px;
      color: green;
    }
  </style>
</head>
<body>

<div class="sidebar">
  <div class="logo"><img src="logo.png" alt="BRUY Logo" /></div>
  <ul>
    <li><a href="seller-dashboard.php">Dashboard</a></li>
    <li><a href="create-product.php">Create Product</a></li>
    <li><a href="seller-my-transactions.php">My Transactions</a></li>
    <li class="active"><a href="seller-disputes.php">Disputes</a></li>
    <li><a href="seller-profile.php">Profile</a></li>
    <li><a href="../login/login.php">Logout</a></li>
  </ul>
</div>

<div class="main-content fade-in">
  <h1 style="margin-bottom: 20px;">Submit a Dispute</h1>
  <?php if ($message): ?>
    <p class="message"><?= $message ?></p>
  <?php endif; ?>

  <form class="form-container" method="POST">
    <div class="form-group">
      <label for="transaction_id">Transaction Code</label>
      <input type="text" id="transaction_id" name="transaction_id" required>
    </div>

    <div class="form-group">
      <label for="reason">Reason</label>
      <textarea id="reason" name="reason" required></textarea>
    </div>

    <button type="submit" class="submit-btn">Submit Dispute</button>
  </form>
</div>

</body>
</html>
