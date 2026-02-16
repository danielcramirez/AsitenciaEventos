<?php
declare(strict_types=1);

class RegistrationModel {
  public static function findByEventAndPerson(int $event_id, int $person_id): ?array {
    $st = db()->prepare('SELECT * FROM registrations WHERE event_id=? AND person_id=? LIMIT 1');
    $st->execute([$event_id, $person_id]);
    $row = $st->fetch();
    return $row ?: null;
  }

  public static function countActiveByEvent(int $event_id): int {
    $st = db()->prepare("SELECT COUNT(*) c FROM registrations WHERE event_id=? AND status='ACTIVE'");
    $st->execute([$event_id]);
    return (int)$st->fetch()['c'];
  }

  public static function create(int $event_id, int $person_id): int {
    $ins = db()->prepare('INSERT INTO registrations(event_id, person_id) VALUES(?,?)');
    $ins->execute([$event_id, $person_id]);
    return (int)db()->lastInsertId();
  }
}
