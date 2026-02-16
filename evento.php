<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare("SELECT * FROM events WHERE id=? LIMIT 1");
$stmt->execute([$id]);
$event = $stmt->fetch();
if (!$event || $event['estado'] !== 'PUBLISHED') { http_response_code(404); exit("Evento no disponible"); }

require __DIR__ . '/header.php';
?>
<div class="card shadow-sm">
  <div class="card-body">
    <h3><?= h($event['nombre']) ?></h3>
    <div class="text-muted"><?= h($event['lugar'] ?? '') ?></div>
    <div class="small">Inicio: <?= h($event['fecha_inicio']) ?> · Fin: <?= h($event['fecha_fin']) ?></div>

    <hr>
    <div class="row g-3">
      <div class="col-md-6">
        <h5>Registrarme</h5>
        <form method="post" action="<?= BASE_URL ?>/registrar.php">
          <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
          <input type="hidden" name="event_id" value="<?= (int)$event['id'] ?>">

          <div class="mb-2">
            <label class="form-label">Cédula</label>
            <input class="form-control" name="cedula" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Nombres</label>
            <input class="form-control" name="nombres" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Apellidos</label>
            <input class="form-control" name="apellidos" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Celular</label>
            <input class="form-control" name="celular">
          </div>

          <button class="btn btn-brand w-100">Guardar y generar QR</button>
        </form>
      </div>
      <div class="col-md-6">
        <h5>Consultar mi QR</h5>
        <form method="get" action="<?= BASE_URL ?>/consulta_qr.php">
          <input type="hidden" name="event_id" value="<?= (int)$event['id'] ?>">
          <div class="mb-2">
            <label class="form-label">Cédula</label>
            <input class="form-control" name="cedula" required>
          </div>
          <button class="btn btn-outline-secondary w-100">Consultar</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php require __DIR__ . '/footer.php'; ?>
