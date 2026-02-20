<?php
$title = $title ?? APP_NAME;
$design = app_design_settings();
$brandColor = (string)($design['primary_color'] ?? '#006838');
$brandHoverColor = (string)($design['primary_hover_color'] ?? '#0D9A49');
$logoPath = (string)($design['logo_path'] ?? '');
$faviconPath = (string)($design['favicon_path'] ?? '');
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($title) ?></title>
  <?php if ($faviconPath !== ''): ?>
    <link rel="icon" href="<?= h($faviconPath) ?>">
  <?php endif; ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root{
      --brand:<?= h($brandColor) ?>;
      --brand2:<?= h($brandHoverColor) ?>;
      --menu-btn-bg:<?= h((string)($design['menu_button_bg'] ?? '#F5EB28')) ?>;
      --menu-btn-fg:<?= h((string)($design['menu_button_text'] ?? '#111111')) ?>;
      --menu-btn-hover-bg:<?= h((string)($design['menu_button_hover_bg'] ?? '#F89621')) ?>;
      --menu-btn-hover-fg:<?= h((string)($design['menu_button_hover_text'] ?? '#111111')) ?>;
      --menu-btn2-bg:<?= h((string)($design['menu_secondary_bg'] ?? '#FFFFFF')) ?>;
      --menu-btn2-fg:<?= h((string)($design['menu_secondary_text'] ?? '#111111')) ?>;
      --menu-btn2-hover-bg:<?= h((string)($design['menu_secondary_hover_bg'] ?? '#E9ECEF')) ?>;
      --menu-btn2-hover-fg:<?= h((string)($design['menu_secondary_hover_text'] ?? '#111111')) ?>;
      --hc-bg:#0b0b0b; --hc-fg:#f5f5f5; --hc-accent:#f5eb28;
    }
    .btn-brand{ background:var(--brand); color:#fff; }
    .btn-brand:hover{ background:var(--brand2); color:#fff; }
    .badge-brand{ background:var(--brand); }
    .btn-menu{
      background:var(--menu-btn-bg);
      color:var(--menu-btn-fg);
      border:1px solid transparent;
    }
    .btn-menu:hover{
      background:var(--menu-btn-hover-bg);
      color:var(--menu-btn-hover-fg);
    }
    .btn-menu-secondary{
      background:var(--menu-btn2-bg);
      color:var(--menu-btn2-fg);
      border:1px solid transparent;
    }
    .btn-menu-secondary:hover{
      background:var(--menu-btn2-hover-bg);
      color:var(--menu-btn2-hover-fg);
    }

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
    <a class="navbar-brand d-flex align-items-center gap-2" href="<?= BASE_URL ?>/">
      <?php if ($logoPath !== ''): ?>
        <img src="<?= h($logoPath) ?>" alt="Logo" style="height:32px;width:auto;">
      <?php endif; ?>
      <span><?= h(APP_NAME) ?></span>
    </a>
    <div class="ms-auto d-flex gap-2">
      <?php if (current_user()): ?>
        <?php if ((int)($design['menu_show_verificar_qr'] ?? 1) === 1 && in_array((string)current_user()['role'], [ROLE_OPERATOR, ROLE_ADMIN], true)): ?>
          <a class="btn btn-sm btn-menu" href="<?= BASE_URL ?>/puerta">Verificar QR</a>
        <?php endif; ?>
        <?php if ((int)($design['menu_show_mis_referidos'] ?? 1) === 1 && in_array((string)current_user()['role'], [ROLE_ENLACE, ROLE_ADMIN], true)): ?>
          <a class="btn btn-sm btn-menu" href="<?= BASE_URL ?>/mis_referidos">Mis referidos</a>
        <?php endif; ?>
        <?php if ((int)($design['menu_show_admin_eventos'] ?? 1) === 1 && (string)current_user()['role'] === ROLE_ADMIN): ?>
          <a class="btn btn-sm btn-menu" href="<?= BASE_URL ?>/admin_eventos">Admin eventos</a>
        <?php endif; ?>
        <?php if ((string)current_user()['role'] === ROLE_ADMIN): ?>
          <a class="btn btn-sm btn-menu-secondary" href="<?= BASE_URL ?>/parametrizacion_diseno">Diseno</a>
        <?php endif; ?>
        <span class="navbar-text me-2"><?= h(current_user()['email']) ?> (<?= h(current_user()['role']) ?>)</span>
        <a class="btn btn-sm btn-menu-secondary" href="<?= BASE_URL ?>/logout">Salir</a>
      <?php else: ?>
        <?php if ((int)($design['menu_show_registro'] ?? 1) === 1): ?>
          <a class="btn btn-sm btn-menu-secondary" href="<?= BASE_URL ?>/registro">Registro</a>
        <?php endif; ?>
        <?php if ((int)($design['menu_show_login'] ?? 1) === 1): ?>
          <a class="btn btn-sm btn-menu-secondary" href="<?= BASE_URL ?>/login">Ingresar</a>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</nav>
<div class="container py-4">
