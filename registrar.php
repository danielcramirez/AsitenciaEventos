<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
csrf_check();

$event_id = (int)($_POST['event_id'] ?? 0);
$cedula = trim($_POST['cedula'] ?? '');
$nombres = trim($_POST['nombres'] ?? '');
$apellidos = trim($_POST['apellidos'] ?? '');
$celular = trim($_POST['celular'] ?? '');

$pdo = db();
$pdo->beginTransaction();

try {
  // Validar evento publicado
  $st = $pdo->prepare("SELECT * FROM events WHERE id=? AND estado='PUBLISHED' LIMIT 1");
  $st->execute([$event_id]);
  $event = $st->fetch();
  if (!$event) throw new RuntimeException("Evento no disponible");

  // Upsert persona por cédula
  $st = $pdo->prepare("SELECT id FROM persons WHERE cedula=? LIMIT 1");
  $st->execute([$cedula]);
  $person = $st->fetch();

  if ($person) {
    $person_id = (int)$person['id'];
    $up = $pdo->prepare("UPDATE persons SET nombres=?, apellidos=?, celular=? WHERE id=?");
    $up->execute([$nombres, $apellidos, $celular ?: null, $person_id]);
  } else {
    $ins = $pdo->prepare("INSERT INTO persons(cedula,nombres,apellidos,celular) VALUES(?,?,?,?)");
    $ins->execute([$cedula, $nombres, $apellidos, $celular ?: null]);
    $person_id = (int)$pdo->lastInsertId();
  }

  // Crear o recuperar inscripción (1 por evento)
  $st = $pdo->prepare("SELECT id FROM registrations WHERE event_id=? AND person_id=? LIMIT 1");
  $st->execute([$event_id, $person_id]);
  $reg = $st->fetch();

  if ($reg) {
    $registration_id = (int)$reg['id'];
  } else {
    // Control de cupo (si cupo > 0)
    if ((int)$event['cupo'] > 0) {
      $cnt = $pdo->prepare("SELECT COUNT(*) c FROM registrations WHERE event_id=? AND status='ACTIVE'");
      $cnt->execute([$event_id]);
      $c = (int)$cnt->fetch()['c'];
      if ($c >= (int)$event['cupo']) throw new RuntimeException("Cupo agotado");
    }
    $ins = $pdo->prepare("INSERT INTO registrations(event_id, person_id) VALUES(?,?)");
    $ins->execute([$event_id, $person_id]);
    $registration_id = (int)$pdo->lastInsertId();
  }

  // Token QR (1 por inscripción)
  $st = $pdo->prepare("SELECT token_hash, qr_image_base64 FROM qr_tokens WHERE registration_id=? LIMIT 1");
  $st->execute([$registration_id]);
  $t = $st->fetch();

  if (!$t) {
    $token = new_token();           // secreto (solo en QR)
    $hash = sha256($token);         // se guarda hash
    $qr_image = generate_qr_base64($token);  // generar QR como base64
    $ins = $pdo->prepare("INSERT INTO qr_tokens(registration_id, token_hash, qr_image_base64) VALUES(?,?,?)");
    $ins->execute([$registration_id, $hash, $qr_image]);
    // Guardar token en sesión solo para mostrar QR inmediatamente
    $_SESSION['last_token'] = $token;
  } else {
    $_SESSION['last_token'] = null; // ya existe, el QR se consulta por cédula
  }

  $pdo->commit();
  header('Location: ' . BASE_URL . '/consulta_qr.php?event_id=' . $event_id . '&cedula=' . urlencode($cedula) . '&just=1');
  exit;

} catch (Throwable $e) {
  $pdo->rollBack();
  http_response_code(400);
  echo "Error: " . h($e->getMessage());
}
