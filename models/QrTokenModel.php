<?php
declare(strict_types=1);

class QrTokenModel {
  public static function findByRegistrationId(int $registration_id): ?array {
    $st = db()->prepare('SELECT * FROM qr_tokens WHERE registration_id=? LIMIT 1');
    $st->execute([$registration_id]);
    $row = $st->fetch();
    return $row ?: null;
  }

  public static function create(int $registration_id): array {
    $token = new_token();
    $hash = sha256($token);
    $qr_image = generate_qr_base64($token);
    if ($qr_image === '') {
      throw new RuntimeException('No se pudo generar el QR. Verifique ext-gd e intente nuevamente.');
    }

    $ins = db()->prepare('INSERT INTO qr_tokens(registration_id, token_hash, qr_image_base64) VALUES(?,?,?)');
    $ins->execute([$registration_id, $hash, $qr_image]);

    return ['token' => $token, 'hash' => $hash, 'qr_image' => $qr_image];
  }

  public static function rotate(int $registration_id): array {
    $token = new_token();
    $hash = sha256($token);
    $qr_image = generate_qr_base64($token);
    if ($qr_image === '') {
      throw new RuntimeException('No se pudo regenerar el QR. Verifique ext-gd e intente nuevamente.');
    }

    $up = db()->prepare('UPDATE qr_tokens SET token_hash=?, qr_image_base64=?, issued_at=NOW(), revoked_at=NULL WHERE registration_id=?');
    $up->execute([$hash, $qr_image, $registration_id]);

    return ['token' => $token, 'hash' => $hash, 'qr_image' => $qr_image];
  }
}
