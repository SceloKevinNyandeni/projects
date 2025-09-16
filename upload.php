<?php
session_start();

if (!isset($_SESSION['reseller_id'])) {
    die("❌ Unauthorized. Please <a href='reseller_login.php'>login</a> first.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name  = trim($_POST['product_name']);
    $product_price = trim($_POST['product_price']);

    if (empty($product_name) || empty($product_price)) {
        die("❌ All fields are required.");
    }

    if (!is_numeric($product_price) || $product_price <= 0) {
        die("❌ Invalid product price.");
    }

    // Handle image upload
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

    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'm228_db');
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }

    // Create products table with reseller_id as FK
    $conn->query("
        CREATE TABLE IF NOT EXISTS products (
            product_id INT AUTO_INCREMENT PRIMARY KEY,
            reseller_id INT NOT NULL,
            name VARCHAR(100) NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            image_path VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (reseller_id) REFERENCES resellers(reseller_id) ON DELETE CASCADE
        )
    ");

    $image_path = 'uploads/' . $new_name;
    $stmt = $conn->prepare("INSERT INTO products (reseller_id, name, price, image_path) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isds", $_SESSION['reseller_id'], $product_name, $product_price, $image_path);

    if ($stmt->execute()) {
        echo "✅ Product uploaded successfully! <a href='product_upload.php'>Upload another</a>";
    } else {
        echo "❌ Database error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
