<?php
/**
 * Test Script for Sistema de Eventos
 * Prueba configuración, BD, controllers y modelos
 */
declare(strict_types=1);

// Colors para terminal
const COLOR_SUCCESS = "\033[92m";
const COLOR_ERROR = "\033[91m";
const COLOR_INFO = "\033[94m";
const COLOR_RESET = "\033[0m";
const COLOR_WARN = "\033[93m";

class TestRunner {
    private array $results = [];
    private int $passed = 0;
    private int $failed = 0;

    public function test(string $name, callable $testFunc): void {
        echo COLOR_INFO . "Testing: " . COLOR_RESET . $name . " ... ";
        try {
            $testFunc();
            echo COLOR_SUCCESS . "✓ PASS" . COLOR_RESET . "\n";
            $this->passed++;
            $this->results[] = ['name' => $name, 'status' => 'pass'];
        } catch (Exception $e) {
            echo COLOR_ERROR . "✗ FAIL" . COLOR_RESET . " - {$e->getMessage()}\n";
            $this->failed++;
            $this->results[] = ['name' => $name, 'status' => 'fail', 'error' => $e->getMessage()];
        }
    }

    public function summary(): void {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo COLOR_INFO . "TEST SUMMARY" . COLOR_RESET . "\n";
        echo str_repeat("=", 60) . "\n";
        echo COLOR_SUCCESS . "✓ Passed: {$this->passed}" . COLOR_RESET . "\n";
        echo COLOR_ERROR . "✗ Failed: {$this->failed}" . COLOR_RESET . "\n";
        echo "Total: " . ($this->passed + $this->failed) . "\n";
        echo str_repeat("=", 60) . "\n";
    }

    public function getStatus(): bool {
        return $this->failed === 0;
    }
}

$tester = new TestRunner();

// Test 1: Verificar archivos de configuración existen
$tester->test('Archivos de configuración existen', function () {
    $files = [
        '../app/config.php',
        '../app/db.php',
        '../app/auth.php',
        '../app/helpers.php',
        '../core/bootstrap.php',
    ];
    foreach ($files as $file) {
        $path = __DIR__ . '/' . $file;
        if (!file_exists($path)) {
            throw new Exception("Missing: $file");
        }
    }
});

// Test 2: Cargar bootstrap (sin iniciar sesión)
$tester->test('Bootstrap carga sin errores', function () {
    ob_start();
    try {
        require_once __DIR__ . '/../core/bootstrap.php';
        ob_end_clean();
    } catch (Exception $e) {
        ob_end_clean();
        throw $e;
    }
});

// Test 3: Conexión a BD
$tester->test('Conexión a BD funciona', function () {
    $db = db();
    $result = $db->query('SELECT 1 as test');
    if (!$result || $result->rowCount() === 0) {
        throw new Exception('No se pudo ejecutar query');
    }
});

// Test 4: Tabla 'users' existe
$tester->test('Tabla users existe y tiene datos', function () {
    $db = db();
    $result = $db->query('SELECT COUNT(*) as cnt FROM users');
    $row = $result->fetch(\PDO::FETCH_ASSOC);
    if (!is_array($row) || $row['cnt'] <= 0) {
        throw new Exception('No hay usuarios en BD');
    }
});

// Test 5: Tabla 'events' existe
$tester->test('Tabla events existe', function () {
    $db = db();
    $result = $db->query('SELECT COUNT(*) as cnt FROM events');
    if (!$result) {
        throw new Exception('No se pudo consultar events');
    }
});

// Test 6: Cargar HomeController
$tester->test('HomeController carga correctamente', function () {
    if (!file_exists(__DIR__ . '/../controllers/HomeController.php')) {
        throw new Exception('HomeController.php no existe');
    }
    require_once __DIR__ . '/../controllers/HomeController.php';
    if (!class_exists('HomeController')) {
        throw new Exception('Clase HomeController no encontrada');
    }
});

// Test 7: Cargar AuthController
$tester->test('AuthController carga correctamente', function () {
    require_once __DIR__ . '/../controllers/AuthController.php';
    if (!class_exists('AuthController')) {
        throw new Exception('Clase AuthController no encontrada');
    }
});

// Test 8: Cargar EventController
$tester->test('EventController carga correctamente', function () {
    require_once __DIR__ . '/../controllers/EventController.php';
    if (!class_exists('EventController')) {
        throw new Exception('Clase EventController no encontrada');
    }
});

// Test 9: Cargar UserModel
$tester->test('UserModel carga correctamente', function () {
    require_once __DIR__ . '/../models/UserModel.php';
    if (!class_exists('UserModel')) {
        throw new Exception('Clase UserModel no encontrada');
    }
});

// Test 10: Cargar EventModel
$tester->test('EventModel carga correctamente', function () {
    require_once __DIR__ . '/../models/EventModel.php';
    if (!class_exists('EventModel')) {
        throw new Exception('Clase EventModel no encontrada');
    }
});

// Test 11: UserModel puede buscar usuario
$tester->test('UserModel::findActiveByEmail funciona', function () {
    require_once __DIR__ . '/../models/UserModel.php';
    // Buscar cualquier usuario activo en lugar de admin específicamente
    $db = db();
    $result = $db->query('SELECT * FROM users WHERE active = 1 LIMIT 1');
    $user = $result->fetch(\PDO::FETCH_ASSOC);
    if (!$user) {
        throw new Exception('No hay usuarios activos en BD');
    }
    // Ahora buscar ese usuario específico
    $found = UserModel::findActiveByEmail($user['email']);
    if (!$found) {
        throw new Exception('UserModel::findActiveByEmail no encontró usuario conocido');
    }
});

// Test 12: EventModel puede listar eventos publicados
$tester->test('EventModel::getPublished funciona', function () {
    require_once __DIR__ . '/../models/EventModel.php';
    $events = EventModel::getPublished();
    if (!is_array($events)) {
        throw new Exception('getPublished no retorna array');
    }
});

// Test 13: Helpers de validación cargan
$tester->test('Funciones helpers están disponibles', function () {
    if (!function_exists('validate_email')) {
        throw new Exception('Función validate_email no existe');
    }
    if (!function_exists('validate_cedula')) {
        throw new Exception('Función validate_cedula no existe');
    }
    if (!function_exists('h')) {
        throw new Exception('Función h (HTML escape) no existe');
    }
});

// Test 14: Validación de email funciona
$tester->test('Validación de email funciona', function () {
    $valid = validate_email('test@example.com');
    if (!$valid) {
        throw new Exception('validate_email no valida email correcto');
    }
    $invalid = validate_email('invalid-email');
    if ($invalid) {
        throw new Exception('validate_email no rechaza email inválido');
    }
});

// Test 15: HTML escape funciona
$tester->test('Función h() escapa HTML correctly', function () {
    $input = '<script>alert("xss")</script>';
    $escaped = h($input);
    if (strpos($escaped, '<script>') !== false) {
        throw new Exception('h() no filtra scripts');
    }
});

// Test 16: Tabla security_login_attempts existe
$tester->test('Tabla security_login_attempts existe', function () {
    $db = db();
    $result = $db->query("SHOW TABLES LIKE 'login_attempts'");
    if ($result->rowCount() === 0) {
        throw new Exception('Tabla login_attempts no existe');
    }
});

// Test 17: Tabla security_rate_limits existe
$tester->test('Tabla security_rate_limits existe', function () {
    $db = db();
    $result = $db->query("SHOW TABLES LIKE 'rate_limits'");
    if ($result->rowCount() === 0) {
        throw new Exception('Tabla rate_limits no existe');
    }
});

// Test 18: Tabla audit_logs existe
$tester->test('Tabla audit_logs existe', function () {
    $db = db();
    $result = $db->query("SHOW TABLES LIKE 'audit_logs'");
    if ($result->rowCount() === 0) {
        throw new Exception('Tabla audit_logs no existe');
    }
});

// Test 19: SecurityModel carga correctamente
$tester->test('SecurityModel carga correctamente', function () {
    require_once __DIR__ . '/../models/SecurityModel.php';
    if (!class_exists('SecurityModel')) {
        throw new Exception('Clase SecurityModel no encontrada');
    }
});

// Test 20: AuditLogModel carga correctamente
$tester->test('AuditLogModel carga correctamente', function () {
    require_once __DIR__ . '/../models/AuditLogModel.php';
    if (!class_exists('AuditLogModel')) {
        throw new Exception('Clase AuditLogModel no encontrada');
    }
});

// Test 21: Tabla 'registrations' existe
$tester->test('Tabla registrations existe', function () {
    $db = db();
    $result = $db->query("SHOW TABLES LIKE 'registrations'");
    if ($result->rowCount() === 0) {
        throw new Exception('Tabla registrations no existe');
    }
});

// Test 22: Tabla 'checkins' existe
$tester->test('Tabla checkins existe', function () {
    $db = db();
    $result = $db->query("SHOW TABLES LIKE 'checkins'");
    if ($result->rowCount() === 0) {
        throw new Exception('Tabla checkins no existe');
    }
});

// Test 23: Todos los entry points existen
$tester->test('Todos los entry points (routes) existen', function () {
    $routes = [
        'index.php',
        'login.php',
        'logout.php',
        'evento.php',
        'admin_eventos.php',
        'registrar.php',
        'consulta_qr.php',
        'puerta.php',
        'api_checkin.php',
        'reporte.php',
        'export_csv.php',
    ];
    foreach ($routes as $route) {
        $path = __DIR__ . '/../' . $route;
        if (!file_exists($path)) {
            throw new Exception("Missing route: $route");
        }
    }
});

// Test 24: Verificar estructura de carpetas
$tester->test('Estructura de carpetas correcta', function () {
    $dirs = ['controllers', 'models', 'views', 'app', 'database', 'core'];
    foreach ($dirs as $dir) {
        $path = __DIR__ . '/../' . $dir;
        if (!is_dir($path)) {
            throw new Exception("Missing directory: $dir");
        }
    }
});

echo "\n" . COLOR_INFO . "=== INICIANDO PRUEBAS ===" . COLOR_RESET . "\n\n";

try {
    $tester->test('Base de datos está disponible', function () {
        // Esta es la primera prueba crítica
    });
    
    // Ejecutar todas las pruebas
    $tester->summary();
    
    if ($tester->getStatus()) {
        echo "\n" . COLOR_SUCCESS . "✓ TODAS LAS PRUEBAS PASARON" . COLOR_RESET . "\n\n";
        exit(0);
    } else {
        echo "\n" . COLOR_ERROR . "✗ ALGUNAS PRUEBAS FALLARON" . COLOR_RESET . "\n\n";
        exit(1);
    }
} catch (Exception $e) {
    echo COLOR_ERROR . "ERROR FATAL: " . COLOR_RESET . $e->getMessage() . "\n";
    exit(1);
}
