<?php
session_start();

// 🔒 Ensure reseller is logged in
if (!isset($_SESSION['reseller_id'])) {
    die("❌ Unauthorized. Please <a href='reseller-login.php'>login</a> first.");
}

$resellerId = $_SESSION['reseller_id'];

// 🧩 Connect to the database
$conn = new mysqli('localhost', 'root', '', 'm228_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

// 📝 Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $order_id = (int)$_POST['order_id'];
    $status   = $_POST['status'];

    // Only update orders for this reseller
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ? AND reseller_id = ?");
    $stmt->bind_param("sii", $status, $order_id, $resellerId);
    $stmt->execute();
    $stmt->close();

    // Refresh page to show updated status
    header("Location: orders.php");
    exit;
}

/* 🧩 Helper function: check if table exists */
function table_exists($conn, $table) {
    $res = $conn->query("SHOW TABLES LIKE '$table'");
    return $res && $res->num_rows > 0;
}

$missingTables = [];
$orders = [];

if (table_exists($conn, 'orders') && table_exists($conn, 'users')) {
    $query = "
        SELECT 
            o.order_id,
            u.full_name AS buyer,
            o.total_amount,
            o.status,
            o.created_at
        FROM orders o
        INNER JOIN users u ON o.user_id = u.user_id
        WHERE o.reseller_id = ?
        ORDER BY o.created_at DESC
    ";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("i", $resellerId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        $stmt->close();
    }
} else {
    $missingTables = array_filter([
        table_exists($conn, 'orders') ? null : 'orders',
        table_exists($conn, 'users') ? null : 'users',
    ]);
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reseller Orders | Mashobane & Co</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: "Poppins", sans-serif; }
    body { display: flex; background: #f4f7fa; min-height: 100vh; }

    /* Sidebar */
    .sidebar {
      width: 220px;
      background: #3e0fbe;
      color: white;
      padding: 20px;
      flex-shrink: 0;
      display: flex;
      flex-direction: column;
      height: 100vh;
    }
    .sidebar h2 { text-align: center; margin-bottom: 20px; }
    .sidebar a { color: white; padding: 12px; margin: 5px 0; border-radius: 6px; text-decoration: none; display: block; }
    .sidebar a:hover { background: #1b0066; }
    .sidebar a.logout { margin-top: auto; background: #dc3545; }
    .sidebar a.logout:hover { background: #a71d2a; }

    /* Main */
    .main { flex-grow: 1; padding: 30px; }
    h1 { text-align: center; color: #3e0fbe; margin-bottom: 30px; }

    /* Orders Table */
    table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; }
    th, td { padding: 12px; border: 1px solid #ddd; text-align: center; }
    th { background: #3e0fbe; color: white; }
    tr:nth-child(even) { background: #f9f9f9; }
    tr:hover { background: #f1f1f1; }

    select, button {
      padding: 6px 10px;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 0.9rem;
      cursor: pointer;
    }
    button {
      background: #3e0fbe;
      color: white;
      border: none;
      transition: background 0.3s;
    }
    button:hover { background: #1b0066; }

    .no-orders {
      text-align: center;
      padding: 20px;
      font-size: 1.1rem;
      color: #555;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      max-width: 500px;
      margin: auto;
    }

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
    <h2><i class="fa-solid fa-shop"></i> M228 Shopping</h2>
      <a href="reseller.php"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
      <a href="upload.php"><i class="fa-solid fa-box-open"></i> Upload Products</a>
      <a href="orders.php"><i class="fa-solid fa-cart-shopping"></i> Orders</a>
      <a href="reseller-analytics.php"><i class="fa-solid fa-chart-line"></i> Analytics</a>
      <a href="reseller-logout.php" class="logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
  </div>

  <!-- Main -->
  <div class="main">
    <h1>Orders Received</h1>

    <?php if (!empty($missingTables)): ?>
      <div class="notice">
        ⚠️ Some data might be missing because these tables were not found: 
        <strong><?= implode(', ', $missingTables) ?></strong>
      </div>
    <?php endif; ?>

    <?php if (!empty($orders)): ?>
      <table>
        <tr>
          <th>Order ID</th>
          <th>Buyer</th>
          <th>Amount (R)</th>
          <th>Status</th>
          <th>Date</th>
          <th>Action</th>
        </tr>
        <?php foreach ($orders as $row): ?>
          <tr>
            <td><?= htmlspecialchars($row['order_id']) ?></td>
            <td><?= htmlspecialchars($row['buyer']) ?></td>
            <td>R <?= number_format($row['total_amount'], 2) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td><?= htmlspecialchars($row['created_at']) ?></td>
            <td>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>">
                <select name="status">
                  <option value="Pending"   <?= $row['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                  <option value="Shipped"   <?= $row['status'] === 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                  <option value="Order On The Way" <?= $row['status'] === 'Order On The Way' ? 'selected' : '' ?>>Order On The Way</option>
                  <option value="Delivered" <?= $row['status'] === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                  <option value="Cancelled" <?= $row['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
                <button type="submit">Update</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php else: ?>
      <p class="no-orders">No orders received yet.</p>
    <?php endif; ?>
  </div>
</body>
</html>
