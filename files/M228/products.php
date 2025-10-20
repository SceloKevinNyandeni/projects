<?php
session_start();
if (!isset($_SESSION['reseller_id'])) {
    die("❌ Unauthorized. Please <a href='resellerLogin.html'>login</a> first.");
}

// DB
$conn = new mysqli("localhost", "root", "", "m228_db");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$reseller_id = (int)$_SESSION['reseller_id'];

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
}

// Filters/sort
$categoryFilter = isset($_GET['category']) ? intval($_GET['category']) : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// fetch categories
$catStmt = $conn->prepare("SELECT category_id, name FROM categories ORDER BY name ASC");
$catStmt->execute();
$catResult = $catStmt->get_result();

// build products query (prepared)
$sql = "SELECT p.product_id, p.name, p.price, p.image_path, p.quantity, p.category_id, c.name AS category, p.created_at
        FROM products p
        JOIN categories c ON p.category_id = c.category_id
        WHERE p.reseller_id = ?";
$params = [$reseller_id];
$types = "i";

if ($categoryFilter > 0) {
    $sql .= " AND p.category_id = ?";
    $types .= "i";
    $params[] = $categoryFilter;
}

switch ($sort) {
    case 'oldest':     $sql .= " ORDER BY p.created_at ASC"; break;
    case 'price_low':  $sql .= " ORDER BY p.price ASC"; break;
    case 'price_high': $sql .= " ORDER BY p.price DESC"; break;
    default:           $sql .= " ORDER BY p.created_at DESC"; break;
}

$stmt = $conn->prepare($sql);
if ($types === "i") {
    $stmt->bind_param($types, $params[0]);
} else {
    // support two params (reseller_id + category)
    $stmt->bind_param($types, $params[0], $params[1]);
}
$stmt->execute();
$result = $stmt->get_result();

// flash messages
$flash = '';
if (isset($_GET['success'])) $flash = htmlspecialchars($_GET['success']);
if (isset($_GET['error'])) $flash = htmlspecialchars($_GET['error']);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>My Products - Reseller Dashboard</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
<style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');
  *{box-sizing:border-box;font-family:"Poppins",sans-serif}
  body{display:flex;background:#f4f7fa;min-height:100vh;margin:0}
  .sidebar{width:220px;background:#3e0fbe;color:#fff;padding:20px;flex-shrink:0;display:flex;flex-direction:column;height:100vh}
  .sidebar h2{text-align:center;margin-bottom:20px}
  .sidebar a{color:#fff;padding:12px;margin:5px 0;border-radius:6px;text-decoration:none;display:block}
  .sidebar a:hover{background:#0056b3}
  .sidebar a.logout{margin-top:auto;background:#dc3545}.sidebar a.logout:hover{background:#a71d2a}
  .main{flex-grow:1;padding:30px}
  h1{color:#3e0fbe;margin-bottom:10px;text-align:center}
  .flash{max-width:900px;margin:10px auto;padding:10px;border-radius:6px;text-align:center}
  .flash.success{background:#e6ffed;color:#1b7a3a}
  .flash.error{background:#ffe6e6;color:#a12b2b}
  .filters{display:flex;gap:12px;justify-content:center;margin:20px 0}
  .filters select, .filters button{padding:10px;border:1px solid #ccc;border-radius:6px}
  .product-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:20px;max-width:1200px;margin:0 auto}
  .product-card{background:#fff;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,0.08);padding:15px;text-align:center;display:flex;flex-direction:column;justify-content:space-between}
  .product-card img{width:100%;height:180px;object-fit:cover;border-radius:8px}
  .product-card h4{margin:12px 0 6px;font-size:18px;color:#333}
  .price{font-weight:700;color:#3e0fbe;margin-bottom:6px}
  .stock{color:#555;margin-bottom:8px}
  .meta{font-size:12px;color:#777}
  .btn-group{display:flex;gap:10px;margin-top:12px}
  .btn{flex:1;padding:9px;border-radius:8px;text-decoration:none;color:#fff;font-weight:600;display:inline-block; }
  .btn-edit{background:#007bff; height: 85%;}.btn-edit:hover{background:#0056b3}
  .btn-delete{background:#dc3545;}.btn-delete:hover{background:#a71d2a}
  form.delete-form{display:inline-block;width:50%}
  .delete-form button{width:100%;border:none;padding:9px;border-radius:8px;background:#dc3545;color:#fff;font-weight:600;cursor:pointer}
</style>
</head>
<body>

  <div class="sidebar">
    <h2><i class="fa-solid fa-shop"></i> Mashobane and Co</h2>
      <a href="reseller.php"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
      <a href="upload.php"><i class="fa-solid fa-box-open"></i> Upload Products</a>
      <a href="orders.php"><i class="fa-solid fa-cart-shopping"></i> Orders</a>
      <a href="reseller-analytics.php"><i class="fa-solid fa-chart-line"></i> Analytics</a>
      <a href="reseller-logout.php" class="logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
  </div>

  <div class="main">
    <h1>My Products</h1>

    <?php if ($flash): ?>
      <div class="flash <?= isset($_GET['success']) ? 'success' : 'error' ?>"><?= $flash ?></div>
    <?php endif; ?>

    <form method="GET" class="filters">
      <select name="category">
        <option value="0">All Categories</option>
        <?php
        $catResult->data_seek(0);
        while ($cat = $catResult->fetch_assoc()): ?>
          <option value="<?= (int)$cat['category_id'] ?>" <?= $categoryFilter == $cat['category_id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($cat['name']) ?>
          </option>
        <?php endwhile; ?>
      </select>

      <select name="sort">
        <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Newest</option>
        <option value="oldest" <?= $sort == 'oldest' ? 'selected' : '' ?>>Oldest</option>
        <option value="price_low" <?= $sort == 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
        <option value="price_high" <?= $sort == 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
      </select>

      <button type="submit">Apply</button>
    </form>

    <div class="product-grid">
      <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="product-card">
            <div>
              <img src="<?= htmlspecialchars($row['image_path']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
              <h4><?= htmlspecialchars($row['name']) ?></h4>
              <div class="price">R <?= number_format($row['price'], 2) ?></div>
              <div class="stock">Stock: <?= (int)$row['quantity'] ?></div>
              <div class="meta">Category: <?= htmlspecialchars($row['category']) ?> • Added: <?= date('d M Y', strtotime($row['created_at'])) ?></div>
            </div>

            <div class="btn-group">
              <a class="btn btn-edit" href="edit-product.php?id=<?= (int)$row['product_id'] ?>"><i class="fa-solid fa-pen-to-square"></i> Edit</a>

              <!-- Delete uses POST with CSRF token -->
              <form class="delete-form" method="POST" action="delete-product.php" onsubmit="return confirm('Delete this product?');">
                <input type="hidden" name="id" value="<?= (int)$row['product_id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <button type="submit"><i class="fa-solid fa-trash"></i> Delete</button>
              </form>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p style="text-align:center;width:100%;">No products found.</p>
      <?php endif; ?>
    </div>
  </div>

</body>
</html>
<?php
$stmt->close();
$catStmt->close();
$conn->close();
