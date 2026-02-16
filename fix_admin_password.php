<?php
require_once __DIR__ . '/config.php';

$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
$pdo = new PDO($dsn, DB_USER, DB_PASS);

$hash = '$2y$10$r.GRXmkyeQEeFi.n.5lGkegyZjpxjZOILC3P.MB/UHQkbQ7f56vTO';
$pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?")
  ->execute([$hash, 'admin@local']);

echo "✓ Contraseña actualizada. Usa: admin@local / Admin123*\n";
