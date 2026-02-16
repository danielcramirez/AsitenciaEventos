<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

$event_id = (int)($_GET['event_id'] ?? 0);
$cedula = trim($_GET['cedula'] ?? '');
$just = (int)($_GET['just'] ?? 0);

$event = null;
if ($event_id) {
  $st = db()->prepare("SELECT * FROM events WHERE id=? LIMIT 1");
  $st->execute([$event_id]);
  $event = $st->fetch();
}

$data = null;

if ($event && $cedula) {
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
  $st->execute([$event_id, $cedula]);
  $data = $st->fetch();
}

require __DIR__ . '/header.php';
?>
<h3 class="mb-3">Consulta QR</h3>

<?php if (!$event): ?>
  <div class="alert alert-warning">Evento no válido.</div>
<?php else: ?>
  <div class="card shadow-sm mb-3">
    <div class="card-body">
      <h5 class="mb-0"><?= h($event['nombre']) ?></h5>
      <div class="text-muted small"><?= h($event['lugar'] ?? '') ?></div>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <form class="row g-2 mb-3" method="get">
        <input type="hidden" name="event_id" value="<?= (int)$event_id ?>">
        <div class="col-md-8">
          <input class="form-control" name="cedula" placeholder="Cédula" value="<?= h($cedula) ?>" required>
        </div>
        <div class="col-md-4">
          <button class="btn btn-outline-secondary w-100">Consultar</button>
        </div>
      </form>

      <?php if ($just): ?>
        <div class="alert alert-success">Registro exitoso. Aquí está tu QR.</div>
      <?php endif; ?>

      <?php if (!$cedula): ?>
        <div class="alert alert-info">Ingresa tu cédula para ver tu QR.</div>
      <?php elseif (!$data): ?>
        <div class="alert alert-danger">No existe registro para esa cédula en este evento.</div>
      <?php else: ?>
        <div class="row g-3">
          <div class="col-md-6">
            <div class="border rounded p-3 bg-white">
              <div><strong>Nombre:</strong> <?= h($data['nombres'] . ' ' . $data['apellidos']) ?></div>
              <div><strong>Cédula:</strong> <?= h($data['cedula']) ?></div>
              <div><strong>Estado:</strong> <?= h($data['status']) ?></div>
              <div><strong>Asistencia:</strong>
                <?= ((int)$data['checked'] > 0) ? 'YA INGRESÓ (' . h((string)$data['checkin_at']) . ')' : 'NO HA INGRESADO' ?>
              </div>
            </div>
          </div>
          <div class="col-md-6 text-center">
            <?php if (!empty($data['qr_image_base64'])): ?>
              <img class="img-fluid" alt="QR" src="<?= $data['qr_image_base64'] ?>">
              <div class="small text-muted mt-2">Presenta este QR en la entrada.</div>
            <?php else: ?>
              <div class="alert alert-warning">QR no disponible</div>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/footer.php'; ?>
