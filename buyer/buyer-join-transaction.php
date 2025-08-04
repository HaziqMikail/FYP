<?php
session_start();
require '../database/db.php';

// Redirect if not a logged-in buyer
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'buyer') {
    header("Location: ../login/login.php");
    exit();
}

$transaction = null;
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $transaction_id = trim($_POST['transaction_id']);

    // Validate ID format (must start with TXN)
    if (!preg_match("/^TXN[A-Z0-9]+$/", $transaction_id)) {
        $error = "❌ Invalid Transaction ID format.";
    } else {
        // Check if transaction exists
        $stmt = $conn->prepare("SELECT t.*, u.username as seller_name 
                                FROM transactions t 
                                JOIN users u ON t.seller_id = u.id 
                                WHERE t.transaction_id = ?");
        $stmt->bind_param("s", $transaction_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $transaction = $result->fetch_assoc();
        } else {
            $error = "❌ Transaction not found.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Join Transaction | BRUY</title>
  <link rel="stylesheet" href="user-style.css">
  <style>
    .card {
      background: #fff;
      padding: 20px;
      margin-top: 20px;
      border-radius: 12px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .form-inline {
      display: flex;
      gap: 10px;
      margin-top: 10px;
    }
    .form-inline input {
      flex: 1;
      padding: 10px;
      border-radius: 8px;
      border: 1px solid #ccc;
    }
    .btn {
      background: #0c1f45;
      color: #fff;
      padding: 10px 16px;
      border-radius: 8px;
      border: none;
      cursor: pointer;
      transition: 0.3s;
    }
    .btn:hover {
      background: #00bcd4;
    }
    .pay-btn {
      margin-top: 15px;
      width: 100%;
    }
    .error {
      color: red;
      margin-top: 10px;
    }
    .product-img {
      max-width: 200px;
      border-radius: 10px;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>

<div class="sidebar">
  <div class="logo"><img src="logo.png" alt="BRUY Logo" /></div>
  <ul>
    <li><a href="buyer-dashboard.php">Dashboard</a></li>
    <li class="active"><a href="buyer-join-transaction.php">Join Transaction</a></li>
    <li><a href="buyer-my-purchases.php">My Purchases</a></li>
    <li><a href="buyer-disputes.php">Disputes</a></li>
    <li><a href="buyer-profile.php">Profile</a></li>
    <li><a href="../login/logout.php">Logout</a></li>
  </ul>
</div>

<div class="main fade-in">
  <h1>Join Transaction</h1>

  <form method="POST" class="card">
    <label><strong>Enter Transaction ID</strong></label>
    <div class="form-inline">
      <input type="text" name="transaction_id" placeholder="e.g. TXN64D9A2F1..." required>
      <button type="submit" class="btn">Check</button>
    </div>
    <?php if ($error): ?><p class="error"><?= $error ?></p><?php endif; ?>
  </form>

  <?php if ($transaction): ?>
    <div class="card">
      <h2><?= htmlspecialchars($transaction['item_name']) ?></h2>
      <?php if (!empty($transaction['image_path'])): ?>
        <img src="../<?= $transaction['image_path'] ?>" alt="Product" class="product-img">
      <?php endif; ?>
      <p><strong>Amount:</strong> RM <?= number_format($transaction['amount'], 2) ?></p>
      <p><strong>Type:</strong> <?= ucfirst($transaction['product_type']) ?></p>
      <p><strong>Seller:</strong> <?= htmlspecialchars($transaction['seller_name']) ?></p>
      <p><strong>Status:</strong> <?= ucfirst($transaction['status']) ?></p>

      <?php if ($transaction['status'] === 'pending'): ?>
      <form method="POST" action="pay-transaction.php">
        <input type="hidden" name="transaction_id" value="<?= $transaction['transaction_id'] ?>">
        <button type="submit" class="btn pay-btn">💳 Pay Now</button>
      </form>
      <?php else: ?>
        <p><em>This item is already <?= htmlspecialchars($transaction['status']) ?>.</em></p>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>

</body>
</html>
