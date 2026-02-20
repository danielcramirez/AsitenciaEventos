<h3 class="mb-3">Parametrizacion de Diseno</h3>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= h((string)$error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
  <div class="alert alert-success"><?= h((string)$success) ?></div>
<?php endif; ?>

<div class="card shadow-sm">
  <div class="card-body">
    <form method="post" enctype="multipart/form-data" class="row g-3">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">

      <div class="col-md-2">
        <label class="form-label">Color principal</label>
        <input
          class="form-control form-control-color"
          type="color"
          name="primary_color"
          value="<?= h((string)($settings['primary_color'] ?? '#006838')) ?>"
          required
        >
      </div>
      <div class="col-md-2">
        <label class="form-label">Hover principal</label>
        <input
          class="form-control form-control-color"
          type="color"
          name="primary_hover_color"
          value="<?= h((string)($settings['primary_hover_color'] ?? '#0D9A49')) ?>"
          required
        >
      </div>

      <div class="col-md-8">
        <label class="form-label">Logo (PNG, JPG, WEBP - max 2MB)</label>
        <input class="form-control" type="file" name="logo" accept=".png,.jpg,.jpeg,.webp">
        <?php if (!empty($settings['logo_path'])): ?>
          <div class="mt-2">
            <img src="<?= h((string)$settings['logo_path']) ?>" alt="Logo actual" style="max-height:64px;">
          </div>
          <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" name="remove_logo" value="1" id="remove_logo">
            <label class="form-check-label" for="remove_logo">Quitar logo actual</label>
          </div>
        <?php endif; ?>
      </div>

      <div class="col-md-8">
        <label class="form-label">Favicon navegador (PNG, ICO, SVG - max 1MB)</label>
        <input class="form-control" type="file" name="favicon" accept=".png,.ico,.svg">
        <?php if (!empty($settings['favicon_path'])): ?>
          <div class="mt-2 d-flex align-items-center gap-2">
            <img src="<?= h((string)$settings['favicon_path']) ?>" alt="Favicon actual" style="height:24px;width:24px;">
            <span class="small text-muted">Favicon actual</span>
          </div>
          <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" name="remove_favicon" value="1" id="remove_favicon">
            <label class="form-check-label" for="remove_favicon">Quitar favicon actual</label>
          </div>
        <?php endif; ?>
      </div>

      <div class="col-12">
        <h5 class="mb-2">Colores de botones del menu</h5>
        <div class="row g-3">
          <div class="col-md-2">
            <label class="form-label">Boton fondo</label>
            <input class="form-control form-control-color" type="color" name="menu_button_bg" value="<?= h((string)($settings['menu_button_bg'] ?? '#F5EB28')) ?>" required>
          </div>
          <div class="col-md-2">
            <label class="form-label">Boton texto</label>
            <input class="form-control form-control-color" type="color" name="menu_button_text" value="<?= h((string)($settings['menu_button_text'] ?? '#111111')) ?>" required>
          </div>
          <div class="col-md-2">
            <label class="form-label">Boton hover</label>
            <input class="form-control form-control-color" type="color" name="menu_button_hover_bg" value="<?= h((string)($settings['menu_button_hover_bg'] ?? '#F89621')) ?>" required>
          </div>
          <div class="col-md-2">
            <label class="form-label">Texto hover</label>
            <input class="form-control form-control-color" type="color" name="menu_button_hover_text" value="<?= h((string)($settings['menu_button_hover_text'] ?? '#111111')) ?>" required>
          </div>
          <div class="col-md-2">
            <label class="form-label">Secundario fondo</label>
            <input class="form-control form-control-color" type="color" name="menu_secondary_bg" value="<?= h((string)($settings['menu_secondary_bg'] ?? '#FFFFFF')) ?>" required>
          </div>
          <div class="col-md-2">
            <label class="form-label">Secundario texto</label>
            <input class="form-control form-control-color" type="color" name="menu_secondary_text" value="<?= h((string)($settings['menu_secondary_text'] ?? '#111111')) ?>" required>
          </div>
          <div class="col-md-2">
            <label class="form-label">Secundario hover</label>
            <input class="form-control form-control-color" type="color" name="menu_secondary_hover_bg" value="<?= h((string)($settings['menu_secondary_hover_bg'] ?? '#E9ECEF')) ?>" required>
          </div>
          <div class="col-md-2">
            <label class="form-label">Sec. texto hover</label>
            <input class="form-control form-control-color" type="color" name="menu_secondary_hover_text" value="<?= h((string)($settings['menu_secondary_hover_text'] ?? '#111111')) ?>" required>
          </div>
        </div>
      </div>

      <div class="col-12">
        <h5 class="mb-2">Menu</h5>
        <div class="row">
          <div class="col-md-4">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="menu_admin_eventos" name="menu_show_admin_eventos" <?= !empty($settings['menu_show_admin_eventos']) ? 'checked' : '' ?>>
              <label class="form-check-label" for="menu_admin_eventos">Mostrar "Admin eventos"</label>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="menu_verificar_qr" name="menu_show_verificar_qr" <?= !empty($settings['menu_show_verificar_qr']) ? 'checked' : '' ?>>
              <label class="form-check-label" for="menu_verificar_qr">Mostrar "Verificar QR"</label>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="menu_referidos" name="menu_show_mis_referidos" <?= !empty($settings['menu_show_mis_referidos']) ? 'checked' : '' ?>>
              <label class="form-check-label" for="menu_referidos">Mostrar "Mis referidos"</label>
            </div>
          </div>
          <div class="col-md-4 mt-2">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="menu_registro" name="menu_show_registro" <?= !empty($settings['menu_show_registro']) ? 'checked' : '' ?>>
              <label class="form-check-label" for="menu_registro">Mostrar "Registro" (visitante)</label>
            </div>
          </div>
          <div class="col-md-4 mt-2">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="menu_login" name="menu_show_login" <?= !empty($settings['menu_show_login']) ? 'checked' : '' ?>>
              <label class="form-check-label" for="menu_login">Mostrar "Ingresar" (visitante)</label>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12">
        <button class="btn btn-brand">Guardar configuracion</button>
      </div>
    </form>
  </div>
</div>
