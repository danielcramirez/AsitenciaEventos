# Sistema de Gestión de Eventos - Documentación Completa

## 📋 Descripción General

Sistema web de gestión de eventos con registro de asistentes, generación y validación de códigos QR, y check-in automatizado. Implementado con PHP 7.4+, MySQL y arquitectura MVC con seguridad robusta.

## ✨ Características Principales

- ✅ **Autenticación y Autorización:** Login seguro con protección contra fuerza bruta
- ✅ **Gestión de Eventos:** CRUD completo de eventos con publicación
- ✅ **Registro de Asistentes:** Formulario con generación automática de QR
- ✅ **QR Dinámico:** Generación de códigos QR con rotación de tokens
- ✅ **Check-in en Puerta:** Interface con scanner QR (online + offline)
- ✅ **Reportes:** Estadísticas y exportación a CSV
- ✅ **Auditoría:** Registro completo de acciones en base de datos
- ✅ **Rate Limiting:** Protección contra abuse de API
- ✅ **Validaciones:** Entrada sanitizada y validada
- ✅ **Routing Centralizado:** Seguridad a través de router único
- ✅ **CSRF Protection:** Protección en todos los formularios

## 🏗️ Arquitectura

### Estructura de Carpetas

```
eventos/
├── index.php                 # Router centralizado (único entry point)
├── .htaccess                # Reglas de seguridad y reescritura
│
├── app/                     # Infraestructura de la aplicación
│   ├── config.php          # Configuración (BD, seguridad, constantes)
│   ├── db.php              # Conexión a base de datos (PDO singleton)
│   ├── auth.php            # Gestión de sesiones y autenticación
│   └── helpers.php         # Funciones auxiliares (validación, helpers)
│
├── core/                    # Núcleo de la aplicación
│   ├── bootstrap.php       # Inicialización y require chain
│   └── view.php            # Helper para renderizar vistas
│
├── controllers/            # Lógica de negocio (11 archivos)
│   ├── HomeController.php
│   ├── AuthController.php
│   ├── EventController.php
│   ├── EventAdminController.php
│   ├── RegistrationController.php
│   ├── QrController.php
│   ├── CheckinController.php
│   └── ReportController.php
│
├── models/                 # Acceso a datos (9 archivos)
│   ├── UserModel.php
│   ├── EventModel.php
│   ├── PersonModel.php
│   ├── RegistrationModel.php
│   ├── QrTokenModel.php
│   ├── CheckinModel.php
│   ├── SecurityModel.php
│   ├── AuditLogModel.php
│   └── ...
│
├── views/                  # Templates HTML (organizados por feature)
│   ├── layout/
│   │   ├── header.php
│   │   └── footer.php
│   ├── auth/
│   │   └── login.php
│   ├── events/
│   ├── qr/
│   ├── checkin/
│   ├── reports/
│   └── home/
│
├── database/              # Base de datos
│   ├── migrations.sql     # Schema con tablas de seguridad
│   └── seed.php           # Script para cargar datos iniciales
│
├── vendor/                # Librerías externas (Composer)
│   ├── bacon/qr-code/
│   ├── endroid/qr-code/
│   └── ...
│
└── tests/                 # Scripts de prueba
    ├── test_routes.php    # Pruebas de integración
    ├── test_endpoints.php # Pruebas de estructura
    └── README.md          # Documentación de tests

# Documentación
├── README.md              # Este archivo
├── ROUTING.md             # Patrón de routing centralizado
├── API.md                 # Referencia de endpoints
├── SECURITY.md            # (próximo) Detalles de seguridad
└── CONTRIBUTING.md        # (próximo) Guía para contribuidores
```

### Patrón Arquitectónico

```
request HTTP
    ↓
index.php (single entry point)
    ↓
router (parseRequest + executeRoute)
    ↓
Controller (controla el flujo)
    ↓
Models (acceden a BD)
    ↓
Views (renderizar HTML/JSON)
    ↓
response HTTP
```

## 🚀 Inicio Rápido

### 1. Requisitos

- **PHP:** 7.4 o superior
- **MySQL:** 5.7 o superior
- **Apache:** 2.2+ con mod_rewrite
- **Composer:** para gestionar dependencias

### 2. Instalación

```bash
# Clonar repositorio
git clone https://github.com/danielcramirez/AsitenciaEventos.git
cd eventos

# Instalar dependencias
composer install

# Crear base de datos
mysql -u root < database/migrations.sql

# Cargar datos iniciales
php database/seed.php

# Acceder a la aplicación
# http://localhost/eventos/
```

### 3. Configuración Inicial

**Editar `app/config.php`:**
```php
define('DB_HOST', 'localhost');      // Host de BD
define('DB_NAME', 'eventos');        // Nombre de BD
define('DB_USER', 'root');           // Usuario MySQL
define('DB_PASS', '');               // Contraseña MySQL
```

### 4. Verificar Instalación

```bash
# Ejecutar tests
php tests/test_routes.php
php tests/test_endpoints.php

# Ambos deben mostrar todos los tests en ✓ PASS
```

## 🔐 Seguridad Implementada

### 1. Protección contra Fuerza Bruta
- Límite: 5 intentos fallidos de login
- Bloqueo: 30 minutos por IP + Email
- Log: Todos los intentos registrados en `login_attempts`

### 2. Rate Limiting
- Consulta QR: 10 por 5 minutos por IP
- Sistema de ventana deslizante
- Bloqueo automático temporal

### 3. CSRF Protection
- Token único por sesión
- Validación en todos los POST
- Regeneración después de login

### 4. Input Validation
- Sanitización de entrada (XSS prevention)
- Validación de tipos de datos
- Cédula, email, teléfono con regex

### 5. SQL Injection Prevention
- Prepared statements en todas las queries
- PDO con placeholders (?)
- Parameterized queries

### 6. Routing Seguro
- Router centralizado en `index.php`
- `.htaccess` bloquea acceso directo a `.php`
- No se puede acceder a `login.php`, `api_checkin.php`, etc directamente

### 7. Session Security
- HttpOnly cookies (si está configurado)
- Session timeout (30 minutos)
- Regeneración de ID después de login

### 8. Auditoría Completa
- Tabla `audit_logs` registra:
  - Usuario que ejecutó la acción
  - IP del cliente
  - User agent
  - Acción realizada
  - Timestamp
  - Datos adicionales (JSON)

## 📱 Flujos de Usuario

### Flujo 1: Visitante Registrándose en Evento

```
1. Visitante accede a /eventos
2. Ve lista de eventos publicados
3. Hace click en evento
4. Ve detalles y formulario de registro
5. Ingresa: cédula, nombres, apellidos, celular
6. Sistema crea automáticamente usuario si no existe
7. Sistema genera QR para asistente
8. QR se muestra en pantalla y se guarda en BD
```

### Flujo 2: Admin Gestionar Eventos

```
1. Admin accede a /eventos/login
2. Ingresa email y contraseña
3. Sistema valida contra tabla users
4. Crea sesión con role = 'admin'
5. Redirige a /eventos/admin_eventos
6. Puede CRUD eventos
7. Ver asistentes registrados
8. Exportar a CSV
```

### Flujo 3: Check-in en Puerta

```
1. Operador accede a /eventos/puerta?event_id=1
2. Interface muestra scanner QR (html5-qrcode)
3. Escanea QR del asistente
4. Sistema parsea token_hash del QR
5. POST a /eventos/api_checkin (JSON)
6. Sistema valida token en qr_tokens
7. Marca check-in en tabla checkins
8. Retorna confirmación (nombre, hora)
9. Sonido + visual feedback
```

### Flujo 4: Rotar QR

```
1. Asistente accede a /eventos/consulta_qr?registration_id=123
2. Ve su QR actual
3. Hace click en "Rotar Token"
4. Sistema:
   - Genera nuevo token_hash
   - Genera nuevo QR con token
   - Invalida token anterior (revoked_at)
   - Retorna nuevo QR
5. QR anterior ya no funciona para check-in
```

## 🗄️ Base de Datos

### Tablas Principales

| Tabla | Propósito | Registros |
|-------|----------|-----------|
| `users` | Cuentas de admin/operadores | ~3 |
| `events` | Eventos disponibles | >0 |
| `persons` | Asistentes (cédula única) | Variables |
| `registrations` | Registros en evento (persona + evento) | Variables |
| `qr_tokens` | QR con token y hash | 1 por registro |
| `checkins` | Confirmación de asistencia | < registrations |
| `login_attempts` | Log de intentos fallidos | Limpiar cada 30 min |
| `rate_limits` | Contador de requests por IP/cedula | Dinámico |
| `audit_logs` | Toda acción del sistema | Crece constante |
| `persons_users` | Muchos-a-muchos (auxiliar) | Variables |

### Relaciones

```
users (1) ──→ audit_logs (N)
       └→ checkins (N) [operator_user_id]

events (1) ──→ registrations (N)
        └→ qr_tokens (N) [indirecta]
        └→ checkins (N)

persons (1) ──→ registrations (N)
         └→ persons_users (N)

registrations (1) ──→ qr_tokens (1)
              └→ checkins (0..N)
```

## 🧪 Testing

Ejecutar todas las pruebas:

```bash
# Pruebas de integración (25 checks)
php tests/test_routes.php

# Pruebas de endpoints (33 checks)
php tests/test_endpoints.php
```

### Qué validan

- ✓ Archivos de configuración existen
- ✓ Conexión a BD funciona
- ✓ Tablas existen y tienen datos
- ✓ Controllers y models cargan correctamente
- ✓ Métodos existen y son accesibles
- ✓ Funciones helpers disponibles
- ✓ Validaciones funcionan
- ✓ Entry points existen
- ✓ Estructura de carpetas correcta

## 📚 Documentación Adicional

- **[ROUTING.md](ROUTING.md)** - Patrón de routing centralizado y reescritura con `.htaccess`
- **[API.md](API.md)** - Referencia completa de endpoints y ejemplos
- **[tests/README.md](tests/README.md)** - Guía para ejecutar y crear tests

## 🛠️ Desarrollo

### Agregar Nuevo Endpoint

1. **Crear controller:**
   ```php
   // controllers/MyController.php
   class MyController {
       public static function myaction() {
           // lógica...
           render_view('my_view', $data);
       }
   }
   ```

2. **Agregar ruta en `index.php`:**
   ```php
   $routes = [
       // ...
       'my_route' => ['controller' => 'my', 'action' => 'myaction'],
   ];
   ```

3. **Acceder en navegador:**
   ```
   http://localhost/eventos/my_route
   ```

### Agregar Nuevo Model

```php
// models/MyModel.php
class MyModel {
    public static function findById(int $id): ?array {
        $st = db()->prepare('SELECT * FROM mytable WHERE id = ? LIMIT 1');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }
}
```

### Agregar Nueva Vista

```php
// views/home/myview.php
<h1><?= h($title) ?></h1>
<p><?= h($description) ?></p>
```

Renderizar desde controller:
```php
render_view('home/myview', [
    'title' => 'Mi Título',
    'description' => 'Descripción'
]);
```

## 🚨 Troubleshooting

### Error: "Clase no encontrada"
- Verificar que el archivo existe en `controllers/`
- Verificar que la clase está definida correctamente
- Verificar la capitalización (MyController.php vs myController.php)

### Error: "Método no encontrado"
- Verificar que el método existe en el controller
- Verificar que es `public static function`

### Error: "Ruta no encontrada (404)"
- Verificar que la ruta está en el `$routes` array en index.php
- Verificar la spelling de la ruta

### Error: "Base de datos no conecta"
- Verificar que MySQL está corriendo
- Verificar credenciales en `app/config.php`
- Verificar que la BD `eventos` existe

### .htaccess no funciona
- Verificar que Apache tiene `mod_rewrite` habilitado
- En XAMPP: editar `apache/conf/httpd.conf`
- Buscar y descomentar: `LoadModule rewrite_module modules/mod_rewrite.so`

## 📊 Estadísticas del Proyecto

| Métrica | Valor |
|---------|-------|
| Controllers | 8 |
| Models | 9 |
| Views | 8+ |
| Tablas de BD | 10 |
| Endpoints públicos | 11 |
| Tests | 58 |
| Líneas de código | ~2000 |
| Dependencias | 3 (QR codes) |

## 📝 Convenciones de Código

- **PHP:** camelCase para variables/métodos, PascalCase para clases
- **BD:** snake_case para tablas/columnas
- **Vistas:** lowercase separado por slash (home/index)
- **Controllers:** Nombre + Controller en PascalCase
- **Models:** Nombre + Model en PascalCase
- **Funciones helpers:** lowercase con underscore

## 🤝 Contribuciones

Para contribuir:
1. Fork el repositorio
2. Crea una rama (`git checkout -b feature/nueva-función`)
3. Commit cambios (`git commit -m 'Agregar función X'`)
4. Push a la rama (`git push origin feature/nueva-función`)
5. Abre Pull Request

Asegúrate de:
- Pasar todos los tests
- Seguir las convenciones de código
- Añadir tests para nuevas funcionalidades
- Actualizar documentación

## 📄 Licencia

MIT License - ver archivo LICENSE

## 👨‍💻 Autor

Daniel Ramírez - [GitHub](https://github.com/danielcramirez)

## 📞 Soporte

Para reportar bugs o solicitar features:
- Abrir issue en GitHub
- Contactar al equipo de desarrollo

---

**Última actualización:** 16 de Febrero de 2026
**Versión:** 2.0 (Con router centralizado)
