# Guía de Seguridad - Sistema de Gestión de Eventos

## 📋 Tabla de Contenidos

1. [Visión General de Seguridad](#visión-general)
2. [Control de Acceso Basado en Roles (RBAC)](#rbac)
3. [Protección contra Ataques](#protección-ataques)
4. [Gestión de Credenciales](#credenciales)
5. [Auditoría y Logging](#auditoría)
6. [Variables de Entorno Seguras](#variables-entorno)
7. [Checklist de Seguridad](#checklist)
8. [Respuesta a Incidentes](#incidentes)

---

## Visión General de Seguridad

### Principios Implementados

1. **Defense in Depth:** Múltiples capas de seguridad
2. **Least Privilege:** Mínimos permisos necesarios
3. **Zero Trust:** Verificar cada request
4. **Secure by Default:** Configuración segura
5. **Fail Secure:** En caso de error, denegar acceso

### Amenazas Mitigadas

| Amenaza | Protección | Capa |
|---------|-----------|------|
| SQL Injection | Prepared statements con PDO | BD |
| XSS | Input sanitización + output escaping | App |
| CSRF | Tokens únicos por sesión | App |
| Brute Force | Rate limiting + bloqueo temporal | App |
| Acceso no autenticado | Autenticación obligatoria | Auth |
| Acceso no autorizado | RBAC con permisos granulares | Auth |
| Exposición de fuente | Router único + .htaccess | Web |
| Session hijacking | HttpOnly + Secure cookies | Session |

---

## RBAC - Control de Acceso Basado en Roles

### Roles Definidos

#### 1. **ADMIN** (Administrador)
```
Permisos:
✓ Crear, editar, eliminar eventos
✓ Ver todos los reportes
✓ Exportar datos a CSV
✓ Hacer check-in en puerta
✓ Ver QR de cualquier asistente
✓ Acceder a auditoría completa
```

**Caso de uso:** Personal administrativo, coordinadores de eventos

#### 2. **OPERATOR** (Operador)
```
Permisos:
✓ Hacer check-in en puerta (scan QR)
✓ Ver reportes del evento actual
✗ No puede crear eventos
✗ No puede ver QR de otros
✗ No puede exportar datos
```

**Caso de uso:** Personas en la puerta/entrada

#### 3. **GUEST** (Visitante/Invitado)
```
Permisos:
✓ Ver eventos publicados
✓ Registrarse en un evento
✓ Ver su propio QR
✓ Consultar su registro
✗ No puede crear eventos
✗ No puede ver reportes
✗ No puede acceder a admin
```

**Caso de uso:** Asistentes, público en general

### Implementación

**Ubicación:** `app/permissions.php`

Uso en controllers:
```php
class EventAdminController {
    public static function index() {
        // Requerir autenticación
        require_auth();
        
        // Requerir rol admin
        require_role(ROLE_ADMIN);
        
        // O verificar permiso específico
        require_permission('admin_eventos');
        
        // Lógica del controller...
    }
}
```

Uso en vistas:
```php
<?php if (PermissionManager::canManageEvents()): ?>
    <button class="btn-add-event">Crear Evento</button>
<?php endif; ?>
```

### Flujo de Validación

```
Request HTTP
    ↓
Router (index.php)
    ↓
Controller::método()
    ↓
require_auth()  ← ¿Está logueado?
    ↓
require_permission('acción')  ← ¿Tiene permiso?
    ↓
Lógica del negocio
    ↓
Response
```

---

## Protección Contra Ataques Comunes

### 1. SQL Injection

**✓ Implementado:** Prepared Statements

```php
// INSEGURO (nunca hacer esto)
$sql = "SELECT * FROM users WHERE email = '" . $_GET['email'] . "'";

// SEGURO (siempre usar esto)
$st = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
$st->execute([$_GET['email']]);
$user = $st->fetch();
```

**Ubicación:** Todos los models (`models/*.php`)

### 2. Cross-Site Scripting (XSS)

**✓ Implementado:** Output Escaping

```php
// INSEGURO
<h1><?= $_GET['title'] ?></h1>

// SEGURO
<h1><?= h($_GET['title']) ?></h1>

// Función h() en app/helpers.php
function h(string $s): string {
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
```

**Ubicación:** Todas las vistas (`views/**/*.php`)

### 3. Cross-Site Request Forgery (CSRF)

**✓ Implementado:** CSRF Tokens

```php
// En formulario
<form method="post" action="/eventos/login">
  <input type="hidden" name="csrf" value="<?= csrf_token(); ?>">
  <input type="email" name="email" required>
  <input type="password" name="password" required>
  <button type="submit">Login</button>
</form>

// En controller
csrf_check();  // Valida el token

// Funciones en app/helpers.php
function csrf_token(): string
function csrf_check(): void
```

**Ubicación:** `app/helpers.php`

### 4. Brute Force Attack

**✓ Implementado:** Rate Limiting + Bloqueo Temporal

```
Máximo intentos fallidos: 5
Ventana de tiempo: 15 minutos
Bloqueo: 30 minutos

Almacenado en: tabla `login_attempts`
```

**Modelo:** `models/SecurityModel.php`

```php
// Verificar si está bloqueado
if (SecurityModel::isLoginBlocked($email, $ip)) {
    http_response_code(429);  // Too Many Requests
    exit('Demasiados intentos. Intente más tarde.');
}

// Registrar intento
SecurityModel::recordLoginAttempt($email, $ip, false);
```

### 5. Session Hijacking

**✓ Implementado:**
- HttpOnly: Las cookies no se pueden acceder desde JavaScript
- Secure: Se envía solo por HTTPS en producción
- Regeneración: Después de login

```php
// En app/auth.php
session_start([
    'use_strict_mode' => true,
    'cookie_httponly' => true,
    'cookie_secure' => EnvLoader::getBool('SESSION_SECURE', false),
    'cookie_samesite' => 'Strict',
]);

// Regenerar después de login
session_regenerate_id(true);
```

### 6. Path Traversal / Directory Listing

**✓ Implementado:** .htaccess + Router

```apache
# En .htaccess
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Bloquear .php directo
<FilesMatch "\.php$">
    Require all denied
</FilesMatch>
```

**Resultado:**
- No se puede acceder a `/login.php`
- No se puede hacer listado de directorios
- Todo pasa por `index.php` (router centralizado)

### 7. Information Disclosure

**✓ Implementado:** Headers de Seguridad

```apache
# En .htaccess
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
Header set X-XSS-Protection "1; mode=block"
Header set Content-Security-Policy "default-src 'self'"
```

---

## Gestión de Credenciales

### Contraseñas

**Almacenamiento:**
```php
// Crear nuevo usuario
$password_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

// Verificar password
if (password_verify($password, $password_hash)) {
    // Correcto
}
```

**Datos:** Tabla `users` columna `password` (hash bcrypt)

**Ubicación:** `models/UserModel.php`

### Restricciones de Contraseña

Recomendado (agregar si es necesario):
- Mínimo 8 caracteres
- Letras mayúsculas y minúsculas
- Números y caracteres especiales
- No igual a email
- Expiración cada 90 días

### Gestión de Sesiones

**Timeout:** 30 minutos de inactividad

```php
// En app/config.php
const SESSION_TIMEOUT = null;  // From .env: 1800 segundos

// En app/auth.php
if (isset($_SESSION['last_activity']) && 
    time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
    session_destroy();
    // Redirigir a login
}
$_SESSION['last_activity'] = time();
```

### Tokens Especiales

**CSRF Token:**
- Generado por sesión
- Longitud: 64 caracteres
- Validado en cada POST

**QR Token:**
- 256 bits (64 caracteres hex)
- Único por registro
- Hash SHA256
- Puede ser rotado

---

## Auditoría y Logging

### Sistema de Logs

**Ubicación:** Directorio `logs/`

**Archivos:**
- `YYYY-MM-DD.log` - Logs del día
- `error.log` - Solo errores crítricos
- Se mantienen últimos 30 días

**Niveles de Log (en orden de severidad):**
```
100 = DEBUG   (información de debugging)
200 = INFO    (eventos normales)
250 = NOTICE  (eventos notables)
300 = WARNING (advertencias)
400 = ERROR   (errores)
500 = CRITICAL (fallos críticos)
600 = EMERGENCY (emergencia)
```

### Tabla de Auditoría

**Tabla:** `audit_logs`

**Campos:**
```
id           - ID único
user_id      - Usuario que hizo acción (NULL si guest)
action       - Acción realizada (login, create_event, etc)
action_type  - CREATE, UPDATE, DELETE, VIEW, LOGIN, LOGOUT
resource     - Recurso afectado (events, registrations, etc)
resource_id  - ID del recurso
old_data     - Datos anteriores (JSON)
new_data     - Datos nuevos (JSON)
ip           - IP del cliente
user_agent   - Navegador/cliente
metadata     - Datos adicionales (JSON)
status       - success, error
timestamp    - Fecha y hora de la acción
```

**Modelo:** `models/AuditLogModel.php`

Uso:
```php
AuditLogModel::log([
    'user_id' => $user_id,
    'action' => 'create_event',
    'action_type' => 'CREATE',
    'resource' => 'events',
    'resource_id' => $event_id,
    'new_data' => $event_data,
    'status' => 'success',
]);
```

### Consultar Auditoría

SQL para investigaciones:
```sql
-- Todos los accesos de un usuario
SELECT * FROM audit_logs WHERE user_id = 5 ORDER BY timestamp DESC;

-- Acciones fallidas en las últimas 24h
SELECT * FROM audit_logs 
WHERE status = 'error' 
AND timestamp >= DATE_SUB(NOW(), INTERVAL 1 DAY);

-- Cambios en un evento
SELECT * FROM audit_logs 
WHERE resource = 'events' AND resource_id = 3
ORDER BY timestamp DESC;

-- Intentos fallidos de login
SELECT * FROM login_attempts 
WHERE success = 0 
ORDER BY created_at DESC 
LIMIT 10;
```

---

## Variables de Entorno Seguras

### Archivo .env

**Ubicación:** `/eventos/.env`

**Importante:** NO INCLUIR EN GIT (agregar a .gitignore)

```
.env
.env.local
.env.*.local
logs/
```

### Variables Críticas

```env
# BD - NUNCA guardar credenciales en código
DB_HOST=localhost
DB_NAME=eventos
DB_USER=root
DB_PASS=mi_contraseña_segura

# App Debug - OFF EN PRODUCCIÓN
APP_ENV=production
APP_DEBUG=false

# Logging
LOG_LEVEL=warning
LOG_PATH=/var/log/eventos/
```

### Cargar Desde .env

```php
// Usar EnvLoader
$host = EnvLoader::get('DB_HOST', 'localhost');
$debug = EnvLoader::getBool('APP_DEBUG', false);
$timeout = EnvLoader::getInt('SESSION_TIMEOUT', 1800);
```

### En Producción

1. **NO incluir .env en git**
2. **Usar variables del servidor** (Apache SetEnv, etc)
3. **Permisos:** `chmod 600 .env`
4. **Propietario:** Usuario del web server

---

## Checklist de Seguridad

### Antes de Producción

- [ ] Establecer `APP_ENV=production` en .env
- [ ] Establecer `APP_DEBUG=false` (no revelar errores)
- [ ] Cambiar `DB_PASS` por contraseña fuerte
- [ ] Habilitar HTTPS (SESSION_SECURE=true)
- [ ] Configurar permisos de archivos (644 para .php, 755 para dirs)
- [ ] Eliminar archivos de test (tests/)
- [ ] Remover .env del repositorio (agregarlo a .gitignore)
- [ ] Revisar logs regularmente
- [ ] Hacer backup de BD regularmente
- [ ] Actualizar PHP a versión soportada (8.x mínimo)
- [ ] Remover archivos de debugging
- [ ] Validar HTTPS en todos los endpoints

### 17. Configuración Apache

```apache
# /etc/apache2/sites-available/eventos.conf
<VirtualHost *:443>
    ServerName eventos.example.com
    DocumentRoot /var/www/eventos
    
    # SSL (requerido)
    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/certificate.crt
    SSLCertificateKeyFile /etc/ssl/private/private.key
    
    # Headers de seguridad
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    
    # Logs
    ErrorLog ${APACHE_LOG_DIR}/eventos_error.log
    CustomLog ${APACHE_LOG_DIR}/eventos_access.log combined
    
    # Rewrite (mod_rewrite debe estar activo)
    <Directory /var/www/eventos>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>

# Redirigir HTTP a HTTPS
<VirtualHost *:80>
    ServerName eventos.example.com
    Redirect permanent / https://eventos.example.com/
</VirtualHost>
```

### Regulares (Cada día)

- [ ] Revisar logs de error
- [ ] Verificar intentos fallidos de login
- [ ] Monitorear espacio en disco

### Semanales (Cada semana)

- [ ] Revisar auditoría de acciones críticas (evento management, admin changes)
- [ ] Validar base de datos (integridad)
- [ ] Actualizar dependencias si hay security patches

### Mensuales (Cada mes)

- [ ] Revisar permisos de usuarios (eliminar inactivos)
- [ ] Analizar patrones de uso
- [ ] Hacer penetration testing interno
- [ ] Revisar políticas de seguridad
- [ ] Actualizar documentación

---

## Respuesta a Incidentes

### Niveles de Severidad

| Nivel | Definición | Tiempo Respuesta |
|-------|-----------|---|
| Crítico | Breach confirmado, datos expuestos | Inmediato |
| Alto | Acceso no autorizado activo | 1 hora |
| Medio | Anomalía sospechosa | 4 horas |
| Bajo | Advertencia de seguridad | 1 día |

### Proceso de Incident Response

#### Paso 1: Confirmación
```
1. Revisar logs relevantes
2. Validar si es falsa alarma
3. Cuantificar el impacto
4. Documentar todos los hallazgos
```

#### Paso 2: Aislamiento
```
1. Bloquear IP sospechosa si es necesario
2. Desactivalr cuenta comprometida
3. Hacer backup de evidencia (logs)
4. Prevenir propagación
```

#### Paso 3: Erradicación
```
1. Eliminar acceso no autorizado
2. Parchear vulnerabilidad
3. Actualizar contraseñas relacionadas
4. Revisar otros sistemas afectados
```

#### Paso 4: Recuperación
```
1. Restaurar sistemas afectados
2. Validar funcionalidad
3. Monitorear intensamente
4. Gradualmente volver a normal
```

#### Paso 5: Análisis PostInciden
```
1. Escribir reporte
2. Identificar gaps de seguridad
3. Implementar mejoras
4. Entrenar equipo
5. Actualizar políticas
```

### Checklist de Incident Response

```
[ ] Contabilizar severidad
[ ] Notificar al responsable de seguridad
[ ] Documentar timestamp del descubrimiento
[ ] Hacer backup de logs/evidencia
[ ] Aislar sistemas afectados
[ ] Contactar a affected parties si aplica
[ ] Activar damage control
[ ] Implementar parches
[ ] Validar
[ ] Post-mortem
[ ] Comunicar lessons learned
```

---

## Recursos Adicionales

### Documentación Interna
- [README.md](README.md) - Descripción general del proyecto
- [API.md](API.md) - Referencia de endpoints
- [ROUTING.md](ROUTING.md) - Arquitectura de routing

### Referencias Externas
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [OWASP Authentication Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html)
- [PHP Security](https://www.php.net/manual/en/security.php)
- [MDN Web Security](https://developer.mozilla.org/en-US/docs/Web/Security)

### Herramientas Recomendadas
- **SNYK** - Vulnerabilidades en dependencias
- **ZAPProxy** - Testing de seguridad web
- **Burp Suite** - Penetration testing
- **SQLMap** - Detección de SQL injection
- **PhpStorm Security Inspection** - IDE built-in checks

---

**Última actualización:** 16 de Febrero de 2026
**Versión:** 2.0
**Estado:** Production Ready
