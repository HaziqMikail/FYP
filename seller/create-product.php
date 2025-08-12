<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../login/login.php");
    exit();
}

$seller_id = $_SESSION['id'];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item_name = $_POST['item_name'];
    $amount = $_POST['amount'];
    $marketplace_link = $_POST['marketplace_link'];
    $product_type = $_POST['product_type'];

    // Prepare digital credentials (only for digital products)
    $digital_email = ($product_type === 'digital') ? trim($_POST['email']) : '';
    $digital_password = ($product_type === 'digital') ? trim($_POST['password']) : '';
    $digital_credentials = json_encode([
        "email" => $digital_email,
        "password" => $digital_password
    ]);

    // Handle image upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image_name = uniqid() . '_' . basename($_FILES["image"]["name"]);
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
        $target_file = $target_dir . $image_name;
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = "uploads/" . $image_name;
        }
    }

    // Generate transaction ID
    $transaction_id = strtoupper(uniqid('TXN'));

    // Insert into DB
    $stmt = $conn->prepare("INSERT INTO transactions 
        (transaction_id, seller_id, item_name, amount, marketplace_link, product_type, image_path, digital_credentials) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sisdssss", $transaction_id, $seller_id, $item_name, $amount, $marketplace_link, $product_type, $image_path, $digital_credentials);

    if ($stmt->execute()) {
        $message = "✅ Product created successfully. Share this code with buyer: <strong>$transaction_id</strong>";
    } else {
        $message = "❌ Error: " . $stmt->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Product | BRUY</title>
  <link rel="stylesheet" href="user-style.css">
  <style>
    .main-content { margin-left: 260px; padding: 40px; }
    .product-form { max-width: 600px; }
    .form-group { margin-bottom: 20px; }
    .form-group input, .form-group select {
      width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px;
    }
    .form-group input:focus { border-color: #00bcd4; outline: none; }
    .submit-btn {
      background-color: #0c1f45; color: #fff; border: none;
      padding: 12px 25px; border-radius: 8px; cursor: pointer;
    }
    .submit-btn:hover { background-color: #00bcd4; }
    .message { margin-bottom: 20px; color: green; }
    .image-upload img { margin-top: 10px; max-width: 150px; }
  </style>
</head>
<body>

<div class="sidebar">
  <div class="logo"><img src="logo.png" alt="BRUY Logo" /></div>
  <ul>
    <li><a href="seller-dashboard.php">Dashboard</a></li>
    <li class="active"><a href="create-product.php">Create Product</a></li>
    <li><a href="seller-my-transactions.php">My Transactions</a></li>
    <li><a href="seller-disputes.php">Disputes</a></li>
    <li><a href="seller-profile.php">Profile</a></li>
    <li><a href="../login/login.php">Logout</a></li>
  </ul>
</div>

<div class="main-content fade-in">
  <h1>Create Product</h1>
  <?php if ($message): ?>
    <p class="message"><?= $message ?></p>
  <?php endif; ?>

  <form class="product-form" method="POST" enctype="multipart/form-data">
    <div class="form-group">
      <label>Product Type</label>
      <select name="product_type" id="productType" required onchange="toggleDigitalFields()">
        <option value="physical">Physical</option>
        <option value="digital">Digital</option>
      </select>
    </div>

    <div class="form-group">
      <label>Item Name</label>
      <input type="text" name="item_name" required>
    </div>

    <div class="form-group">
      <label>Price (MYR)</label>
      <input type="number" name="amount" step="0.01" required>
    </div>

    <div class="form-group">
      <label>Marketplace Link</label>
      <input type="url" name="marketplace_link" required>
    </div>

    <div id="digitalFields" style="display: none;">
      <div class="form-group">
        <label>Download Email (for digital product)</label>
        <input type="text" name="email">
      </div>
      <div class="form-group">
        <label>Download Password (for digital product)</label>
        <input type="text" name="password">
      </div>
    </div>

    <div class="form-group image-upload">
      <label>Upload Product Image</label>
      <input type="file" name="image" accept="image/*">
    </div>

    <button type="submit" class="submit-btn">Create Product</button>
  </form>
</div>

<script>
  function toggleDigitalFields() {
    const type = document.getElementById('productType').value;
    document.getElementById('digitalFields').style.display = (type === 'digital') ? 'block' : 'none';
  }
</script>

</body>
</html>
