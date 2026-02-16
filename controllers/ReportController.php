<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/bootstrap.php';
require_once __DIR__ . '/../models/EventModel.php';
require_once __DIR__ . '/../models/AuditLogModel.php';

class ReportController {
  public static function report(): void {
    require_role(ROLE_ADMIN);
    $event_id = (int)($_GET['event_id'] ?? 0);

    $event = EventModel::findById($event_id);
    if (!$event) {
      render_error('Evento no existe', 404);
    }

    $tot = db()->prepare("SELECT COUNT(*) c FROM registrations WHERE event_id=? AND status='ACTIVE'");
    $tot->execute([$event_id]);
    $registrados = (int)$tot->fetch()['c'];

    $as = db()->prepare('SELECT COUNT(*) c FROM checkins WHERE event_id=?');
    $as->execute([$event_id]);
    $asistentes = (int)$as->fetch()['c'];

    render_view('layout/header', ['title' => 'Reporte']);
    render_view('reports/event', [
      'event' => $event,
      'registrados' => $registrados,
      'asistentes' => $asistentes
    ]);
    render_view('layout/footer');
  }

  public static function exportCsv(): void {
    require_role(ROLE_ADMIN);
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
    header('Content-Disposition: attachment; filename="reporte_evento_' . $event_id . '.csv"');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['cedula','nombres','apellidos','celular','fecha_registro','checkin_at']);

    while ($row = $st->fetch(PDO::FETCH_NUM)) {
      fputcsv($out, $row);
    }
    fclose($out);

    $u = current_user();
    AuditLogModel::log('report_export', $u ? (int)$u['id'] : null, $event_id, []);
    exit;
  }
}
