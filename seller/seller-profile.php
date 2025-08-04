<?php
session_start();
require '../database/db.php';

// Handle AJAX password update
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['new_password'])) {
    header('Content-Type: application/json');

    if (!isset($_SESSION['id'])) {
        echo json_encode(["success" => false, "message" => "Unauthorized."]);
        exit();
    }

    $user_id = $_SESSION['id'];
    $new_password     = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($new_password !== $confirm_password) {
        echo json_encode(["success" => false, "message" => "❌ Passwords do not match."]);
        exit();
    }

    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $user_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "✅ Password updated successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "❌ Failed to update password."]);
    }
    $stmt->close();
    exit();
}

// -------- Normal page load --------
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../login/login.php");
    exit();
}

$user_id = $_SESSION['id'];
$stmt = $conn->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $email, $joined);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Profile | BRUY</title>
  <link rel="stylesheet" href="user-style.css">
  <style>
    .main-content { margin-left: 260px; padding: 40px; }
    .profile-card { background: #fff; padding: 25px; border-radius: 10px; max-width: 600px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .form-group { margin-bottom: 20px; }
    .form-group input { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc; }
    .submit-btn { background-color: #0c1f45; color: #fff; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; }
    .submit-btn:hover { background-color: #00bcd4; }

    /* Banner */
    .banner {
      display: none;
      padding: 12px;
      margin-top: 15px;
      border-radius: 6px;
      font-weight: bold;
      text-align: center;
    }
    .banner.success { background: #d4edda; color: #155724; }
    .banner.error { background: #f8d7da; color: #721c24; }
  </style>
</head>
<body>

<div class="sidebar">
  <div class="logo"><img src="logo.png" alt="BRUY Logo" /></div>
  <ul>
    <li><a href="seller-dashboard.php">Dashboard</a></li>
    <li><a href="create-product.php">Create Product</a></li>
    <li><a href="seller-my-transactions.php">My Transactions</a></li>
    <li><a href="seller-disputes.php">Disputes</a></li>
    <li class="active"><a href="seller-profile.php">Profile</a></li>
    <li><a href="../login/logout.php">Logout</a></li>
  </ul>
</div>

<div class="main-content fade-in">
  <h1 style="margin-bottom: 20px;">My Profile</h1>
  <div class="profile-card">

    <p><strong>Username:</strong> <?= htmlspecialchars($username) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
    <p><strong>Role:</strong> Seller</p>
    <p><strong>Joined:</strong> <?= htmlspecialchars($joined) ?></p>

    <hr style="margin: 20px 0;">

    <h3>Change Password</h3>
    <form id="passwordForm">
      <div class="form-group">
        <input type="password" name="new_password" placeholder="New Password" required>
      </div>
      <div class="form-group">
        <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
      </div>
      <button type="submit" class="submit-btn">Update Password</button>
      <div id="banner" class="banner"></div> <!-- 👈 Banner under button -->
    </form>
  </div>
</div>

<script>
document.getElementById("passwordForm").addEventListener("submit", function(e) {
  e.preventDefault();

  let formData = new FormData(this);

  fetch("seller-profile.php", {
    method: "POST",
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    let banner = document.getElementById("banner");
    banner.style.display = "block";
    banner.textContent = data.message;

    if (data.success) {
      banner.className = "banner success";
    } else {
      banner.className = "banner error";
    }

    setTimeout(() => { banner.style.display = "none"; }, 4000);
    this.reset();
  })
  .catch(() => {
    alert("Error updating password.");
  });
});
</script>

</body>
</html>
