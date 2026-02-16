<?php
/**
 * Event List
 */

require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../models/Event.php';

secure_session_start();
require_any_role(['Administrador', 'Operador']);

$page_title = 'Lista de Eventos';

$eventModel = new Event();
$events = $eventModel->getAll();

include __DIR__ . '/../layouts/header.php';
?>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h1 class="card-title">Eventos</h1>
        <a href="/views/events/create.php" class="btn btn-primary">➕ Crear Evento</a>
    </div>

    <?php if (empty($events)): ?>
        <p style="text-align: center; color: #666; padding: 2rem;">No hay eventos registrados.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Fecha</th>
                    <th>Ubicación</th>
                    <th>Capacidad</th>
                    <th>Registrados</th>
                    <th>Disponibles</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $event): ?>
                <tr>
                    <td><?php echo $event['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($event['name']); ?></strong></td>
                    <td><?php echo format_date($event['event_date']); ?></td>
                    <td><?php echo htmlspecialchars($event['location']); ?></td>
                    <td><?php echo $event['max_capacity']; ?></td>
                    <td><?php echo $event['current_registrations']; ?></td>
                    <td>
                        <?php 
                        $available = $event['available_spots'];
                        if ($available > 0): ?>
                            <span class="badge badge-success"><?php echo $available; ?></span>
                        <?php else: ?>
                            <span class="badge badge-danger">0</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($event['active']): ?>
                            <span class="badge badge-success">Activo</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="/views/events/view.php?id=<?php echo $event['id']; ?>" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Ver</a>
                        <a href="/views/registrations/create.php?event_id=<?php echo $event['id']; ?>" class="btn btn-success" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Registrar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
