<?php
/** file: admin-logout.php */
session_start();

/* ---------- Database (optional cleanup) ---------- */
$conn = @new mysqli('localhost', 'root', '', 'm228_db');
if ($conn->connect_error) {
    $conn = null; // continue logout even if DB unavailable
}

/* ---------- Helper: Clear cookie ---------- */
function clear_cookie(string $name): void {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
              || (($_SERVER['SERVER_PORT'] ?? null) == 443);
    setcookie($name, '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

/* ---------- Clear Remember-Me Token (if used) ---------- */
if (!empty($_COOKIE['remember_admin']) && $conn) {
    $parts = explode(':', $_COOKIE['remember_admin'], 2);
    if (count($parts) === 2) {
        [$selector, $validator] = $parts;
        $stmt = $conn->prepare("DELETE FROM remember_tokens_admins WHERE selector=?");
        $stmt->bind_param("s", $selector);
        $stmt->execute();
        $stmt->close();
    }
    clear_cookie('remember_admin');
}

/* ---------- Destroy Session ---------- */
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires'  => time() - 42000,
        'path'     => $params['path'],
        'domain'   => $params['domain'],
        'secure'   => $params['secure'],
        'httponly' => $params['httponly'],
        'samesite' => 'Lax',
    ]);
}
session_destroy();

/* ---------- Redirect ---------- */
header("Location: admin-login.php?logged_out=1");
exit;
?>
