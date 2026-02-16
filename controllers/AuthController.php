<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/bootstrap.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/SecurityModel.php';
require_once __DIR__ . '/../models/AuditLogModel.php';

class AuthController {
  public static function login(): void {
    $error = null;
    $notice = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      csrf_check();
      $email = trim((string)($_POST['email'] ?? ''));
      $pass = (string)($_POST['password'] ?? '');
      $ip = get_client_ip();

      if (!validate_email($email) || $pass === '') {
        $error = 'Credenciales inválidas.';
      } else {
        $blocked = SecurityModel::isLoginBlocked($email, $ip);
        if ($blocked['blocked']) {
          $notice = 'Demasiados intentos. Intenta en ' . $blocked['minutes'] . ' min.';
          AuditLogModel::log('login_blocked', null, null, ['email' => $email]);
        } else {
          $u = UserModel::findActiveByEmail($email);
          if ($u && password_verify($pass, $u['password_hash'])) {
            SecurityModel::clearLoginAttempts($email, $ip);
            login_user($u);
            AuditLogModel::log('login_success', (int)$u['id'], null, ['email' => $email]);
            header('Location: ' . BASE_URL . '/index.php');
            exit;
          }
          SecurityModel::registerLoginAttempt($email, $ip);
          AuditLogModel::log('login_failed', $u ? (int)$u['id'] : null, null, ['email' => $email]);
          $error = 'Credenciales inválidas.';
        }
      }
    }

    render_view('layout/header', ['title' => 'Ingresar']);
    render_view('auth/login', ['error' => $error, 'notice' => $notice]);
    render_view('layout/footer');
  }

  public static function logout(): void {
    $u = current_user();
    if ($u) {
      AuditLogModel::log('logout', (int)$u['id'], null, []);
    }
    logout_user();
    header('Location: ' . BASE_URL . '/login.php');
    exit;
  }
}
