<?php
/** file: auth/logout.php */
session_start();
$conn = @new mysqli('localhost', 'root', '', 'm228_db');
if ($conn->connect_error) {
    $fatalMsg = 'Database connection failed. Please try again later.';
}

// Clear Remember Me token if exists
if (!empty($_COOKIE['remember_reseller']) && !empty($_SESSION['reseller_id'])) {
    $parts = explode(':', $_COOKIE['remember_reseller'], 2);
    if (count($parts) === 2) {
        $selector = $parts[0];
        $stmt = $conn->prepare("DELETE FROM remember_tokens_resellers WHERE reseller_id=? AND selector=?");
        $stmt->bind_param("is", $_SESSION['reseller_id'], $selector);
        $stmt->execute();
        $stmt->close();
    }

    // Clear the cookie
    setcookie('remember_reseller', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

// Destroy session
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $params["path"],
        'domain' => $params["domain"],
        'secure' => $params["secure"],
        'httponly' => $params["httponly"],
        'samesite' => 'Lax'
    ]);
}
session_destroy();

// Redirect to login page
header("Location: reseller-login.php");
exit;
