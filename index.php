<?php
/**
 * Central Router - Punto de entrada único de la aplicación
 * Todas las requests pasan por aquí para validación y routing
 */
declare(strict_types=1);

// Iniciar sesión antes de cualquier header
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/core/bootstrap.php';

/**
 * Parsear la request URL
 */
function parseRequest(): array {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $basePath = '/eventos';
    
    // Remover la ruta base de la aplicación
    if (str_starts_with($path, $basePath)) {
        $path = substr($path, strlen($basePath));
    }
    
    // Limpiar slashes
    $path = trim($path, '/');
    
    // Si está vacía, es home
    if ($path === '' || $path === 'index.php') {
        return ['controller' => 'home', 'action' => 'index'];
    }
    
    // Parsear path/action?params
    $segments = explode('/', $path);
    $route = array_shift($segments);
    
    // Mapeo de rutas a controllers y acciones
    $routes = [
        'home' => ['controller' => 'home', 'action' => 'index'],
        'login' => ['controller' => 'auth', 'action' => 'login'],
        'logout' => ['controller' => 'auth', 'action' => 'logout'],
        'evento' => ['controller' => 'event', 'action' => 'show'],
        'admin_eventos' => ['controller' => 'event_admin', 'action' => 'index'],
        'registrar' => ['controller' => 'registration', 'action' => 'register'],
        'consulta_qr' => ['controller' => 'qr', 'action' => 'consult'],
        'puerta' => ['controller' => 'checkin', 'action' => 'door'],
        'api_checkin' => ['controller' => 'checkin', 'action' => 'apiCheckin'],
        'reporte' => ['controller' => 'report', 'action' => 'report'],
        'export_csv' => ['controller' => 'report', 'action' => 'exportCsv'],
    ];
    
    if (isset($routes[$route])) {
        return $routes[$route];
    }
    
    // Ruta no encontrada
    http_response_code(404);
    exit('Ruta no encontrada: ' . h($route));
}

/**
 * Ejecutar controller con validación
 */
function executeRoute(array $route): void {
    $controller = ucfirst($route['controller']) . 'Controller';
    $action = $route['action'];
    
    $controllerFile = __DIR__ . '/controllers/' . $controller . '.php';
    
    if (!file_exists($controllerFile)) {
        http_response_code(500);
        exit("Controller no encontrado: $controller");
    }
    
    require_once $controllerFile;
    
    if (!class_exists($controller)) {
        http_response_code(500);
        exit("Clase no encontrada: $controller");
    }
    
    if (!method_exists($controller, $action)) {
        http_response_code(500);
        exit("Acción no encontrada: $action en $controller");
    }
    
    // Ejecutar el controller
    call_user_func([$controller, $action]);
}

// Parsear y ejecutar
try {
    $route = parseRequest();
    executeRoute($route);
} catch (Exception $e) {
    http_response_code(500);
    echo "Error: " . h($e->getMessage());
    exit;
}
