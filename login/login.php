<?php
include '../database/db.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    if (empty($username) || empty($password) || empty($role)) {
        $error = "All fields are required.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND role = ?");
        $stmt->bind_param("ss", $username, $role);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // ✅ Redirect based on role
                if ($role === 'buyer') {
                    header("Location: ../buyer/buyer-dashboard.php");
                } elseif ($role === 'seller') {
                    header("Location: ../seller/seller-dashboard.php");
                } else {
                    $error = "Unknown role.";
                }
                exit();
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "Invalid credentials or role.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login | BRUY</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
    <div class="left-panel">
      <img src="logo.png" alt="BRUY Logo" class="logo">
      <h1>What is Bruy?</h1>
      <p>BRUY is a secure online payment protection system that ensures your transactions are protected from fraud.</p>
    </div>

    <div class="right-panel">
      <div class="login-box">
        <h2>Sign in</h2>
        <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <form method="POST">
          <input type="text" name="username" placeholder="Username" required>
          <input type="password" name="password" placeholder="Your password" required>

          <select name="role" required>
            <option value="">Select Role</option>
            <option value="buyer">Buyer</option>
            <option value="seller">Seller</option>
          </select>

          <button type="submit">Log in</button>

          <div class="login-links">
            <a href="sign-in-help.php">Other issues with sign in</a> |
            <a href="forgot-password.php">Forgot your password?</a>
          </div>

          <p>New to our community? <a href="signup.php">Create an account</a></p>
        </form>

        <p class="admin-link"><a href="admin-login.php">Admin</a></p>
      </div>
    </div>
  </div>
</body>
</html>
