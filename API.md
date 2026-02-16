# API y Rutas - Sistema de Gestión de Eventos

## Base URL

```
http://localhost/eventos/
```

## Estructura de Rutas Públicas

### 1. Autenticación

#### Login (GET/POST)
```
GET  /eventos/login                  # Mostrar formulario
POST /eventos/login                  # Procesar login
```

**Request (POST):**
```json
{
  "email": "usuario@eventos.local",
  "password": "contraseña",
  "csrf": "token_desde_formulario"
}
```

**Response (200 OK):**
```
Redirect: /eventos/
Set-Cookie: PHPSESSID=...
```

#### Logout (GET)
```
GET /eventos/logout                  # Cerrar sesión
```

**Response (200 OK):**
```
Redirect: /eventos/login
```

### 2. Consulta de Eventos

#### Home - Listar Eventos Públicos (GET)
```
GET /eventos/                        # Página inicio
GET /eventos/home
```

**Response (HTML):**
Página con lista de eventos publicados.

#### Ver Evento (GET)
```
GET /eventos/evento?id=1             # Ver evento específico
```

**Query Parameters:**
- `id` (int, required) - ID del evento

**Response (HTML):**
Página con detalles del evento + formulario de registro.

### 3. Registro de Participantes

#### Registrar en Evento (GET/POST)
```
GET  /eventos/registrar              # Mostrar formulario
POST /eventos/registrar              # Guardar registro
```

**Request (POST):**
```json
{
  "event_id": 1,
  "cedula": "12345678",
  "nombres": "Juan",
  "apellidos": "Pérez",
  "celular": "+57301234567",
  "csrf": "token_desde_formulario"
}
```

**Response (201 Created):**
```json
{
  "message": "Registrado correctamente",
  "registration_id": 123,
  "qr_code": "data:image/png;base64,..."
}
```

### 4. Consulta de QR

#### Ver QR de Registro (GET)
```
GET /eventos/consulta_qr?registration_id=123
```

**Query Parameters:**
- `registration_id` (int, required) - ID del registro

**Response (HTML):**
Página con QR visible + opción para rotar token.

**POST - Rotar Token (POST):**
```
POST /eventos/consulta_qr
Body: registration_id=123&csrf=token
```

**Response (JSON):**
```json
{
  "status": "ok",
  "new_qr": "data:image/png;base64,..."
}
```

### 5. Check-in en Puerta

#### Página de Check-in (GET)
```
GET /eventos/puerta?event_id=1       # Interfaz de puerta
```

**Query Parameters:**
- `event_id` (int, required) - ID del evento

**Response (HTML):**
Página con scanner de QR interactivo.

#### API de Check-in (POST)
```
POST /eventos/api_checkin
Content-Type: application/json
```

**Request:**
```json
{
  "event_id": 1,
  "token_hash": "abc123def456...",
  "operator_id": 5
}
```

**Response (200 OK):**
```json
{
  "status": "success",
  "message": "Check-in registrado",
  "cedula": "12345678",
  "names": "Juan Pérez",
  "timestamp": "2026-02-16 16:30:45"
}
```

**Response (400 Bad Request):**
```json
{
  "status": "error",
  "message": "Token inválido o ya fue usado",
  "code": "INVALID_TOKEN"
}
```

### 6. Reportes

#### Ver Reporte (GET)
```
GET /eventos/reporte?event_id=1      # Estadísticas de evento
```

**Query Parameters:**
- `event_id` (int, required) - ID del evento

**Response (HTML):**
Tabla con estadísticas del evento.

#### Descargar CSV (GET)
```
GET /eventos/export_csv?event_id=1   # Descargar datos CSV
```

**Query Parameters:**
- `event_id` (int, required) - ID del evento

**Response (200 OK):**
```
Content-Type: text/csv
Content-Disposition: attachment; filename="evento_1.csv"

cedula,nombres,apellidos,celular,registered_at,checkin_at,status
12345678,Juan,Pérez,+573001234567,2026-02-01 10:00:00,2026-02-16 16:30:45,checked_in
...
```

### 7. Administración de Eventos (Requiere Login + Rol Admin)

#### Listar/Crear Eventos (GET/POST)
```
GET  /eventos/admin_eventos          # Ver formulario
POST /eventos/admin_eventos          # Crear/editar evento
```

**Request (POST):**
```json
{
  "id": 1,
  "title": "Conferencia 2026",
  "description": "Descripción del evento",
  "event_date": "2026-03-15",
  "capacity": 100,
  "location": "Salón A",
  "status": "published",
  "csrf": "token_desde_formulario"
}
```

**Response (201 Created / 200 OK):**
```json
{
  "message": "Evento guardado",
  "event_id": 1,
  "redirect": "/eventos/admin_eventos"
}
```

## Códigos de Estado HTTP

| Código | Significado | Cuándo ocurre |
|--------|------------|---------------|
| 200 | OK | Request exitoso |
| 201 | Created | Recurso creado exitosamente |
| 400 | Bad Request | Parámetros inválidos o faltantes |
| 401 | Unauthorized | No está autenticado |
| 403 | Forbidden | No tiene permisos |
| 404 | Not Found | Ruta o recurso no existe |
| 500 | Server Error | Error en el servidor |

## Manejo de Errores

### Error JSON (API endpoints)
```json
{
  "status": "error",
  "message": "Descripción del error",
  "code": "ERROR_CODE"
}
```

### Error HTML (Web endpoints)
```html
<div class="alert alert-error">
  Descripción del error
</div>
```

## Autenticación

### Session-based (Para páginas web)
```php
// En el servidor (controllers)
session_start();
$_SESSION['user_id'] = 5;
$_SESSION['role'] = 'admin';

// En el navegador
Cookie: PHPSESSID=abcd1234...
```

### Validación CSRF
Todos los formularios POST requieren token CSRF:
```html
<form method="post" action="/eventos/login">
  <input type="hidden" name="csrf" value="<?= csrf_token(); ?>">
  ...
</form>
```

## Límites de Rate Limiting

**Por IP + Email (Login):**
- 5 intentos fallidos en 15 minutos = bloqueado 30 minutos

**Por IP + Cedula (Consulta QR):**
- 10 consultas en 5 minutos = bloqueado 10 minutos

## Ejemplos Completos

### Flujo de Registro

```bash
# 1. Obtener página del evento
curl http://localhost/eventos/evento?id=1

# 2. Llenar formulario y enviar registro
curl -X POST http://localhost/eventos/registrar \
  -d "event_id=1&cedula=12345678&nombres=Juan&apellidos=Pérez&csrf=TOKEN"

# 3. Ver QR generado
curl http://localhost/eventos/consulta_qr?registration_id=123
```

### Flujo de Check-in

```bash
# 1. Abrir página de puerta (escanear QR con código)
curl http://localhost/eventos/puerta?event_id=1

# 2. Enviar QR escaneado a API
curl -X POST http://localhost/eventos/api_checkin \
  -H "Content-Type: application/json" \
  -d '{"event_id":1,"token_hash":"...","operator_id":5}'

# 3. Descargar reporte
curl http://localhost/eventos/export_csv?event_id=1 > evento.csv
```

## Notas Importantes

1. **URLs amigables:** Las rutas no terminan en `.php` (ej: `/login` no `/login.php`)
2. **Parámetros GET:** Se pasan en la URL (ej: `?event_id=1`)
3. **Parámetros POST:** Se envían en el body del formulario o JSON
4. **CSRF:** Requerido en todos los formularios POST
5. **Sessions:** Automáticamente manejadas por PHP
6. **JSON responses:** Solo en endpoints de API (api_checkin, etc.)
7. **Redirects:** Los controllers pueden hacer redirect con `header('Location: ...')`

## Variables de Entorno (.env)

Si necesita configurar valores en tiempo de ejecución, edite `app/config.php`:

```php
// app/config.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'eventos');
define('DB_USER', 'root');
define('DB_PASS', '');

define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCK_DURATION', 1800); // segundos
```

## Testing de Rutas

Para verificar que todas las rutas están disponibles:

```bash
php tests/test_routes.php
php tests/test_endpoints.php
```

Ambos scripts deben mostrar: ✓ 100% de pruebas pasadas
