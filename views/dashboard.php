<?php
/**
 * Dashboard
 */

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../models/Event.php';
require_once __DIR__ . '/../models/Registration.php';
require_once __DIR__ . '/../models/Checkin.php';

secure_session_start();
require_login();

$page_title = 'Dashboard';

$eventModel = new Event();
$events = $eventModel->getAll(true);

// Get statistics
$total_events = count($events);
$upcoming_events = 0;
$total_registrations = 0;
$total_checkins = 0;

foreach ($events as $event) {
    if (strtotime($event['event_date']) > time()) {
        $upcoming_events++;
    }
    $total_registrations += $event['current_registrations'];
    
    $checkinModel = new Checkin();
    $total_checkins += $checkinModel->getCountByEvent($event['id']);
}

include __DIR__ . '/layouts/header.php';
?>

<div class="card">
    <div class="card-header">
        <h1 class="card-title">Dashboard</h1>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 2rem; border-radius: 10px;">
            <div style="font-size: 3rem; font-weight: 700; margin-bottom: 0.5rem;"><?php echo $total_events; ?></div>
            <div style="font-size: 1.1rem; opacity: 0.9;">Eventos Totales</div>
        </div>

        <div style="background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); color: white; padding: 2rem; border-radius: 10px;">
            <div style="font-size: 3rem; font-weight: 700; margin-bottom: 0.5rem;"><?php echo $upcoming_events; ?></div>
            <div style="font-size: 1.1rem; opacity: 0.9;">Eventos Próximos</div>
        </div>

        <div style="background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%); color: white; padding: 2rem; border-radius: 10px;">
            <div style="font-size: 3rem; font-weight: 700; margin-bottom: 0.5rem;"><?php echo $total_registrations; ?></div>
            <div style="font-size: 1.1rem; opacity: 0.9;">Registros Totales</div>
        </div>

        <div style="background: linear-gradient(135deg, #f6ad55 0%, #ed8936 100%); color: white; padding: 2rem; border-radius: 10px;">
            <div style="font-size: 3rem; font-weight: 700; margin-bottom: 0.5rem;"><?php echo $total_checkins; ?></div>
            <div style="font-size: 1.1rem; opacity: 0.9;">Check-ins Realizados</div>
        </div>
    </div>

    <h2 style="margin-bottom: 1rem; font-size: 1.5rem; color: #333;">Accesos Rápidos</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
        <?php if (has_any_role(['Administrador', 'Operador'])): ?>
        <a href="/views/events/create.php" class="btn btn-primary">➕ Crear Evento</a>
        <a href="/views/events/list.php" class="btn btn-primary">📋 Ver Eventos</a>
        <a href="/views/registrations/list.php" class="btn btn-primary">👥 Ver Registros</a>
        <?php endif; ?>
        <a href="/views/qr/scanner.php" class="btn btn-success">📷 Escanear QR</a>
        <?php if (has_role('Administrador')): ?>
        <a href="/views/reports/index.php" class="btn btn-secondary">📊 Reportes</a>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($events)): ?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Eventos Activos</h2>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Evento</th>
                <th>Fecha</th>
                <th>Ubicación</th>
                <th>Capacidad</th>
                <th>Registrados</th>
                <th>Check-ins</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($events as $event): 
                $stats = $eventModel->getStats($event['id']);
                $is_upcoming = strtotime($event['event_date']) > time();
            ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($event['name']); ?></strong></td>
                <td><?php echo format_date($event['event_date']); ?></td>
                <td><?php echo htmlspecialchars($event['location']); ?></td>
                <td><?php echo $event['max_capacity']; ?></td>
                <td><?php echo $event['current_registrations']; ?></td>
                <td><?php echo $stats['total_checkins']; ?></td>
                <td>
                    <?php if ($is_upcoming): ?>
                        <span class="badge badge-success">Próximo</span>
                    <?php else: ?>
                        <span class="badge badge-info">Finalizado</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php include __DIR__ . '/layouts/footer.php'; ?>
