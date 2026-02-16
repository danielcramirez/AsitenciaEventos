<?php
declare(strict_types=1);

/**
 * Gestor de variables de entorno (.env)
 */
class EnvLoader {
    private static array $vars = [];
    private static bool $loaded = false;

    /**
     * Cargar archivo .env
     */
    public static function load(string $path = ''): void {
        if (self::$loaded) return;

        if ($path === '') {
            $path = __DIR__ . '/../.env';
        }

        if (!file_exists($path)) {
            // Usar valores por defecto si .env no existe
            self::setDefaults();
            self::$loaded = true;
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Ignorar comentarios
            if (str_starts_with(trim($line), '#')) {
                continue;
            }

            // Parsear KEY=VALUE
            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Remover comillas si existen
                if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                    (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                    $value = substr($value, 1, -1);
                }

                self::$vars[$key] = $value;
                // También establecer en $_ENV y putenv para compatibilidad
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }

        self::$loaded = true;
    }

    /**
     * Obtener variable de entorno
     */
    public static function get(string $key, string $default = ''): string {
        if (!self::$loaded) {
            self::load();
        }
        return self::$vars[$key] ?? $default;
    }

    /**
     * Obtener como booleano
     */
    public static function getBool(string $key, bool $default = false): bool {
        $value = self::get($key, '');
        if ($value === '') return $default;
        return in_array(strtolower($value), ['true', '1', 'yes', 'on'], true);
    }

    /**
     * Obtener como entero
     */
    public static function getInt(string $key, int $default = 0): int {
        $value = self::get($key, '');
        if ($value === '') return $default;
        return (int)$value;
    }

    /**
     * Verificar si existe variable
     */
    public static function has(string $key): bool {
        if (!self::$loaded) {
            self::load();
        }
        return isset(self::$vars[$key]);
    }

    /**
     * Valores por defecto si .env no existe
     */
    private static function setDefaults(): void {
        self::$vars = [
            'DB_HOST' => 'localhost',
            'DB_NAME' => 'eventos',
            'DB_USER' => 'root',
            'DB_PASS' => '',
            'DB_PORT' => '3306',
            'DB_CHARSET' => 'utf8mb4',
            'APP_ENV' => 'development',
            'APP_DEBUG' => 'true',
            'APP_URL' => 'http://localhost/eventos',
            'APP_NAME' => 'Sistema de Gestión de Eventos',
            'APP_VERSION' => '2.0',
            'LOGIN_MAX_ATTEMPTS' => '5',
            'LOGIN_LOCK_DURATION' => '1800',
            'RATE_LIMIT_QR_CHECKS' => '10',
            'RATE_LIMIT_QR_WINDOW' => '300',
            'SESSION_TIMEOUT' => '1800',
            'SESSION_SECURE' => 'false',
            'SESSION_HTTPONLY' => 'true',
            'CSRF_TOKEN_LENGTH' => '64',
            'LOG_LEVEL' => 'debug',
            'LOG_PATH' => './logs',
            'LOG_MAX_FILES' => '30',
            'QR_SIZE' => '320',
            'QR_MARGIN' => '10',
            'QR_ERROR_CORRECTION' => 'M',
            'DEFAULT_ROLE' => 'guest',
            'TIMEZONE' => 'America/Bogota',
        ];
    }
}

// Cargar variables de entorno automáticamente
EnvLoader::load();
