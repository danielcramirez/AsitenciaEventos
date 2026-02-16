<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

require_role(['ADMIN','OPERATOR']);

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$token = trim((string)($body['token'] ?? ''));
$event_id = (int)($body['event_id'] ?? 0);

header('Content-Type: application/json');

if (!$token || $event_id <= 0) {
  echo json_encode(['ok'=>false,'message'=>'Datos inválidos']); exit;
}

$pdo = db();
$hash = sha256($token);

$sql = "
  SELECT r.id registration_id, r.event_id, r.status,
         p.cedula, p.nombres, p.apellidos,
         (SELECT c.checkin_at FROM checkins c WHERE c.event_id=r.event_id AND c.registration_id=r.id LIMIT 1) as checkin_at
  FROM qr_tokens qt
  JOIN registrations r ON r.id=qt.registration_id
  JOIN persons p ON p.id=r.person_id
  WHERE qt.token_hash=? AND r.event_id=? AND qt.revoked_at IS NULL
  LIMIT 1";

$st = $pdo->prepare($sql);
$st->execute([$hash, $event_id]);
$row = $st->fetch();

if (!$row) {
  echo json_encode(['ok'=>false,'message'=>'QR inválido para este evento']); exit;
}
if ($row['status'] !== 'ACTIVE') {
  echo json_encode(['ok'=>false,'message'=>'Registro no activo']); exit;
}

if (!empty($row['checkin_at'])) {
  echo json_encode([
    'ok'=>true,'already'=>true,'message'=>'YA INGRESÓ',
    'person'=>['cedula'=>$row['cedula'],'nombres'=>$row['nombres'],'apellidos'=>$row['apellidos']],
    'checkin_at'=>$row['checkin_at']
  ]);
  exit;
}

try {
  $ins = $pdo->prepare("INSERT INTO checkins(event_id,registration_id,operator_user_id) VALUES(?,?,?)");
  $ins->execute([(int)$row['event_id'], (int)$row['registration_id'], (int)current_user()['id']]);

  echo json_encode([
    'ok'=>true,'already'=>false,'message'=>'BIENVENIDO/A',
    'person'=>['cedula'=>$row['cedula'],'nombres'=>$row['nombres'],'apellidos'=>$row['apellidos']],
    'checkin_at'=>date('Y-m-d H:i:s')
  ]);
} catch (Throwable $e) {
  // Si hubo carrera (otro operador insertó primero) cae aquí por UNIQUE
  $st = $pdo->prepare("SELECT checkin_at FROM checkins WHERE event_id=? AND registration_id=? LIMIT 1");
  $st->execute([(int)$row['event_id'], (int)$row['registration_id']]);
  $c = $st->fetch();

  echo json_encode([
    'ok'=>true,'already'=>true,'message'=>'YA INGRESÓ',
    'person'=>['cedula'=>$row['cedula'],'nombres'=>$row['nombres'],'apellidos'=>$row['apellidos']],
    'checkin_at'=>$c['checkin_at'] ?? null
  ]);
}
