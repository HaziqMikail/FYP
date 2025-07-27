<?php
require 'db.php';

if (!isset($_GET['code'])) {
  die("❌ No transaction code provided.");
}

$code = $_GET['code'];
$stmt = $conn->prepare("SELECT * FROM products WHERE transaction_code = ?");
$stmt->bind_param("s", $code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  die("❌ No transaction found.");
}

$product = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Transaction Status | BRUY</title>
  <link rel="stylesheet" href="user-style.css" />
  <style>
    .main { margin-left: 260px; padding: 50px; }
    .status-box {
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      max-width: 700px;
      box-shadow: 0 4px 14px rgba(0,0,0,0.1);
    }
    .status-box img {
      width: 150px;
      border-radius: 8px;
      margin-bottom: 20px;
    }
    .agree-box {
      margin-top: 30px;
    }
    .agree-box input[type="checkbox"] {
      margin-right: 10px;
    }
    .action-buttons {
      margin-top: 20px;
    }
    .action-buttons button {
      margin-right: 15px;
      padding: 12px 20px;
      font-weight: bold;
      border: none;
      border-radius: 8px;
      cursor: pointer;
    }
    .proceed-btn {
      background-color: #0c1f45;
      color: white;
    }
    .home-btn {
      background-color: #ddd;
      color: #000;
    }
  </style>
</head>
<body>
  <div class="sidebar">
    <!-- same sidebar as before -->
  </div>

  <div class="main fade-in">
    <div class="status-box">
      <h2>Product: <?= htmlspecialchars($product['name']) ?></h2>
      <img src="images/<?= htmlspecialchars($product['image']) ?>" alt="Product Image">
      <p><strong>Type:</strong> <?= $product['type'] ?></p>
      <p><strong>Price:</strong> RM <?= $product['price'] ?></p>
      <p><strong>Marketplace Link:</strong> <a href="<?= $product['marketplace_link'] ?>" target="_blank">View</a></p>

      <div class="agree-box">
        <input type="checkbox" id="agree">
        <label for="agree">I agree to the Terms and Conditions</label>
      </div>

      <div class="action-buttons">
        <button class="proceed-btn" onclick="proceed()">✅ Proceed to Payment</button>
        <button class="home-btn" onclick="saveAndGoHome()">🏠 Back to Homepage</button>
      </div>
    </div>
  </div>

  <script>
    function proceed() {
      if (!document.getElementById('agree').checked) {
        alert("Please agree to the terms before proceeding.");
        return;
      }
      alert("✅ You've successfully joined the transaction!");
      // Redirect to payment page later
    }

    function saveAndGoHome() {
      if (!document.getElementById('agree').checked) {
        alert("Please agree to the terms before proceeding.");
        return;
      }
      alert("✅ Transaction saved. Returning to dashboard.");
      // Redirect to buyer homepage (can also trigger save via AJAX here)
      window.location.href = "buyer-dashboard.html";
    }
  </script>
</body>
</html>
