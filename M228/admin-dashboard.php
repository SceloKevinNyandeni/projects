<?php
session_start();

// 1️⃣ Admin check
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    die("❌ You are not authorized to access this page.");
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'm228_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2️⃣ Handle category creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category_name'])) {
    $category_name = trim($_POST['category_name']);
    if (!empty($category_name)) {
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $category_name);
        if ($stmt->execute()) {
            $msg = "✅ Category created successfully!";
        } else if ($conn->errno == 1062) {
            $msg = "❌ Category already exists!";
        } else {
            $msg = "❌ Error: " . $conn->error;
        }
        $stmt->close();
    } else {
        $msg = "❌ Category name cannot be empty!";
    }
}

// 3️⃣ Handle category deletion
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM categories WHERE category_id = $delete_id");
    $msg = "✅ Category deleted successfully!";
}

// Fetch all categories
$result = $conn->query("SELECT * FROM categories ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Categories</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { text-align: center; }
        form { margin-bottom: 20px; }
        input[type="text"] { padding: 8px; width: 250px; }
        button { padding: 8px 15px; margin-left: 5px; cursor: pointer; }
        table { border-collapse: collapse; width: 100%; max-width: 500px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .msg { margin-bottom: 10px; color: green; font-weight: bold; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Admin Dashboard - Manage Categories</h1>

    <?php if (!empty($msg)) echo "<p class='msg'>$msg</p>"; ?>

    <!-- Create Category Form -->
    <form method="post" action="">
        <input type="text" name="category_name" placeholder="New category name" required>
        <button type="submit">Create Category</button>
    </form>

    <!-- Categories Table -->
    <table>
        <tr>
            <th>ID</th>
            <th>Category Name</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) : ?>
        <tr>
            <td><?= $row['category_id'] ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td>
                <a href="?delete_id=<?= $row['category_id'] ?>" onclick="return confirm('Delete this category?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
<?php $conn->close(); ?>
