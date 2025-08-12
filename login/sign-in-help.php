<?php
require '../database/db.php'; // adjust path to your db.php

$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $issue = trim($_POST['issue']);

    if (empty($email) || empty($issue)) {
        $message = "<span style='color:red;'>❌ Please fill in all fields.</span>";
    } else {
        // Insert into support_requests table
        $stmt = $conn->prepare("INSERT INTO support_requests (email, issue) VALUES (?, ?)");
        $stmt->bind_param("ss", $email, $issue);

        if ($stmt->execute()) {
            // Dummy email sending (only works if mail() is configured on server)
            $to = "haziqmikail.faizulazeli@student.gmi.edu.my"; 
            $subject = "New Support Request from $email";
            $body = "A new support request was submitted:\n\n".
                    "Email: $email\n".
                    "Issue:\n$issue\n\n".
                    "Submitted at: " . date("Y-m-d H:i:s");

            // Try sending (will not work on localhost unless SMTP is set up)
            @mail($to, $subject, $body, "From: no-reply@bruy.com");

            $message = "<span style='color:green;'>✅ Thank you. We’ve received your report and will get back to you soon.</span>";
        } else {
            $message = "<span style='color:red;'>❌ Failed to submit your request. Please try again.</span>";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sign-in Help | BRUY</title>
  <link rel="stylesheet" href="style.css"> <!-- same as login/signup -->
  <style>
    .login-box { 
        max-width: 400px; 
        margin: 60px auto; 
        background: #fff; 
        padding: 30px; 
        border-radius: 12px; 
        box-shadow: 0 2px 8px rgba(0,0,0,0.1); 
    }
    input, textarea { 
        width: 95%; 
        padding: 10px; 
        border: 1px solid #ccc; 
        border-radius: 8px; 
        margin-bottom: 15px; 
        resize: vertical; 
    }
    button { 
        width: 100%; 
        padding: 12px; 
        border: none; 
        border-radius: 8px; 
        background-color: #0c1f45; 
        color: #fff; 
        cursor: pointer; 
        font-size: 15px;
        font-weight: bold;
    }
    button:hover { background-color: #00bcd4; }
    .banner { margin: 10px 0; text-align: center; }
    .login-links { margin-top: 20px; text-align: center; }
    .login-links a { color: #0c1f45; text-decoration: none; font-weight: 500; }
    .login-links a:hover { text-decoration: underline; }
  </style>
</head>
<body>
  <div class="login-box fade-in">
    <h2>Sign-in Help</h2>
    <?php if (!empty($message)): ?>
      <div class="banner"><?= $message ?></div>
    <?php endif; ?>
    
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
