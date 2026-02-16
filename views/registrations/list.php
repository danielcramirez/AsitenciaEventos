<?php
/**
 * Registration List
 */

require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../models/Event.php';
require_once __DIR__ . '/../../models/Registration.php';

secure_session_start();
require_any_role(['Administrador', 'Operador']);

$page_title = 'Lista de Registros';

$eventModel = new Event();
$registrationModel = new Registration();

$event_id = intval($_GET['event_id'] ?? 0);
$events = $eventModel->getAll();

$registrations = [];
if ($event_id) {
    $registrations = $registrationModel->getByEvent($event_id);
}

include __DIR__ . '/../layouts/header.php';
?>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h1 class="card-title">Registros de Asistentes</h1>
        <a href="/views/events/list.php" class="btn btn-primary">Ver Eventos</a>
    </div>

    <form method="GET" action="" style="margin-bottom: 2rem;">
        <div class="form-group">
            <label class="form-label" for="event_id">Filtrar por Evento</label>
            <select id="event_id" name="event_id" class="form-control" onchange="this.form.submit()">
                <option value="">-- Seleccionar Evento --</option>
                <?php foreach ($events as $event): ?>
                    <option value="<?php echo $event['id']; ?>" <?php echo $event_id == $event['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($event['name']); ?> - <?php echo format_date($event['event_date']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>

    <?php if (empty($registrations)): ?>
        <p style="text-align: center; color: #666; padding: 2rem;">
            <?php echo $event_id ? 'No hay registros para este evento.' : 'Selecciona un evento para ver sus registros.'; ?>
        </p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
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
                    <td><?php echo $reg['id']; ?></td>
                    <td><?php echo htmlspecialchars($reg['attendee_name']); ?></td>
                    <td><?php echo htmlspecialchars($reg['attendee_email']); ?></td>
                    <td><?php echo htmlspecialchars($reg['attendee_phone'] ?? '-'); ?></td>
                    <td><?php echo format_date($reg['registration_date']); ?></td>
                    <td>
                        <?php if ($reg['checked_in']): ?>
                            <span class="badge badge-success">✓ Sí</span>
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
