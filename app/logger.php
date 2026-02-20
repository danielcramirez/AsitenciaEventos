<?php
declare(strict_types=1);

require_once __DIR__ . '/env.php';

/**
 * Sistema de Logging Mejorado
 * Simula la interfaz de Monolog pero es ligero
 */
class Logger {
    // Niveles de log (compatible con syslog)
    const DEBUG = 100;
    const INFO = 200;
    const NOTICE = 250;
    const WARNING = 300;
    const ERROR = 400;
    const CRITICAL = 500;
    const EMERGENCY = 600;

    private static ?Logger $instance = null;
    private string $logDir;
    private int $minLevel;
    private array $levelNames = [
        100 => 'DEBUG',
        200 => 'INFO',
        250 => 'NOTICE',
        300 => 'WARNING',
        400 => 'ERROR',
        500 => 'CRITICAL',
        600 => 'EMERGENCY',
    ];

    private function __construct() {
        $this->logDir = EnvLoader::get('LOG_PATH', './logs');
        
        // Crear directorio si no existe
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }

        // Mapear nombre de nivel desde .env
        $levelName = strtoupper(EnvLoader::get('LOG_LEVEL', 'debug'));
        $levelMap = array_flip($this->levelNames);
        $this->minLevel = $levelMap[$levelName] ?? self::DEBUG;
    }

    /**
     * Obtener instancia singleton
     */
    public static function getInstance(): Logger {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Log genérico
     */
    public function log(int $level, string $message, array $context = []): void {
        if ($level < $this->minLevel) {
            return; // No loguear si está bajo el nivel mínimo
        }

        $levelName = $this->levelNames[$level] ?? 'UNKNOWN';
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        
        // Formatear contexto como JSON si existe
        $contextStr = '';
        if (!empty($context)) {
            $contextStr = ' | ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        }

        $logLine = sprintf(
            "[%s] %s - %s - %s %s\n",
            $timestamp,
            $levelName,
            $ip,
            $message,
            $contextStr
        );

        // Escribir a archivo de log del día
        $logFile = $this->logDir . '/' . date('Y-m-d') . '.log';
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);

        // Si es nivel ERROR o superior, también escribir al error.log
        if ($level >= self::ERROR) {
            $errorFile = $this->logDir . '/error.log';
            file_put_contents($errorFile, $logLine, FILE_APPEND | LOCK_EX);
        }

        // Limpiar logs antiguos
        $this->cleanOldLogs();
    }

    // ==== Métodos de conveniencia ====

    public function debug(string $message, array $context = []): void {
        $this->log(self::DEBUG, $message, $context);
    }

    public function info(string $message, array $context = []): void {
        $this->log(self::INFO, $message, $context);
    }

    public function notice(string $message, array $context = []): void {
        $this->log(self::NOTICE, $message, $context);
    }

    public function warning(string $message, array $context = []): void {
        $this->log(self::WARNING, $message, $context);
    }

    public function error(string $message, array $context = []): void {
        $this->log(self::ERROR, $message, $context);
    }

    public function critical(string $message, array $context = []): void {
        $this->log(self::CRITICAL, $message, $context);
    }

    public function emergency(string $message, array $context = []): void {
        $this->log(self::EMERGENCY, $message, $context);
    }

    /**
     * Limpiar logs más antiguos que el máximo especificado
     */
    private function cleanOldLogs(): void {
        $maxFiles = EnvLoader::getInt('LOG_MAX_FILES', 30);
        
        $files = glob($this->logDir . '/*.log');
        if (count($files) > $maxFiles) {
            // Ordenar por fecha de modificación
            usort($files, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });

            // Eliminar los más viejos
            $filesToDelete = array_slice($files, 0, count($files) - $maxFiles);
            foreach ($filesToDelete as $file) {
                unlink($file);
            }
        }
    }
}

/**
 * Función global para logging fácil
 */
function log_msg(string $level, string $message, array $context = []): void {
    static $logger = null;
    if ($logger === null) {
        $logger = Logger::getInstance();
    }

    $levelMap = [
        'debug' => Logger::DEBUG,
        'info' => Logger::INFO,
        'notice' => Logger::NOTICE,
        'warning' => Logger::WARNING,
        'error' => Logger::ERROR,
        'critical' => Logger::CRITICAL,
        'emergency' => Logger::EMERGENCY,
    ];

    $level = strtolower($level);
    $levelCode = $levelMap[$level] ?? Logger::INFO;
    $logger->log($levelCode, $message, $context);
}

// Crear logs iniciales de aplicación
if (EnvLoader::getBool('APP_DEBUG', false)) {
    Logger::getInstance()->debug('Logger inicializado', [
        'env' => EnvLoader::get('APP_ENV', 'development'),
        'timezone' => EnvLoader::get('TIMEZONE', 'America/Bogota'),
    ]);
}
