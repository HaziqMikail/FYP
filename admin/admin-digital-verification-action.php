<?php
session_start();
require_once '../database/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login/admin-login.php");
    exit();
}

// Validate POST data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['transaction_id'], $_POST['action'])) {
        // Missing data, redirect back with error
        header("Location: admin-digital-verification.php");
        exit();
    }

    $transaction_id = intval($_POST['transaction_id']);
    $action = $_POST['action'];

    // Determine the new status based on action
    if ($action === 'approve') {
        $new_status = 'verified';  // or whatever your approved status is
    } elseif ($action === 'reject') {
        $new_status = 'rejected';
    } else {
        // Invalid action, redirect back
        header("Location: admin-digital-verification.php");
        exit();
    }

    // Update the transaction status in the database
    $stmt = $conn->prepare("UPDATE transactions SET status = ? WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param('si', $new_status, $transaction_id);
        $stmt->execute();
        $stmt->close();
    }

    // Redirect back to digital verification page after update
    header("Location: admin-digital-verification.php");
    exit();
} else {
    // Not a POST request, redirect back
    header("Location: admin-digital-verification.php");
    exit();
}
