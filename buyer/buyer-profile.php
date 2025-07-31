<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'buyer') {
    header("Location: ../login/login.php");
    exit();
}

$user_id = $_SESSION['id'];
$message = "";

// Fetch current profile info
$stmt = $conn->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $email, $joined);
$stmt->fetch();
$stmt->close();

// Handle password update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $new_password, $user_id);

    if ($stmt->execute()) {
        $message = "✅ Password updated successfully.";
    } else {
        $message = "❌ Failed to update password.";
    }
    $stmt->close();
}
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
    .message { color: green; margin-bottom: 20px; }
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
    <li class="active"><a href="buyer-profile.php">Profile</a></li>
    <li><a href="../login/login.php">Logout</a></li>
  </ul>
</div>

<div class="main-content fade-in">
  <h1 style="margin-bottom: 20px;">My Profile</h1>
  <div class="profile-card">
    <?php if ($message): ?>
      <p class="message"><?= $message ?></p>
    <?php endif; ?>

    <p><strong>Username:</strong> <?= htmlspecialchars($username) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
    <p><strong>Role:</strong> Buyer</p>
    <p><strong>Joined:</strong> <?= htmlspecialchars($joined) ?></p>

    <hr style="margin: 20px 0;">

    <h3>Change Password</h3>
    <form method="POST">
      <div class="form-group">
        <input type="password" name="new_password" placeholder="New Password" required>
      </div>
      <button type="submit" class="submit-btn">Update Password</button>
    </form>
  </div>
</div>

</body>
</html>
