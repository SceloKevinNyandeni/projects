<?php
session_start();
if (!isset($_SESSION['reseller_id'])) {
    die("❌ Unauthorized. Please <a href='resellerLogin.html'>login</a> first.");
}

$conn = new mysqli("localhost", "root", "", "m228_db");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$reseller_id = (int)$_SESSION['reseller_id'];

// CSRF token ensure
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die("Invalid product ID.");
}

// fetch product and verify ownership
$stmt = $conn->prepare("SELECT product_id, reseller_id, category_id, name, price, image_path, quantity FROM products WHERE product_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$prodRes = $stmt->get_result();
if ($prodRes->num_rows === 0) {
    die("Product not found.");
}
$product = $prodRes->fetch_assoc();
if ((int)$product['reseller_id'] !== $reseller_id) {
    die("Unauthorized to edit this product.");
}

// fetch categories
$catStmt = $conn->prepare("SELECT category_id, name FROM categories ORDER BY name ASC");
$catStmt->execute();
$categories = $catStmt->get_result();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // validate CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }

    $name = trim($_POST['product_name'] ?? '');
    $price = trim($_POST['product_price'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $qty = (int)($_POST['product_quantity'] ?? 0);

    if ($name === '' || $price === '' || $category_id <= 0) {
        $message = "❌ Name, price and category are required.";
    } elseif (!is_numeric($price) || (float)$price <= 0) {
        $message = "❌ Invalid price.";
    } elseif ($qty < 0) {
        $message = "❌ Quantity cannot be negative.";
    } else {
        // handle optional image
        $new_image_path = $product['image_path'];
        if (!empty($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['product_image']['tmp_name'];
            $file_name = basename($_FILES['product_image']['name']);
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp','gif'];

            if (!in_array($file_ext, $allowed)) {
                $message = "❌ Invalid image format.";
            } elseif ($_FILES['product_image']['size'] > 5*1024*1024) {
                $message = "❌ Image too large (max 5MB).";
            } else {
                $new_name = uniqid('prod_', true) . '.' . $file_ext;
                $upload_dir = __DIR__ . '/uploads/';
                if (!file_exists($upload_dir)) mkdir($upload_dir, 0755, true);
                $upload_path = $upload_dir . $new_name;
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    // delete old file if it exists and is inside uploads
                    if (!empty($product['image_path']) && file_exists(__DIR__ . '/' . $product['image_path'])) {
                        @unlink(__DIR__ . '/' . $product['image_path']);
                    }
                    $new_image_path = 'uploads/' . $new_name;
                } else {
                    $message = "❌ Failed to move uploaded image.";
                }
            }
        }

        if ($message === '') {
            // update
            $up = $conn->prepare("UPDATE products SET category_id=?, name=?, price=?, image_path=?, quantity=? WHERE product_id=? AND reseller_id=?");
            $priceFloat = (float)$price;
            $up->bind_param("isdssii", $category_id, $name, $priceFloat, $new_image_path, $qty, $id, $reseller_id);
            if ($up->execute()) {
                $up->close();
                $stmt->close();
                $catStmt->close();
                $conn->close();
                header("Location: products.php?success=" . urlencode("Product updated successfully."));
                exit;
            } else {
                $message = "❌ Database error: " . $up->error;
                $up->close();
            }
        }
    }
}

?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Edit Product</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');
  body{font-family:"Poppins",sans-serif;background:#f4f7fa;margin:0;padding:30px}
  .wrapper{max-width:700px;margin:0 auto;background:#fff;padding:20px;border-radius:10px;box-shadow:0 6px 20px rgba(0,0,0,0.08)}
  h1{color:#3e0fbe;text-align:center}
  label{display:block;margin:12px 0 6px;font-weight:600}
  input[type=text], input[type=number], select{width:100%;padding:10px;border:1px solid #ccc;border-radius:6px}
  .row{display:flex;gap:12px}
  .row .col{flex:1}
  .actions{display:flex;gap:10px;margin-top:16px}
  .btn{padding:10px 14px;border-radius:8px;border:none;cursor:pointer;font-weight:600}
  .btn-save{background:#007bff;color:#fff}
  .btn-cancel{background:#6c757d;color:#fff}
  .current-img{display:block;margin-top:10px;max-width:200px;border-radius:8px}
  .message{margin:10px 0;padding:8px;border-radius:6px}
  .message.error{background:#ffe6e6;color:#a12b2b}
</style>
</head>
<body>
  <div class="wrapper">
    <h1>Edit Product</h1>

    <?php if ($message): ?>
      <div class="message error"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <label>Product Name</label>
      <input type="text" name="product_name" value="<?= htmlspecialchars($product['name']) ?>" required>

      <div class="row">
        <div class="col">
          <label>Price (R)</label>
          <input type="number" name="product_price" step="0.01" min="0.01" value="<?= htmlspecialchars($product['price']) ?>" required>
        </div>
        <div class="col">
          <label>Quantity</label>
          <input type="number" name="product_quantity" step="1" min="0" value="<?= (int)$product['quantity'] ?>" required>
        </div>
      </div>

      <label>Category</label>
      <select name="category_id" required>
        <option value="">-- Select Category --</option>
        <?php while ($c = $categories->fetch_assoc()): ?>
          <option value="<?= (int)$c['category_id'] ?>" <?= $c['category_id'] == $product['category_id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($c['name']) ?>
          </option>
        <?php endwhile; ?>
      </select>

      <label>Replace Image (optional)</label>
      <input type="file" name="product_image" accept="image/*">
      <?php if (!empty($product['image_path'])): ?>
        <img class="current-img" src="<?= htmlspecialchars($product['image_path']) ?>" alt="current image">
      <?php endif; ?>

      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

      <div class="actions">
        <button type="submit" class="btn btn-save">Save Changes</button>
        <a href="products.php" class="btn btn-cancel" style="text-decoration:none;display:inline-flex;align-items:center;justify-content:center;">Cancel</a>
      </div>
    </form>
  </div>
</body>
</html>
<?php
$catStmt->close();
$stmt->close();
$conn->close();
