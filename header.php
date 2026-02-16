<?php require_once __DIR__ . '/auth.php'; require_once __DIR__ . '/helpers.php'; ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h(APP_NAME) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root{
      --brand:#006838; --brand2:#0D9A49; --warn:#F5EB28; --accent:#F89621;
    }
    .btn-brand{ background:var(--brand); color:#fff; }
    .btn-brand:hover{ background:var(--brand2); color:#fff; }
    .badge-brand{ background:var(--brand); }
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
