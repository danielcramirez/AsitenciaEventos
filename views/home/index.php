<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="mb-0">Eventos publicados</h3>
  <div class="d-flex gap-2">
    <?php if (current_user() && current_user()['role']==='ADMIN'): ?>
      <a class="btn btn-brand" href="<?= BASE_URL ?>/admin_eventos.php">Administrar eventos</a>
    <?php endif; ?>
    <?php if (current_user() && in_array(current_user()['role'], ['OPERATOR','ADMIN'], true)): ?>
      <a class="btn btn-outline-dark" href="<?= BASE_URL ?>/puerta.php">Puerta (Check-in)</a>
    <?php endif; ?>
  </div>
</div>

<div class="row g-3">
  <?php foreach ($events as $e): ?>
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5><?= h($e['nombre']) ?></h5>
          <div class="text-muted"><?= h($e['lugar'] ?? '') ?></div>
          <div class="small">Inicio: <?= h($e['fecha_inicio']) ?> · Fin: <?= h($e['fecha_fin']) ?></div>
          <div class="mt-3 d-flex gap-2">
            <a class="btn btn-brand" href="<?= BASE_URL ?>/evento.php?id=<?= (int)$e['id'] ?>">Ver / Registrarme</a>
            <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/consulta_qr.php?event_id=<?= (int)$e['id'] ?>">Consultar QR</a>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$events): ?>
    <div class="col-12"><div class="alert alert-info">No hay eventos publicados.</div></div>
  <?php endif; ?>
</div>
