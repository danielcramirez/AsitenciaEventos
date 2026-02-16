<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

require_role(['ADMIN']);

$event_id = (int)($_GET['event_id'] ?? 0);

$st = db()->prepare("SELECT * FROM events WHERE id=?");
$st->execute([$event_id]);
$event = $st->fetch();
if (!$event) { http_response_code(404); exit("Evento no existe"); }

$tot = db()->prepare("SELECT COUNT(*) c FROM registrations WHERE event_id=? AND status='ACTIVE'");
$tot->execute([$event_id]);
$registrados = (int)$tot->fetch()['c'];

$as = db()->prepare("SELECT COUNT(*) c FROM checkins WHERE event_id=?");
$as->execute([$event_id]);
$asistentes = (int)$as->fetch()['c'];

require __DIR__ . '/header.php';
?>
<h3>Reporte: <?= h($event['nombre']) ?></h3>

<div class="row g-3 my-2">
  <div class="col-md-4"><div class="card shadow-sm"><div class="card-body">
    <div class="text-muted">Registrados</div><div class="display-6"><?= $registrados ?></div>
  </div></div></div>
  <div class="col-md-4"><div class="card shadow-sm"><div class="card-body">
    <div class="text-muted">Asistentes</div><div class="display-6"><?= $asistentes ?></div>
  </div></div></div>
  <div class="col-md-4"><div class="card shadow-sm"><div class="card-body">
    <div class="text-muted">No asistieron</div><div class="display-6"><?= max(0,$registrados-$asistentes) ?></div>
  </div></div></div>
</div>

<a class="btn btn-outline-dark" href="<?= BASE_URL ?>/export_csv.php?event_id=<?= (int)$event_id ?>">Exportar CSV</a>

<?php require __DIR__ . '/footer.php'; ?>
