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
        <input type="hidden" name="event_id" value="<?= (int)$event['id'] ?>">
        <div class="col-md-8">
          <input class="form-control" name="cedula" placeholder="Cédula" value="<?= h($cedula) ?>" required>
        </div>
        <div class="col-md-4">
          <button class="btn btn-outline-secondary w-100">Consultar</button>
        </div>
      </form>

      <?php if (!empty($just)): ?>
        <div class="alert alert-success">Registro exitoso. Aquí está tu QR.</div>
      <?php endif; ?>
      <?php if (!empty($rotated)): ?>
        <div class="alert alert-info">QR reemitido. El anterior ya no es válido.</div>
      <?php endif; ?>
      <?php if (!empty($rate_limited)): ?>
        <div class="alert alert-warning">Demasiadas consultas. Intenta nuevamente en un minuto.</div>
      <?php endif; ?>

      <?php if (!$cedula): ?>
        <div class="alert alert-info">Ingresa tu cédula para ver tu QR.</div>
      <?php elseif (!$data): ?>
        <div class="alert alert-danger">No existe registro para esa cédula en este evento.</div>
      <?php else: ?>
        <?php
          $fullName = trim((string)($data['nombres'] ?? '') . ' ' . (string)($data['apellidos'] ?? ''));
          $ced = (string)($data['cedula'] ?? '');
          $status = (string)($data['status'] ?? '');
          $checked = (int)($data['checked'] ?? 0);
          $checkinAt = (string)($data['checkin_at'] ?? '');
          $qrImage = (string)($data['qr_image_base64'] ?? '');
        ?>
        <div class="row g-3">
          <div class="col-md-6">
            <div class="border rounded p-3 bg-white">
              <div><strong>Nombre:</strong> <?= h($fullName) ?></div>
              <div><strong>Cédula:</strong> <?= h($ced) ?></div>
              <div><strong>Estado:</strong> <?= h($status) ?></div>
              <div><strong>Asistencia:</strong>
                <?= ($checked > 0) ? 'YA INGRESÓ (' . h($checkinAt) . ')' : 'NO HA INGRESADO' ?>
              </div>
            </div>
            <form class="mt-3" method="post">
              <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
              <input type="hidden" name="event_id" value="<?= (int)$event['id'] ?>">
              <input type="hidden" name="cedula" value="<?= h($cedula) ?>">
              <input type="hidden" name="action" value="rotate">
              <button class="btn btn-outline-dark">Reemitir QR</button>
            </form>
          </div>
          <div class="col-md-6 text-center">
            <?php if ($qrImage !== ''): ?>
              <img class="img-fluid" alt="QR" src="<?= h($qrImage) ?>">
              <div class="small text-muted mt-2">Presenta este QR en la entrada.</div>
            <?php else: ?>
              <div class="alert alert-warning">QR no disponible. Pulsa "Reemitir QR" para generarlo otra vez.</div>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>
