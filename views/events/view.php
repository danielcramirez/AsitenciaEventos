<?php
/**
 * View Event Details
 */

require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../models/Event.php';
require_once __DIR__ . '/../../models/Registration.php';
require_once __DIR__ . '/../../models/Checkin.php';

secure_session_start();
require_any_role(['Administrador', 'Operador']);

$event_id = intval($_GET['id'] ?? 0);

if (!$event_id) {
    set_flash('error', 'Evento no encontrado.');
    redirect('/views/events/list.php');
}

$eventModel = new Event();
$event = $eventModel->getById($event_id);

if (!$event) {
    set_flash('error', 'Evento no encontrado.');
    redirect('/views/events/list.php');
}

$registrationModel = new Registration();
$registrations = $registrationModel->getByEvent($event_id);
$stats = $eventModel->getStats($event_id);

$page_title = 'Detalles del Evento';
include __DIR__ . '/../layouts/header.php';
?>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h1 class="card-title"><?php echo htmlspecialchars($event['name']); ?></h1>
        <a href="/views/events/list.php" class="btn btn-secondary">← Volver</a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
        <div style="background: #f7fafc; padding: 1.5rem; border-radius: 8px;">
            <div style="font-size: 0.9rem; color: #666; margin-bottom: 0.5rem;">Capacidad Total</div>
            <div style="font-size: 2rem; font-weight: 700; color: #333;"><?php echo $event['max_capacity']; ?></div>
        </div>
        <div style="background: #e6f2ff; padding: 1.5rem; border-radius: 8px;">
            <div style="font-size: 0.9rem; color: #666; margin-bottom: 0.5rem;">Registrados</div>
            <div style="font-size: 2rem; font-weight: 700; color: #4299e1;"><?php echo $event['current_registrations']; ?></div>
        </div>
        <div style="background: #d4edda; padding: 1.5rem; border-radius: 8px;">
            <div style="font-size: 0.9rem; color: #666; margin-bottom: 0.5rem;">Check-ins</div>
            <div style="font-size: 2rem; font-weight: 700; color: #48bb78;"><?php echo $stats['total_checkins']; ?></div>
        </div>
        <div style="background: #fff3cd; padding: 1.5rem; border-radius: 8px;">
            <div style="font-size: 0.9rem; color: #666; margin-bottom: 0.5rem;">Disponibles</div>
            <div style="font-size: 2rem; font-weight: 700; color: #f6ad55;"><?php echo $stats['available_spots']; ?></div>
        </div>
    </div>

    <div style="margin-bottom: 2rem;">
        <h3 style="margin-bottom: 1rem; font-size: 1.2rem;">Información del Evento</h3>
        <div style="display: grid; gap: 1rem;">
            <div><strong>Descripción:</strong> <?php echo nl2br(htmlspecialchars($event['description'])); ?></div>
            <div><strong>Ubicación:</strong> <?php echo htmlspecialchars($event['location']); ?></div>
            <div><strong>Fecha:</strong> <?php echo format_date($event['event_date']); ?></div>
            <div><strong>Creado por:</strong> <?php echo htmlspecialchars($event['creator_name']); ?></div>
            <div><strong>Estado:</strong> 
                <?php if ($event['active']): ?>
                    <span class="badge badge-success">Activo</span>
                <?php else: ?>
                    <span class="badge badge-danger">Inactivo</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div style="display: flex; gap: 1rem; margin-bottom: 2rem;">
        <a href="/views/registrations/create.php?event_id=<?php echo $event['id']; ?>" class="btn btn-success">➕ Registrar Asistente</a>
        <a href="/views/reports/export.php?type=registrations&event_id=<?php echo $event['id']; ?>" class="btn btn-primary">📥 Exportar Registros CSV</a>
        <a href="/views/reports/export.php?type=checkins&event_id=<?php echo $event['id']; ?>" class="btn btn-primary">📥 Exportar Check-ins CSV</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Registros (<?php echo count($registrations); ?>)</h2>
    </div>

    <?php if (empty($registrations)): ?>
        <p style="text-align: center; color: #666; padding: 2rem;">No hay registros para este evento.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Fecha Registro</th>
                    <th>Check-in</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registrations as $reg): ?>
                <tr>
                    <td><?php echo htmlspecialchars($reg['attendee_name']); ?></td>
                    <td><?php echo htmlspecialchars($reg['attendee_email']); ?></td>
                    <td><?php echo htmlspecialchars($reg['attendee_phone'] ?? '-'); ?></td>
                    <td><?php echo format_date($reg['registration_date']); ?></td>
                    <td>
                        <?php if ($reg['checked_in']): ?>
                            <span class="badge badge-success">✓ <?php echo format_date($reg['checkin_time']); ?></span>
                        <?php else: ?>
                            <span class="badge badge-warning">Pendiente</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="/views/qr/view.php?token=<?php echo $reg['qr_token']; ?>" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;" target="_blank">Ver QR</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
