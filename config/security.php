<?php
/**
 * SECURITY CONFIGURATION FILE
 * Highly Secure Campus Recruitment Portal
 */

// ===== SECURITY HEADERS =====
// Prevent clickjacking
header('X-Frame-Options: DENY');

// Prevent MIME type sniffing
header('X-Content-Type-Options: nosniff');

// Enable XSS protection
header('X-XSS-Protection: 1; mode=block');

// Content Security Policy
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://unpkg.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:;");

// Referrer Policy
header('Referrer-Policy: strict-origin-when-cross-origin');

// Permissions Policy
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");

// ===== SESSION CONFIGURATION =====
define('SESSION_TIMEOUT', 30 * 60); // 30 minutes
define('SESSION_REGENERATE_INTERVAL', 15 * 60); // Regenerate every 15 minutes
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_ATTEMPT_TIMEOUT', 15 * 60); // 15 minutes
define('SESSION_IP_CHECK', true); // Check IP address matches
define('SESSION_USER_AGENT_CHECK', true); // Check User Agent matches

// ===== PASSWORD REQUIREMENTS =====
define('MIN_PASSWORD_LENGTH', 8);
define('REQUIRE_UPPERCASE', true);
define('REQUIRE_LOWERCASE', true);
define('REQUIRE_NUMBERS', true);
define('REQUIRE_SPECIAL_CHARS', true);

// ===== LOGGING =====
define('ENABLE_LOGIN_LOGGING', true);
define('ENABLE_ACTIVITY_LOGGING', true);
define('LOG_FAILED_ATTEMPTS', true);

// ===== SECURITY FLAGS =====
define('ALLOW_CONCURRENT_SESSIONS', false);
define('REGENERATE_AFTER_LOGIN', true);
define('VALIDATE_REFERRER', false);

?>
