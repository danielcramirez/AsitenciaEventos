<?php
/**
 * Reports Index
 */

require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../models/Event.php';

secure_session_start();
require_role('Administrador');

$page_title = 'Reportes';

$eventModel = new Event();
$events = $eventModel->getAll();

include __DIR__ . '/../layouts/header.php';
?>

<div class="card">
    <div class="card-header">
        <h1 class="card-title">📊 Reportes</h1>
    </div>

    <p style="margin-bottom: 2rem; color: #666;">
        Genera y exporta reportes de eventos, registros y check-ins en formato CSV.
    </p>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 2rem; border-radius: 10px;">
            <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">📋 Todos los Eventos</h3>
            <p style="opacity: 0.9; margin-bottom: 1.5rem;">Exporta la lista completa de eventos con sus estadísticas.</p>
            <a href="/views/reports/export.php?type=events" class="btn" style="background: white; color: #667eea; width: 100%;">
                💾 Descargar CSV
            </a>
        </div>

        <div style="background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); color: white; padding: 2rem; border-radius: 10px;">
            <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">👥 Todos los Registros</h3>
            <p style="opacity: 0.9; margin-bottom: 1.5rem;">Exporta todos los registros de asistentes de todos los eventos.</p>
            <a href="/views/reports/export.php?type=all_registrations" class="btn" style="background: white; color: #48bb78; width: 100%;">
                💾 Descargar CSV
            </a>
        </div>

        <div style="background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%); color: white; padding: 2rem; border-radius: 10px;">
            <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">✅ Todos los Check-ins</h3>
            <p style="opacity: 0.9; margin-bottom: 1.5rem;">Exporta todos los check-ins realizados en todos los eventos.</p>
            <a href="/views/reports/export.php?type=all_checkins" class="btn" style="background: white; color: #4299e1; width: 100%;">
                💾 Descargar CSV
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Reportes por Evento</h2>
    </div>

    <?php if (empty($events)): ?>
        <p style="text-align: center; color: #666; padding: 2rem;">No hay eventos disponibles.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Evento</th>
                    <th>Fecha</th>
                    <th>Registrados</th>
                    <th>Check-ins</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $event): 
                    $stats = $eventModel->getStats($event['id']);
                ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($event['name']); ?></strong></td>
                    <td><?php echo format_date($event['event_date']); ?></td>
                    <td><?php echo $event['current_registrations']; ?></td>
                    <td><?php echo $stats['total_checkins']; ?></td>
                    <td>
                        <a href="/views/reports/export.php?type=registrations&event_id=<?php echo $event['id']; ?>" 
                           class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                            📥 Registros CSV
                        </a>
                        <a href="/views/reports/export.php?type=checkins&event_id=<?php echo $event['id']; ?>" 
                           class="btn btn-success" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                            📥 Check-ins CSV
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
