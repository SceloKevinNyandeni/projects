<?php
session_start();

// 🔒 Reseller check
if (!isset($_SESSION['reseller_id'])) {
    die('<i class="fa-solid fa-triangle-exclamation" style="color:red;"></i> You are not authorized to access this page.');
}

// DB connection
$conn = new mysqli('localhost', 'root', '', 'm228_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

$resellerId = $_SESSION['reseller_id'];

/* 🧩 Helper function: check if a table exists */
function table_exists($conn, $table) {
    $res = $conn->query("SHOW TABLES LIKE '$table'");
    return $res && $res->num_rows > 0;
}

$resellerName = "Reseller";
$totalProducts = 0;
$totalOrders = 0;
$totalRevenue = 0;
$missingTables = [];

/* ✅ Check tables and fetch data safely */
if (table_exists($conn, 'resellers')) {
    $stmt = $conn->prepare("SELECT full_name FROM resellers WHERE reseller_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $resellerId);
        $stmt->execute();
        $res = $stmt->get_result();
        $resellerRow = $res->fetch_assoc();
        $resellerName = $resellerRow['full_name'] ?? "Reseller";
        $stmt->close();
    }
} else {
    $missingTables[] = "resellers";
}

if (table_exists($conn, 'products')) {
    $result = $conn->query("SELECT COUNT(*) AS total FROM products WHERE reseller_id = $resellerId");
    $totalProducts = $result ? ($result->fetch_assoc()['total'] ?? 0) : 0;
} else {
    $missingTables[] = "products";
}

if (table_exists($conn, 'orders')) {
    $resultOrders = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE reseller_id = $resellerId");
    $resultRevenue = $conn->query("SELECT SUM(total_amount) AS revenue FROM orders WHERE reseller_id = $resellerId");
    $totalOrders = $resultOrders ? ($resultOrders->fetch_assoc()['total'] ?? 0) : 0;
    $totalRevenue = $resultRevenue ? ($resultRevenue->fetch_assoc()['revenue'] ?? 0) : 0;
} else {
    $missingTables[] = "orders";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reseller Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Poppins", Arial, sans-serif;
  }

  body { display: flex; background: #f4f7fa; min-height: 100vh; }

  /* Sidebar */
  .sidebar {
    width: 220px; background: #3e0fbe; color: white; padding: 20px;
    flex-shrink: 0; display: flex; flex-direction: column; height: 100vh;
  }
  .sidebar h2 { text-align: center; margin-bottom: 20px; }
  .sidebar a { color: white; padding: 12px; margin: 5px 0; border-radius: 6px; text-decoration: none; display: flex; align-items: center; gap: 10px; }
  .sidebar a:hover { background: #7d51f6ff; }
  .sidebar a.logout { margin-top: auto; background: #dc3545; }
  .sidebar a.logout:hover { background: #a71d2a; }

  /* Main content */
  .main { flex-grow: 1; padding: 30px; }
  h1 { text-align: center; color: #3e0fbe; margin-bottom: 30px; display: flex; justify-content: center; align-items: center; gap: 10px; }

  /* Dashboard Cards */
  .dashboard-cards {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 25px;
    max-width: 900px;
    margin: auto;
  }

  .card {
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    text-align: center;
    transition: transform 0.2s;
  }
  .card:hover { transform: translateY(-5px); }

  .card i {
    font-size: 40px;
    color: #3e0fbe;
    margin-bottom: 15px;
  }
  .card h3 { margin-bottom: 10px; color: #333; }
  .card p { font-size: 0.9rem; margin-bottom: 15px; color: #555; }
  .card a {
    display: inline-block;
    padding: 8px 15px;
    background:#3e0fbe;
    color: white;
    border-radius: 6px;
    text-decoration: none;
    transition: background 0.3s;
  }
  .card a:hover { background: #8056f3ff; }

  .notice {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeeba;
    border-radius: 8px;
    padding: 10px 15px;
    text-align: center;
    margin: 20px auto;
    max-width: 700px;
  }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <h2><i class="fa-solid fa-shop"></i> Mashobane and Co</h2>
      <a href="reseller.php"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
      <a href="upload.php"><i class="fa-solid fa-box-open"></i> Upload Products</a>
      <a href="orders.php"><i class="fa-solid fa-cart-shopping"></i> Orders</a>
      <a href="reseller-analytics.php"><i class="fa-solid fa-chart-line"></i> Analytics</a>
      <a href="reseller-logout.php" class="logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
  </div>

  <!-- Main -->
  <div class="main">
    <h1><i class="fa-solid fa-user-tie"></i> Welcome back, <?= htmlspecialchars($resellerName) ?></h1>

    <?php if (!empty($missingTables)): ?>
      <div class="notice">
        ⚠️ Some data might be missing because these tables were not found: 
        <strong><?= implode(', ', $missingTables) ?></strong>
      </div>
    <?php endif; ?>

    <div class="dashboard-cards">
      <!-- Products Card -->
      <div class="card">
        <i class="fa-solid fa-box"></i>
        <h3>My Products</h3>
        <p>Total Products: <strong><?= htmlspecialchars($totalProducts) ?></strong></p>
        <a href="products.php">Manage Products</a>
      </div>

      <!-- Orders Card -->
      <div class="card">
        <i class="fa-solid fa-receipt"></i>
        <h3>Orders Received</h3>
        <p>Total Orders: <strong><?= htmlspecialchars($totalOrders) ?></strong></p>
        <a href="orders.php">View Orders</a>
      </div>

      <!-- Revenue Card -->
      <div class="card">
        <i class="fa-solid fa-sack-dollar"></i>
        <h3>Total Revenue</h3>
        <p>You have earned: <br><strong>R <?= number_format($totalRevenue, 2) ?></strong></p>
        <a href="reseller-analytics.php">View Analytics</a>
      </div>

      <!-- Profile Card -->
      <div class="card">
        <i class="fa-solid fa-user-gear"></i>
        <h3>My Profile</h3>
        <p>Update your details and account settings.</p>
        <a href="reseller-profile.php">Edit Profile</a>
      </div>
    </div>
  </div>
</body>
</html>
