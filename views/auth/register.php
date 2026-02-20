<?php $old = is_array($old ?? null) ? $old : []; ?>
<div class="row justify-content-center">
  <div class="col-md-7">
    <div class="card shadow-sm">
      <div class="card-body">
        <h4 class="mb-3">Registro de Elector</h4>
        <?php if (!empty($error)): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?>
        <?php if (!empty($success)): ?><div class="alert alert-success"><?= h($success) ?></div><?php endif; ?>

        <form method="post" class="row g-2">
          <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
          <div class="col-md-6">
            <label class="form-label">Cedula</label>
            <input class="form-control" name="cedula" required value="<?= h((string)($old['cedula'] ?? '')) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Celular</label>
            <input class="form-control" name="celular" value="<?= h((string)($old['celular'] ?? '')) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Nombres</label>
            <input class="form-control" name="nombres" required value="<?= h((string)($old['nombres'] ?? '')) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Apellidos</label>
            <input class="form-control" name="apellidos" required value="<?= h((string)($old['apellidos'] ?? '')) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input class="form-control" name="email" type="email" required value="<?= h((string)($old['email'] ?? '')) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Contrasena</label>
            <input class="form-control" name="password" type="password" required>
          </div>
          <div class="col-12">
            <label class="form-label">Codigo de referido (opcional)</label>
            <input class="form-control" name="referral_code" placeholder="Ej: ENLAB12CD34" value="<?= h((string)($old['referral_code'] ?? '')) ?>">
          </div>
          <div class="col-12">
            <button class="btn btn-brand w-100">Crear cuenta</button>
          </div>
        </form>
      </div>
    </div>
    <div class="small text-muted mt-3">Si ya tienes cuenta, <a href="<?= BASE_URL ?>/login">inicia sesion</a>.</div>
  </div>
</div>
