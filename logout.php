<?php
require_once 'config/db.php';

// Ensure session is initialized
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Use SecurityHelper to destroy session securely
if ($security instanceof SecurityHelper) {
    $security->destroySessionSecurely();
} else {
    // Fallback if security helper not initialized
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_unset();
    session_destroy();
}

// Redirect to home
header('Location: /campuss/', true, 302);
exit;

