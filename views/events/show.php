<div class="card shadow-sm">
  <div class="card-body">
    <h3><?= h($event['nombre']) ?></h3>
    <div class="text-muted"><?= h($event['lugar'] ?? '') ?></div>
    <div class="small">Inicio: <?= h($event['fecha_inicio']) ?> · Fin: <?= h($event['fecha_fin']) ?></div>

    <hr>
    <div class="row g-3">
      <div class="col-md-6">
        <h5>Registrarme</h5>
        <?php if (!current_user()): ?>
          <div class="alert alert-warning">Debes iniciar sesión para inscribirte.</div>
          <a class="btn btn-brand w-100" href="<?= BASE_URL ?>/login">Ir a login</a>
        <?php elseif (empty($person)): ?>
          <div class="alert alert-warning">Tu cuenta no tiene datos personales. Regístrate nuevamente.</div>
          <a class="btn btn-brand w-100" href="<?= BASE_URL ?>/registro">Ir a registro</a>
        <?php else: ?>
          <div class="border rounded p-3 bg-white mb-3">
            <div><strong>Cédula:</strong> <?= h($person['cedula']) ?></div>
            <div><strong>Nombre:</strong> <?= h($person['nombres'] . ' ' . $person['apellidos']) ?></div>
            <div><strong>Celular:</strong> <?= h($person['celular'] ?? '') ?></div>
          </div>
          <form method="post" action="<?= BASE_URL ?>/registrar">
            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="event_id" value="<?= (int)$event['id'] ?>">
            <button class="btn btn-brand w-100">Inscribirme y generar QR</button>
          </form>
        <?php endif; ?>
      </div>
      <div class="col-md-6">
        <h5>Consultar mi QR</h5>
        <form method="get" action="<?= BASE_URL ?>/consulta_qr">
          <input type="hidden" name="event_id" value="<?= (int)$event['id'] ?>">
          <div class="mb-2">
            <label class="form-label">Cédula</label>
            <input class="form-control" name="cedula" required value="<?= h($person['cedula'] ?? '') ?>">
          </div>
          <button class="btn btn-outline-secondary w-100">Consultar</button>
        </form>
      </div>
    </div>
  </div>
</div>
