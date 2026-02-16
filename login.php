<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $email = trim($_POST['email'] ?? '');
  $pass = $_POST['password'] ?? '';

  $stmt = db()->prepare("SELECT * FROM users WHERE email = ? AND active=1 LIMIT 1");
  $stmt->execute([$email]);
  $u = $stmt->fetch();

  if ($u && password_verify($pass, $u['password_hash'])) {
    login_user($u);
    header('Location: ' . BASE_URL . '/index.php');
    exit;
  }
  $error = "Credenciales inválidas";
}

require __DIR__ . '/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card shadow-sm">
      <div class="card-body">
        <h4 class="mb-3">Ingresar</h4>
        <?php if ($error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?>
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
<?php require __DIR__ . '/footer.php'; ?>
