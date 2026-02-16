<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

function db(): PDO {
  static $pdo = null;
  if ($pdo) return $pdo;

  $host = getDbConfig('host', 'localhost');
  $name = getDbConfig('name', 'eventos');
  $user = getDbConfig('user', 'root');
  $pass = getDbConfig('pass', '');

  $dsn = 'mysql:host=' . $host . ';dbname=' . $name . ';charset=' . DB_CHARSET;
  $pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
  ]);
  return $pdo;
}
