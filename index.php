<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Autoload controllers
spl_autoload_register(function ($class) {
    $controllerPath = __DIR__ . '/controllers/' . $class . '.php';
    if (file_exists($controllerPath)) {
        require_once $controllerPath;
    }
});

// Get action from query string
$action = $_GET['action'] ?? 'login';

try {
    // Route to appropriate controller and method
    switch ($action) {
        // Auth routes
        case 'login':
            $controller = new AuthController();
            $controller->showLogin();
            break;
            
        case 'login_process':
            $controller = new AuthController();
            $controller->login();
            break;
            
        case 'logout':
            $controller = new AuthController();
            $controller->logout();
            break;
            
        case 'dashboard':
            $controller = new AuthController();
            $controller->dashboard();
            break;
            
        // Event routes
        case 'events':
            $controller = new EventController();
            $controller->index();
            break;
            
        case 'event_create':
            $controller = new EventController();
            $controller->create();
            break;
            
        case 'event_view':
            $controller = new EventController();
            $controller->view();
            break;
            
        case 'event_edit':
            $controller = new EventController();
            $controller->edit();
            break;
            
        // Attendee routes
        case 'attendee_register':
            $controller = new AttendeeController();
            $controller->register();
            break;
            
        case 'attendee_list':
            $controller = new AttendeeController();
            $controller->list();
            break;
            
        case 'qr_query':
            $controller = new AttendeeController();
            $controller->queryByCedula();
            break;
            
        case 'qr_view':
            $controller = new AttendeeController();
            $controller->showQR();
            break;
            
        case 'qr_regenerate':
            $controller = new AttendeeController();
            $controller->regenerateQR();
            break;
            
        // Check-in routes
        case 'checkin_door':
            $controller = new CheckInController();
            $controller->doorScreen();
            break;
            
        case 'checkin_validate':
            $controller = new CheckInController();
            $controller->validate();
            break;
            
        case 'checkin_recent':
            $controller = new CheckInController();
            $controller->recentCheckIns();
            break;
            
        case 'checkin_export':
            $controller = new CheckInController();
            $controller->exportCSV();
            break;
            
        case 'checkin_stats':
            $controller = new CheckInController();
            $controller->stats();
            break;
            
        default:
            // Default to login page
            $controller = new AuthController();
            $controller->showLogin();
            break;
    }
    
} catch (Exception $e) {
    error_log("Application error: " . $e->getMessage());
    http_response_code(500);
    echo "An error occurred. Please try again later.";
}
