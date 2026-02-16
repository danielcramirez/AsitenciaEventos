<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-container {
            max-width: 400px;
            margin: auto;
        }
        .login-card {
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card login-card">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-calendar-event text-primary" style="font-size: 3rem;"></i>
                        <h3 class="mt-3"><?php echo APP_NAME; ?></h3>
                        <p class="text-muted">Inicie sesión para continuar</p>
                    </div>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="bi bi-exclamation-triangle"></i>
                            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?php echo BASE_URL; ?>/index.php?action=login_process">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">
                                <i class="bi bi-person"></i> Usuario
                            </label>
                            <input type="text" class="form-control" id="username" name="username" required autofocus>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label">
                                <i class="bi bi-lock"></i> Contraseña
                            </label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                        </button>
                    </form>
                    
                    <div class="text-center mt-4">
                        <hr>
                        <p class="text-muted mb-0">
                            <small>¿Usuario nuevo? <a href="<?php echo BASE_URL; ?>/index.php?action=qr_query">Consultar mi QR</a></small>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-3 text-white">
                <small>Sistema de gestión de asistencia a eventos v<?php echo APP_VERSION; ?></small>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
