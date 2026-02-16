# Documentación Técnica - Sistema de Asistencia a Eventos

## Arquitectura del Sistema

### Modelo de Capas

El sistema está construido con una arquitectura MVC (Modelo-Vista-Controlador) simplificada:

```
┌─────────────────────────────────────────┐
│           CAPA DE PRESENTACIÓN          │
│  (Views - HTML/CSS/JS)                  │
│  - Login                                │
│  - Dashboard                            │
│  - Gestión de Eventos                   │
│  - Registro de Asistentes               │
│  - Scanner QR                           │
│  - Reportes                             │
└─────────────────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────┐
│         CAPA DE LÓGICA DE NEGOCIO       │
│  (Models - PHP)                         │
│  - User.php                             │
│  - Event.php                            │
│  - Registration.php                     │
│  - Checkin.php                          │
└─────────────────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────┐
│         CAPA DE DATOS                   │
│  (Database - MySQL)                     │
│  - users                                │
│  - events                               │
│  - registrations                        │
│  - checkins                             │
└─────────────────────────────────────────┘
```

## Seguridad Implementada

### 1. Autenticación
- **Password Hashing**: bcrypt con cost factor 10
- **Sesiones Seguras**: HttpOnly, SameSite
- **Timeout**: Sesiones expiran tras inactividad

```php
// Ejemplo de hash de contraseña
$password_hash = password_hash($password, PASSWORD_BCRYPT);
```

### 2. Protección CSRF
Todos los formularios incluyen tokens CSRF:

```php
$csrf_token = generate_csrf_token();
verify_csrf_token($_POST['csrf_token']);
```

### 3. Prevención SQL Injection
Uso de PDO con prepared statements:

```php
$stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
```

### 4. Validación de Entrada
- Sanitización con `htmlspecialchars()`
- Validación de tipos de datos
- Verificación de formato de email

### 5. Control de Acceso
- Roles: Administrador, Operador, Asistente
- Verificación de permisos en cada página
- Separación de funcionalidades por rol

## Base de Datos

### Diagrama ER

```
┌─────────────┐         ┌──────────────┐
│   users     │         │   events     │
├─────────────┤         ├──────────────┤
│ id (PK)     │◄────────│ created_by   │
│ username    │         │ id (PK)      │
│ email       │         │ name         │
│ password_hash│        │ event_date   │
│ role        │         │ max_capacity │
│ active      │         │ current_regs │
└─────────────┘         └──────────────┘
                              │
                              │
                              ▼
                    ┌──────────────────┐
                    │  registrations   │
                    ├──────────────────┤
                    │ id (PK)          │
                    │ event_id (FK)    │
                    │ attendee_name    │
                    │ attendee_email   │
                    │ qr_token (UNIQUE)│
                    │ registered_by    │
                    └──────────────────┘
                              │
                              │
                              ▼
                    ┌──────────────────┐
                    │    checkins      │
                    ├──────────────────┤
                    │ id (PK)          │
                    │ registration_id  │
                    │ event_id (FK)    │
                    │ checked_in_by    │
                    │ checkin_time     │
                    └──────────────────┘
```

### Índices para Optimización

```sql
-- Búsqueda rápida de usuarios
INDEX idx_username ON users(username)
INDEX idx_email ON users(email)

-- Búsqueda de eventos por fecha
INDEX idx_event_date ON events(event_date)

-- Validación rápida de QR
INDEX idx_qr_token ON registrations(qr_token)

-- Prevención de duplicados
UNIQUE KEY unique_attendee_event ON registrations(event_id, attendee_email)
UNIQUE KEY unique_checkin ON checkins(registration_id)
```

## Flujo de Datos

### 1. Registro de Asistente

```
Usuario (Operador/Admin)
    │
    ├─► Selecciona Evento
    │
    ├─► Ingresa datos del asistente
    │
    ├─► Sistema valida:
    │   ├─ Email no duplicado
    │   ├─ Capacidad disponible
    │   └─ Evento activo
    │
    ├─► Se genera token QR único
    │
    ├─► Se incrementa contador de registros
    │
    └─► Se muestra código QR
```

### 2. Check-in en Puerta

```
Asistente llega al evento
    │
    ├─► Presenta código QR
    │
    ├─► Operador escanea con cámara
    │
    ├─► Sistema valida:
    │   ├─ Token existe
    │   ├─ Evento activo
    │   └─ No hay check-in previo
    │
    ├─► Se registra check-in
    │
    └─► Confirmación visual
```

## API de QR Codes

El sistema utiliza la API pública de QR Server para generar códigos QR:

```
https://api.qrserver.com/v1/create-qr-code/
  ?size=300x300
  &data=[TOKEN_64_CARACTERES]
```

Ventajas:
- No requiere librerías adicionales
- Generación instantánea
- Sin límite de uso
- Alta disponibilidad

## Exportación CSV

### Estructura de Reportes

**Eventos:**
```csv
ID,Nombre,Descripción,Ubicación,Fecha,Capacidad,Registrados,Disponibles,Check-ins,Creado Por,Estado,Fecha Creación
```

**Registros:**
```csv
ID,Nombre,Email,Teléfono,Token QR,Fecha Registro,Check-in,Fecha Check-in,Registrado Por
```

**Check-ins:**
```csv
ID Check-in,Nombre,Email,Teléfono,Fecha Check-in,Check-in Por
```

### Formato UTF-8 con BOM
Los archivos CSV incluyen BOM (Byte Order Mark) para compatibilidad con Excel:

```php
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
```

## Roles y Permisos

| Funcionalidad | Administrador | Operador | Asistente |
|--------------|---------------|----------|-----------|
| Ver Dashboard | ✅ | ✅ | ✅ |
| Crear Eventos | ✅ | ✅ | ❌ |
| Listar Eventos | ✅ | ✅ | ❌ |
| Registrar Asistentes | ✅ | ✅ | ❌ |
| Escanear QR | ✅ | ✅ | ✅ |
| Check-in | ✅ | ✅ | ✅ |
| Ver Reportes | ✅ | ❌ | ❌ |
| Exportar CSV | ✅ | ❌ | ❌ |
| Gestionar Usuarios | ✅ | ❌ | ❌ |

## Escalabilidad

### Optimizaciones Implementadas

1. **Consultas Eficientes**
   - Uso de índices en columnas de búsqueda frecuente
   - JOINs optimizados
   - Paginación (preparado para implementar)

2. **Caché de Sesiones**
   - Datos del usuario en sesión
   - Reducción de consultas a BD

3. **Generación de QR Externa**
   - No consume recursos del servidor
   - Escalable horizontalmente

### Recomendaciones para Producción

1. **Base de Datos**
   - Usar conexión pooling
   - Implementar réplicas de lectura
   - Caché con Redis/Memcached

2. **Aplicación**
   - Implementar CDN para assets
   - Usar HTTPS obligatorio
   - Configurar rate limiting

3. **Monitoreo**
   - Logs de acceso y errores
   - Métricas de performance
   - Alertas automáticas

## Testing Manual

### Casos de Prueba Críticos

1. **Autenticación**
   - ✅ Login exitoso con credenciales válidas
   - ✅ Login fallido con credenciales inválidas
   - ✅ Protección de páginas sin autenticación
   - ✅ Logout correcto

2. **Gestión de Eventos**
   - ✅ Crear evento con todos los campos
   - ✅ Validación de capacidad máxima
   - ✅ Listado de eventos
   - ✅ Visualización de estadísticas

3. **Registro de Asistentes**
   - ✅ Registro exitoso con capacidad disponible
   - ✅ Rechazo por capacidad agotada
   - ✅ Prevención de email duplicado
   - ✅ Generación de QR único

4. **Check-in**
   - ✅ Escaneo exitoso de QR válido
   - ✅ Rechazo de QR ya usado
   - ✅ Rechazo de QR inválido
   - ✅ Validación manual de token

5. **Reportes**
   - ✅ Exportación CSV de eventos
   - ✅ Exportación CSV de registros
   - ✅ Exportación CSV de check-ins
   - ✅ Formato UTF-8 correcto

## Mantenimiento

### Tareas Periódicas Recomendadas

1. **Diarias**
   - Backup de base de datos
   - Revisión de logs de error

2. **Semanales**
   - Limpieza de sesiones expiradas
   - Revisión de espacio en disco

3. **Mensuales**
   - Actualización de dependencias
   - Revisión de seguridad
   - Optimización de índices

### Logs Importantes

```bash
# Errores de PHP
tail -f /var/log/php/error.log

# Errores de MySQL
tail -f /var/log/mysql/error.log

# Accesos al servidor
tail -f /var/log/apache2/access.log
```

## Soporte Técnico

### Problemas Comunes

**1. Error de conexión a base de datos**
```
Solución: Verificar credenciales en config/database.php
```

**2. Sesión no persiste**
```
Solución: Verificar permisos de /tmp o session.save_path
```

**3. QR no se genera**
```
Solución: Verificar conectividad a api.qrserver.com
```

**4. CSV con caracteres extraños**
```
Solución: Abrir con UTF-8, el BOM está incluido
```

**5. Cámara no funciona en scanner**
```
Solución: Usar HTTPS o localhost, navegadores bloquean cámara en HTTP
```
