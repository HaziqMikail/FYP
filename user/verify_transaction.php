<?php
require 'db.php';

if (!isset($_GET['code']) || empty($_GET['code'])) {
  die("⚠️ No transaction code provided.");
}

$code = $_GET['code'];

$stmt = $conn->prepare("SELECT * FROM products WHERE transaction_code = ?");
$stmt->bind_param("s", $code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  echo "❌ No transaction found.";
  exit;
}

$product = $result->fetch_assoc();
$stmt->close();
$conn->close();

// Redirect to the detail page with code in URL
header("Location: transaction-details.php?code=" . urlencode($code));
exit();
?>
