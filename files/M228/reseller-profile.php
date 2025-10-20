<?php
session_start();

/* ---------- Security Check ---------- */
if (!isset($_SESSION['reseller_id'])) {
    die("❌ Unauthorized. Please <a href='resellerLogin.php'>login</a> first.");
}

/* ---------- DB Connection ---------- */
$conn = new mysqli('localhost', 'root', '', 'm228_db');
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$resellerId = $_SESSION['reseller_id'];

/* ---------- Ensure Required Columns Exist ---------- */
$requiredColumns = [
    'reseller_id' => 'INT AUTO_INCREMENT PRIMARY KEY',
    'full_name'   => 'VARCHAR(100) NOT NULL',
    'email'       => 'VARCHAR(100) NOT NULL UNIQUE',
    'password'    => 'VARCHAR(255) NOT NULL',
    'phone'       => 'VARCHAR(15) DEFAULT NULL',
    'company'     => 'VARCHAR(100) DEFAULT NULL',
    'dob'         => 'DATE DEFAULT NULL',
    'created_at'  => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
];

foreach ($requiredColumns as $col => $type) {
    $check = $conn->query("SHOW COLUMNS FROM resellers LIKE '$col'");
    if ($check->num_rows === 0) {
        $conn->query("ALTER TABLE resellers ADD COLUMN $col $type");
    }
}

/* ---------- Load Current Reseller Info ---------- */
$stmt = $conn->prepare("SELECT full_name, email, phone, company, dob FROM resellers WHERE reseller_id = ?");
$stmt->bind_param("i", $resellerId);
$stmt->execute();
$stmt->bind_result($full_name, $email, $phone, $company, $dob);
$stmt->fetch();
$stmt->close();

/* ---------- Handle Update ---------- */
$updateMsg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email     = trim($_POST['email']);
    $phone     = trim($_POST['phone']);
    $company   = trim($_POST['company']);
    $dob       = $_POST['dob'];
    $password  = $_POST['password'];

    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE resellers SET full_name=?, email=?, phone=?, company=?, dob=?, password=? WHERE reseller_id=?");
        $stmt->bind_param("ssssssi", $full_name, $email, $phone, $company, $dob, $hashed, $resellerId);
    } else {
        $stmt = $conn->prepare("UPDATE resellers SET full_name=?, email=?, phone=?, company=?, dob=? WHERE reseller_id=?");
        $stmt->bind_param("sssssi", $full_name, $email, $phone, $company, $dob, $resellerId);
    }

    if ($stmt->execute()) {
        $updateMsg = '<div class="alert success"><i class="fa-solid fa-circle-check"></i> Profile updated successfully.</div>';
    } else {
        $updateMsg = '<div class="alert error"><i class="fa-solid fa-circle-exclamation"></i> Update failed. Try again later.</div>';
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reseller Profile | Mashobane & Co</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
<style>
:root {
  --primary: #3e0fbe; --primary-dark: #1b0066;
  --border: #e5e7eb; --bg: #fafaff; --danger:#b91c1c; --success:#0f766e;
}
body {
  font-family: 'Poppins', sans-serif; background: var(--bg); color: #1f1f29;
  display: flex; justify-content: center; align-items: center;
  min-height: 100vh; margin: 0; padding: 20px;
}
.profile-container {
  background: #fff; border: 1px solid var(--border); border-radius: 16px;
  box-shadow: 0 10px 30px rgba(31,41,55,.1);
  padding: 32px; max-width: 600px; width: 100%;
}
h2 { color: var(--primary-dark); margin-bottom: 10px; }
.alert {
  margin-bottom: 16px; padding: 10px 14px; border-radius: 10px; font-size: 14px; display: flex; align-items: center; gap: 8px;
}
.alert.success { background: #f1fcfa; border: 1px solid #bde5df; color: var(--success); }
.alert.error { background: #fff4f4; border: 1px solid #f3c7c7; color: var(--danger); }

.group { position: relative; margin-bottom: 16px; }
.icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); opacity: .6; }
.input {
  width: 90%; padding: 12px 14px 12px 42px; border: 1px solid var(--border);
  border-radius: 10px; font-size: 14px; outline: none; transition: border .2s;
}
.input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(62,15,190,.12); }
.toggle-pass {
  position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
  cursor: pointer; opacity: .6;
}
.toggle-pass:hover { opacity: .9; }

button {
  background: linear-gradient(135deg, var(--primary), var(--primary-dark));
  color: #fff; border: none; padding: 12px 18px; border-radius: 999px;
  font-weight: 600; cursor: pointer; width: 100%;
}
button:hover { filter: brightness(1.05); }
.logout { display:block; margin-top:16px; text-align:center; color: var(--primary-dark); text-decoration:none; font-weight:500; }
.logout:hover { text-decoration:underline; }
</style>
</head>
<body>

<div class="profile-container">
  <h2>Reseller Profile</h2>
  <?= $updateMsg ?>
  <form method="POST">
    <div class="group">
      <span class="icon"><i class="fa-solid fa-user"></i></span>
      <input class="input" type="text" name="full_name" value="<?= htmlspecialchars($full_name) ?>" placeholder="Full Name" required>
    </div>

    <div class="group">
      <span class="icon"><i class="fa-regular fa-envelope"></i></span>
      <input class="input" type="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="Email" required>
    </div>

    <div class="group">
      <span class="icon"><i class="fa-solid fa-phone"></i></span>
      <input class="input" type="text" name="phone" value="<?= htmlspecialchars($phone) ?>" placeholder="Phone Number">
    </div>

    <div class="group">
      <span class="icon"><i class="fa-solid fa-building"></i></span>
      <input class="input" type="text" name="company" value="<?= htmlspecialchars($company) ?>" placeholder="Company Name">
    </div>

    <div class="group">
      <span class="icon"><i class="fa-regular fa-calendar"></i></span>
      <input class="input" type="date" name="dob" value="<?= htmlspecialchars($dob) ?>">
    </div>

    <div class="group">
      <span class="icon"><i class="fa-solid fa-lock"></i></span>
      <input class="input" type="password" name="password" id="password" placeholder="New Password (optional)">
      <span class="toggle-pass" id="togglePass"><i class="fa-regular fa-eye"></i></span>
    </div>

    <button type="submit">Update Profile</button>
  </form>
  <a href="reseller-logout.php" class="logout"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
</div>

<script>
const passInput = document.getElementById('password');
const togglePass = document.getElementById('togglePass');
togglePass.addEventListener('click', () => {
  const type = passInput.type === 'password' ? 'text' : 'password';
  passInput.type = type;
  togglePass.innerHTML = type === 'password'
    ? '<i class="fa-regular fa-eye"></i>'
    : '<i class="fa-regular fa-eye-slash"></i>';
});
</script>
</body>
</html>
