# Arquitectura de Routing - Sistema de Gestión de Eventos

## Cambios Implementados

### 1. Router Centralizado
- **Archivo:** `index.php`
- **Función:** Punto de entrada único para toda la aplicación
- **Ventajas:**
  - ✓ Control centralizado de acceso
  - ✓ Validación de permisos antes de ejecutar acciones
  - ✓ Prevención de acceso directo a archivos PHP
  - ✓ Mejor auditoría de requests
  - ✓ Redirección segura de URLs

### 2. Mapeo de Rutas

| URL | HTTP | Controller | Método | Descripción |
|-----|------|-----------|--------|-------------|
| `/eventos/` | GET | HomeController | index | Página de inicio |
| `/eventos/login` | GET/POST | AuthController | login | Login de usuario |
| `/eventos/logout` | GET | AuthController | logout | Logout de usuario |
| `/eventos/evento` | GET | EventController | show | Ver evento específico |
| `/eventos/admin_eventos` | GET/POST | EventAdminController | index | Administar eventos |
| `/eventos/registrar` | GET/POST | RegistrationController | register | Registrar en evento |
| `/eventos/consulta_qr` | GET | QrController | consult | Ver QR de registro |
| `/eventos/puerta` | GET | CheckinController | door | Check-in en puerta |
| `/eventos/api_checkin` | POST | CheckinController | apiCheckin | API de check-in |
| `/eventos/reporte` | GET | ReportController | report | Ver reportes |
| `/eventos/export_csv` | GET | ReportController | exportCsv | Descargar CSV |

### 3. URLs Antiguas vs Nuevas

Antes (inseguro - acceso directo a archivos):
```
http://localhost/eventos/login.php?action=login
http://localhost/eventos/admin_eventos.php
http://localhost/eventos/evento.php?id=1
```

Ahora (seguro - router centralizado):
```
http://localhost/eventos/login
http://localhost/eventos/admin_eventos
http://localhost/eventos/evento?id=1
```

### 4. Seguridad Implementada (.htaccess)

#### Protecciones:
- ✓ Redirige todas las requests a `index.php`
- ✓ Bloquea acceso directo a archivos `.php`
- ✓ Permite solo `index.php` acceso directo
- ✓ Bloquea acceso a directorios sensibles (app, database, core)
- ✓ Agrega headers de seguridad

#### Reglas de reescritura:
```
RewriteCond %{REQUEST_FILENAME} !-f  # No reescribir si existe archivo
RewriteCond %{REQUEST_FILENAME} !-d  # No reescribir si existe directorio
RewriteRule ^(.*)$ index.php          # Todo va a index.php
```

### 5. Flujo de una Request

```
1. Cliente solicita: GET /eventos/login
                        ↓
2. Apache lee .htaccess
                        ↓
3. .htaccess redirige a: /eventos/index.php (internally)
                        ↓
4. index.php: parseRequest()
                        ↓
5. Router mapea: /login → ['controller' => 'auth', 'action' => 'login']
                        ↓
6. Carga: controllers/AuthController.php
                        ↓
7. Ejecuta: AuthController::login()
                        ↓
8. Retorna respuesta al cliente
```

### 6. Validaciones en el Router

El router valida:
- ✓ Que la ruta exista en el mapeo
- ✓ Que el controlador exista
- ✓ Que la clase esté definida
- ✓ Que el método exista en el controller

Si algo falla:
```php
http_response_code(404 | 500);  // Error HTTP apropiado
exit('Mensaje de error');         // Salida segura
```

### 7. Parámetros GET/POST

Los parámetros se pasan normalmente en la URL:

```
GET /eventos/evento?id=5&name=test
GET /eventos/admin_eventos?action=edit&id=3
POST /eventos/login (form data en body)
```

Los controllers acceden a través de `$_GET`, `$_POST`, `$_REQUEST` como siempre.

### 8. Archivos Eliminados

Se eliminaron los archivos PHP individuales de la raíz:
- login.php ✗
- logout.php ✗
- evento.php ✗
- admin_eventos.php ✗
- registrar.php ✗
- consulta_qr.php ✗
- puerta.php ✗
- api_checkin.php ✗
- reporte.php ✗
- export_csv.php ✗

Razón: Prevenir acceso directo que podría saltar validaciones de seguridad.

### 9. Controllers Sin Cambios

Los controllers funcionan exactamente igual. El router simplemente los llama dinamicamente:

**Antes:**
```php
// login.php
<?php
require_once __DIR__ . '/controllers/AuthController.php';
AuthController::login();
```

**Ahora:**
```php
// index.php (router)
$controller = 'AuthController';
$action = 'login';
call_user_func([$controller, $action]);
```

El resultado es idéntico, pero centralizado.

### 10. Archivo .htaccess

**Ubicación:** `/eventos/.htaccess`

**Requisitos del servidor:**
- ✓ Apache 2.2 o superior
- ✓ mod_rewrite habilitado
- ✓ AllowOverride configurado para aplicar .htaccess

**Verificación en XAMPP:**
```bash
# Apache debe tener habilitado mod_rewrite
# En XAMPP: xampp/apache/conf/httpd.conf
# Buscar: LoadModule rewrite_module modules/mod_rewrite.so
```

### 11. Testing

Para verificar que el router funciona:

```bash
# Prueba que index.php es el único PHP accesible
php tests/test_routes.php
```

El test valida:
- ✓ Todos los controllers existen
- ✓ Todos los métodos existen
- ✓ El mapeo de rutas funciona

### 12. Debugging

Si una ruta no funciona:

1. **Error 404:** Verificar que la ruta existe en el mapeo
   ```php
   // En index.php, revisar $routes array
   ```

2. **Error Controller no encontrado:** Verificar que el archivo existe
   ```bash
   ls controllers/AuthController.php  # Debe existir
   ```

3. **Error Método no encontrado:** Verificar que el method existe en el controller
   ```php
   // En controllers/AuthController.php
   public static function login() { ... }
   ```

4. **.htaccess no funciona:** Verificar que mod_rewrite está habilitado
   ```bash
   # En XAMPP
   a2enmod rewrite  # Linux
   # O editar httpd.conf en Windows
   ```

### 13. Beneficios de Seguridad

| Amenaza | Antes | Ahora |
|---------|-------|-------|
| Acceso directo a login.php | ✗ Posible | ✓ Bloqueado |
| Acceso directo a api_checkin.php | ✗ Posible | ✓ Bloqueado |
| Bypass del router | ✗ Fácil | ✓ Imposible |
| Path traversal (/../../config.php) | ✗ Posible | ✓ Bloqueado |
| Acceso a .htaccess | ✗ Posible | ✓ Bloqueado |
| Información en headers HTTP | ✗ Sin protección | ✓ Headers secure |

## Ejemplos Prácticos

### Acceso a página de login
```
Cliente solicita: http://localhost/eventos/login
Apache ejecuta: /index.php?
Router mapea: 'login' → AuthController::login()
```

### Consumir API de checkin
```
Cliente POST: http://localhost/eventos/api_checkin
Body: {qr_token: "abc123", event_id: 5}
Router mapea: 'api_checkin' → CheckinController::apiCheckin()
Respuesta: {"status": "ok", "message": "Check-in registrado"}
```

### Ver evento
```
Cliente solicita: http://localhost/eventos/evento?id=3
Router mapea: 'evento' → EventController::show()
Controller accede a: $_GET['id'] = 3
```

## Próximos Pasos

Si necesita expandir el routing:

1. Agregar nueva ruta en `$routes` array
2. Crear nuevo controller con el método correspondiente
3. El rest funciona automáticamente

Ejemplo:
```php
// En index.php
$routes = [
    // ...
    'mi_nueva_ruta' => ['controller' => 'mything', 'action' => 'myaction'],
];

// Crear controllers/MithingController.php
class MithingController {
    public static function myaction() { ... }
}

// Acceder en: http://localhost/eventos/mi_nueva_ruta
```

## Referencias

- [Apache mod_rewrite](https://httpd.apache.org/docs/current/mod/mod_rewrite.html)
- [OWASP: Path Traversal](https://owasp.org/www-community/attacks/Path_Traversal)
- [PHP: call_user_func](https://www.php.net/manual/en/function.call-user-func.php)
