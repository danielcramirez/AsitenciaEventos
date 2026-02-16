<?php
declare(strict_types=1);

function render_view(string $view, array $data = []): void {
  if (!empty($data)) {
    extract($data, EXTR_SKIP);
  }
  require __DIR__ . '/../views/' . $view . '.php';
}
