<?php
/**
 * Login Page
 */

require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../models/User.php';

secure_session_start();

// Redirect if already logged in
if (is_logged_in()) {
    redirect('/views/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = 'Token de seguridad inválido. Por favor, intenta de nuevo.';
    } else {
        $username = sanitize_input($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = 'Por favor, completa todos los campos.';
        } else {
            $userModel = new User();
            $user = $userModel->authenticate($username, $password);

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user'] = $user;
                redirect('/views/dashboard.php');
            } else {
                $error = 'Usuario o contraseña incorrectos.';
            }
        }
    }
}

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Sistema de Asistencia</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 450px;
            padding: 3rem;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-title {
            font-size: 2rem;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .login-subtitle {
            color: #666;
            font-size: 1rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
            background: #fed7d7;
            color: #742a2a;
            border-left: 4px solid #f56565;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            background: #667eea;
            color: white;
        }

        .btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .login-footer {
            margin-top: 2rem;
            text-align: center;
            color: #666;
            font-size: 0.9rem;
        }

        .credentials-info {
            background: #e6f2ff;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1.5rem;
            font-size: 0.85rem;
        }

        .credentials-info strong {
            display: block;
            margin-bottom: 0.5rem;
            color: #2c5282;
        }

        .credentials-info code {
            background: white;
            padding: 0.2rem 0.4rem;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1 class="login-title">📋 Asistencia Eventos</h1>
            <p class="login-subtitle">Sistema de gestión con códigos QR</p>
        </div>

        <?php if ($error): ?>
            <div class="alert"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="form-group">
                <label class="form-label" for="username">Usuario</label>
                <input type="text" id="username" name="username" class="form-control" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Contraseña</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>

            <button type="submit" class="btn">Iniciar Sesión</button>
        </form>

        <div class="credentials-info">
            <strong>Credenciales de prueba:</strong>
            Admin: <code>admin / admin123</code><br>
            Operador: <code>operador1 / admin123</code><br>
            Asistente: <code>asistente1 / admin123</code>
        </div>

        <div class="login-footer">
            <p>&copy; <?php echo date('Y'); ?> Sistema de Asistencia a Eventos</p>
        </div>
    </div>
</body>
</html>
