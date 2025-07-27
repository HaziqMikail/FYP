<?php
require 'db.php';

$type = $_POST['type'];
$name = $_POST['name'];
$price = $_POST['price'];
$link = $_POST['marketplace_link'];
$image_name = '';
$transaction_code = uniqid(); // Generate unique code

// === Upload Image ===
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = __DIR__ . "/images/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $image_name = uniqid() . "_" . basename($_FILES["image"]["name"]);
    $target_path = $upload_dir . $image_name;

    if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_path)) {
        die("❌ Failed to upload image.");
    }
}

// === Insert to Database ===
$stmt = $conn->prepare("INSERT INTO products (type, name, price, marketplace_link, image, transaction_code) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssdsss", $type, $name, $price, $link, $image_name, $transaction_code);

if ($stmt->execute()) {
    // Redirect to transaction status page with the code
    header("Location: transaction-status.php?code=" . $transaction_code);
    exit();
} else {
    echo "❌ Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
