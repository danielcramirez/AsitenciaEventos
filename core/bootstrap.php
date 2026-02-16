<?php
declare(strict_types=1);

// Cargar configuración y variables de entorno
require_once __DIR__ . '/../app/env.php';
require_once __DIR__ . '/../app/config.php';

// Cargar base de datos y helpers
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';

// Cargar logging y autenticación
require_once __DIR__ . '/../app/logger.php';
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/permissions.php';

// Cargar renderizador de vistas
require_once __DIR__ . '/view.php';
