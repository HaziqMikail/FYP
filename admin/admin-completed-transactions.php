<?php
session_start();
require_once '../database/db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login/admin-login.php");
    exit();
}

// Logout handler
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../login/admin-login.php");
    exit();
}

// Handle release payment
if (isset($_POST['release_payment'])) {
    $transaction_id = $_POST['transaction_id']; // VARCHAR, so don't cast to int
    $stmt = $conn->prepare("UPDATE transactions SET status = 'released', paid_at = NOW() WHERE transaction_id = ?");
    $stmt->bind_param("s", $transaction_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch completed transactions
$stmt = $conn->prepare("
    SELECT t.transaction_id, t.item_name, t.amount, t.status, 
           b.username AS buyer_name, s.username AS seller_name, t.created_at
    FROM transactions t
    JOIN users b ON t.buyer_id = b.id
    JOIN users s ON t.seller_id = s.id
    WHERE t.status = 'completed'
    ORDER BY t.created_at DESC
");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Completed Transactions | BRUY Admin</title>
  <link rel="stylesheet" href="admin-style.css">
  <style>
    .dashboard {
      padding: 30px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      background: #fff;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    th, td {
      padding: 14px;
      text-align: left;
      border-bottom: 1px solid #eee;
    }
    th {
      background: #0c1f45;
      color: #fff;
      font-size: 15px;
    }
    tr:hover {
      background: #f9f9f9;
    }
    .release-btn {
      background: #28a745;
      color: #fff;
      padding: 6px 12px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
      transition: 0.3s ease;
    }
    .release-btn:hover {
      background: #218838;
    }
    .status {
      font-weight: bold;
      color: #0c1f45;
    }
  </style>
</head>
<body>

<div class="admin-container">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="logo">
      <img src="logo.png" alt="BRUY Logo">
      <h3>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></h3>
    </div>
    <ul>
      <li><a href="admin-dashboard.php">Dashboard</a></li>
      <li><a href="admin-users.php">User List</a></li>
      <li><a href="admin-transactions.php">Transactions</a></li>
      <li class="active"><a href="admin-completed-transactions.php">Completed</a></li>
      <li><a href="admin-disputes.php">Dispute Center</a></li>
      <li><a href="admin-support.php">Support Requests</a></li>
      <li><a href="admin-completed-transactions.php?logout=true">Logout</a></li>
    </ul>
  </aside>

  <!-- Dashboard Content -->
  <main class="dashboard">
    <header>
      <h1>✅ Completed Transactions</h1>
    </header>

    <section class="content-section">
      <p>Here you can view all <strong>completed transactions</strong> and release payments to sellers.</p>
    </section>

        <table>
        <thead>
            <tr>
            <th>ID</th>
            <th>Item</th>
            <th>Amount</th>
            <th>Buyer</th>
            <th>Seller</th>
            <th>Date</th>
            <th>Status</th>
            <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                <td><?php echo $row['transaction_id']; ?></td>
                <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                <td>$<?php echo number_format($row['amount'], 2); ?></td>
                <td><?php echo htmlspecialchars($row['buyer_name']); ?></td>
                <td><?php echo htmlspecialchars($row['seller_name']); ?></td>
                <td><?php echo $row['created_at']; ?></td>
                <td class="status"><?php echo ucfirst($row['status']); ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                    <input type="hidden" name="transaction_id" value="<?php echo $row['transaction_id']; ?>">
                    <button type="submit" name="release_payment" class="release-btn">Release Payment</button>
                    </form>
                </td>
                </tr>
            <?php endwhile; ?>
            <?php else: ?>
            <tr><td colspan="8" style="text-align:center;">No completed transactions found.</td></tr>
            <?php endif; ?>
        </tbody>
        </table>
  </main>
</div>

</body>
</html>
