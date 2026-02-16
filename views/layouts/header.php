<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Sistema de Asistencia a Eventos'; ?></title>
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
            flex-direction: column;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1rem 2rem;
        }

        .navbar-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: #667eea;
            text-decoration: none;
        }

        .navbar-menu {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .navbar-menu a {
            color: #333;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .navbar-menu a:hover {
            color: #667eea;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding-left: 2rem;
            border-left: 2px solid #e0e0e0;
        }

        .user-name {
            font-weight: 600;
            color: #555;
        }

        .user-role {
            background: #667eea;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
        }

        .container {
            flex: 1;
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
            width: 100%;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .card-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }

        .card-title {
            font-size: 1.75rem;
            color: #333;
            font-weight: 700;
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-success {
            background: #48bb78;
            color: white;
        }

        .btn-success:hover {
            background: #38a169;
        }

        .btn-danger {
            background: #f56565;
            color: white;
        }

        .btn-danger:hover {
            background: #e53e3e;
        }

        .btn-secondary {
            background: #718096;
            color: white;
        }

        .btn-secondary:hover {
            background: #4a5568;
        }

        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: #c6f6d5;
            color: #22543d;
            border-left: 4px solid #48bb78;
        }

        .alert-danger {
            background: #fed7d7;
            color: #742a2a;
            border-left: 4px solid #f56565;
        }

        .alert-info {
            background: #bee3f8;
            color: #2c5282;
            border-left: 4px solid #4299e1;
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
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background: #f7fafc;
            font-weight: 600;
            color: #4a5568;
        }

        tr:hover {
            background: #f7fafc;
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .badge-success {
            background: #c6f6d5;
            color: #22543d;
        }

        .badge-danger {
            background: #fed7d7;
            color: #742a2a;
        }

        .badge-warning {
            background: #feebc8;
            color: #744210;
        }

        .badge-info {
            background: #bee3f8;
            color: #2c5282;
        }
    </style>
</head>
<body>
    <?php if (is_logged_in()): ?>
    <nav class="navbar">
        <div class="navbar-content">
            <a href="/views/dashboard.php" class="navbar-brand">📋 Asistencia Eventos</a>
            <div class="navbar-menu">
                <a href="/views/dashboard.php">Dashboard</a>
                <?php if (has_any_role(['Administrador', 'Operador'])): ?>
                <a href="/views/events/list.php">Eventos</a>
                <a href="/views/registrations/list.php">Registros</a>
                <?php endif; ?>
                <a href="/views/qr/scanner.php">Escanear QR</a>
                <?php if (has_role('Administrador')): ?>
                <a href="/views/reports/index.php">Reportes</a>
                <?php endif; ?>
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars(get_logged_user()['full_name']); ?></span>
                    <span class="user-role"><?php echo htmlspecialchars(get_logged_user()['role']); ?></span>
                    <a href="/views/auth/logout.php" class="btn btn-secondary" style="padding: 0.5rem 1rem;">Salir</a>
                </div>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <div class="container">
        <?php
        // Display flash messages
        if ($success = get_flash('success')): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if ($error = get_flash('error')): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($info = get_flash('info')): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($info); ?></div>
        <?php endif; ?>
