<?php
include '../database/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    if (empty($email)) {
        $message = "Please enter your email address.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            $message = "Password reset instructions have been sent to $email. (Simulated)";
            // TODO: Send real email with reset link/token
        } else {
            $message = "No account found with that email.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password | BRUY</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="login-box">
    <h2>Forgot Password</h2>
    <?php if (isset($message)) echo "<p style='color:blue;'>$message</p>"; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Enter your email" required>
        <button type="submit">Send Reset Link</button>
    </form>
    <div class="login-links">
        <a href="login.php">← Back to Login</a>
    </div>
</div>
</body>
</html>
