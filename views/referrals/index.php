<h3 class="mb-3">Referidos del Enlace</h3>

<?php $isAdminDashboard = (bool)($is_admin_dashboard ?? false); ?>

<?php if (!$isAdminDashboard): ?>
  <div class="card shadow-sm mb-3">
    <div class="card-body">
      <div><strong>Email enlace:</strong> <?= h((string)($enlace['email'] ?? '')) ?></div>
      <div><strong>Codigo:</strong> <span class="badge bg-warning text-dark"><?= h((string)($referral_code ?? '')) ?></span></div>
    </div>
  </div>
<?php endif; ?>

<div class="row g-3 mb-3">
  <div class="col-md-4">
    <div class="card shadow-sm"><div class="card-body">
      <div class="text-muted">Total referidos</div>
      <div class="display-6"><?= (int)($summary['total_referidos'] ?? 0) ?></div>
    </div></div>
  </div>
  <div class="col-md-4">
    <div class="card shadow-sm"><div class="card-body">
      <div class="text-muted">Con inscripcion a eventos</div>
      <div class="display-6"><?= (int)($summary['total_registros_evento'] ?? 0) ?></div>
    </div></div>
  </div>
  <div class="col-md-4">
    <div class="card shadow-sm"><div class="card-body">
      <div class="text-muted">Con asistencia</div>
      <div class="display-6"><?= (int)($summary['total_asistencias'] ?? 0) ?></div>
    </div></div>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <h5>Detalle de electores referidos</h5>
    <div class="table-responsive">
      <table class="table table-sm align-middle">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Cedula</th>
            <th>Email</th>
            <th>Registrado</th>
            <th>Eventos</th>
            <th>Asistencias</th>
            <th>Ultima asistencia</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach (($electors ?? []) as $e): ?>
            <tr>
              <td><?= h($e['nombres'] . ' ' . $e['apellidos']) ?></td>
              <td><?= h($e['cedula']) ?></td>
              <td><?= h($e['email']) ?></td>
              <td><?= h($e['fecha_registro_usuario']) ?></td>
              <td><?= (int)$e['eventos_registrados'] ?></td>
              <td><?= (int)$e['eventos_asistidos'] ?></td>
              <td><?= h($e['ultima_asistencia'] ?? '') ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!($electors ?? [])): ?>
            <tr><td colspan="7" class="text-muted">No hay electores referidos todavia.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
