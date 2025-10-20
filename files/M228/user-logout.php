<?php
/* ============================
   file: auth/user-logout.php
   ============================ */
session_start();

/* ---------- DB Connection ---------- */
$conn = @new mysqli('localhost', 'root', '', 'm228_db');
if ($conn->connect_error) {
    // If DB fails, we still destroy session and cookies.
    $conn = null;
}

/* ---------- Helpers ---------- */
function clear_cookie(string $name): void {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? null) == 443);
    setcookie($name, '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

/* ---------- Handle Remember Me Cleanup ---------- */
if (!empty($_COOKIE['remember_user'])) {
    $parts = explode(':', $_COOKIE['remember_user'], 2);
    if (count($parts) === 2 && $conn) {
        [$selector, $validator] = $parts;
        $stmt = $conn->prepare("DELETE FROM remember_tokens_users WHERE selector=?");
        $stmt->bind_param("s", $selector);
        $stmt->execute();
        $stmt->close();
    }
    clear_cookie('remember_user');
}

/* ---------- Clear Session ---------- */
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires'  => time() - 42000,
        'path'     => $params["path"],
        'domain'   => $params["domain"],
        'secure'   => $params["secure"],
        'httponly' => $params["httponly"],
        'samesite' => 'Lax',
    ]);
}
session_destroy();

/* ---------- Redirect ---------- */
header("Location: user-login.php?logged_out=1");
exit;
?>
