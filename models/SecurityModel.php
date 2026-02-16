<?php
declare(strict_types=1);

class SecurityModel {
  public static function isLoginBlocked(string $email, string $ip): array {
    $st = db()->prepare('SELECT attempts, blocked_until FROM login_attempts WHERE email=? AND ip=? LIMIT 1');
    $st->execute([$email, $ip]);
    $row = $st->fetch();

    if (!$row || empty($row['blocked_until'])) {
      return ['blocked' => false, 'minutes' => 0];
    }

    $blocked_until = strtotime((string)$row['blocked_until']);
    if ($blocked_until !== false && $blocked_until > time()) {
      $minutes = (int)ceil(($blocked_until - time()) / 60);
      return ['blocked' => true, 'minutes' => $minutes];
    }

    return ['blocked' => false, 'minutes' => 0];
  }

  public static function registerLoginAttempt(string $email, string $ip): void {
    $st = db()->prepare('SELECT id, attempts, first_attempt_at FROM login_attempts WHERE email=? AND ip=? LIMIT 1');
    $st->execute([$email, $ip]);
    $row = $st->fetch();

    $now = date('Y-m-d H:i:s');
    $window_seconds = LOGIN_BLOCK_MINUTES * 60;

    if ($row) {
      $first_attempt = strtotime((string)$row['first_attempt_at']) ?: time();
      $attempts = (int)$row['attempts'];

      if (time() - $first_attempt > $window_seconds) {
        $attempts = 0;
        $first_attempt = time();
      }

      $attempts++;
      $blocked_until = null;
      if ($attempts >= LOGIN_MAX_ATTEMPTS) {
        $blocked_until = date('Y-m-d H:i:s', time() + $window_seconds);
      }

      $up = db()->prepare('UPDATE login_attempts SET attempts=?, first_attempt_at=?, last_attempt_at=?, blocked_until=? WHERE id=?');
      $up->execute([
        $attempts,
        date('Y-m-d H:i:s', $first_attempt),
        $now,
        $blocked_until,
        (int)$row['id']
      ]);
      return;
    }

    $blocked_until = null;
    if (LOGIN_MAX_ATTEMPTS <= 1) {
      $blocked_until = date('Y-m-d H:i:s', time() + $window_seconds);
    }

    $ins = db()->prepare('INSERT INTO login_attempts(email, ip, attempts, first_attempt_at, last_attempt_at, blocked_until) VALUES(?,?,?,?,?,?)');
    $ins->execute([$email, $ip, 1, $now, $now, $blocked_until]);
  }

  public static function clearLoginAttempts(string $email, string $ip): void {
    $del = db()->prepare('DELETE FROM login_attempts WHERE email=? AND ip=?');
    $del->execute([$email, $ip]);
  }

  public static function checkRateLimit(string $key, int $max, int $window_seconds): array {
    $hash = sha256($key);
    $st = db()->prepare('SELECT id, attempts, window_start FROM rate_limits WHERE key_hash=? LIMIT 1');
    $st->execute([$hash]);
    $row = $st->fetch();

    $now = time();
    if ($row) {
      $window_start = strtotime((string)$row['window_start']) ?: $now;
      $attempts = (int)$row['attempts'];

      if ($now - $window_start > $window_seconds) {
        $attempts = 0;
        $window_start = $now;
      }

      $attempts++;
      $up = db()->prepare('UPDATE rate_limits SET attempts=?, window_start=? WHERE id=?');
      $up->execute([$attempts, date('Y-m-d H:i:s', $window_start), (int)$row['id']]);

      $allowed = $attempts <= $max;
      return ['allowed' => $allowed, 'attempts' => $attempts];
    }

    $ins = db()->prepare('INSERT INTO rate_limits(key_hash, attempts, window_start) VALUES(?,?,?)');
    $ins->execute([$hash, 1, date('Y-m-d H:i:s', $now)]);

    return ['allowed' => true, 'attempts' => 1];
  }
}
