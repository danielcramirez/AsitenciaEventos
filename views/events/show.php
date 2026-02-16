<div class="card shadow-sm">
  <div class="card-body">
    <h3><?= h($event['nombre']) ?></h3>
    <div class="text-muted"><?= h($event['lugar'] ?? '') ?></div>
    <div class="small">Inicio: <?= h($event['fecha_inicio']) ?> · Fin: <?= h($event['fecha_fin']) ?></div>

    <hr>
    <div class="row g-3">
      <div class="col-md-6">
        <h5>Registrarme</h5>
        <?php if (!empty($error)): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?>
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
