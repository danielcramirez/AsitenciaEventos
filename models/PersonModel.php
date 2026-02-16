<?php
declare(strict_types=1);

class PersonModel {
  public static function findByCedula(string $cedula): ?array {
    $st = db()->prepare('SELECT * FROM persons WHERE cedula=? LIMIT 1');
    $st->execute([$cedula]);
    $row = $st->fetch();
    return $row ?: null;
  }

  public static function upsert(string $cedula, string $nombres, string $apellidos, ?string $celular): int {
    $st = db()->prepare('SELECT id FROM persons WHERE cedula=? LIMIT 1');
    $st->execute([$cedula]);
    $row = $st->fetch();

    if ($row) {
      $person_id = (int)$row['id'];
      $up = db()->prepare('UPDATE persons SET nombres=?, apellidos=?, celular=? WHERE id=?');
      $up->execute([$nombres, $apellidos, $celular, $person_id]);
      return $person_id;
    }

    $ins = db()->prepare('INSERT INTO persons(cedula,nombres,apellidos,celular) VALUES(?,?,?,?)');
    $ins->execute([$cedula, $nombres, $apellidos, $celular]);
    return (int)db()->lastInsertId();
  }
}
