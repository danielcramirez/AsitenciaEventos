<?php
$title = $title ?? APP_NAME;
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($title) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root{
      --brand:#006838; --brand2:#0D9A49; --warn:#F5EB28; --accent:#F89621;
      --hc-bg:#0b0b0b; --hc-fg:#f5f5f5; --hc-accent:#f5eb28;
    }
    .btn-brand{ background:var(--brand); color:#fff; }
    .btn-brand:hover{ background:var(--brand2); color:#fff; }
    .badge-brand{ background:var(--brand); }

    body.hc-mode{ background:var(--hc-bg) !important; color:var(--hc-fg) !important; }
    body.hc-mode .card, body.hc-mode .navbar{ background:#141414 !important; color:var(--hc-fg) !important; }
    body.hc-mode .btn-brand{ background:var(--hc-accent); color:#000; }
    body.hc-mode .btn-outline-dark, body.hc-mode .btn-outline-secondary{
      border-color:var(--hc-accent); color:var(--hc-accent);
    }
  </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark" style="background:var(--brand);">
  <div class="container">
    <a class="navbar-brand" href="<?= BASE_URL ?>/index.php"><?= h(APP_NAME) ?></a>
    <div class="ms-auto d-flex gap-2">
      <?php if (current_user()): ?>
        <span class="navbar-text me-2"><?= h(current_user()['email']) ?> (<?= h(current_user()['role']) ?>)</span>
        <a class="btn btn-sm btn-light" href="<?= BASE_URL ?>/logout.php">Salir</a>
      <?php else: ?>
        <a class="btn btn-sm btn-light" href="<?= BASE_URL ?>/login.php">Ingresar</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
<div class="container py-4">
