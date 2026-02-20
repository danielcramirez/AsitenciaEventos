<?php
declare(strict_types=1);

require_once __DIR__ . '/env.php';

// ============================================
// Base de Datos
// ============================================
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

const BASE_URL = '/eventos';
const APP_NAME = 'Sistema de Gestion de Eventos';
const APP_ENV = 'development';
const APP_DEBUG = true;

// ============================================
// Seguridad
// ============================================
const LOGIN_MAX_ATTEMPTS = 5;
const LOGIN_LOCK_DURATION = 900;
const LOGIN_BLOCK_MINUTES = 15;

const RATE_LIMIT_QR_CHECKS = 10;
const RATE_LIMIT_QR_WINDOW = 300;

const QR_RATE_LIMIT_MAX = RATE_LIMIT_QR_CHECKS;
const QR_RATE_LIMIT_WINDOW = RATE_LIMIT_QR_WINDOW;

const TOKEN_MIN_LEN = 20;

// ============================================
// Permisos y Roles
// ============================================
const ROLE_ADMIN = 'ADMIN';
const ROLE_OPERATOR = 'OPERATOR';
const ROLE_ENLACE = 'ENLACE';
const ROLE_ELECTOR = 'ELECTOR';
const ROLE_ATTENDEE = 'ATTENDEE';
const ROLE_GUEST = 'GUEST';
const DEFAULT_ROLE = ROLE_ELECTOR;

// Permisos por rol
const ROLE_PERMISSIONS = [
    'ADMIN' => [
        'admin_eventos' => true,      // CRUD de eventos
        'reporte' => true,             // Ver reportes
        'export_csv' => true,          // Exportar CSV
        'puerta' => true,              // Check-in en puerta
        'consulta_qr' => true,         // Ver QR ajenos
    ],
    'OPERATOR' => [
        'puerta' => true,              // Check-in en puerta
        'reporte' => true,             // Ver reportes propios
        'consulta_qr' => false,        // No puede ver QR ajenos
    ],
    'ENLACE' => [
        'registrar' => true,
        'consulta_qr' => true,
        'evento' => true,
        'mis_referidos' => true,
    ],
    'ELECTOR' => [
        'registrar' => true,
        'consulta_qr' => true,
        'evento' => true,
        'mis_referidos' => false,
    ],
    'ATTENDEE' => [
        'registrar' => true,           // Registrarse
        'consulta_qr' => true,         // Ver su QR
        'evento' => true,              // Ver evento
    ],
    'GUEST' => [
        'registrar' => false,
        'consulta_qr' => false,
        'evento' => false,
        'mis_referidos' => false,
    ],
];

// ============================================
// Timezone (desde .env)
// ============================================
date_default_timezone_set(EnvLoader::get('TIMEZONE', 'America/Bogota'));
