<?php
if (session_status() === PHP_SESSION_NONE) {
    // Configure secure session cookie parameters and start the session.
    $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    // Strip any port number from the host before using it as the cookie domain.
    // A Domain attribute that includes a port (e.g. "127.0.0.1:8000") is invalid
    // per RFC 6265 and browsers will silently reject the cookie, which breaks
    // login on any non-standard port (PHP's built-in server, Docker, XAMPP, etc.).
    $cookieDomain = explode(':', $_SERVER['HTTP_HOST'] ?? '')[0];
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => $cookieDomain,
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
}

if (!defined('BASE_URL')) {
    // Auto-detect scheme + host so the app works unmodified on any live
    // domain. Falls back to the XAMPP-style local subfolder only when
    // running on localhost. Set the BASE_URL environment variable to
    // override entirely (e.g. if deployed under a subpath).
    $autoScheme = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || ($_SERVER['SERVER_PORT'] ?? '') == 443) ? 'https' : 'http';
    $autoHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $isLocalHost = (strpos($autoHost, 'localhost') === 0) || (strpos($autoHost, '127.0.0.1') === 0);
    $defaultBaseUrl = $autoScheme . '://' . $autoHost . ($isLocalHost ? '/restaurant-management-system' : '');
    define('BASE_URL', getenv('BASE_URL') ?: $defaultBaseUrl);
}

require_once ROOT_PATH . 'config/database.php';

// Session timeout (seconds) - 30 minutes by default
if (!defined('SESSION_TIMEOUT')) {
    define('SESSION_TIMEOUT', 1800);
}

// Enforce session timeout: destroy session after inactivity.
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
    // Log out the user on timeout
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    // Start a fresh session to show a timeout message
    session_start();
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Session expired due to inactivity. Please sign in again.'];
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();
?>
