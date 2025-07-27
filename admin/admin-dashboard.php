<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login/admin-login.php"); // Redirect to login if not logged in
    exit();
}

// Logout logic
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../login/admin-login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard | BRUY</title>
  <link rel="stylesheet" href="admin-style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<?php if (!isset($_SESSION['admin_logged_in'])): ?>
  <!-- Admin Login -->
  <div style="display: flex; height: 100vh; justify-content: center; align-items: center;">
    <form method="POST" style="background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 300px;">
      <h2 style="text-align: center; margin-bottom: 20px;">Admin Login</h2>
      <?php if (isset($error)) echo "<p style='color:red; text-align:center;'>$error</p>"; ?>
      <input type="text" name="username" placeholder="Admin Username" required style="width: 100%; padding: 10px; margin-bottom: 10px;">
      <input type="password" name="password" placeholder="Password" required style="width: 100%; padding: 10px; margin-bottom: 20px;">
      <button type="submit" name="login" style="width: 100%; padding: 10px; background-color: #0c1f45; color: white; border: none; border-radius: 5px;">Login</button>
    </form>
  </div>

<?php else: ?>
  <!-- Admin Dashboard -->
  <div class="admin-container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="logo">
        <img src="logo.png" alt="BRUY Logo">
        <h3>Welcome, <?php echo $_SESSION['admin_name']; ?></h3>
      </div>
      <ul>
        <li class="active"><a href="admin-dashboard.php">Dashboard</a></li>
        <li><a href="#">Transaction</a></li>
        <li><a href="#">Report</a></li>
        <li><a href="#">Dispute Center</a></li>
        <li><a href="#">My Profile</a></li>
        <li><a href="admin-dashboard.php?logout=true">Logout</a></li>
      </ul>
    </aside>

    <!-- Main Content -->
    <main class="dashboard">
      <header>
        <h1>Analytics</h1>
        <input type="text" placeholder="Search here...">
      </header>

      <!-- Analytics Cards -->
      <section class="cards">
        <div class="card"><h3>89,935</h3><p>Total Users</p></div>
        <div class="card"><h3>23,283.5</h3><p>Total Products</p></div>
        <div class="card"><h3>46,827</h3><p>New Users</p></div>
        <div class="card"><h3>124,854</h3><p>Refunded</p></div>
        <div class="card"><h3>89,935</h3><p>Total Earnings</p></div>
        <div class="card"><h3>46,827</h3><p>Download Apps</p></div>
        <div class="card"><h3>124,854</h3><p>Total Sales</p></div>
      </section>

      <!-- Chart Section -->
      <section class="charts">
        <div class="chart-box">
          <canvas id="ordersChart"></canvas>
        </div>
        <div class="chart-box">
          <canvas id="earningsChart"></canvas>
        </div>
      </section>
    </main>
  </div>

  <script>
    const ordersCtx = document.getElementById('ordersChart').getContext('2d');
    new Chart(ordersCtx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
          label: 'Orders',
          data: [120, 190, 300, 250, 320, 400],
          borderColor: '#0c1f45',
          fill: false,
          tension: 0.3
        }]
      }
    });

    const earningsCtx = document.getElementById('earningsChart').getContext('2d');
    new Chart(earningsCtx, {
      type: 'bar',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
          label: 'Earnings',
          data: [1200, 1500, 1800, 2000, 1700, 2100],
          backgroundColor: '#143d86'
        }]
      }
    });
  </script>
<?php endif; ?>

</body>
</html>
