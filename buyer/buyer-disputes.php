<?php
session_start();
require '../database/db.php';

// Check buyer login
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'buyer') {
    header("Location: ../login/login.php");
    exit();
}

$user_id = $_SESSION['id'];
$username = $_SESSION['username'];
$email = "";

// Get buyer's email
$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($email);
$stmt->fetch();
$stmt->close();

$message = "";

// Handle dispute submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $transaction_id = trim($_POST['transaction_id']);
    $reason = trim($_POST['reason']);

    if (!preg_match("/^TXN[0-9A-F]{10,13}$/i", $transaction_id)) {
        $message = "❌ Invalid Transaction Code format. Please enter a valid code.";
    } else {
        $check = $conn->prepare("SELECT transaction_id FROM transactions WHERE transaction_id = ?");
        $check->bind_param("s", $transaction_id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows === 0) {
            $message = "❌ Transaction Code not found in our system.";
        } else {
            $stmt = $conn->prepare("INSERT INTO disputes (transaction_id, user_id, email, reason) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("siss", $transaction_id, $user_id, $email, $reason);

            if ($stmt->execute()) {
                $message = "✅ Dispute submitted successfully. Admin will review it.";
            } else {
                $message = "❌ Error submitting dispute: " . $stmt->error;
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Submit Dispute | BRUY</title>
  <link rel="stylesheet" href="user-style.css">
  <style>
    .main-content { margin-left: 260px; padding: 40px; }
    .form-container { max-width: 600px; }
    .form-group { margin-bottom: 20px; }
    .form-group input, .form-group textarea { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px; }
    .form-group textarea { resize: vertical; height: 120px; }
    .submit-btn { background-color: #0c1f45; color: #fff; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; }
    .submit-btn:hover { background-color: #00bcd4; }
    .message { margin-bottom: 20px; color: green; }
    .error { color: red; }
  </style>
</head>
<body>

<div class="sidebar">
  <div class="logo"><img src="logo.png" alt="BRUY Logo" /></div>
  <ul>
    <li><a href="buyer-dashboard.php">Dashboard</a></li>
    <li><a href="buyer-join-transaction.php">Join Transaction</a></li>
    <li><a href="buyer-my-purchases.php">My Purchases</a></li>
    <li class="active"><a href="buyer-disputes.php">Disputes</a></li>
    <li><a href="buyer-profile.php">Profile</a></li>
    <li><a href="../login/logout.php">Logout</a></li>
  </ul>
</div>

<div class="main-content fade-in">
  <h1 style="margin-bottom: 20px;">Submit a Dispute</h1>
  <?php if ($message): ?>
    <p class="<?= strpos($message, '❌') !== false ? 'error' : 'message' ?>"><?= $message ?></p>
  <?php endif; ?>

  <form class="form-container" method="POST">
    <div class="form-group">
      <label for="transaction_id">Transaction Code</label>
      <input type="text" id="transaction_id" name="transaction_id" 
             pattern="^TXN[0-9A-F]{10,13}$" 
             title="Transaction Code must look like: TXN66AB3A5C3F1D2" 
             required>
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
