<?php
/**
 * Create Event
 */

require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../models/Event.php';

secure_session_start();
require_any_role(['Administrador', 'Operador']);

$page_title = 'Crear Evento';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = 'Token de seguridad inválido.';
    } else {
        $data = [
            'name' => sanitize_input($_POST['name'] ?? ''),
            'description' => sanitize_input($_POST['description'] ?? ''),
            'location' => sanitize_input($_POST['location'] ?? ''),
            'event_date' => $_POST['event_date'] ?? '',
            'max_capacity' => intval($_POST['max_capacity'] ?? 0)
        ];

        // Validation
        if (empty($data['name']) || empty($data['event_date']) || $data['max_capacity'] <= 0) {
            $error = 'Por favor, completa todos los campos requeridos.';
        } else {
            $eventModel = new Event();
            if ($eventModel->create($data, $_SESSION['user_id'])) {
                set_flash('success', 'Evento creado exitosamente.');
                redirect('/views/events/list.php');
            } else {
                $error = 'Error al crear el evento.';
            }
        }
    }
}

$csrf_token = generate_csrf_token();
include __DIR__ . '/../layouts/header.php';
?>

<div class="card">
    <div class="card-header">
        <h1 class="card-title">Crear Nuevo Evento</h1>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

        <div class="form-group">
            <label class="form-label" for="name">Nombre del Evento *</label>
            <input type="text" id="name" name="name" class="form-control" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label class="form-label" for="description">Descripción</label>
            <textarea id="description" name="description" class="form-control" rows="4"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label" for="location">Ubicación *</label>
            <input type="text" id="location" name="location" class="form-control" required value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label class="form-label" for="event_date">Fecha y Hora del Evento *</label>
            <input type="datetime-local" id="event_date" name="event_date" class="form-control" required value="<?php echo $_POST['event_date'] ?? ''; ?>">
        </div>

        <div class="form-group">
            <label class="form-label" for="max_capacity">Capacidad Máxima *</label>
            <input type="number" id="max_capacity" name="max_capacity" class="form-control" min="1" required value="<?php echo $_POST['max_capacity'] ?? ''; ?>">
        </div>

        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">Crear Evento</button>
            <a href="/views/events/list.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
