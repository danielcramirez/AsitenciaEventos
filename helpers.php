<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

function h(string $s): string {
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
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
    $result = (new Builder(
      writer: new PngWriter(),
      data: $data,
      size: 320,
      margin: 10
    ))->build();
    
    return 'data:image/png;base64,' . base64_encode($result->getString());
  } catch (Throwable $e) {
    return '';
  }
}