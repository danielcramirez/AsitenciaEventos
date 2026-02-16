<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

require_role(['ADMIN']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $nombre = trim($_POST['nombre'] ?? '');
  $lugar = trim($_POST['lugar'] ?? '');
  $fi = $_POST['fecha_inicio'] ?? '';
  $ff = $_POST['fecha_fin'] ?? '';
  $cupo = (int)($_POST['cupo'] ?? 0);
  $estado = $_POST['estado'] ?? 'DRAFT';

  $stmt = db()->prepare("INSERT INTO events(nombre,lugar,fecha_inicio,fecha_fin,cupo,estado) VALUES(?,?,?,?,?,?)");
  $stmt->execute([$nombre, $lugar, $fi, $ff, $cupo, $estado]);
  header('Location: ' . BASE_URL . '/admin_eventos.php');
  exit;
}

$events = db()->query("SELECT * FROM events ORDER BY id DESC")->fetchAll();

require __DIR__ . '/header.php';
?>
<h3 class="mb-3">Administrar eventos</h3>

<div class="card shadow-sm mb-4">
  <div class="card-body">
    <h5>Crear evento</h5>
    <form method="post" class="row g-3">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <div class="col-md-6">
        <label class="form-label">Nombre</label>
        <input class="form-control" name="nombre" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Lugar</label>
        <input class="form-control" name="lugar">
      </div>
      <div class="col-md-3">
        <label class="form-label">Inicio</label>
        <input class="form-control" type="datetime-local" name="fecha_inicio" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Fin</label>
        <input class="form-control" type="datetime-local" name="fecha_fin" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Cupo</label>
        <input class="form-control" type="number" name="cupo" min="0" value="0">
      </div>
      <div class="col-md-3">
        <label class="form-label">Estado</label>
        <select class="form-select" name="estado">
          <option value="DRAFT">DRAFT</option>
          <option value="PUBLISHED">PUBLISHED</option>
          <option value="CLOSED">CLOSED</option>
        </select>
      </div>
      <div class="col-12">
        <button class="btn btn-brand">Guardar</button>
      </div>
    </form>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <h5>Eventos</h5>
    <div class="table-responsive">
      <table class="table table-sm align-middle">
        <thead><tr>
          <th>ID</th><th>Nombre</th><th>Fechas</th><th>Estado</th><th>Acciones</th>
        </tr></thead>
        <tbody>
        <?php foreach ($events as $e): ?>
          <tr>
            <td><?= (int)$e['id'] ?></td>
            <td><?= h($e['nombre']) ?></td>
            <td class="small"><?= h($e['fecha_inicio']) ?> → <?= h($e['fecha_fin']) ?></td>
            <td><span class="badge badge-brand"><?= h($e['estado']) ?></span></td>
            <td class="d-flex gap-2">
              <a class="btn btn-sm btn-outline-secondary" href="<?= BASE_URL ?>/evento.php?id=<?= (int)$e['id'] ?>">Ver</a>
              <a class="btn btn-sm btn-outline-dark" href="<?= BASE_URL ?>/reporte.php?event_id=<?= (int)$e['id'] ?>">Reporte</a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require __DIR__ . '/footer.php'; ?>
