<?php
declare(strict_types=1);

class CheckinModel {
  public static function findByTokenHash(string $hash, int $event_id): ?array {
    $sql = "
      SELECT r.id registration_id, r.event_id, r.status,
             p.cedula, p.nombres, p.apellidos,
             (SELECT c.checkin_at FROM checkins c WHERE c.event_id=r.event_id AND c.registration_id=r.id LIMIT 1) as checkin_at
      FROM qr_tokens qt
      JOIN registrations r ON r.id=qt.registration_id
      JOIN persons p ON p.id=r.person_id
      WHERE qt.token_hash=? AND r.event_id=? AND qt.revoked_at IS NULL
      LIMIT 1";

    $st = db()->prepare($sql);
    $st->execute([$hash, $event_id]);
    $row = $st->fetch();
    return $row ?: null;
  }

  public static function createCheckin(int $event_id, int $registration_id, int $operator_user_id): void {
    $ins = db()->prepare('INSERT INTO checkins(event_id,registration_id,operator_user_id) VALUES(?,?,?)');
    $ins->execute([$event_id, $registration_id, $operator_user_id]);
  }

  public static function findCheckinAt(int $event_id, int $registration_id): ?string {
    $st = db()->prepare('SELECT checkin_at FROM checkins WHERE event_id=? AND registration_id=? LIMIT 1');
    $st->execute([$event_id, $registration_id]);
    $row = $st->fetch();
    return $row['checkin_at'] ?? null;
  }
}
