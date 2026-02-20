<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/bootstrap.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/SecurityModel.php';
require_once __DIR__ . '/../models/AuditLogModel.php';
require_once __DIR__ . '/../models/PersonModel.php';

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
            if ($u['role'] === ROLE_ENLACE) {
              UserModel::ensureReferralCode((int)$u['id']);
              $u = UserModel::findById((int)$u['id']) ?? $u;
            }
            SecurityModel::clearLoginAttempts($email, $ip);
            login_user($u);
            AuditLogModel::log('login_success', (int)$u['id'], null, ['email' => $email]);
            $redirect = $_SESSION['redirect_after_login'] ?? '';
            unset($_SESSION['redirect_after_login']);

            if ($redirect === '' || $redirect === BASE_URL || $redirect === BASE_URL . '/' || $redirect === BASE_URL . '/index.php') {
              $redirect = PermissionManager::isAdmin() ? (BASE_URL . '/admin_eventos') : (BASE_URL . '/');
            }

            header('Location: ' . $redirect);
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

  public static function register(): void {
    $error = null;
    $success = null;
    $old = [
      'cedula' => '',
      'nombres' => '',
      'apellidos' => '',
      'celular' => '',
      'email' => '',
      'referral_code' => '',
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      csrf_check();
      $cedula = trim((string)($_POST['cedula'] ?? ''));
      $nombres = trim((string)($_POST['nombres'] ?? ''));
      $apellidos = trim((string)($_POST['apellidos'] ?? ''));
      $celular = trim((string)($_POST['celular'] ?? ''));
      $email = trim((string)($_POST['email'] ?? ''));
      $password = (string)($_POST['password'] ?? '');
      $referralCode = strtoupper(trim((string)($_POST['referral_code'] ?? '')));
      $old = [
        'cedula' => $cedula,
        'nombres' => $nombres,
        'apellidos' => $apellidos,
        'celular' => $celular,
        'email' => $email,
        'referral_code' => $referralCode,
      ];

      if (!validate_cedula($cedula)) {
        $error = 'Cedula invalida.';
      } elseif (!validate_name($nombres) || !validate_name($apellidos)) {
        $error = 'Nombre invalido.';
      } elseif (!validate_phone($celular)) {
        $error = 'Celular invalido.';
      } elseif (!validate_email($email)) {
        $error = 'Email invalido.';
      } elseif (strlen($password) < 8) {
        $error = 'La contrasena debe tener minimo 8 caracteres.';
      } elseif (UserModel::emailExists($email)) {
        $error = 'El email ya esta registrado.';
      } else {
        $referredBy = null;
        if ($referralCode !== '') {
          $enlace = UserModel::findByReferralCode($referralCode);
          if (!$enlace) {
            $error = 'Codigo de referido no valido.';
          } else {
            $referredBy = (int)$enlace['id'];
          }
        }
      }

      if ($error === null) {
        $pdo = db();
        $pdo->beginTransaction();
        try {
          $person_id = PersonModel::upsert($cedula, $nombres, $apellidos, $celular ?: null);
          $existingUser = db()->prepare('SELECT user_id FROM user_person WHERE person_id=? LIMIT 1');
          $existingUser->execute([$person_id]);
          if ($existingUser->fetch()) {
            throw new RuntimeException('La cedula ya esta asociada a otro usuario.');
          }

          $hash = password_hash($password, PASSWORD_BCRYPT);
          $newUserId = UserModel::create($email, $hash, ROLE_ELECTOR, $referredBy, null);
          UserModel::linkUserPerson($newUserId, $person_id);

          $pdo->commit();

          $u = UserModel::findById($newUserId);
          if ($u) {
            login_user($u);
          }
          AuditLogModel::log('self_register_elector', $newUserId, null, ['email' => $email, 'referred_by' => $referredBy]);
          header('Location: ' . BASE_URL . '/');
          exit;
        } catch (Throwable $e) {
          $pdo->rollBack();
          $error = $e->getMessage();
        }
      }
    }

    render_view('layout/header', ['title' => 'Registro']);
    render_view('auth/register', ['error' => $error, 'success' => $success, 'old' => $old]);
    render_view('layout/footer');
  }

  public static function logout(): void {
    $u = current_user();
    if ($u) {
      AuditLogModel::log('logout', (int)$u['id'], null, []);
    }
    logout_user();
    header('Location: ' . BASE_URL . '/login');
    exit;
  }
}
