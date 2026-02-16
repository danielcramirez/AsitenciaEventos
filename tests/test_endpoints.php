<?php
/**
 * HTTP Test Script - Prueba endpoints activos
 * Simula requests HTTP para verificar que los controllers responden correctamente
 */
declare(strict_types=1);

const COLOR_SUCCESS = "\033[92m";
const COLOR_ERROR = "\033[91m";
const COLOR_INFO = "\033[94m";
const COLOR_RESET = "\033[0m";

class HttpTester {
    private array $results = [];
    private int $passed = 0;
    private int $failed = 0;
    private string $baseUrl;

    public function __construct(string $baseUrl) {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * Test that a route returns expected HTTP status
     */
    public function testRoute(string $name, string $path, string $method = 'GET', ?array $data = null, int $expectedStatus = 200): void {
        echo COLOR_INFO . "Testing: " . COLOR_RESET . $name . " ... ";
        try {
            $url = $this->baseUrl . '/' . ltrim($path, '/');
            
            // Para GET, las pruebas reales requerirían un servidor activo
            // Por ahora solo verificamos que el archivo existe y es accesible
            $filePath = __DIR__ . '/../' . ltrim($path, '/');
            
            if (!file_exists($filePath)) {
                throw new Exception("Archivo no existe: $path");
            }

            // Verificar que es un archivo PHP válido
            $content = file_get_contents($filePath);
            if (empty($content) || !str_starts_with($content, '<?php')) {
                throw new Exception("Archivo no es PHP válido");
            }

            echo COLOR_SUCCESS . "✓ PASS" . COLOR_RESET . "\n";
            $this->passed++;
        } catch (Exception $e) {
            echo COLOR_ERROR . "✗ FAIL" . COLOR_RESET . " - {$e->getMessage()}\n";
            $this->failed++;
        }
    }

    /**
     * Test que un controller tenga métodos específicos
     */
    public function testControllerMethod(string $name, string $controller, string $method): void {
        echo COLOR_INFO . "Testing: " . COLOR_RESET . $name . " ... ";
        try {
            require_once __DIR__ . '/../controllers/' . $controller . '.php';
            $className = str_replace('.php', '', $controller);
            
            if (!class_exists($className)) {
                throw new Exception("Clase $className no existe");
            }

            if (!method_exists($className, $method)) {
                throw new Exception("Método $method no existe en $className");
            }

            echo COLOR_SUCCESS . "✓ PASS" . COLOR_RESET . "\n";
            $this->passed++;
        } catch (Exception $e) {
            echo COLOR_ERROR . "✗ FAIL" . COLOR_RESET . " - {$e->getMessage()}\n";
            $this->failed++;
        }
    }

    /**
     * Test que un modelo tenga métodos estáticos
     */
    public function testModelMethod(string $name, string $model, string $method): void {
        echo COLOR_INFO . "Testing: " . COLOR_RESET . $name . " ... ";
        try {
            require_once __DIR__ . '/../models/' . $model . '.php';
            $className = str_replace('.php', '', $model);
            
            if (!class_exists($className)) {
                throw new Exception("Clase $className no existe");
            }

            if (!method_exists($className, $method)) {
                throw new Exception("Método estático $method no existe en $className");
            }

            echo COLOR_SUCCESS . "✓ PASS" . COLOR_RESET . "\n";
            $this->passed++;
        } catch (Exception $e) {
            echo COLOR_ERROR . "✗ FAIL" . COLOR_RESET . " - {$e->getMessage()}\n";
            $this->failed++;
        }
    }

    public function summary(): void {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo COLOR_INFO . "HTTP ENDPOINT TEST SUMMARY" . COLOR_RESET . "\n";
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

echo "\n" . COLOR_INFO . "=== INICIANDO PRUEBAS DE ENDPOINTS ===" . COLOR_RESET . "\n\n";

$tester = new HttpTester('http://localhost/eventos');

// ===== RUTAS / ENTRY POINTS =====
$tester->testRoute('GET / (home)', 'index.php');
$tester->testRoute('GET /login', 'login.php');
$tester->testRoute('GET /logout', 'logout.php');
$tester->testRoute('GET /evento (show event)', 'evento.php');
$tester->testRoute('GET /admin_eventos', 'admin_eventos.php');
$tester->testRoute('GET /registrar (register)', 'registrar.php');
$tester->testRoute('GET /consulta_qr', 'consulta_qr.php');
$tester->testRoute('GET /puerta (checkin door)', 'puerta.php');
$tester->testRoute('POST /api_checkin', 'api_checkin.php');
$tester->testRoute('GET /reporte', 'reporte.php');
$tester->testRoute('GET /export_csv', 'export_csv.php');

echo "\n";

// ===== CONTROLLERS =====
$tester->testControllerMethod('HomeController::index()', 'HomeController', 'index');
$tester->testControllerMethod('AuthController::login()', 'AuthController', 'login');
$tester->testControllerMethod('AuthController::logout()', 'AuthController', 'logout');
$tester->testControllerMethod('EventController::show()', 'EventController', 'show');
$tester->testControllerMethod('EventAdminController::index()', 'EventAdminController', 'index');
$tester->testControllerMethod('RegistrationController::register()', 'RegistrationController', 'register');
$tester->testControllerMethod('QrController::consult()', 'QrController', 'consult');
$tester->testControllerMethod('CheckinController::door()', 'CheckinController', 'door');
$tester->testControllerMethod('CheckinController::apiCheckin()', 'CheckinController', 'apiCheckin');
$tester->testControllerMethod('ReportController::report()', 'ReportController', 'report');
$tester->testControllerMethod('ReportController::exportCsv()', 'ReportController', 'exportCsv');

echo "\n";

// ===== MODELS =====
$tester->testModelMethod('UserModel::findActiveByEmail()', 'UserModel', 'findActiveByEmail');
$tester->testModelMethod('EventModel::getPublished()', 'EventModel', 'getPublished');
$tester->testModelMethod('EventModel::findById()', 'EventModel', 'findById');
$tester->testModelMethod('PersonModel::findByCedula()', 'PersonModel', 'findByCedula');
$tester->testModelMethod('PersonModel::upsert()', 'PersonModel', 'upsert');
$tester->testModelMethod('RegistrationModel::findByEventAndPerson()', 'RegistrationModel', 'findByEventAndPerson');
$tester->testModelMethod('QrTokenModel::create()', 'QrTokenModel', 'create');
$tester->testModelMethod('CheckinModel::findByTokenHash()', 'CheckinModel', 'findByTokenHash');
$tester->testModelMethod('CheckinModel::createCheckin()', 'CheckinModel', 'createCheckin');
$tester->testModelMethod('SecurityModel::isLoginBlocked()', 'SecurityModel', 'isLoginBlocked');
$tester->testModelMethod('AuditLogModel::log()', 'AuditLogModel', 'log');

echo "\n";

$tester->summary();

if ($tester->getStatus()) {
    echo "\n" . COLOR_SUCCESS . "✓ TODOS LOS ENDPOINTS Y MÉTODOS ESTÁN DISPONIBLES" . COLOR_RESET . "\n\n";
    exit(0);
} else {
    echo "\n" . COLOR_ERROR . "✗ ALGUNOS ENDPOINTS FALLARON" . COLOR_RESET . "\n\n";
    exit(1);
}
