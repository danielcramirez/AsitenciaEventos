<?php
/**
 * Create Registration
 */

require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../models/Event.php';
require_once __DIR__ . '/../../models/Registration.php';

secure_session_start();
require_any_role(['Administrador', 'Operador']);

$page_title = 'Registrar Asistente';
$error = '';

$event_id = intval($_GET['event_id'] ?? 0);
$eventModel = new Event();
$event = null;

if ($event_id) {
    $event = $eventModel->getById($event_id);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = 'Token de seguridad inválido.';
    } else {
        $data = [
            'event_id' => intval($_POST['event_id'] ?? 0),
            'attendee_name' => sanitize_input($_POST['attendee_name'] ?? ''),
            'attendee_email' => sanitize_input($_POST['attendee_email'] ?? ''),
            'attendee_phone' => sanitize_input($_POST['attendee_phone'] ?? '')
        ];

        // Validation
        if (empty($data['event_id']) || empty($data['attendee_name']) || empty($data['attendee_email'])) {
            $error = 'Por favor, completa todos los campos requeridos.';
        } elseif (!filter_var($data['attendee_email'], FILTER_VALIDATE_EMAIL)) {
            $error = 'Email inválido.';
        } else {
            $registrationModel = new Registration();
            
            // Check if email is already registered for this event
            if ($registrationModel->isEmailRegistered($data['event_id'], $data['attendee_email'])) {
                $error = 'Este email ya está registrado para el evento.';
            } elseif (!$eventModel->hasCapacity($data['event_id'])) {
                $error = 'El evento ha alcanzado su capacidad máxima.';
            } else {
                $result = $registrationModel->create($data, $_SESSION['user_id']);
                
                if ($result) {
                    // Increment event registration count
                    $eventModel->incrementRegistrations($data['event_id']);
                    
                    set_flash('success', 'Asistente registrado exitosamente. ID: ' . $result['id']);
                    redirect('/views/qr/view.php?token=' . $result['qr_token']);
                } else {
                    $error = 'Error al registrar el asistente.';
                }
            }
        }
    }
}

$csrf_token = generate_csrf_token();
$events = $eventModel->getAll(true);

include __DIR__ . '/../layouts/header.php';
?>

<div class="card">
    <div class="card-header">
        <h1 class="card-title">Registrar Asistente</h1>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

        <div class="form-group">
            <label class="form-label" for="event_id">Evento *</label>
            <select id="event_id" name="event_id" class="form-control" required>
                <option value="">-- Seleccionar Evento --</option>
                <?php foreach ($events as $ev): ?>
                    <option value="<?php echo $ev['id']; ?>" <?php echo ($event && $event['id'] == $ev['id']) || ($event_id == $ev['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($ev['name']); ?> - <?php echo format_date($ev['event_date']); ?> 
                        (Disponibles: <?php echo $ev['available_spots']; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="attendee_name">Nombre Completo *</label>
            <input type="text" id="attendee_name" name="attendee_name" class="form-control" required value="<?php echo htmlspecialchars($_POST['attendee_name'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label class="form-label" for="attendee_email">Email *</label>
            <input type="email" id="attendee_email" name="attendee_email" class="form-control" required value="<?php echo htmlspecialchars($_POST['attendee_email'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label class="form-label" for="attendee_phone">Teléfono</label>
            <input type="tel" id="attendee_phone" name="attendee_phone" class="form-control" value="<?php echo htmlspecialchars($_POST['attendee_phone'] ?? ''); ?>">
        </div>

        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">Registrar Asistente</button>
            <a href="/views/events/list.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
