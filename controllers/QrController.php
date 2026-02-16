<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/bootstrap.php';
require_once __DIR__ . '/../models/EventModel.php';
require_once __DIR__ . '/../models/PersonModel.php';
require_once __DIR__ . '/../models/RegistrationModel.php';
require_once __DIR__ . '/../models/QrTokenModel.php';
require_once __DIR__ . '/../models/SecurityModel.php';
require_once __DIR__ . '/../models/AuditLogModel.php';

class QrController {
  public static function consult(): void {
    $event_id = (int)($_REQUEST['event_id'] ?? 0);
    $cedula = trim((string)($_REQUEST['cedula'] ?? ''));
    $just = (int)($_GET['just'] ?? 0);
    $rotated = (int)($_GET['rotated'] ?? 0);

    $event = null;
    if ($event_id) {
      $event = EventModel::findById($event_id);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      csrf_check();
      $action = (string)($_POST['action'] ?? '');

      if ($action === 'rotate') {
        if (!$event || !validate_cedula($cedula)) {
          render_error('Datos inválidos', 400);
        }

        $person = PersonModel::findByCedula($cedula);
        if (!$person) {
          render_error('Registro no encontrado', 404);
        }

        $reg = RegistrationModel::findByEventAndPerson((int)$event['id'], (int)$person['id']);
        if (!$reg) {
          render_error('Registro no encontrado', 404);
        }

        QrTokenModel::rotate((int)$reg['id']);
        AuditLogModel::log('qr_rotated', null, (int)$event['id'], ['cedula' => $cedula]);

        header('Location: ' . BASE_URL . '/consulta_qr.php?event_id=' . (int)$event['id'] . '&cedula=' . urlencode($cedula) . '&rotated=1');
        exit;
      }
    }

    $data = null;
    $rate_limited = false;

    if ($event && $cedula) {
      if (!validate_cedula($cedula)) {
        $data = null;
      } else {
        $key = 'consulta_qr:' . (int)$event['id'] . ':' . $cedula . ':' . get_client_ip();
        $rate = SecurityModel::checkRateLimit($key, QR_RATE_LIMIT_MAX, QR_RATE_LIMIT_WINDOW);
        if (!$rate['allowed']) {
          $rate_limited = true;
          AuditLogModel::log('qr_rate_limited', null, (int)$event['id'], ['cedula' => $cedula]);
        } else {
          $sql = "
            SELECT r.id registration_id, r.status, p.cedula, p.nombres, p.apellidos,
                   (SELECT COUNT(*) FROM checkins c WHERE c.event_id=r.event_id AND c.registration_id=r.id) as checked,
                   (SELECT c.checkin_at FROM checkins c WHERE c.event_id=r.event_id AND c.registration_id=r.id LIMIT 1) as checkin_at,
                   qt.token_hash, qt.qr_image_base64
            FROM registrations r
            JOIN persons p ON p.id=r.person_id
            JOIN qr_tokens qt ON qt.registration_id=r.id
            WHERE r.event_id=? AND p.cedula=? LIMIT 1";
          $st = db()->prepare($sql);
          $st->execute([(int)$event['id'], $cedula]);
          $data = $st->fetch();
          AuditLogModel::log('qr_consult', null, (int)$event['id'], ['cedula' => $cedula]);
        }
      }
    }

    render_view('layout/header', ['title' => 'Consulta QR']);
    render_view('qr/consulta', [
      'event' => $event,
      'cedula' => $cedula,
      'data' => $data,
      'just' => $just,
      'rotated' => $rotated,
      'rate_limited' => $rate_limited
    ]);
    render_view('layout/footer');
  }
}
