# AsitenciaEventos

Sistema web en PHP 8 y MySQL para gestión de eventos con registro de asistentes y control de ingreso mediante códigos QR. Incluye autenticación con roles (Administrador, Operador, Asistente), generación y consulta de QR, validación en puerta con cámara, check-in único por evento y reportes exportables en CSV.

## Características

### 🔐 Autenticación y Roles
- Sistema de login con sesiones seguras
- Tres roles con diferentes permisos:
  - **Administrador**: Acceso total, gestión de eventos, registros y reportes
  - **Operador**: Crear eventos, registrar asistentes, realizar check-ins
  - **Asistente**: Escanear códigos QR y realizar check-ins

### 📋 Gestión de Eventos
- Crear, editar y listar eventos
- Control de capacidad máxima
- Registro de asistentes por evento
- Estadísticas en tiempo real

### 🎫 Registro de Asistentes
- Registro de asistentes con datos personales
- Generación automática de códigos QR únicos
- Validación de capacidad del evento
- Prevención de registros duplicados

### 📷 Sistema de QR
- Generación de códigos QR para cada registro
- Escáner integrado con cámara web
- Validación manual de tokens
- Check-in único por asistente

### ✅ Check-in
- Validación de códigos QR en tiempo real
- Registro de fecha y hora de entrada
- Control de check-ins duplicados
- Interfaz visual para validación

### 📊 Reportes
- Exportación de datos en formato CSV
- Reportes por evento o globales
- Listados de registros y check-ins
- Compatible con Excel y Google Sheets

### 🔒 Seguridad
- Contraseñas hasheadas con bcrypt
- Protección CSRF en formularios
- Preparación de consultas SQL con PDO
- Validación y sanitización de datos
- Sesiones seguras con HttpOnly

## Requisitos

- PHP 8.0 o superior
- MySQL 8.0 o superior
- Servidor web (Apache, Nginx)
- Navegador con soporte para cámara web (para escaneo QR)

## Instalación

1. **Clonar el repositorio**
```bash
git clone https://github.com/danielcramirez/AsitenciaEventos.git
cd AsitenciaEventos
```

2. **Configurar la base de datos**

Crear la base de datos y ejecutar el schema:
```bash
mysql -u root -p < database/schema.sql
```

3. **Configurar la conexión a la base de datos**

El archivo `config/database.php` ya está configurado con valores por defecto. Si necesitas cambiarlos, edita:
```php
return [
    'host' => 'localhost',
    'dbname' => 'asistencia_eventos',
    'username' => 'root',
    'password' => 'tu_password',
    'charset' => 'utf8mb4',
];
```

4. **Configurar el servidor web**

**Apache:**
Asegúrate de que el DocumentRoot apunte al directorio del proyecto o usa un alias.

**PHP Built-in Server (desarrollo):**
```bash
php -S localhost:8000
```

5. **Acceder al sistema**

Abre tu navegador y visita:
```
http://localhost:8000
```

## Credenciales de Prueba

El sistema incluye usuarios de prueba (todos con contraseña `admin123`):

- **Administrador**: `admin` / `admin123`
- **Operador**: `operador1` / `admin123`
- **Asistente**: `asistente1` / `admin123`

## Uso

### Crear un Evento
1. Inicia sesión como Administrador u Operador
2. Ve a "Eventos" → "Crear Evento"
3. Completa los datos del evento (nombre, fecha, ubicación, capacidad)
4. Haz clic en "Crear Evento"

### Registrar Asistentes
1. Ve a "Eventos" → Selecciona un evento → "Registrar"
2. Completa los datos del asistente
3. El sistema generará automáticamente un código QR
4. Descarga o imprime el código QR

### Realizar Check-in
1. Ve a "Escanear QR"
2. Permite el acceso a la cámara
3. Escanea el código QR del asistente
4. Confirma el check-in

### Exportar Reportes
1. Ve a "Reportes" (solo Administrador)
2. Selecciona el tipo de reporte
3. Haz clic en "Descargar CSV"
4. Abre el archivo en Excel o Google Sheets

## Estructura del Proyecto

```
AsitenciaEventos/
├── config/              # Configuración
│   ├── Database.php     # Clase de conexión a BD
│   ├── app.php         # Configuración general
│   ├── helpers.php     # Funciones auxiliares
│   └── database.php    # Credenciales de BD
├── models/             # Modelos de datos
│   ├── User.php
│   ├── Event.php
│   ├── Registration.php
│   └── Checkin.php
├── views/              # Vistas
│   ├── auth/          # Login/Logout
│   ├── events/        # Gestión de eventos
│   ├── registrations/ # Gestión de registros
│   ├── qr/            # Escaneo y validación QR
│   ├── reports/       # Reportes y exportación
│   └── layouts/       # Plantillas
├── database/           # Scripts SQL
│   └── schema.sql     # Schema de la base de datos
├── index.php          # Punto de entrada
└── README.md          # Este archivo
```

## Arquitectura

### Base de Datos
- **users**: Usuarios del sistema con roles
- **events**: Eventos con capacidad y fecha
- **registrations**: Registros de asistentes con tokens QR
- **checkins**: Check-ins realizados (único por registro)

### Seguridad
- PDO con prepared statements
- Tokens CSRF en formularios
- Password hashing con bcrypt
- Validación de entrada
- Control de acceso basado en roles

### Flujo de Trabajo
1. Usuario se autentica
2. Crea evento con capacidad definida
3. Registra asistentes (genera QR automáticamente)
4. Asistentes presentan QR en la entrada
5. Sistema valida y registra check-in único
6. Exporta reportes en CSV

## Tecnologías Utilizadas

- **Backend**: PHP 8 (POO)
- **Base de Datos**: MySQL 8
- **Frontend**: HTML5, CSS3, JavaScript
- **QR Scanner**: html5-qrcode library
- **QR Generator**: API de QR Server
- **Seguridad**: PDO, bcrypt, CSRF tokens

## Soporte y Contribuciones

Para reportar problemas o sugerir mejoras, abre un issue en GitHub.

## Licencia

Ver archivo LICENSE para más detalles.

## Autor

Daniel C. Ramirez
