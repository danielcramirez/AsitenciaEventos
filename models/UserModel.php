<?php
declare(strict_types=1);

class UserModel {
  public static function findActiveByEmail(string $email): ?array {
    $st = db()->prepare('SELECT * FROM users WHERE email = ? AND active = 1 LIMIT 1');
    $st->execute([$email]);
    $row = $st->fetch();
    return $row ?: null;
  }
}
