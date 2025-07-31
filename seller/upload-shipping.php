<?php
session_start();
require '../database/db.php';

// Must be seller
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../login/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_id = $_POST['transaction_id'];
    $tracking_number = $_POST['tracking_number'];
    $seller_id = $_SESSION['id'];

    // Handle proof image upload
    $proof_path = null;
    if (isset($_FILES['shipping_proof']) && $_FILES['shipping_proof']['error'] == 0) {
        $proof_name = uniqid() . '_' . basename($_FILES["shipping_proof"]["name"]);
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
        $target_file = $target_dir . $proof_name;
        move_uploaded_file($_FILES["shipping_proof"]["tmp_name"], $target_file);
        $proof_path = "uploads/" . $proof_name;
    }

    // Update transaction
    $stmt = $conn->prepare("UPDATE transactions 
        SET tracking_number = ?, shipping_proof = ?, status = 'shipped' 
        WHERE transaction_id = ? AND seller_id = ?");
    $stmt->bind_param("sssi", $tracking_number, $proof_path, $transaction_id, $seller_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "✅ Shipping details submitted.";
    } else {
        $_SESSION['error'] = "❌ Error: " . $stmt->error;
    }

    header("Location: seller-my-transactions.php");
    exit();
}
?>
