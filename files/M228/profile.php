<?php
/** file: auth/user-profile.php */
session_start();
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized. Please <a href='user-login.php'>login</a> first.");
}

$userId = $_SESSION['user_id'];
$msg = "";

// Connect to DB
$conn = new mysqli('localhost', 'root', '', 'm228_db');
if ($conn->connect_error) die("Database connection failed.");

// 1️⃣ Ensure users table exists
$tableSql = "
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    dob DATE NOT NULL DEFAULT '1970-01-01',
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    address VARCHAR(255) NOT NULL,
    suburb VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    postalcode VARCHAR(20) NOT NULL,
    province VARCHAR(100) NOT NULL,
    cardPhoto VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
";

if (!$conn->query($tableSql)) die("Failed to create table: " . $conn->error);

// 2️⃣ Ensure all required columns exist
$requiredColumns = [
    'firstname'  => "VARCHAR(100) NOT NULL",
    'lastname'   => "VARCHAR(100) NOT NULL",
    'dob'        => "DATE NOT NULL DEFAULT '1970-01-01'",
    'phone'      => "VARCHAR(20) NOT NULL",
    'address'    => "VARCHAR(255) NOT NULL",
    'suburb'     => "VARCHAR(100) NOT NULL",
    'city'       => "VARCHAR(100) NOT NULL",
    'postalcode' => "VARCHAR(20) NOT NULL",
    'province'   => "VARCHAR(100) NOT NULL",
    'cardPhoto'  => "VARCHAR(255)"
];

foreach ($requiredColumns as $col => $type) {
    $check = $conn->query("SHOW COLUMNS FROM users LIKE '$col'");
    if ($check->num_rows === 0) {
        // Add column with safe default for dob
        $conn->query("ALTER TABLE users ADD COLUMN $col $type");
    } else if ($col === 'dob') {
        // Ensure existing dob column has safe default
        $conn->query("ALTER TABLE users MODIFY dob DATE NOT NULL DEFAULT '1970-01-01'");
    }
}

// 3️⃣ Fetch existing user data
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc() ?? [];
$stmt->close();

// 4️⃣ Collect POST data
$firstname  = $_POST['firstname']  ?? "";
$lastname   = $_POST['lastname']   ?? "";
$dob        = $_POST['dob']        ?? "";
$phone      = $_POST['phone']      ?? "";
$email      = $_POST['email']      ?? ($user['email'] ?? "");
$address    = $_POST['address']    ?? "";
$suburb     = $_POST['suburb']     ?? "";
$city       = $_POST['city']       ?? "";
$postalcode = $_POST['postalcode'] ?? "";
$province   = $_POST['province']   ?? "";
$cardPhoto  = $_FILES['cardphoto'] ?? null;

// 5️⃣ Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    if (!$firstname || strlen($firstname) < 2) $errors[] = "First name must be at least 2 characters.";
    if (!$lastname || strlen($lastname) < 2) $errors[] = "Last name must be at least 2 characters.";
    if (!$dob || !preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dob)) $errors[] = "DOB must be in MM/DD/YYYY format.";
    if (!$phone || strlen(preg_replace('/\D/', '', $phone)) < 10) $errors[] = "Phone number must be at least 10 digits.";
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if (!$address || strlen($address) < 5) $errors[] = "Address must be at least 5 characters.";
    if (!$suburb) $errors[] = "Suburb is required.";
    if (!$city) $errors[] = "City is required.";
    if (!$postalcode) $errors[] = "Postal code is required.";
    if (!$province) $errors[] = "Province is required.";

    if (empty($errors)) {
        // 6️⃣ Handle card photo upload
        $cardPhotoName = $user['cardPhoto'] ?? null;
        if ($cardPhoto && $cardPhoto['name']) {
            $targetDir = __DIR__ . "/uploads/";
            if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
            $cardPhotoName = time() . "_" . basename($cardPhoto['name']);
            move_uploaded_file($cardPhoto['tmp_name'], $targetDir . $cardPhotoName);
        }

        // 7️⃣ Convert DOB
        $dobFormatted = date('Y-m-d', strtotime($dob));

        // 8️⃣ Update existing user
        $stmt = $conn->prepare("
            UPDATE users SET
                firstname=?, lastname=?, dob=?, phone=?, address=?, suburb=?, city=?, postalcode=?, province=?, cardPhoto=?
            WHERE user_id=?
        ");
        $stmt->bind_param("ssssssssssi", $firstname, $lastname, $dobFormatted, $phone, $address, $suburb, $city, $postalcode, $province, $cardPhotoName, $userId);
        $stmt->execute();

        // 9️⃣ If user does not exist yet, insert
        if ($stmt->affected_rows === 0) {
            $stmt = $conn->prepare("
                INSERT INTO users (firstname, lastname, dob, phone, email, address, suburb, city, postalcode, province, cardPhoto)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("sssssssssss", $firstname, $lastname, $dobFormatted, $phone, $email, $address, $suburb, $city, $postalcode, $province, $cardPhotoName);
            $stmt->execute();
        }

        $stmt->close();
        $msg = '<div class="toast success show"><i class="fas fa-check-circle"></i><span class="toast-message">Profile saved successfully!</span></div>';
    } else {
        $msg = '<div class="toast error show"><i class="fas fa-exclamation-circle"></i><span class="toast-message">' . htmlspecialchars(implode(' ', $errors)) . '</span></div>';
    }
}

$conn->close();

// Helper to prefill form safely
function val($field) { global $user; return htmlspecialchars($_POST[$field] ?? $user[$field] ?? ''); }
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Profile</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');
:root{
    --ink:#0f172a; 
    --muted:#6b7280; 
    --line:#e5e7eb; 
    --brand:hsl(216,77%,48%); 
    --brand-600:hsl(216,77%,40%);
    --bg:#fafafa; 
    --card:#ffffff; 
    --ok:#16a34a;
  }
  *{
    box-sizing:border-box;
  }
  html,body{
    margin:0;
  }
  body{
    font-family:'Poppins', system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif; background:var(--bg); 
    color:var(--ink);
  }

  /* Top black bar */
  .topbar{
    background:#000; 
    color:#fff; 
    padding:10px 5%;
    display:flex; 
    align-items:center; 
    justify-content:space-between; 
    gap:10px;
  }
  .topbar a{
    color:#fff; 
    text-decoration:none; 
    font-weight:600; 
    opacity:.9;
  }
  .topbar a:hover{
    opacity:1;
  }

  /* Main header under bar */
  .pagehead{
    padding:24px 5% 8px;
    display:flex; 
    align-items:end; 
    justify-content:space-between; 
    gap:16px;
    border-bottom:1px solid var(--line);
    background:#fff;
  }
  .pagehead h1{
    margin:0; 
    font-weight:800; 
    letter-spacing:.2px;
    font-size: 1.3rem;
    font-weight: 700;
  }
  .crumb a{
    color:var(--brand); 
    text-decoration:none; 
    font-weight:600;
  }
  .crumb a:hover{
    color:var(--brand-600);
  }
  


    .container {
      max-width: 1000px;
      margin: 0 auto;
    }

    .profile-card {
      background: white;
      border-radius: 1rem;
      box-shadow: 0 8px 32px -8px rgba(30, 41, 59, 0.12);
      overflow: hidden;
    }

    .card-header {
      padding: 2rem;
      border-bottom: 1px solid #e2e8f0;
      background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    }

    .card-title {
      font-size: 2rem;
      font-weight: 700;
      background: #1b0066ff;
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 0.5rem;
    }

    .card-description {
      color: #64748b;
      font-size: 1rem;
    }

    .card-content {
      padding: 2rem;
    }

    .form-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 1.5rem;
    }

    @media (min-width: 768px) {
      .form-grid {
        grid-template-columns: repeat(2, 1fr);
      }
      
      .full-width {
        grid-column: 1 / -1;
      }
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }

    label {
      font-size: 0.875rem;
      font-weight: 500;
      color: #334155;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    label i {
      color: #1b0066ff;
      width: 1rem;
    }

    input[type="text"],
    input[type="tel"],
    input[type="email"] {
      width: 100%;
      padding: 0.75rem 1rem;
      border: 1px solid #e2e8f0;
      border-radius: 0.75rem;
      font-size: 0.875rem;
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
      background: white;
    }

    input:focus {
      outline: none;
      border-color: #1b0066ff;
      box-shadow: 0 0 0 3px rgba(8, 145, 178, 0.1);
    }

    input::placeholder {
      color: #94a3b8;
    }

    .error-message {
      color: #ef4444;
      font-size: 0.75rem;
      display: none;
    }

    .error-message.show {
      display: block;
    }

    input.error {
      border-color: #ef4444;
    }

    .file-upload {
      border: 2px dashed #cbd5e1;
      border-radius: 0.75rem;
      padding: 2rem;
      text-align: center;
      cursor: pointer;
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
      background: #f8fafc;
    }

    .file-upload:hover,
    .file-upload.drag-over {
      border-color: #1b0066ff;
      background: #e0f2fe;
    }

    .file-upload i {
      font-size: 2.5rem;
      color: #94a3b8;
      margin-bottom: 1rem;
    }

    .file-upload p {
      font-size: 0.875rem;
      font-weight: 500;
      color: #1e293b;
      margin-bottom: 0.25rem;
    }

    .file-upload span {
      font-size: 0.75rem;
      color: #64748b;
    }

    .file-upload .file-name {
      margin-top: 0.5rem;
      font-size: 0.875rem;
      font-weight: 500;
      color: #1b0066ff;
    }

    input[type="file"] {
      display: none;
    }

    .submit-btn {
      width: 100%;
      padding: 0.875rem 1.5rem;
      background: linear-gradient(135deg, #1b0066ff 0%, rgb(119, 70, 255) 100%);
      color: white;
      border: none;
      border-radius: 0.75rem;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: 0 4px 16px -4px rgba(8, 145, 178, 0.3);
      margin-top: 1rem;
    }

    .submit-btn:hover {
      transform: scale(1.02);
      box-shadow: 0 8px 32px -8px rgba(8, 145, 178, 0.4);
    }

    .submit-btn:active {
      transform: scale(0.98);
    }

    .submit-btn:disabled {
      opacity: 0.6;
      cursor: not-allowed;
      transform: none;
    }

    .toast {
      position: fixed;
      bottom: 2rem;
      right: 2rem;
      background: white;
      padding: 1rem 1.5rem;
      border-radius: 0.75rem;
      box-shadow: 0 8px 32px -8px rgba(30, 41, 59, 0.2);
      display: none;
      align-items: center;
      gap: 0.75rem;
      z-index: 1000;
      animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
      from {
        transform: translateX(100%);
        opacity: 0;
      }
      to {
        transform: translateX(0);
        opacity: 1;
      }
    }

    .toast.show {
      display: flex;
    }

    .toast.success {
      border-left: 4px solid #10b981;
    }

    .toast.error {
      border-left: 4px solid #ef4444;
    }

    .toast i {
      font-size: 1.25rem;
    }

    .toast.success i {
      color: #10b981;
    }

    .toast.error i {
      color: #ef4444;
    }

    .toast-message {
      font-size: 0.875rem;
      font-weight: 500;
      color: #1e293b;
    }
  </style>
</head>
<body>
<!-- Toast -->
<?= $msg ?? '' ?>

<div class="topbar">
  <div><a href="profile.php"><i class="fa-regular fa-user"></i></a></div>
  <div style="font-weight:800; letter-spacing:.3px">MASHOBANE & CO</div>
  <div><a href="cart.php"><i class="fa-solid fa-bag-shopping"></i> <span class="cart-count">(0)</span></a></div>
</div>

<div class="pagehead">
  <h1 class="card-title">Your Personal Details</h1>
  <div class="crumb"><a href="index.php"><i class="fa-solid fa-arrow-left"></i> Continue Shopping</a></div>
</div>

<div class="container">
  <div class="profile-card">
    <div class="card-header"><p class="card-description">Please fill in your information to complete your profile</p></div>
    <div class="card-content">
      <form id="profile-form" method="POST" action="" enctype="multipart/form-data">
        <div class="form-grid">
          <?php
          // Helper to prefill value safely
          //function val($field) { global $user; return htmlspecialchars($_POST[$field] ?? $user[$field] ?? ''); }
          ?>
          <div class="form-group">
            <label for="firstname"><i class="fas fa-user"></i> First name</label>
            <input type="text" id="firstname" name="firstname" placeholder="Enter first name" required value="<?= val('firstname') ?>">
          </div>
          <div class="form-group">
            <label for="lastname"><i class="fas fa-user"></i> Last name</label>
            <input type="text" id="lastname" name="lastname" placeholder="Enter last name" required value="<?= val('lastname') ?>">
          </div>
          <div class="form-group">
            <label for="dob"><i class="fas fa-calendar"></i> Date of birth</label>
            <input type="text" id="dob" name="dob" placeholder="MM/DD/YYYY" required value="<?= !empty($user['dob']) ? date('m/d/Y', strtotime($user['dob'])) : '' ?>">
          </div>
          <div class="form-group">
            <label for="phone"><i class="fas fa-phone"></i> Contact number</label>
            <input type="tel" id="phone" name="phone" placeholder="Enter phone number" required value="<?= val('phone') ?>">
          </div>
          <div class="form-group full-width">
            <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required value="<?= val('email') ?>">
          </div>
          <div class="form-group full-width">
            <label for="address"><i class="fas fa-home"></i> Street Address</label>
            <input type="text" id="address" name="address" placeholder="Enter street address" required value="<?= val('address') ?>">
          </div>
          <div class="form-group">
            <label for="suburb"><i class="fas fa-map-pin"></i> Suburb</label>
            <input type="text" id="suburb" name="suburb" placeholder="Enter your suburb" required value="<?= val('suburb') ?>">
          </div>
          <div class="form-group">
            <label for="city"><i class="fas fa-city"></i> City</label>
            <input type="text" id="city" name="city" placeholder="Enter your city" required value="<?= val('city') ?>">
          </div>
          <div class="form-group">
            <label for="postalcode"><i class="fas fa-mail-bulk"></i> Postal Code</label>
            <input type="text" id="postalcode" name="postalcode" placeholder="Enter postal code" required value="<?= val('postalcode') ?>">
          </div>
          <div class="form-group">
            <label for="province"><i class="fas fa-globe"></i> Province</label>
            <input type="text" id="province" name="province" placeholder="Enter province" required value="<?= val('province') ?>">
          </div>
          <div class="form-group full-width">
            <label><i class="fas fa-id-card"></i> Upload a photo of your card</label>
            <div class="file-upload" id="file-upload">
              <i class="fas fa-cloud-upload-alt"></i>
              <p>Click to upload or drag and drop</p>
              <span>PNG, JPG, GIF (max 5MB)</span>
              <div class="file-name" id="file-name"><?= htmlspecialchars($user['cardPhoto'] ?? '') ?></div>
              <input type="file" id="cardphoto" name="cardphoto" accept="image/png,image/jpeg,image/gif">
            </div>
          </div>
        </div>
        <button type="submit" class="submit-btn" id="submit-btn">Save and Continue</button>
      </form>
    </div>
  </div>
</div>

<script>
  // Drag & Drop file upload
  const fileUpload = document.getElementById('file-upload');
  const fileInput = document.getElementById('cardphoto');
  const fileName = document.getElementById('file-name');

  fileUpload.addEventListener('click', () => fileInput.click());
  fileInput.addEventListener('change', () => {
    fileName.textContent = fileInput.files[0]?.name ?? '';
  });

  fileUpload.addEventListener('dragover', e => { e.preventDefault(); fileUpload.classList.add('dragover'); });
  fileUpload.addEventListener('dragleave', e => { e.preventDefault(); fileUpload.classList.remove('dragover'); });
  fileUpload.addEventListener('drop', e => {
    e.preventDefault();
    fileUpload.classList.remove('dragover');
    if (e.dataTransfer.files.length) {
      fileInput.files = e.dataTransfer.files;
      fileName.textContent = e.dataTransfer.files[0].name;
    }
  });

  // Toast auto-dismiss
  setTimeout(() => document.querySelectorAll('.toast.show').forEach(t => t.remove()), 5000);
</script>
</body>
</html>
