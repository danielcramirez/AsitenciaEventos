<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/bootstrap.php';
require_once __DIR__ . '/../models/EventModel.php';
require_once __DIR__ . '/../models/PersonModel.php';
require_once __DIR__ . '/../models/RegistrationModel.php';
require_once __DIR__ . '/../models/QrTokenModel.php';
require_once __DIR__ . '/../models/AuditLogModel.php';

class RegistrationController {
  public static function register(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      render_error('Método no permitido', 405);
    }

    csrf_check();

    $event_id = (int)($_POST['event_id'] ?? 0);
    $cedula = trim((string)($_POST['cedula'] ?? ''));
    $nombres = trim((string)($_POST['nombres'] ?? ''));
    $apellidos = trim((string)($_POST['apellidos'] ?? ''));
    $celular = trim((string)($_POST['celular'] ?? ''));

    if ($event_id <= 0) {
      render_error('Evento inválido', 400);
    }
    if (!validate_cedula($cedula)) {
      render_error('Cédula inválida', 400);
    }
    if (!validate_name($nombres) || !validate_name($apellidos)) {
      render_error('Nombre inválido', 400);
    }
    if (!validate_phone($celular)) {
      render_error('Celular inválido', 400);
    }

    $pdo = db();
    $pdo->beginTransaction();

    try {
      $event = EventModel::findPublishedById($event_id);
      if (!$event) {
        throw new RuntimeException('Evento no disponible');
      }

      $person_id = PersonModel::upsert($cedula, $nombres, $apellidos, $celular ?: null);

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
      }

      AuditLogModel::log('registration', null, $event_id, ['cedula' => $cedula]);
      $pdo->commit();

      header('Location: ' . BASE_URL . '/consulta_qr.php?event_id=' . $event_id . '&cedula=' . urlencode($cedula) . '&just=1');
      exit;
    } catch (Throwable $e) {
      $pdo->rollBack();
      render_error('Error: ' . $e->getMessage(), 400);
    }
  }
}
