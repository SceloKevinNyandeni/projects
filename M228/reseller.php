<?php 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="reseller-styles.css">
    <title>Reseller Dashboard</title>
</head>
<body>
    <div class="navigation">
        <img src="" alt="logo">

        <div class="nav-menu">
            <nav>
                <ul>
                    <li class="active">Profile</li>
                    <li>Sell</li>
                </ul>
            </nav>
        </div>

        <div class="logout">
            <button class="logOut">LOGOUT</button>
        </div>
    </div>

    <div class="dashboard">
        <div class="sales">
        <div class="addProducts">
            <form method="post" action="upload.php" enctype="multipart/form-data">
            <input type="text" id="product-name" name="product_name" placeholder="Enter product name" required>
            <input type="text" id="product-price" name="product_price" placeholder="Enter product price" required>
            
              <!-- Category dropdown -->
    <select name="category_id" required>
        <option value="">Select Category</option>
        <?php
        $conn = new mysqli('localhost', 'root', '', 'm228_db');
        $categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");
        while ($cat = $categories->fetch_assoc()) {
            echo '<option value="' . $cat['category_id'] . '">' . htmlspecialchars($cat['name']) . '</option>';
        }
        $conn->close();
        ?>
    </select>

            <input type="file" id="product-image" name="product_image" accept="image/*" required>

            <div class="btns">
              <button type="submit" class="done">Done</button>
              <button type="button" class="edit">Edit</button>
            </div>
        
             </form>
        </div>
        </div>

        <div class="orders">
         <div class="ordersReceived">
           <h3>Orders received</h3>
           <p>product name</p>
           <p>Order value</p>
         </div>
        </div>
    </div>
</body>
</html>


