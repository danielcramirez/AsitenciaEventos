# Sistema de Pruebas - Gestión de Eventos

Este directorio contiene scripts de prueba para validar la integridad del sistema de gestión de eventos.

## Requisitos

- PHP 7.4 o superior
- MySQL 5.7 o superior en ejecución
- Base de datos `eventos` creada con las tablas ( ejecutar `database/migrations.sql`)
- Datos iniciales cargados ( ejecutar `database/seed.php`)

## Scripts Disponibles

### 1. `test_routes.php` - Pruebas de Integración Básica

Valida que toda la aplicación esté correctamente configurada.

**Ejecutar:**
```bash
php tests/test_routes.php
```

**Verifica:**
- ✓ Archivos de configuración existen
- ✓ Bootstrap carga correctamente
- ✓ Conexión a base de datos funciona
- ✓ Tablas de base de datos existen
- ✓ Controllers cargan correctamente
- ✓ Models cargan correctamente
- ✓ Funciones helpers están disponibles
- ✓ Validaciones funcionan (email, cédula, etc.)
- ✓ Estructura de carpetas es correcta
- ✓ Entry points (routes) existen

**Resultado esperado:**
```
✓ Passed: 25
✗ Failed: 0
```

### 2. `test_endpoints.php` - Pruebas de Estructura de Endpoints

Valida que todos los controllers y models existan con los métodos esperados.

**Ejecutar:**
```bash
php tests/test_endpoints.php
```

**Verifica:**
- ✓ Todos los entry points existen (11 routes)
- ✓ Todos los controllers existen con sus métodos
  - HomeController::index()
  - AuthController::login(), logout()
  - EventController::show()
  - EventAdminController::index()
  - RegistrationController::register()
  - QrController::consult()
  - CheckinController::door(), apiCheckin()
  - ReportController::report(), exportCsv()
- ✓ Todos los models existen con sus métodos
  - UserModel::findActiveByEmail()
  - EventModel::getPublished(), findById()
  - PersonModel::findByCedula(), upsert()
  - RegistrationModel::findByEventAndPerson()
  - QrTokenModel::create()
  - CheckinModel::findByTokenHash(), createCheckin()
  - SecurityModel::isLoginBlocked()
  - AuditLogModel::log()

**Resultado esperado:**
```
✓ Passed: 33
✗ Failed: 0
```

## Estructura del Proyecto

```
eventos/
├── app/                      # Infraestructura de la app
│   ├── config.php           # Configuración (BD, seg., etc)
│   ├── db.php               # Conexión a BD
│   ├── auth.php             # Gestión de sesiones
│   └── helpers.php          # Funciones auxiliares
├── controllers/             # Lógica de negocio (11)
├── models/                  # Acceso a datos (9)
├── views/                   # Templates HTML
├── core/                    # Bootstrap y helpers comunes
├── database/
│   ├── migrations.sql       # Schema de BD
│   └── seed.php             # Datos iniciales
├── routes/                  # Entry points derivados [deprecated - moved to root]
├── tests/                   # Scripts de prueba
├── vendor/                  # Librerías externas (Composer)
└── [routes en raíz]         # Entry points finales
    ├── index.php
    ├── login.php
    ├── logout.php
    ├── evento.php
    ├── admin_eventos.php
    ├── registrar.php
    ├── consulta_qr.php
    ├── puerta.php
    ├── api_checkin.php
    ├── reporte.php
    └── export_csv.php
```

## Ejecución Completa de Pruebas

Para ejecutar todas las pruebas secuencialmente:

```bash
echo "=== Ejecutando Pruebas de Integración ==="
php tests/test_routes.php
echo ""
echo "=== Ejecutando Pruebas de Endpoints ==="
php tests/test_endpoints.php
```

## Configuración de Base de Datos

Si necesita reinicializar la BD:

```bash
# 1. Ejecutar migraciones
mysql -u root eventos < database/migrations.sql

# 2. Cargar datos iniciales
php database/seed.php
```

## Interpretvar Resultados

### Caso 1: Todas las pruebas pasan ✓
- Sistema está funcional
- Todos los componentes están presentes
- Puede acceder a `http://localhost/eventos/`

### Caso 2: Fallan pruebas de conexión ✗
- Verificar que MySQL está ejecutándose
- Verificar credenciales en `app/config.php`
- Verificar que la BD `eventos` existe

### Caso 3: Fallan pruebas de archivos ✗
- Verificar que los directorios `controllers/`, `models/`, `views/`, etc. existen
- Verificar que no se han movido archivos sin actualizar paths

### Caso 4: Fallan pruebas de funciones ✗
- Verificar que `app/helpers.php` se cargó correctamente
- Verificar sintaxis PHP en helpers o config

## Agregar Nuevas Pruebas

Para agregar pruebas nuevas, edite el script correspondiente:

```php
$tester->test('Descripción de la prueba', function () {
    // Código de validación
    if (!condition) {
        throw new Exception('Mensaje de error');
    }
});
```

## Notas Importantes

- Los tests **NO** hacen requests HTTP reales (no requieren servidor web corriendo)
- Validan la **estructura y carga** de archivos, no el comportamiento runtime
- Las advertencias de sesiones (`session_start()`) son esperadas y no indican error
- Para probar endpoints reales, use navegador: `http://localhost/eventos/`

## Contribución

Si agrega nuevos controllers, models o helpers:
1. Agregue test al script correspondiente
2. Ejecute: `php tests/test_routes.php && php tests/test_endpoints.php`
3. Asegúrese que pasen todas las pruebas antes de hacer commit
