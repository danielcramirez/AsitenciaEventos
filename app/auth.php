<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
  session_start();
}

function current_user(): ?array {
  return $_SESSION['user'] ?? null;
}

function require_login(): void {
  if (!current_user()) {
    header('Location: ' . BASE_URL . '/login');
    exit;
  }
}

/**
 * DEPRECATED: Usar PermissionManager::requireRole() desde app/permissions.php
 * O la función global require_role(string $role) que se carga en bootstrap
 */
function require_role_legacy(array $roles): void {
  require_login();
  $u = current_user();
  if (!$u || !in_array($u['role'], $roles, true)) {
    http_response_code(403);
    echo "403 Forbidden";
    exit;
  }
}

function login_user(array $user): void {
  $_SESSION['user_id'] = (int)$user['id'];
  $_SESSION['email'] = $user['email'];
  $_SESSION['role'] = $user['role'];  // ← Para PermissionManager::getCurrentRole()
  
  // Legacy structure (deprecated)
  $_SESSION['user'] = [
    'id' => (int)$user['id'],
    'email' => $user['email'],
    'role' => $user['role'],
    'referral_code' => $user['referral_code'] ?? null,
  ];
}

function logout_user(): void {
  $_SESSION = [];
  if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
      $params["path"], $params["domain"], $params["secure"], $params["httponly"]
    );
  }
  session_destroy();
}
