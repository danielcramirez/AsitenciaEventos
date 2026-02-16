<?php
/**
 * Helper Functions
 */

/**
 * Start session securely if not already started
 */
function secure_session_start() {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
        session_start();
    }
}

/**
 * Generate CSRF token
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate secure QR token
 */
function generate_qr_token($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Sanitize input
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Get current user
 */
function get_current_user() {
    return $_SESSION['user'] ?? null;
}

/**
 * Check if user has role
 */
function has_role($role) {
    if (!is_logged_in()) {
        return false;
    }
    return $_SESSION['user']['role'] === $role;
}

/**
 * Check if user has any of the roles
 */
function has_any_role($roles) {
    if (!is_logged_in()) {
        return false;
    }
    return in_array($_SESSION['user']['role'], $roles);
}

/**
 * Redirect to URL
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Set flash message
 */
function set_flash($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

/**
 * Get and clear flash message
 */
function get_flash($type) {
    if (isset($_SESSION['flash'][$type])) {
        $message = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $message;
    }
    return null;
}

/**
 * Format date in Spanish
 */
function format_date($date) {
    $timestamp = strtotime($date);
    return date('d/m/Y H:i', $timestamp);
}

/**
 * Require login
 */
function require_login() {
    if (!is_logged_in()) {
        redirect('/views/auth/login.php');
    }
}

/**
 * Require role
 */
function require_role($role) {
    require_login();
    if (!has_role($role)) {
        http_response_code(403);
        die('Acceso denegado. No tienes permisos para acceder a esta página.');
    }
}

/**
 * Require any of the roles
 */
function require_any_role($roles) {
    require_login();
    if (!has_any_role($roles)) {
        http_response_code(403);
        die('Acceso denegado. No tienes permisos para acceder a esta página.');
    }
}
