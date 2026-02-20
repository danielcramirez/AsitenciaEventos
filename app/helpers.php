<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

function h(?string $s): string {
  return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string {
  if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
  return $_SESSION['csrf'];
}

function csrf_check(): void {
  $t = $_POST['csrf'] ?? '';
  if (!$t || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $t)) {
    http_response_code(400);
    exit('CSRF inválido');
  }
}

function sha256(string $s): string {
  return hash('sha256', $s);
}

function new_token(): string {
  return bin2hex(random_bytes(32)); // 64 hex chars
}
function generate_qr_base64(string $data): string {
  try {
    $builder = new Builder(writer: new PngWriter());
    $result = $builder->build(data: $data, size: 320, margin: 10);
    
    return 'data:image/png;base64,' . base64_encode($result->getString());
  } catch (Throwable $e) {
    error_log('QR generation failed: ' . $e->getMessage());
    return '';
  }
}

function get_client_ip(): string {
  $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
  foreach ($keys as $k) {
    if (!empty($_SERVER[$k])) {
      $ip = trim(explode(',', (string)$_SERVER[$k])[0]);
      if ($ip !== '') return $ip;
    }
  }
  return '0.0.0.0';
}

function get_user_agent(): string {
  return substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
}

function json_response(array $payload, int $code = 200): void {
  http_response_code($code);
  header('Content-Type: application/json');
  echo json_encode($payload);
  exit;
}

function render_error(string $message, int $code = 400): void {
  http_response_code($code);
  echo h($message);
  exit;
}

function validate_email(string $email): bool {
  if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    return true;
  }
  return (bool)preg_match('/^[^@\s]+@[^@\s]+$/', $email);
}

function validate_cedula(string $cedula): bool {
  return (bool)preg_match('/^[0-9]{5,20}$/', $cedula);
}

function validate_name(string $name): bool {
  return (bool)preg_match('/^[A-Za-z0-9 .\'-]{1,120}$/', $name);
}

function validate_phone(string $phone): bool {
  if ($phone === '') return true;
  return (bool)preg_match('/^[0-9+() -]{6,30}$/', $phone);
}

function app_design_settings(): array {
  static $settings = null;
  if ($settings !== null) {
    return $settings;
  }

  $defaults = [
    'primary_color' => '#006838',
    'primary_hover_color' => '#0D9A49',
    'logo_path' => null,
    'favicon_path' => null,
    'menu_button_bg' => '#F5EB28',
    'menu_button_text' => '#111111',
    'menu_button_hover_bg' => '#F89621',
    'menu_button_hover_text' => '#111111',
    'menu_secondary_bg' => '#FFFFFF',
    'menu_secondary_text' => '#111111',
    'menu_secondary_hover_bg' => '#E9ECEF',
    'menu_secondary_hover_text' => '#111111',
    'menu_show_admin_eventos' => 1,
    'menu_show_verificar_qr' => 1,
    'menu_show_mis_referidos' => 1,
    'menu_show_registro' => 1,
    'menu_show_login' => 1,
  ];

  $modelFile = __DIR__ . '/../models/DesignSettingsModel.php';
  if (!file_exists($modelFile)) {
    $settings = $defaults;
    return $settings;
  }

  require_once $modelFile;
  if (!class_exists('DesignSettingsModel')) {
    $settings = $defaults;
    return $settings;
  }

  try {
    $raw = DesignSettingsModel::get();
    $settings = array_merge($defaults, $raw);
  } catch (Throwable $e) {
    $settings = $defaults;
  }

  $colorKeys = [
    'primary_color',
    'primary_hover_color',
    'menu_button_bg',
    'menu_button_text',
    'menu_button_hover_bg',
    'menu_button_hover_text',
    'menu_secondary_bg',
    'menu_secondary_text',
    'menu_secondary_hover_bg',
    'menu_secondary_hover_text',
  ];
  foreach ($colorKeys as $key) {
    if (!preg_match('/^#[0-9A-Fa-f]{6}$/', (string)($settings[$key] ?? ''))) {
      $settings[$key] = $defaults[$key];
    }
  }

  return $settings;
}
