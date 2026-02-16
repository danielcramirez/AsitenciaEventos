<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

$t = $_GET['t'] ?? '';
if (!$t || strlen($t) < 20) { http_response_code(400); exit; }

try {
  $result = (new Builder(
    writer: new PngWriter(),
    data: $t,
    size: 320,
    margin: 10
  ))->build();

  header('Content-Type: image/png');
  echo $result->getString();
} catch (Throwable $e) {
  http_response_code(500);
  echo "Error generando QR: " . $e->getMessage();
}
