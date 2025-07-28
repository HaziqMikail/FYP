<?php
session_start();
require_once '../database/db.php';

if (!isset($_SESSION['admin_name'])) {
    header('Location: admin-login.php');
    exit();
}

// Fetch transactions with seller and buyer usernames
$query = "
    SELECT 
        t.id,
        t.transaction_id,
        s.username AS seller_username,
        b.username AS buyer_username,
        t.item_name,
        t.amount,
        t.status,
        t.created_at,
        t.paid_at,
        t.completed_at
    FROM transactions t
    JOIN users s ON t.seller_id = s.id
    LEFT JOIN users b ON t.buyer_id = b.id
    ORDER BY t.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
$transactions = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Transactions | Admin Panel</title>
  <link rel="stylesheet" href="admin-style.css">
</head>
<body>

<div class="admin-container">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="logo">
      <img src="logo.png" alt="BRUY Logo">
      <h3>Welcome, <?php echo $_SESSION['admin_name']; ?></h3>
    </div>
    <ul>
      <li><a href="admin-dashboard.php">Dashboard</a></li>
      <li><a href="admin-users.php">User List</a></li>
      <li class="active"><a href="admin-transactions.php">Transactions</a></li>
      <li><a href="admin-disputes.php">Dispute Center</a></li>
      <li><a href="admin-dashboard.php?logout=true">Logout</a></li>
    </ul>
  </aside>

  <!-- Main Content -->
  <main class="dashboard">
    <header>
      <h1>Transactions</h1>
    </header>

    <table class="transaction-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Transaction ID</th>
          <th>Seller</th>
          <th>Buyer</th>
          <th>Item</th>
          <th>Amount (RM)</th>
          <th>Status</th>
          <th>Created</th>
          <th>Paid</th>
          <th>Completed</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($transactions) > 0): ?>
          <?php foreach ($transactions as $txn): ?>
            <tr>
              <td><?php echo $txn['id']; ?></td>
              <td><?php echo htmlspecialchars($txn['transaction_id']); ?></td>
              <td><?php echo htmlspecialchars($txn['seller_username']); ?></td>
              <td><?php echo htmlspecialchars($txn['buyer_username']); ?></td>
              <td><?php echo htmlspecialchars($txn['item_name']); ?></td>
              <td>RM <?php echo number_format($txn['amount'], 2); ?></td>
              <td class="status <?php echo $txn['status']; ?>">
                <?php echo ucfirst($txn['status']); ?>
              </td>
              <td><?php echo $txn['created_at']; ?></td>
              <td><?php echo $txn['paid_at'] ?? '-'; ?></td>
              <td><?php echo $txn['completed_at'] ?? '-'; ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="10">No transactions found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </main>
</div>

</body>
</html>
