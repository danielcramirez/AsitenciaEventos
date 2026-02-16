<?php
declare(strict_types=1);

require_once __DIR__ . '/env.php';

// ============================================
// Base de Datos (desde .env)
// ============================================
const DB_HOST = null;     // Usar EnvLoader::get('DB_HOST')
const DB_NAME = null;     // Usar EnvLoader::get('DB_NAME')
const DB_USER = null;     // Usar EnvLoader::get('DB_USER')
const DB_PASS = null;     // Usar EnvLoader::get('DB_PASS')
const DB_CHARSET = 'utf8mb4';

// Valores desde .env con fallback
function getDbConfig(string $key, string $default = ''): string {
    $map = [
        'host' => 'DB_HOST',
        'name' => 'DB_NAME',
        'user' => 'DB_USER',
        'pass' => 'DB_PASS',
    ];
    return EnvLoader::get($map[$key] ?? $key, $default);
}

// ============================================
// Aplicación (desde .env)
// ============================================
const APP_NAME = null;    // Usar EnvLoader::get('APP_NAME')
const APP_ENV = null;     // Usar EnvLoader::get('APP_ENV')
const APP_DEBUG = null;   // Usar EnvLoader::getBool('APP_DEBUG')
const BASE_URL = '/eventos';

// ============================================
// Seguridad (desde .env)
// ============================================
const LOGIN_MAX_ATTEMPTS = null;     // Usar EnvLoader::getInt('LOGIN_MAX_ATTEMPTS')
const LOGIN_LOCK_DURATION = null;    // Usar EnvLoader::getInt('LOGIN_LOCK_DURATION')
const LOGIN_BLOCK_MINUTES = 15;      // Fallback si no está en .env

const RATE_LIMIT_QR_CHECKS = null;   // Usar EnvLoader::getInt('RATE_LIMIT_QR_CHECKS')
const RATE_LIMIT_QR_WINDOW = null;   // Usar EnvLoader::getInt('RATE_LIMIT_QR_WINDOW')

const QR_RATE_LIMIT_MAX = 5;         // Fallback deprecated
const QR_RATE_LIMIT_WINDOW = 60;

const TOKEN_MIN_LEN = 20;

// ============================================
// Permisos y Roles
// ============================================
const DEFAULT_ROLE = 'guest';
const ROLE_ADMIN = 'admin';
const ROLE_OPERATOR = 'operator';
const ROLE_GUEST = 'guest';

// Permisos por rol
const ROLE_PERMISSIONS = [
    'admin' => [
        'admin_eventos' => true,      // CRUD de eventos
        'reporte' => true,             // Ver reportes
        'export_csv' => true,          // Exportar CSV
        'puerta' => true,              // Check-in en puerta
        'consulta_qr' => true,         // Ver QR ajenos
    ],
    'operator' => [
        'puerta' => true,              // Check-in en puerta
        'reporte' => true,             // Ver reportes propios
        'consulta_qr' => false,        // No puede ver QR ajenos
    ],
    'guest' => [
        'registrar' => true,           // Registrarse
        'consulta_qr' => true,         // Ver su QR
        'evento' => true,              // Ver evento
    ],
];

// ============================================
// Timezone (desde .env)
// ============================================
date_default_timezone_set(EnvLoader::get('TIMEZONE', 'America/Bogota'));
