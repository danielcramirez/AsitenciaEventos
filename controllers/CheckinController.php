<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/bootstrap.php';
require_once __DIR__ . '/../models/CheckinModel.php';
require_once __DIR__ . '/../models/AuditLogModel.php';

class CheckinController {
  public static function door(): void {
    require_permission('puerta');

    $events = db()->query("SELECT id,nombre FROM events WHERE estado='PUBLISHED' ORDER BY fecha_inicio DESC")->fetchAll();
    $event_id = (int)($_GET['event_id'] ?? ($events[0]['id'] ?? 0));

    render_view('layout/header', ['title' => 'Puerta (Check-in)']);
    render_view('checkin/door', ['events' => $events, 'event_id' => $event_id]);
    render_view('layout/footer');
  }

  public static function apiCheckin(): void {
    require_permission('puerta');

    $body = json_decode(file_get_contents('php://input'), true) ?? [];
    $token = trim((string)($body['token'] ?? ''));
    $event_id = (int)($body['event_id'] ?? 0);

    if ($token === '' || strlen($token) < TOKEN_MIN_LEN || $event_id <= 0) {
      json_response(['ok' => false, 'message' => 'Datos inválidos'], 400);
    }

    $hash = sha256($token);
    $row = CheckinModel::findByTokenHash($hash, $event_id);

    if (!$row) {
      AuditLogModel::log('checkin_invalid', (int)current_user()['id'], $event_id, []);
      json_response(['ok' => false, 'message' => 'QR inválido para este evento']);
    }
    if ($row['status'] !== 'ACTIVE') {
      AuditLogModel::log('checkin_inactive', (int)current_user()['id'], $event_id, []);
      json_response(['ok' => false, 'message' => 'Registro no activo']);
    }

    if (!empty($row['checkin_at'])) {
      AuditLogModel::log('checkin_already', (int)current_user()['id'], $event_id, ['registration_id' => (int)$row['registration_id']]);
      json_response([
        'ok' => true,
        'already' => true,
        'message' => 'YA INGRESÓ',
        'person' => ['cedula' => $row['cedula'], 'nombres' => $row['nombres'], 'apellidos' => $row['apellidos']],
        'checkin_at' => $row['checkin_at']
      ]);
    }

    try {
      CheckinModel::createCheckin((int)$row['event_id'], (int)$row['registration_id'], (int)current_user()['id']);
      AuditLogModel::log('checkin_success', (int)current_user()['id'], (int)$row['event_id'], ['registration_id' => (int)$row['registration_id']]);

      json_response([
        'ok' => true,
        'already' => false,
        'message' => 'BIENVENIDO/A',
        'person' => ['cedula' => $row['cedula'], 'nombres' => $row['nombres'], 'apellidos' => $row['apellidos']],
        'checkin_at' => date('Y-m-d H:i:s')
      ]);
    } catch (Throwable $e) {
      $checkin_at = CheckinModel::findCheckinAt((int)$row['event_id'], (int)$row['registration_id']);
      AuditLogModel::log('checkin_already', (int)current_user()['id'], (int)$row['event_id'], ['registration_id' => (int)$row['registration_id']]);

      json_response([
        'ok' => true,
        'already' => true,
        'message' => 'YA INGRESÓ',
        'person' => ['cedula' => $row['cedula'], 'nombres' => $row['nombres'], 'apellidos' => $row['apellidos']],
        'checkin_at' => $checkin_at
      ]);
    }
  }
}
