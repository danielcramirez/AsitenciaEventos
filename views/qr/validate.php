<?php
/**
 * Validate QR Code and Check-in
 */

require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../models/Registration.php';
require_once __DIR__ . '/../../models/Checkin.php';

secure_session_start();
require_login();

$token = $_GET['token'] ?? '';
$page_title = 'Validación de QR';

$registrationModel = new Registration();
$checkinModel = new Checkin();

$registration = null;
$error = '';
$success = '';
$can_checkin = false;

if (empty($token)) {
    $error = 'Token de QR no válido.';
} else {
    $registration = $registrationModel->getByQrToken($token);

    if (!$registration) {
        $error = 'Registro no encontrado. El código QR es inválido.';
    } elseif (!$registration['event_active']) {
        $error = 'El evento no está activo.';
    } elseif ($registration['checked_in']) {
        $error = 'Este asistente ya realizó check-in el ' . format_date($registration['checkin_time']) . '.';
    } else {
        $can_checkin = true;
        
        // Perform check-in if requested
        if (isset($_POST['perform_checkin']) && verify_csrf_token($_POST['csrf_token'])) {
            if ($checkinModel->create($registration['id'], $registration['event_id'], $_SESSION['user_id'])) {
                $success = '¡Check-in exitoso!';
                $registration['checked_in'] = true;
                $can_checkin = false;
                
                // Refresh registration data
                $registration = $registrationModel->getByQrToken($token);
            } else {
                $error = 'Error al realizar check-in. Puede que ya se haya realizado.';
            }
        }
    }
}

$csrf_token = generate_csrf_token();

include __DIR__ . '/../layouts/header.php';
?>

<div class="card">
    <div class="card-header">
        <h1 class="card-title">Validación de Código QR</h1>
    </div>

    <?php if ($success): ?>
        <div style="background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); color: white; padding: 3rem; border-radius: 10px; text-align: center; margin-bottom: 2rem;">
            <div style="font-size: 5rem; margin-bottom: 1rem;">✅</div>
            <h2 style="font-size: 2rem; margin-bottom: 0.5rem;">¡Check-in Exitoso!</h2>
            <p style="font-size: 1.2rem; opacity: 0.9;">El asistente ha sido registrado correctamente</p>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div style="background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%); color: white; padding: 3rem; border-radius: 10px; text-align: center; margin-bottom: 2rem;">
            <div style="font-size: 5rem; margin-bottom: 1rem;">❌</div>
            <h2 style="font-size: 2rem; margin-bottom: 0.5rem;">Error de Validación</h2>
            <p style="font-size: 1.2rem; opacity: 0.9;"><?php echo htmlspecialchars($error); ?></p>
        </div>
    <?php endif; ?>

    <?php if ($registration): ?>
        <div style="background: #f7fafc; padding: 2rem; border-radius: 10px; margin-bottom: 2rem;">
            <h3 style="margin-bottom: 1.5rem; font-size: 1.5rem;">Información del Registro</h3>
            
            <div style="display: grid; gap: 1.5rem;">
                <div style="background: white; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #667eea;">
                    <strong style="color: #667eea;">Evento:</strong><br>
                    <span style="font-size: 1.3rem; color: #333; font-weight: 600;"><?php echo htmlspecialchars($registration['event_name']); ?></span>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div style="background: white; padding: 1.5rem; border-radius: 8px;">
                        <strong>Asistente:</strong><br>
                        <span style="font-size: 1.1rem; color: #333;"><?php echo htmlspecialchars($registration['attendee_name']); ?></span>
                    </div>
                    <div style="background: white; padding: 1.5rem; border-radius: 8px;">
                        <strong>Email:</strong><br>
                        <span style="font-size: 1.1rem; color: #333;"><?php echo htmlspecialchars($registration['attendee_email']); ?></span>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div style="background: white; padding: 1.5rem; border-radius: 8px;">
                        <strong>Fecha del Evento:</strong><br>
                        <span style="font-size: 1.1rem; color: #333;"><?php echo format_date($registration['event_date']); ?></span>
                    </div>
                    <div style="background: white; padding: 1.5rem; border-radius: 8px;">
                        <strong>Ubicación:</strong><br>
                        <span style="font-size: 1.1rem; color: #333;"><?php echo htmlspecialchars($registration['location']); ?></span>
                    </div>
                </div>

                <div style="background: white; padding: 1.5rem; border-radius: 8px;">
                    <strong>Estado:</strong><br>
                    <?php if ($registration['checked_in']): ?>
                        <span class="badge badge-success" style="font-size: 1.1rem; padding: 0.5rem 1rem; margin-top: 0.5rem;">
                            ✓ Check-in realizado el <?php echo format_date($registration['checkin_time']); ?>
                        </span>
                    <?php else: ?>
                        <span class="badge badge-warning" style="font-size: 1.1rem; padding: 0.5rem 1rem; margin-top: 0.5rem;">
                            ⏳ Pendiente de check-in
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ($can_checkin): ?>
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="perform_checkin" value="1">
                <button type="submit" class="btn btn-success" style="width: 100%; font-size: 1.2rem; padding: 1.5rem;">
                    ✓ Confirmar Check-in
                </button>
            </form>
        <?php endif; ?>
    <?php endif; ?>

    <div style="margin-top: 2rem; text-align: center;">
        <a href="/views/qr/scanner.php" class="btn btn-primary">← Escanear Otro Código</a>
        <a href="/views/dashboard.php" class="btn btn-secondary">Ir al Dashboard</a>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
