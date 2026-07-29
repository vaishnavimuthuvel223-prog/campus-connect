<?php
/**
 * ROLE-BASED ACCESS CONTROL
 * Use this in all protected pages instead of manual checks
 */

require_once __DIR__ . '/db.php';

/**
 * Enforce role-based access
 * Usage: enforceRole('admin'); at the top of admin pages
 */
function enforceRole($requiredRole = null, $requireApproved = false) {
    global $security;
    
    // Check if authenticated
    if (!$security || !$security->isAuthenticated()) {
        http_response_code(401);
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: /campuss/');
        exit;
    }

    // Check role
    if ($requiredRole !== null) {
        if (!$security->hasRole($requiredRole)) {
            http_response_code(403);
            die('❌ Access Denied: You do not have permission to access this page.');
        }
    }

    // Check approval status for students
    if ($requireApproved && $security->hasRole('student')) {
        if (($_SESSION['verification_status'] ?? null) !== 'approved') {
            http_response_code(403);
            die('❌ Access Denied: Your account is not yet approved. Please wait for verification.');
        }
    }

    return true;
}

/**
 * Require authenticated user
 */
function requireAuth() {
    global $security;
    
    if (!$security || !$security->isAuthenticated()) {
        http_response_code(401);
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: /campuss/');
        exit;
    }

    return true;
}

/**
 * Check if user owns the resource
 */
function canAccessUserResource($resourceUserId) {
    global $security;
    
    if (!$security || !$security->isAuthenticated()) {
        return false;
    }

    // Admin can access anything
    if ($security->hasRole('admin')) {
        return true;
    }

    // Users can only access their own  resources
    return $_SESSION['user_id'] === $resourceUserId;
}

/**
 * Get security headers as HTML comments
 */
function getSecurityHeaders() {
    $headers = "<!-- SECURITY HEADERS -->\n";
    $headers .= "<!-- X-Frame-Options: DENY -->\n";
    $headers .= "<!-- X-Content-Type-Options: nosniff -->\n";
    $headers .= "<!-- X-XSS-Protection: 1; mode=block -->\n";
    $headers .= "<!-- CSP: default-src 'self'; script-src 'self' 'unsafe-inline' -->\n";
    return $headers;
}

/**
 * Safe redirect
 */
function safeRedirect($url) {
    // Prevent open redirect
    if (filter_var($url, FILTER_VALIDATE_URL) === false) {
        $url = '/campuss/';
    }
    
    // Ensure URL is on same domain
    $parsed = parse_url($url);
    if (isset($parsed['host']) && $parsed['host'] !== $_SERVER['HTTP_HOST']) {
        $url = '/campuss/';
    }
    
    header('Location: ' . $url, true, 302);
    exit;
}

?>
