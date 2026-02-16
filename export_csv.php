<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

require_role(['ADMIN']);

$event_id = (int)($_GET['event_id'] ?? 0);

$sql = "
SELECT p.cedula, p.nombres, p.apellidos, p.celular,
       r.created_at AS fecha_registro,
       (SELECT c.checkin_at FROM checkins c WHERE c.event_id=r.event_id AND c.registration_id=r.id LIMIT 1) AS checkin_at
FROM registrations r
JOIN persons p ON p.id=r.person_id
WHERE r.event_id=? AND r.status='ACTIVE'
ORDER BY r.created_at ASC";

$st = db()->prepare($sql);
$st->execute([$event_id]);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="reporte_evento_'.$event_id.'.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['cedula','nombres','apellidos','celular','fecha_registro','checkin_at']);

while ($row = $st->fetch(PDO::FETCH_NUM)) {
  fputcsv($out, $row);
}
fclose($out);
exit;
