<?php
/**
 * FairFare System - Authentication Functions
 * 
 * Provides authentication and authorization utilities
 * 
 * @package FairFare
 * @version 1.0.0
 */

/**
 * Check if user is logged in
 * 
 * @return bool True if user is logged in, false otherwise
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user ID
 * 
 * @return int|null User ID if logged in, null otherwise
 */
function get_current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role
 * 
 * @return string|null User role if logged in, null otherwise
 */
function get_current_user_role() {
    return $_SESSION['role'] ?? null;
}

/**
 * Get current username
 * 
 * @return string|null Username if logged in, null otherwise
 */
function get_current_username() {
    return $_SESSION['username'] ?? null;
}

/**
 * Check if current user is admin
 * 
 * @return bool True if user is admin, false otherwise
 */
function is_admin() {
    return is_logged_in() && isset($_SESSION['role']) && $_SESSION['role'] === ROLE_ADMIN;
}

/**
 * Check if login attempts are rate limited
 * 
 * @param string $email Email to check
 * @return bool True if rate limited, false otherwise
 */
function is_rate_limited($email) {
    $key = 'login_attempts_' . md5($email);
    $attempts = $_SESSION[$key] ?? ['count' => 0, 'first_attempt' => time()];
    
    $time_since_first = time() - $attempts['first_attempt'];
    
    // Reset after 15 minutes
    if ($time_since_first > 900) {
        unset($_SESSION[$key]);
        return false;
    }
    
    // Allow 5 attempts per 15 minutes
    return $attempts['count'] >= 5;
}

/**
 * Record failed login attempt
 * 
 * @param string $email Email to record
 */
function record_failed_login($email) {
    $key = 'login_attempts_' . md5($email);
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 1, 'first_attempt' => time()];
    } else {
        $_SESSION[$key]['count']++;
    }
}

/**
 * Require user to be logged in
 * Redirects to login page if not authenticated
 */
function require_login() {
    if (!is_logged_in()) {
        session_write_close();
        header("Location: " . APP_URL . "/login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
        exit();
    }
}

/**
 * Require user to be admin
 * Redirects to login if not authenticated, or home page if authenticated but not admin
 */
function require_admin() {
    if (!is_logged_in()) {
        session_write_close();
        header("Location: " . APP_URL . "/login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
        exit();
    }
    
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== ROLE_ADMIN) {
        session_write_close();
        header("Location: " . APP_URL . "/index.php");
        exit();
    }
}

/**
 * Generate CSRF token
 * 
 * @return string CSRF token
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * 
 * @param string $token Token to verify
 * @return bool True if token is valid, false otherwise
 */
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Sanitize user input
 * 
 * @param string $input Input to sanitize
 * @return string Sanitized input
 */
function sanitize_input($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Logout user and destroy session
 */
function logout_user() {
    session_destroy();
    session_write_close();
}

/**
 * Log user activity
 * 
 * @param string $action Action performed
 * @param string $details Additional details
 */
function log_activity($action, $details = '') {
    global $conn;
    
    try {
        $user_id = get_current_user_id();
        $username = get_current_username();
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $timestamp = date('Y-m-d H:i:s');
        
        if ($user_id) {
            $stmt = $conn->prepare("
                INSERT INTO activity_logs (user_id, username, action, details, ip_address, user_agent, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$user_id, $username, $action, $details, $ip_address, $user_agent, $timestamp]);
        }
    } catch (Exception $e) {
        error_log("Activity logging failed: " . $e->getMessage());
    }
}

?>