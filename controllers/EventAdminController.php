<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/bootstrap.php';
require_once __DIR__ . '/../models/EventModel.php';
require_once __DIR__ . '/../models/AuditLogModel.php';

class EventAdminController {
  public static function index(): void {
    // Requerir autenticación y rol admin
    require_auth();
    require_role(ROLE_ADMIN);
    
    $error = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      csrf_check();
      $nombre = trim((string)($_POST['nombre'] ?? ''));
      $lugar = trim((string)($_POST['lugar'] ?? ''));
      $fi = (string)($_POST['fecha_inicio'] ?? '');
      $ff = (string)($_POST['fecha_fin'] ?? '');
      $cupo = (int)($_POST['cupo'] ?? 0);
      $estado = (string)($_POST['estado'] ?? 'DRAFT');

      $start = DateTime::createFromFormat('Y-m-d\TH:i', $fi);
      $end = DateTime::createFromFormat('Y-m-d\TH:i', $ff);

      if ($nombre === '' || strlen($nombre) > 180) {
        $error = 'Nombre inválido.';
      } elseif ($lugar !== '' && strlen($lugar) > 180) {
        $error = 'Lugar inválido.';
      } elseif (!$start || !$end || $start > $end) {
        $error = 'Fechas inválidas.';
      } elseif ($cupo < 0) {
        $error = 'Cupo inválido.';
      } elseif (!in_array($estado, ['DRAFT','PUBLISHED','CLOSED'], true)) {
        $error = 'Estado inválido.';
      } else {
        EventModel::create([
          'nombre' => $nombre,
          'lugar' => $lugar,
          'fecha_inicio' => $start->format('Y-m-d H:i:s'),
          'fecha_fin' => $end->format('Y-m-d H:i:s'),
          'cupo' => $cupo,
          'estado' => $estado
        ]);
        
        // Log de auditoría
        Logger::getInstance()->info('Evento creado', [
          'user_id' => PermissionManager::getCurrentUserId(),
          'event_name' => $nombre,
          'event_date' => $start->format('Y-m-d'),
        ]);
        
        AuditLogModel::log([
          'user_id' => PermissionManager::getCurrentUserId(),
          'action' => 'create_event',
          'action_type' => 'CREATE',
          'resource' => 'events',
          'new_data' => ['nombre' => $nombre, 'lugar' => $lugar],
          'status' => 'success',
        ]);
        
        header('Location: ' . BASE_URL . '/admin_eventos');
        exit;
      }
    }

    $events = EventModel::all();
    render_view('layout/header', ['title' => 'Administrar eventos']);
    render_view('events/admin', ['events' => $events, 'error' => $error]);
    render_view('layout/footer');
  }
}
