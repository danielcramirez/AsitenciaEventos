<?php
declare(strict_types=1);

function render_view(string $view, array $vars = []): void {
  if (!empty($vars)) {
    extract($vars, EXTR_SKIP);
  }
  require __DIR__ . '/../views/' . $view . '.php';
}
