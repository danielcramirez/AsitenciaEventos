<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/bootstrap.php';
require_once __DIR__ . '/../models/EventModel.php';
require_once __DIR__ . '/../models/PersonModel.php';
require_once __DIR__ . '/../models/RegistrationModel.php';
require_once __DIR__ . '/../models/QrTokenModel.php';
require_once __DIR__ . '/../models/SecurityModel.php';
require_once __DIR__ . '/../models/AuditLogModel.php';
require_once __DIR__ . '/../models/UserModel.php';

class QrController {
  private static function blobToString(mixed $value): string {
    if (is_resource($value)) {
      $raw = stream_get_contents($value);
      return is_string($raw) ? $raw : '';
    }
    if (is_string($value)) {
      return $value;
    }
    return '';
  }

  private static function normalizeQrData(array $row): array {
    $qrImage = self::blobToString($row['qr_image_base64'] ?? '');
    if ($qrImage !== '' && !str_starts_with($qrImage, 'data:image/')) {
      $qrImage = 'data:image/png;base64,' . base64_encode($qrImage);
    }

    return [
      'registration_id' => (int)($row['registration_id'] ?? 0),
      'status' => (string)($row['status'] ?? ''),
      'cedula' => (string)($row['cedula'] ?? ''),
      'nombres' => (string)($row['nombres'] ?? ''),
      'apellidos' => (string)($row['apellidos'] ?? ''),
      'checked' => (int)($row['checked'] ?? 0),
      'checkin_at' => $row['checkin_at'] ?? null,
      'token_hash' => (string)($row['token_hash'] ?? ''),
      'qr_image_base64' => $qrImage,
    ];
  }

  public static function consult(): void {
    require_auth();

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

        $u = current_user();
        $isPrivileged = in_array((string)$u['role'], [ROLE_ADMIN, ROLE_OPERATOR], true);
        if (!$isPrivileged) {
          $myPerson = UserModel::findPersonByUserId((int)$u['id']);
          if (!$myPerson || (int)$myPerson['id'] !== (int)$person['id']) {
            render_error('No autorizado para reemitir este QR', 403);
          }
        }

        QrTokenModel::rotate((int)$reg['id']);
        AuditLogModel::log('qr_rotated', null, (int)$event['id'], ['cedula' => $cedula]);

        header('Location: ' . BASE_URL . '/consulta_qr?event_id=' . (int)$event['id'] . '&cedula=' . urlencode($cedula) . '&rotated=1');
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
        $skipRateLimit = ($just === 1 || $rotated === 1);
        $rate = ['allowed' => true];
        if (!$skipRateLimit) {
          $rate = SecurityModel::checkRateLimit($key, QR_RATE_LIMIT_MAX, QR_RATE_LIMIT_WINDOW);
        }

        if (!$rate['allowed']) {
          $rate_limited = true;
          AuditLogModel::log('qr_rate_limited', null, (int)$event['id'], ['cedula' => $cedula]);
        } else {
          $sql = "
            SELECT
              r.id AS registration_id,
              r.status,
              p.cedula,
              p.nombres,
              p.apellidos,
              (SELECT COUNT(*) FROM checkins c WHERE c.event_id=r.event_id AND c.registration_id=r.id) AS checked,
              (SELECT c.checkin_at FROM checkins c WHERE c.event_id=r.event_id AND c.registration_id=r.id LIMIT 1) AS checkin_at,
              qt.token_hash,
              qt.qr_image_base64
            FROM registrations r
            JOIN persons p ON p.id=r.person_id
            LEFT JOIN qr_tokens qt ON qt.registration_id=r.id
            WHERE r.event_id=? AND p.cedula=?
            LIMIT 1";
          $st = db()->prepare($sql);
          $st->execute([(int)$event['id'], $cedula]);
          $row = $st->fetch(PDO::FETCH_ASSOC);
          if ($row) {
            $data = self::normalizeQrData($row);

            if ($data['qr_image_base64'] === '' && (int)$data['registration_id'] > 0) {
              $newQr = QrTokenModel::rotate((int)$data['registration_id']);
              $data['token_hash'] = (string)($newQr['hash'] ?? '');
              $data['qr_image_base64'] = (string)($newQr['qr_image'] ?? '');
              AuditLogModel::log('qr_auto_regenerated', null, (int)$event['id'], ['cedula' => $cedula]);
            }
          } else {
            $data = null;
          }
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
