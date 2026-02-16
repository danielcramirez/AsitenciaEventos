<?php
declare(strict_types=1);

class EventModel {
  public static function getPublished(): array {
    return db()->query("SELECT * FROM events WHERE estado='PUBLISHED' ORDER BY fecha_inicio DESC")->fetchAll();
  }

  public static function findById(int $id): ?array {
    $st = db()->prepare('SELECT * FROM events WHERE id=? LIMIT 1');
    $st->execute([$id]);
    $row = $st->fetch();
    return $row ?: null;
  }

  public static function findPublishedById(int $id): ?array {
    $st = db()->prepare("SELECT * FROM events WHERE id=? AND estado='PUBLISHED' LIMIT 1");
    $st->execute([$id]);
    $row = $st->fetch();
    return $row ?: null;
  }

  public static function create(array $data): int {
    $st = db()->prepare('INSERT INTO events(nombre,lugar,fecha_inicio,fecha_fin,cupo,estado) VALUES(?,?,?,?,?,?)');
    $st->execute([
      $data['nombre'],
      $data['lugar'],
      $data['fecha_inicio'],
      $data['fecha_fin'],
      (int)$data['cupo'],
      $data['estado']
    ]);
    return (int)db()->lastInsertId();
  }

  public static function all(): array {
    return db()->query('SELECT * FROM events ORDER BY id DESC')->fetchAll();
  }
}
