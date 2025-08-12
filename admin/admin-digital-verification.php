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

$query = "SELECT t.id, t.transaction_id, t.buyer_id, t.seller_id, t.amount, t.status, 
                 t.digital_credentials,
                 u1.username AS buyer_name, u2.username AS seller_name
          FROM transactions t
          LEFT JOIN users u1 ON t.buyer_id = u1.id
          LEFT JOIN users u2 ON t.seller_id = u2.id
          WHERE t.product_type = 'digital' AND t.status IN ('pending', 'pending_verification')
          ORDER BY t.created_at DESC";

$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Admin Digital Verification | BRUY</title>
  <link rel="stylesheet" href="admin-style.css" />
  <style>
    table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
    th, td { padding: 0.75rem; border: 1px solid #ddd; text-align: left; }
    th { background-color: #f0f0f0; }
    .btn-approve, .btn-reject {
      padding: 0.4rem 0.8rem;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      color: white;
    }
    .btn-approve { background-color: #28a745; }
    .btn-reject { background-color: #dc3545; }
    .btn-approve:hover { background-color: #218838; }
    .btn-reject:hover { background-color: #c82333; }
  </style>
</head>
<body>

<div class="admin-container">
  <aside class="sidebar">
    <div class="logo">
      <img src="logo.png" alt="BRUY Logo" />
      <h3>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></h3>
    </div>
    <ul>
      <li><a href="admin-dashboard.php">Dashboard</a></li>
      <li><a href="admin-users.php">User List</a></li>
      <li><a href="admin-transactions.php">Transactions</a></li>
      <li><a href="admin-completed-transactions.php">Completed</a></li>
      <li class="active"><a href="admin-digital-verification.php">Digital Verification</a></li>
      <li><a href="admin-disputes.php">Dispute Center</a></li>
      <li><a href="admin-support.php">Support Requests</a></li>
      <li><a href="admin-dashboard.php?logout=true">Logout</a></li>
    </ul>
  </aside>

  <main class="dashboard">
    <header>
      <h1>Digital Product Verification</h1>
    </header>

    <section class="content-section">
      <?php if ($result && $result->num_rows > 0): ?>
      <table>
        <thead>
          <tr>
            <th>Transaction ID</th>
            <th>Buyer</th>
            <th>Seller</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Buyer Email</th>
            <th>Buyer Password</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while($row = $result->fetch_assoc()): ?>
          <?php 
            // Decode digital credentials JSON
            $credentials = json_decode($row['digital_credentials'], true);
            $email = $credentials['email'] ?? '';
            $password = $credentials['password'] ?? '';
          ?>
          <tr>
            <td><?php echo htmlspecialchars($row['transaction_id']); ?></td>
            <td><?php echo htmlspecialchars($row['buyer_name']); ?></td>
            <td><?php echo htmlspecialchars($row['seller_name']); ?></td>
            <td>$<?php echo number_format($row['amount'], 2); ?></td>
            <td><?php echo ucfirst($row['status']); ?></td>
            <td><?php echo htmlspecialchars($email); ?></td>
            <td><?php echo htmlspecialchars($password); ?></td>
            <td>
              <form method="post" action="admin-digital-verification-action.php" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to approve this transaction?');">
                <input type="hidden" name="transaction_id" value="<?php echo $row['id']; ?>" />
                <button type="submit" name="action" value="approve" class="btn-approve">Approve</button>
              </form>
              <form method="post" action="admin-digital-verification-action.php" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to reject this transaction?');">
                <input type="hidden" name="transaction_id" value="<?php echo $row['id']; ?>" />
                <button type="submit" name="action" value="reject" class="btn-reject">Reject</button>
              </form>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
      <?php else: ?>
        <p>No pending digital transactions at the moment.</p>
      <?php endif; ?>
    </section>
  </main>
</div>

</body>
</html>
