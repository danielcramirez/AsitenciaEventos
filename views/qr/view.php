<?php
/**
 * View QR Code
 */

require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../models/Registration.php';

secure_session_start();
require_login();

$token = $_GET['token'] ?? '';

if (empty($token)) {
    set_flash('error', 'Token de QR no válido.');
    redirect('/views/dashboard.php');
}

$registrationModel = new Registration();
$registration = $registrationModel->getByQrToken($token);

if (!$registration) {
    set_flash('error', 'Registro no encontrado.');
    redirect('/views/dashboard.php');
}

$page_title = 'Código QR';
$qr_url = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($token);
$checkin_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/views/qr/validate.php?token=' . urlencode($token);

include __DIR__ . '/../layouts/header.php';
?>

<div class="card">
    <div class="card-header">
        <h1 class="card-title">Código QR de Registro</h1>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        <div style="text-align: center;">
            <h3 style="margin-bottom: 1rem;">Código QR</h3>
            <img src="<?php echo $qr_url; ?>" alt="QR Code" style="max-width: 100%; border: 2px solid #e0e0e0; border-radius: 10px; padding: 1rem;">
            <p style="margin-top: 1rem; color: #666; font-size: 0.9rem;">
                Escanea este código QR en la entrada del evento
            </p>
            <div style="margin-top: 1rem;">
                <a href="<?php echo $qr_url; ?>" download="qr_<?php echo $token; ?>.png" class="btn btn-primary">💾 Descargar QR</a>
            </div>
        </div>

        <div>
            <h3 style="margin-bottom: 1rem;">Información del Registro</h3>
            <div style="background: #f7fafc; padding: 1.5rem; border-radius: 8px; margin-bottom: 1rem;">
                <div style="margin-bottom: 1rem;">
                    <strong>Evento:</strong><br>
                    <span style="font-size: 1.2rem; color: #667eea;"><?php echo htmlspecialchars($registration['event_name']); ?></span>
                </div>
                <div style="margin-bottom: 1rem;">
                    <strong>Asistente:</strong><br>
                    <?php echo htmlspecialchars($registration['attendee_name']); ?>
                </div>
                <div style="margin-bottom: 1rem;">
                    <strong>Email:</strong><br>
                    <?php echo htmlspecialchars($registration['attendee_email']); ?>
                </div>
                <div style="margin-bottom: 1rem;">
                    <strong>Fecha del Evento:</strong><br>
                    <?php echo format_date($registration['event_date']); ?>
                </div>
                <div style="margin-bottom: 1rem;">
                    <strong>Ubicación:</strong><br>
                    <?php echo htmlspecialchars($registration['location']); ?>
                </div>
                <div>
                    <strong>Estado de Check-in:</strong><br>
                    <?php if ($registration['checked_in']): ?>
                        <span class="badge badge-success">✓ Check-in realizado el <?php echo format_date($registration['checkin_time']); ?></span>
                    <?php else: ?>
                        <span class="badge badge-warning">⏳ Pendiente de check-in</span>
                    <?php endif; ?>
                </div>
            </div>

            <div style="background: #e6f2ff; padding: 1rem; border-radius: 8px; font-size: 0.9rem;">
                <strong>Token de Validación:</strong><br>
                <code style="background: white; padding: 0.5rem; display: block; margin-top: 0.5rem; border-radius: 4px; word-break: break-all; font-family: monospace; font-size: 0.8rem;">
                    <?php echo htmlspecialchars($token); ?>
                </code>
            </div>
        </div>
    </div>

    <div style="margin-top: 2rem; text-align: center;">
        <a href="/views/events/view.php?id=<?php echo $registration['event_id']; ?>" class="btn btn-primary">Ver Detalles del Evento</a>
        <?php if (!$registration['checked_in']): ?>
            <a href="<?php echo $checkin_url; ?>" class="btn btn-success">Realizar Check-in</a>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
