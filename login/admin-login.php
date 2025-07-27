<?php
include '../database/db.php';

session_start();
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $admin_username = "admin";
    $admin_password = "admin123"; // You can change this securely

      if ($username === $admin_username && $password === $admin_password) {
          $_SESSION['admin_logged_in'] = true;
          $_SESSION['admin_name'] = "Admin";
          header("Location: ../admin/admin-dashboard.php");
          exit();
      }
    } else {
        $error = "Invalid admin credentials.";
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Login | BRUY</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="admin-login-box">
    <img src="logo.png" alt="BRUY Logo" class="admin-logo">
    <h2>Admin Login</h2>
    <p class="admin-subtitle">Restricted access for BRUY administrators only</p>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST">
      <input type="text" name="username" placeholder="Admin Username" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Login</button>
    </form>

    <div class="admin-links">
      <a href="login.php">Back to user login</a>
    </div>
  </div>
</body>
</html>