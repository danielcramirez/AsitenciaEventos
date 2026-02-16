<?php
// Application configuration
define('APP_NAME', 'Sistema de Asistencia a Eventos');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/AsitenciaEventos');

// Security settings
define('SESSION_LIFETIME', 7200); // 2 hours
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes
define('RATE_LIMIT_QR_QUERIES', 10); // per minute
define('RATE_LIMIT_WINDOW', 60); // seconds

// QR Token settings
define('QR_TOKEN_LENGTH', 32);
define('QR_TOKEN_EXPIRY', 86400); // 24 hours

// File upload settings
define('MAX_UPLOAD_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);

// Timezone
date_default_timezone_set('America/Bogota');

// Error reporting (change in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php-errors.log');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS
ini_set('session.cookie_samesite', 'Strict');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    
    // Regenerate session ID periodically for security
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}
