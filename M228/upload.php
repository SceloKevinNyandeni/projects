<?php
session_start();

// 1️⃣ Reseller session check
if (!isset($_SESSION['reseller_id'])) {
    die("❌ Unauthorized. Please <a href='resellerLogin.html'>login</a> first.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name  = trim($_POST['product_name']);
    $product_price = trim($_POST['product_price']);
    $category_id   = intval($_POST['category_id']); // new: selected category

    // 2️⃣ Basic validation
    if (empty($product_name) || empty($product_price) || $category_id <= 0) {
        die("❌ All fields are required, and a valid category must be selected.");
    }

    if (!is_numeric($product_price) || $product_price <= 0) {
        die("❌ Invalid product price.");
    }

    // 3️⃣ Handle image upload
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === 0) {
        $file_tmp  = $_FILES['product_image']['tmp_name'];
        $file_name = basename($_FILES['product_image']['name']);
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if ($_FILES['product_image']['size'] > 5*1024*1024) {
            die("❌ File too large. Max 5MB.");
        }

        $new_name = uniqid('prod_', true) . '.' . $file_ext;
        $upload_dir = __DIR__ . '/uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $upload_path = $upload_dir . $new_name;

        if (!move_uploaded_file($file_tmp, $upload_path)) {
            die("❌ Failed to upload image.");
        }
    } else {
        die("❌ No image uploaded or an error occurred.");
    }

    // 4️⃣ Database connection
    $conn = new mysqli('localhost', 'root', '', 'm228_db');
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }

    // 5️⃣ Ensure products table exists with category_id as FK
    $conn->query("
        CREATE TABLE IF NOT EXISTS products (
            product_id INT AUTO_INCREMENT PRIMARY KEY,
            reseller_id INT NOT NULL,
            category_id INT NOT NULL,
            name VARCHAR(100) NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            image_path VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (reseller_id) REFERENCES resellers(reseller_id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE
        )
    ");

    // 6️⃣ Insert product
    $image_path = 'uploads/' . $new_name;
    $stmt = $conn->prepare("INSERT INTO products (reseller_id, category_id, name, price, image_path) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisd s", $_SESSION['reseller_id'], $category_id, $product_name, $product_price, $image_path);

    if ($stmt->execute()) {
        echo "✅ Product uploaded successfully! <a href='reseller.php'>Upload another</a>";
    } else {
        echo "❌ Database error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
