<?php
/**
 * SECURITY HELPER CLASS
 * Handles all security-related operations
 */

class SecurityHelper {
    private $conn;
    
    public function __construct($database_connection) {
        $this->conn = $database_connection;
    }

    /**
     * Generate CSRF Token
     */
    public function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify CSRF Token
     */
    public function verifyCSRFToken($token) {
        if (empty($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            return false;
        }
        return true;
    }

    /**
     * Regenerate CSRF Token after use (optional)
     */
    public function regenerateCSRFToken() {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }

    /**
     * Initialize session with security checks
     */
    public function initializeSession() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            // Set secure session parameters BEFORE session_start()
            ini_set('session.use_strict_mode', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_samesite', 'Strict');
            
            session_start();
        }

        // Validate session security
        $this->validateSessionSecurity();
    }

    /**
     * Validate Session Security - Check IP, User Agent, etc.
     */
    private function validateSessionSecurity() {
        // Get current IP and User Agent
        $current_ip = $this->getClientIP();
        $current_user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        // Check IP consistency
        if (defined('SESSION_IP_CHECK') && SESSION_IP_CHECK) {
            if (isset($_SESSION['ip_address'])) {
                if ($_SESSION['ip_address'] !== $current_ip) {
                    $this->destroySessionSecurely();
                    $_SESSION['security_alert'] = 'IP address changed. Please login again.';
                    header('Location: /campuss/');
                    exit;
                }
            }
        }

        // Check User Agent consistency
        if (defined('SESSION_USER_AGENT_CHECK') && SESSION_USER_AGENT_CHECK) {
            if (isset($_SESSION['user_agent'])) {
                if ($_SESSION['user_agent'] !== $current_user_agent) {
                    $this->destroySessionSecurely();
                    $_SESSION['security_alert'] = 'User Agent changed. Please login again.';
                    header('Location: /campuss/');
                    exit;
                }
            }
        }

        // Check session timeout
        if (isset($_SESSION['last_activity'])) {
            if ((time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
                $this->destroySessionSecurely();
                $_SESSION['session_expired'] = true;
                header('Location: /campuss/');
                exit;
            }
        }

        // Regenerate session periodically
        if (isset($_SESSION['created_time'])) {
            if ((time() - $_SESSION['created_time']) > SESSION_REGENERATE_INTERVAL) {
                $this->regenerateSessionID();
            }
        }

        // Update last activity
        $_SESSION['last_activity'] = time();
    }

    /**
     * Regenerate Session ID (prevents session fixation)
     */
    private function regenerateSessionID() {
        session_regenerate_id(true);
        $_SESSION['created_time'] = time();
    }

    /**
     * Register User Login with Security Checks
     */
    public function registerLogin($userId, $userRole, $email) {
        // Store in session
        $_SESSION['user_id'] = $userId;
        $_SESSION['role'] = $userRole;
        $_SESSION['email'] = $email;
        $_SESSION['ip_address'] = $this->getClientIP();
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $_SESSION['created_time'] = time();
        $_SESSION['last_activity'] = time();

        // Regenerate session ID to prevent session fixation
        if (defined('REGENERATE_AFTER_LOGIN') && REGENERATE_AFTER_LOGIN) {
            session_regenerate_id(true);
        }

        // Log the login
        $this->logLoginActivity($userId, $userRole, $email, true);
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated() {
        return isset($_SESSION['user_id']) && isset($_SESSION['role']);
    }

    /**
     * Check if user has specific role
     */
    public function hasRole($requiredRole) {
        return isset($_SESSION['role']) && $_SESSION['role'] === $requiredRole;
    }

    /**
     * Check if user is authorized to access resource
     */
    public function isAuthorized($requiredRole) {
        if (!$this->isAuthenticated()) {
            return false;
        }
        return $this->hasRole($requiredRole);
    }

    /**
     * Get Client IP Address
     */
    public function getClientIP() {
        $ip = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        }
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';
    }

    /**
     * Destroy Session Securely
     */
    public function destroySessionSecurely() {
        // Log logout
        if (isset($_SESSION['user_id'])) {
            $this->logLoginActivity($_SESSION['user_id'], $_SESSION['role'] ?? 'unknown', $_SESSION['email'] ?? '', false);
        }

        // Clear all session data
        $_SESSION = [];

        // Delete session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Destroy session
        session_unset();
        session_destroy();
    }

    /**
     * Log Login/Logout Activity
     */
    public function logLoginActivity($userId, $userRole, $email, $isLogin = true) {
        if (!defined('ENABLE_LOGIN_LOGGING') || !ENABLE_LOGIN_LOGGING) {
            return;
        }

        try {
            $action = $isLogin ? 'login' : 'logout';
            $ip_address = $this->getClientIP();
            $user_agent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
            $timestamp = date('Y-m-d H:i:s');

            $table_name = match($userRole) {
                'student' => 'student_login_logs',
                'staff' => 'staff_login_logs',
                'admin' => 'admin_login_logs',
                default => null
            };

            if ($table_name) {
                // Create table if not exists
                $this->conn->exec("CREATE TABLE IF NOT EXISTS `$table_name` (
                    `log_id` INT AUTO_INCREMENT PRIMARY KEY,
                    `user_id` INT NOT NULL,
                    `email` VARCHAR(100) NOT NULL,
                    `action` VARCHAR(20) NOT NULL,
                    `ip_address` VARCHAR(45) NOT NULL,
                    `user_agent` TEXT,
                    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

                // Insert log
                $stmt = $this->conn->prepare("INSERT INTO `$table_name` (user_id, email, action, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$userId, $email, $action, $ip_address, $user_agent]);
            }
        } catch (Exception $e) {
            error_log("Login logging failed: " . $e->getMessage());
        }
    }

    /**
     * Check Login Attempts (Rate Limiting)
     */
    public function checkLoginAttempts($email) {
        try {
            // Create table if not exists
            $this->conn->exec("CREATE TABLE IF NOT EXISTS `login_attempts` (
                `attempt_id` INT AUTO_INCREMENT PRIMARY KEY,
                `email` VARCHAR(100) NOT NULL,
                `attempt_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `ip_address` VARCHAR(45),
                KEY `email_time` (`email`, `attempt_time`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            // Clean old attempts
            $timeout = defined('LOGIN_ATTEMPT_TIMEOUT') ? LOGIN_ATTEMPT_TIMEOUT : 900;
            $this->conn->prepare("DELETE FROM login_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL ? SECOND)")
                ->execute([$timeout]);

            // Get recent attempts
            $stmt = $this->conn->prepare("SELECT COUNT(*) as attempt_count FROM login_attempts WHERE email = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL ? SECOND)");
            $timeout = defined('LOGIN_ATTEMPT_TIMEOUT') ? LOGIN_ATTEMPT_TIMEOUT : 900;
            $stmt->execute([$email, $timeout]);
            $result = $stmt->fetch();

            $max_attempts = defined('MAX_LOGIN_ATTEMPTS') ? MAX_LOGIN_ATTEMPTS : 5;
            if ($result['attempt_count'] >= $max_attempts) {
                return false; // Too many attempts
            }
            return true;
        } catch (Exception $e) {
            error_log("Rate limiting check failed: " . $e->getMessage());
            return true; // Allow if check fails
        }
    }

    /**
     * Record Failed Login Attempt
     */
    public function recordFailedAttempt($email) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO login_attempts (email, ip_address) VALUES (?, ?)");
            $stmt->execute([$email, $this->getClientIP()]);
        } catch (Exception $e) {
            error_log("Failed attempt recording failed: " . $e->getMessage());
        }
    }

    /**
     * Clear Login Attempts after successful login
     */
    public function clearLoginAttempts($email) {
        try {
            $this->conn->prepare("DELETE FROM login_attempts WHERE email = ?")->execute([$email]);
        } catch (Exception $e) {
            error_log("Clear attempts failed: " . $e->getMessage());
        }
    }

    /**
     * Sanitize Input
     */
    public static function sanitizeInput($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate Email
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate Password Strength
     */
    public static function validatePasswordStrength($password) {
        $errors = [];

        if (strlen($password) < (defined('MIN_PASSWORD_LENGTH') ? MIN_PASSWORD_LENGTH : 8)) {
            $errors[] = 'Password must be at least ' . (defined('MIN_PASSWORD_LENGTH') ? MIN_PASSWORD_LENGTH : 8) . ' characters long';
        }

        if (defined('REQUIRE_UPPERCASE') && REQUIRE_UPPERCASE && !preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }

        if (defined('REQUIRE_LOWERCASE') && REQUIRE_LOWERCASE && !preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }

        if (defined('REQUIRE_NUMBERS') && REQUIRE_NUMBERS && !preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }

        if (defined('REQUIRE_SPECIAL_CHARS') && REQUIRE_SPECIAL_CHARS && !preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }

        return $errors;
    }

    /**
     * Prevent Concurrent Sessions
     */
    public function preventConcurrentSessions($userId, $userRole) {
        if (!defined('ALLOW_CONCURRENT_SESSIONS') || ALLOW_CONCURRENT_SESSIONS) {
            return true; // Concurrent sessions allowed
        }

        try {
            // This would require additional implementation with database tracking
            // For now, just return true
            return true;
        } catch (Exception $e) {
            return true;
        }
    }

    /**
     * Get Session Info
     */
    public function getSessionInfo() {
        return [
            'user_id' => $_SESSION['user_id'] ?? null,
            'role' => $_SESSION['role'] ?? null,
            'email' => $_SESSION['email'] ?? null,
            'ip_address' => $_SESSION['ip_address'] ?? null,
            'last_activity' => $_SESSION['last_activity'] ?? null,
            'created_time' => $_SESSION['created_time'] ?? null
        ];
    }
}

?>
