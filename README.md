# AsitenciaEventos

Sistema web en PHP y MySQL para gestión de eventos con registro de asistentes y control de ingreso mediante códigos QR. Incluye roles (Admin, Operador, Asistente), generación y consulta de QR, validación en puerta con cámara, check-in único por evento y reportes exportables en CSV.

## 🚀 Características Principales

### Arquitectura MVC
- **Separación clara** de modelos, vistas y controladores
- **Conexión centralizada** a base de datos con patrón Singleton
- **Estructura organizada** en carpetas `/controllers`, `/models`, `/views`, `/config`
- **Manejo de errores** estructurado y logging

### Seguridad
- ✅ **Protección contra fuerza bruta** en login (5 intentos, bloqueo de 15 minutos)
- ✅ **Rate limiting** en consultas de QR por cédula (10 consultas por minuto)
- ✅ **Validación estricta** de inputs con sanitización
- ✅ **Rotación de tokens QR** al reemitirlos con versionado
- ✅ **Registro de auditoría** completo para accesos y check-ins
- ✅ **Protección CSRF** en todos los formularios
- ✅ **Sesiones seguras** con regeneración periódica

### Check-in Optimizado
- 📷 **Lector QR con cámara** usando jsQR (robusto y rápido)
- ⚡ **Validación automática** sin botón manual
- 🔊 **Sonido de confirmación** al validar exitosamente
- 🎯 **Manejo de errores en tiempo real** con notificaciones visuales
- 🌗 **Modo de alto contraste** para eventos masivos
- 📊 **Estadísticas en vivo** y lista de check-ins recientes
- 🔄 **Actualización automática** cada 30 segundos

### Gestión de Eventos
- Crear, editar y listar eventos
- Registro de asistentes con generación de QR único
- Consulta de QR por número de cédula
- Exportación de check-ins a CSV
- Reportes y estadísticas por evento

### Roles de Usuario
- **Admin**: Acceso completo al sistema
- **Operador**: Gestión de eventos y check-ins
- **Asistente**: Consulta de QR y registro a eventos

## 📋 Requisitos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Apache con mod_rewrite habilitado
- Navegador web moderno con soporte para:
  - getUserMedia API (para cámara)
  - WebRTC
  - JavaScript ES6+

## 🛠️ Instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/danielcramirez/AsitenciaEventos.git
cd AsitenciaEventos
```

### 2. Configurar la base de datos

Crear la base de datos y ejecutar el schema:

```bash
mysql -u root -p < config/schema.sql
```

### 3. Configurar la aplicación

Editar `config/config.php` y `config/database.php` con tus credenciales:

```php
// En config/database.php
private $host = 'localhost';
private $db_name = 'asistencia_eventos';
private $username = 'tu_usuario';
private $password = 'tu_contraseña';
```

```php
// En config/config.php
define('BASE_URL', 'http://tu-dominio.com/AsitenciaEventos');
```

### 4. Configurar permisos

```bash
chmod 755 logs/
chmod 755 assets/
```

### 5. Configurar Apache

Asegúrate de que el `.htaccess` esté habilitado y mod_rewrite esté activo:

```bash
sudo a2enmod rewrite
sudo service apache2 restart
```

### 6. Acceder al sistema

Abrir en el navegador:
```
http://localhost/AsitenciaEventos
```

**Credenciales por defecto:**
- Usuario: `admin`
- Contraseña: `admin123`

⚠️ **IMPORTANTE**: Cambiar la contraseña del administrador después del primer acceso.

## 📁 Estructura del Proyecto

```
AsitenciaEventos/
├── config/
│   ├── config.php           # Configuración general
│   ├── database.php         # Conexión a BD (Singleton)
│   └── schema.sql           # Schema de la base de datos
├── controllers/
│   ├── Controller.php       # Controlador base
│   ├── AuthController.php   # Autenticación
│   ├── EventController.php  # Gestión de eventos
│   ├── AttendeeController.php # Registro de asistentes
│   └── CheckInController.php  # Check-in y validación
├── models/
│   ├── Model.php            # Modelo base
│   ├── User.php             # Usuarios y autenticación
│   ├── Event.php            # Eventos
│   ├── Attendee.php         # Asistentes
│   └── CheckIn.php          # Check-ins
├── views/
│   ├── shared/              # Header, footer
│   ├── auth/                # Login, dashboard
│   ├── events/              # Vistas de eventos
│   ├── attendees/           # Registro y QR
│   └── checkin/             # Pantalla de puerta
├── assets/
│   ├── css/                 # Estilos
│   ├── js/                  # JavaScript
│   ├── sounds/              # Sonidos de confirmación
│   └── images/              # Imágenes
├── logs/                    # Logs de errores
├── index.php               # Punto de entrada
└── .htaccess               # Configuración Apache
```

## 🔐 Características de Seguridad

### Protección contra Fuerza Bruta
- Máximo 5 intentos de login fallidos
- Bloqueo automático de 15 minutos
- Registro en auditoría de todos los intentos

### Rate Limiting
- 10 consultas de QR por cédula por minuto
- Ventana deslizante de 60 segundos
- Bloqueo temporal automático

### Validación de Inputs
- Sanitización de todos los inputs
- Validación de formatos (email, cédula, etc.)
- Protección contra XSS e inyección SQL
- Prepared statements en todas las consultas

### Auditoría
- Registro de todos los logins
- Registro de todos los check-ins
- Registro de acciones administrativas
- Almacenamiento de IP y User-Agent

## 📱 Uso del Sistema

### Para Administradores

1. **Crear un evento**
   - Ir a "Crear Evento"
   - Llenar información del evento
   - Establecer capacidad máxima (opcional)

2. **Gestionar check-ins**
   - Ir al evento deseado
   - Click en "Puerta" para abrir pantalla de check-in
   - Permitir acceso a la cámara
   - Escanear códigos QR automáticamente

3. **Exportar reportes**
   - Ver evento
   - Click en "Exportar CSV"

### Para Asistentes

1. **Registrarse a un evento**
   - Ver eventos disponibles
   - Click en "Registrarse"
   - Completar formulario con cédula y datos

2. **Obtener código QR**
   - El QR se genera automáticamente
   - Guardar o imprimir
   - También se puede consultar después con la cédula

3. **Check-in en el evento**
   - Presentar el código QR en la entrada
   - El sistema valida automáticamente
   - Sonido de confirmación al ingresar

### Para Operadores

- Mismas funciones que administradores
- Sin acceso a gestión de usuarios

## 🎨 Modo de Alto Contraste

El modo de alto contraste está optimizado para eventos masivos con mucha gente:

- Fondo negro con texto blanco
- Bordes y botones de alto contraste
- Indicadores visuales más notorios
- Mejor visibilidad en pantallas grandes

Activar desde la pantalla de check-in con el botón "Alto Contraste".

## 🔊 Sonidos de Confirmación

El sistema reproduce sonidos para:
- ✅ Check-in exitoso
- ❌ Error de validación
- ⚠️ Ya registrado previamente

Los sonidos mejoran la experiencia en entornos ruidosos.

## 📊 Base de Datos

Tablas principales:
- `users`: Usuarios del sistema
- `events`: Eventos
- `attendees`: Asistentes registrados
- `checkins`: Check-ins realizados
- `audit_log`: Registro de auditoría
- `rate_limits`: Control de rate limiting

## 🐛 Solución de Problemas

### La cámara no funciona
- Verificar permisos del navegador
- Usar HTTPS en producción (requerido para getUserMedia)
- Verificar que el dispositivo tenga cámara

### Error de conexión a base de datos
- Verificar credenciales en `config/database.php`
- Verificar que MySQL esté corriendo
- Verificar que la base de datos exista

### Los QR no se escanean
- Verificar buena iluminación
- Acercar más el QR a la cámara
- Verificar que jsQR esté cargado correctamente

## 📄 Licencia

Este proyecto está bajo la licencia GPL-3.0. Ver archivo `LICENSE` para más detalles.

## 👨‍💻 Autor

Daniel Camilo Ramirez Martinez

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Por favor:
1. Fork del proyecto
2. Crear rama para tu feature
3. Commit de cambios
4. Push a la rama
5. Abrir Pull Request
