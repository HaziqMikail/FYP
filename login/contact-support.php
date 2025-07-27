<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $issue = trim($_POST['issue']);

    if (empty($email) || empty($issue)) {
        $message = "Please fill in all fields.";
    } else {
        // Simulate sending support request
        $message = "Thank you. We’ve received your report and will get back to you soon.";
        // TODO: Store into support table or email to support team
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sign-in Help | BRUY</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="login-box">
    <h2>Sign-in Help</h2>
    <?php if (isset($message)) echo "<p style='color:green;'>$message</p>"; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Your Email" required>
        <textarea name="issue" rows="4" placeholder="Describe your issue..." required></textarea>
        <button type="submit">Submit</button>
    </form>
    <div class="login-links">
        <a href="login.php">← Back to Login</a>
    </div>
</div>
</body>
</html>
