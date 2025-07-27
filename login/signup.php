<?php
include '../database/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $role = $_POST["role"];

    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        $error = "All fields are required.";
    } else {
        // Check if email with same role already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
        $stmt->bind_param("ss", $email, $role);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Email already registered with the selected role.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $insert = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $insert->bind_param("ssss", $username, $email, $hashedPassword, $role);
            if ($insert->execute()) {
                header("Location: login.php");
                exit();
            } else {
                $error = "Signup failed. Try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Signup | BRUY</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
  <div class="left-panel">
    <img src="logo.png" alt="BRUY Logo" class="logo">
    <h1>Join BRUY</h1>
    <p>Create an account to securely buy and sell with payment protection built in.</p>
  </div>
  <div class="right-panel">
    <div class="login-box">
      <h2>Create Account</h2>
      <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
      <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email address" required>
        <input type="password" name="password" placeholder="Create password" required>
        <select name="role" required>
          <option value="">Select Role</option>
          <option value="buyer">Buyer</option>
          <option value="seller">Seller</option>
        </select>
        <button type="submit">Sign up</button>
        <div class="login-links">
          <p>Already have an account? <a href="login.php">Log in here</a></p>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>
