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

<a class="btn btn-outline-dark" href="<?= BASE_URL ?>/export_csv?event_id=<?= (int)$event['id'] ?>">Exportar CSV</a>
