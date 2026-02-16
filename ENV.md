# Configuración de Entorno - Guía de Setup

## 📋 Descripción General

El sistema utiliza variables de entorno (`.env`) para gestionar configuraciones que varían según el ambiente (desarrollo, testing, producción).

## 🔧 Instalación Inicial

### Paso 1: Copiar Archivo de Ejemplo

```bash
cd /path/to/eventos
cp .env.example .env
```

### Paso 2: Editar Configuración Local

Edita `.env` con los valores de tu ambiente local:

```env
# Base de Datos
DB_HOST=localhost
DB_NAME=eventos
DB_USER=root
DB_PASS=tu_password

# Aplicación
APP_ENV=development
APP_DEBUG=true

# Seguridad
LOGIN_MAX_ATTEMPTS=5
SESSION_TIMEOUT=1800
```

### Paso 3: Verificar Permisos

```bash
# El archivo .env debe tener permisos 600 (solo lectura para owner)
chmod 600 .env
```

## 📝 Variables Disponibles

### Base de Datos

| Variable | Descripción | Ejemplo | Requerido |
|----------|------------|---------|-----------|
| DB_HOST | Host del servidor MySQL | localhost | ✓ |
| DB_NAME | Nombre de la base de datos | eventos | ✓ |
| DB_USER | Usuario MySQL | root | ✓ |
| DB_PASS | Contraseña MySQL | secret123 | ✗ |
| DB_PORT | Puerto MySQL | 3306 | ✗ |
| DB_CHARSET | Charset de conexión | utf8mb4 | ✗ |

### Aplicación

| Variable | Descripción | Ejemplo | Default |
|----------|------------|---------|---------|
| APP_ENV | Ambiente (development, production) | production | development |
| APP_DEBUG | Mostrar errores detallados | false | true |
| APP_URL | URL base de la aplicación | https://eventos.com | http://localhost/eventos |
| APP_NAME | Nombre de la aplicación | Sistema de Eventos | Sistema de Gestión de Eventos |
| APP_VERSION | Versión actual | 2.0 | 2.0 |

### Seguridad

| Variable | Descripción | Valor | Default |
|----------|------------|-------|---------|
| LOGIN_MAX_ATTEMPTS | Máximos intentos fallidos | 5 | 5 |
| LOGIN_LOCK_DURATION | Segundos de bloqueo | 1800 | 1800 |
| RATE_LIMIT_QR_CHECKS | Máximas consultas QR por ventana | 10 | 10 |
| RATE_LIMIT_QR_WINDOW | Ventana en segundos | 300 | 300 |
| SESSION_TIMEOUT | Timeout de sesión en segundos | 1800 | 1800 |
| SESSION_SECURE | HTTPS only (production) | true | false |
| SESSION_HTTPONLY | Cookie HttpOnly | true | true |
| CSRF_TOKEN_LENGTH | Longitud de token CSRF | 64 | 64 |

### Logging

| Variable | Descripción | Ejemplo | Default |
|----------|------------|---------|---------|
| LOG_LEVEL | Nivel mínimo de log | debug | debug |
| LOG_PATH | Directorio para logs | ./logs | ./logs |
| LOG_MAX_FILES | Máximos archivos de log | 30 | 30 |

### QR Code

| Variable | Descripción | Valor | Default |
|----------|------------|-------|---------|
| QR_SIZE | Tamaño del QR en pixels | 320 | 320 |
| QR_MARGIN | Margen del QR | 10 | 10 |
| QR_ERROR_CORRECTION | Nivel de corrección (L, M, Q, H) | M | M |

### Otros

| Variable | Descripción | Ejemplo | Default |
|----------|------------|---------|---------|
| TIMEZONE | Zona horaria | America/Bogota | America/Bogota |
| DEFAULT_ROLE | Rol por defecto | guest | guest |
| MAIL_FROM | Email remitente | eventos@example.com | eventos@local |

## 🚀 Configuración por Ambiente

### Desarrollo (Development)

```env
APP_ENV=development
APP_DEBUG=true
LOG_LEVEL=debug
DB_HOST=localhost
DB_USER=root
DB_PASS=
SESSION_SECURE=false
```

**Características:**
- Mensajes de error detallados
- Logs en nivel DEBUG
- Sin HTTPS requerido
- Base de datos local

### Producción (Production)

```env
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=warning
DB_HOST=prod-mysql.example.com
DB_USER=eventos_user
DB_PASS=contraseña_fuerte_aqui
SESSION_SECURE=true
SESSION_HTTPONLY=true
```

**Características:**
- Errores genéricos (no revelar detalles)
- Logs solo WARNING y mayores
- HTTPS requerido
- Base de datos remota
- Credenciales fuertes

### Testing/CI

```env
APP_ENV=testing
APP_DEBUG=true
LOG_LEVEL=info
DB_HOST=localhost
DB_NAME=eventos_test
DB_USER=test_user
DB_PASS=test_password
```

**Características:**
- BD separada para testing
- Logs moderados
- Usuario específico para tests

## 💻 Acceso a Variables en Código

### Función EnvLoader

```php
// String
$host = EnvLoader::get('DB_HOST', 'localhost');
$name = EnvLoader::get('DB_NAME', 'eventos');

// Booleano
$debug = EnvLoader::getBool('APP_DEBUG', false);
$secure = EnvLoader::getBool('SESSION_SECURE', false);

// Entero
$timeout = EnvLoader::getInt('SESSION_TIMEOUT', 1800);
$maxAttempts = EnvLoader::getInt('LOGIN_MAX_ATTEMPTS', 5);

// Verificar existencia
if (EnvLoader::has('DB_PASS')) {
    // Variable definida
}
```

### Ejemplo en Controllers

```php
class EventAdminController {
    public static function index() {
        $debug = EnvLoader::getBool('APP_DEBUG');
        
        if ($debug) {
            Logger::getInstance()->debug('Debugging enabled');
        }
        
        // ...resto del controller
    }
}
```

### Ejemplo en Config

```php
// app/config.php
date_default_timezone_set(EnvLoader::get('TIMEZONE', 'America/Bogota'));

const LOGIN_MAX_ATTEMPTS = null;  // Del .env
const LOGIN_LOCK_DURATION = null; // Del .env

// Usar:
function getLoginMaxAttempts(): int {
    return EnvLoader::getInt('LOGIN_MAX_ATTEMPTS', 5);
}
```

## 🔒 Seguridad de .env

### NUNCA hacer esto:

```bash
# ❌ NO: Commitir .env con credenciales reales
git add .env
git commit -m "Add env config"
git push

# ❌ NO: Colocar contraseña en texto plano
DB_PASS=MiContraseñaDelDatos123
```

### SIEMPRE hacer esto:

```bash
# ✓ SÍ: .env está en .gitignore
cat .gitignore
# Deberá contener:
# .env
# .env.local

# ✓ SÍ: Usar .env.example para documentar variables
cp .env.example .env

# ✓ SÍ: Permisos restrictivos
chmod 600 .env

# ✓ SÍ: Credenciales fuertes en producción
DB_PASS=aB7$2kL9@mP4#xQ8&rT1%vW5

# ✓ SÍ: Mantener .env.example en git (sin valores sensibles)
git add .env.example
```

## 📦 Cargar Variables de Entorno

El archivo `.env` se carga automáticamente cuando se carga `app/config.php`:

```php
// En core/bootstrap.php
require_once __DIR__ . '/../app/env.php';    // ← Carga .env
require_once __DIR__ . '/../app/config.php'; // ← Usa variables
```

### Orden de Carga

```
1. app/env.php
   └─ Busca archivo .env
   └─ Lee y parsea líneas
   └─ Establece variables en $_ENV
   
2. app/config.php
   └─ Define constantes
   └─ Usa EnvLoader para valores variables
   
3. app/db.php
   └─ Conecta a BD usando variables
   
4. app/logger.php
   └─ Configura logging según env
   
5. core/bootstrap.php
   └─ Orquesta toda la carga
```

## 🐛 Troubleshooting

### Error: "Undefined variable DB_HOST"

**Causa:** Archivo .env no existe o no se cargó

**Solución:**
```bash
# 1. Verificar que .env existe
ls -la .env

# 2. Si no existe, copiarlo
cp .env.example .env

# 3. Editar con valores reales
vim .env
```

### Error: "Could not connect to MySQL"

**Causa:** Credenciales incorrectas en .env

**Solución:**
```bash
# 1. Verificar los valores en .env
grep DB_ .env

# 2. Probar conexión manual
mysql -h localhost -u root -p eventos

# 3. Asegurarse que MySQL está corriendo
# En XAMPP: Start MySQL de Control Panel
```

### Cambios en .env no se aplican

**Causa:** PHP cacheó las variables

**Solución:**
```bash
# 1. Limpiar cualquier cache de OPcache
php -r "opcache_reset();"

# 2. Reiniciar Apache/PHP-FPM
sudo systemctl restart apache2

# 3. En XAMPP: Restart Apache desde Control Panel
```

## 📋 Checklist de Setup

- [ ] Copiar `.env.example` a `.env`
- [ ] Editar `.env` con credenciales locales
- [ ] Ejecutar migraciones de BD: `mysql -u root eventos < database/migrations.sql`
- [ ] Ejecutar seeder: `php database/seed.php`
- [ ] Crear directorio `logs/`: `mkdir -p logs`
- [ ] Ejecutar tests: `php tests/test_routes.php`
- [ ] Verificar `.env` está en `.gitignore`
- [ ] Establecer permisos: `chmod 600 .env`
- [ ] Acceder a aplicación: `http://localhost/eventos/`

## 🚨 Diferencias de Version Control

La estructura recomendada es:

```
Git Repository
├── .env.example          ← Con TODOS los valores (en git)
├── .gitignore           ← Incluye ".env" (en git) 
├── .env                 ← Con VALORES REALES (NO EN GIT)
└── ... resto de archivos
```

**Ventaja:** Nuevos desarrolladores saben qué variables existen sin tener secretos comprometidos.

## 📞 Soporte

Si tienes problemas con la configuración de variables de entorno:

1. Revisa el archivo [SECURITY.md](SECURITY.md) para mejores prácticas
2. Consulta [README.md](README.md) para setup completo
3. Revisa logs en `logs/` para mensajes de error
