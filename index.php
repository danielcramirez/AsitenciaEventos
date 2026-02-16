<?php
/**
 * Main Entry Point
 */

require_once __DIR__ . '/config/helpers.php';
secure_session_start();

// Redirect to login if not authenticated
if (!is_logged_in()) {
    redirect('/views/auth/login.php');
}

// Redirect to dashboard
redirect('/views/dashboard.php');
