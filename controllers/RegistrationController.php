<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/bootstrap.php';
require_once __DIR__ . '/../models/EventModel.php';
require_once __DIR__ . '/../models/RegistrationModel.php';
require_once __DIR__ . '/../models/QrTokenModel.php';
require_once __DIR__ . '/../models/AuditLogModel.php';
require_once __DIR__ . '/../models/UserModel.php';

class RegistrationController {
  public static function register(): void {
    require_auth();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      render_error('Método no permitido', 405);
    }

    csrf_check();

    $event_id = (int)($_POST['event_id'] ?? 0);
    $u = current_user();
    $person = UserModel::findPersonByUserId((int)$u['id']);

    if ($event_id <= 0) {
      render_error('Evento inválido', 400);
    }
    if (!$person) {
      render_error('Usuario sin perfil personal. Complete su registro.', 400);
    }

    $cedula = (string)$person['cedula'];
    $person_id = (int)$person['id'];

    $pdo = db();
    $pdo->beginTransaction();

    try {
      $event = EventModel::findPublishedById($event_id);
      if (!$event) {
        throw new RuntimeException('Evento no disponible');
      }

      $reg = RegistrationModel::findByEventAndPerson($event_id, $person_id);
      if ($reg) {
        $registration_id = (int)$reg['id'];
      } else {
        if ((int)$event['cupo'] > 0) {
          $c = RegistrationModel::countActiveByEvent($event_id);
          if ($c >= (int)$event['cupo']) {
            throw new RuntimeException('Cupo agotado');
          }
        }
        $registration_id = RegistrationModel::create($event_id, $person_id);
      }

      $token = QrTokenModel::findByRegistrationId($registration_id);
      if (!$token) {
        QrTokenModel::create($registration_id);
      } elseif (empty($token['qr_image_base64'])) {
        QrTokenModel::rotate($registration_id);
      }

      AuditLogModel::log('registration', (int)$u['id'], $event_id, ['cedula' => $cedula]);
      $pdo->commit();

      header('Location: ' . BASE_URL . '/consulta_qr?event_id=' . $event_id . '&cedula=' . urlencode($cedula) . '&just=1');
      exit;
    } catch (Throwable $e) {
      $pdo->rollBack();
      render_error('Error: ' . $e->getMessage(), 400);
    }
  }
}
