<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card shadow-sm">
      <div class="card-body">
        <h4 class="mb-3">Ingresar</h4>
        <?php if (!empty($error)): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?>
        <?php if (!empty($notice)): ?><div class="alert alert-warning"><?= h($notice) ?></div><?php endif; ?>
        <form method="post">
          <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input class="form-control" name="email" type="email" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input class="form-control" name="password" type="password" required>
          </div>
          <button class="btn btn-brand w-100">Entrar</button>
        </form>
      </div>
    </div>
    <div class="small text-muted mt-3">
      Admin inicial: admin@local / Admin123*
    </div>
  </div>
</div>
