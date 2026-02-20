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
