<?php
declare(strict_types=1);

class UserModel {
  private static bool $schemaChecked = false;

  private static function ensureReferralSchema(): void {
    if (self::$schemaChecked) {
      return;
    }

    $pdo = db();

    try {
      $roleInfo = $pdo->query("
        SELECT COLUMN_TYPE
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'users'
          AND COLUMN_NAME = 'role'
        LIMIT 1
      ")->fetch();

      $roleType = strtolower((string)($roleInfo['COLUMN_TYPE'] ?? ''));
      if ($roleType !== '' && (!str_contains($roleType, "'enlace'") || !str_contains($roleType, "'elector'"))) {
        $pdo->exec("ALTER TABLE users MODIFY role ENUM('ADMIN','OPERATOR','ENLACE','ELECTOR','ATTENDEE') NOT NULL");
      }

      $hasReferralCode = $pdo->query("
        SELECT COUNT(*) c
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'users'
          AND COLUMN_NAME = 'referral_code'
      ")->fetch();
      if ((int)($hasReferralCode['c'] ?? 0) === 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN referral_code VARCHAR(24) NULL UNIQUE");
      }

      $hasReferredBy = $pdo->query("
        SELECT COUNT(*) c
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'users'
          AND COLUMN_NAME = 'referred_by_user_id'
      ")->fetch();
      if ((int)($hasReferredBy['c'] ?? 0) === 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN referred_by_user_id INT NULL");
      }

      $fkExists = $pdo->query("
        SELECT COUNT(*) c
        FROM information_schema.REFERENTIAL_CONSTRAINTS
        WHERE CONSTRAINT_SCHEMA = DATABASE()
          AND CONSTRAINT_NAME = 'fk_users_referred_by'
      ")->fetch();
      if ((int)($fkExists['c'] ?? 0) === 0) {
        $pdo->exec("ALTER TABLE users ADD CONSTRAINT fk_users_referred_by FOREIGN KEY (referred_by_user_id) REFERENCES users(id)");
      }
    } catch (Throwable $e) {
      throw new RuntimeException('No se pudo actualizar el esquema de usuarios para referidos: ' . $e->getMessage());
    }

    self::$schemaChecked = true;
  }

  public static function findActiveByEmail(string $email): ?array {
    self::ensureReferralSchema();
    $st = db()->prepare('SELECT * FROM users WHERE email = ? AND active = 1 LIMIT 1');
    $st->execute([$email]);
    $row = $st->fetch();
    return $row ?: null;
  }

  public static function findById(int $id): ?array {
    self::ensureReferralSchema();
    $st = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $st->execute([$id]);
    $row = $st->fetch();
    return $row ?: null;
  }

  public static function findByReferralCode(string $code): ?array {
    self::ensureReferralSchema();
    $st = db()->prepare("SELECT * FROM users WHERE referral_code = ? AND role = 'ENLACE' AND active = 1 LIMIT 1");
    $st->execute([$code]);
    $row = $st->fetch();
    return $row ?: null;
  }

  public static function emailExists(string $email): bool {
    self::ensureReferralSchema();
    $st = db()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $st->execute([$email]);
    return (bool)$st->fetch();
  }

  public static function create(string $email, string $password_hash, string $role, ?int $referred_by_user_id = null, ?string $referral_code = null): int {
    self::ensureReferralSchema();
    $ins = db()->prepare('INSERT INTO users(email, password_hash, role, active, referred_by_user_id, referral_code) VALUES(?,?,?,?,?,?)');
    $ins->execute([$email, $password_hash, $role, 1, $referred_by_user_id, $referral_code]);
    return (int)db()->lastInsertId();
  }

  public static function linkUserPerson(int $user_id, int $person_id): void {
    $ins = db()->prepare('INSERT INTO user_person(user_id, person_id) VALUES(?,?)');
    $ins->execute([$user_id, $person_id]);
  }

  public static function findPersonByUserId(int $user_id): ?array {
    $st = db()->prepare('SELECT p.* FROM persons p JOIN user_person up ON up.person_id=p.id WHERE up.user_id=? LIMIT 1');
    $st->execute([$user_id]);
    $row = $st->fetch();
    return $row ?: null;
  }

  public static function ensureReferralCode(int $user_id): ?string {
    self::ensureReferralSchema();
    $user = self::findById($user_id);
    if (!$user || $user['role'] !== ROLE_ENLACE) {
      return null;
    }
    if (!empty($user['referral_code'])) {
      return (string)$user['referral_code'];
    }

    for ($i = 0; $i < 20; $i++) {
      $code = 'ENL' . strtoupper(substr(bin2hex(random_bytes(5)), 0, 8));
      try {
        $up = db()->prepare('UPDATE users SET referral_code=? WHERE id=? AND referral_code IS NULL');
        $up->execute([$code, $user_id]);
        if ($up->rowCount() > 0) {
          return $code;
        }
      } catch (Throwable $e) {
      }
    }

    return null;
  }
}
