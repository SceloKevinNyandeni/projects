<?php
session_start();
if (!isset($_SESSION['reseller_id'])) {
    die("❌ Unauthorized.");
}
$reseller_id = (int)$_SESSION['reseller_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: products.php");
    exit;
}

if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Invalid CSRF token.");
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id <= 0) {
    header("Location: products.php?error=" . urlencode("Invalid product ID."));
    exit;
}

$conn = new mysqli("localhost", "root", "", "m228_db");
if ($conn->connect_error) {
    header("Location: products.php?error=" . urlencode("DB connection error."));
    exit;
}

// verify ownership and fetch image path
$stmt = $conn->prepare("SELECT image_path FROM products WHERE product_id = ? AND reseller_id = ?");
$stmt->bind_param("ii", $id, $reseller_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    $stmt->close();
    $conn->close();
    header("Location: products.php?error=" . urlencode("Product not found or unauthorized."));
    exit;
}
$row = $res->fetch_assoc();
$image = $row['image_path'];
$stmt->close();

// delete row
$del = $conn->prepare("DELETE FROM products WHERE product_id = ? AND reseller_id = ?");
$del->bind_param("ii", $id, $reseller_id);
if ($del->execute()) {
    // remove image file if exists (and is inside uploads)
    if (!empty($image) && strpos($image, 'uploads/') === 0 && file_exists(__DIR__ . '/' . $image)) {
        @unlink(__DIR__ . '/' . $image);
    }
    $del->close();
    $conn->close();
    header("Location: products.php?success=" . urlencode("Product deleted."));
    exit;
} else {
    $del->close();
    $conn->close();
    header("Location: products.php?error=" . urlencode("Failed to delete product."));
    exit;
}
