<?php
declare(strict_types=1);

class AuditLogModel {
  public static function log(string $action, ?int $user_id, ?int $event_id, array $meta = []): void {
    $ip = get_client_ip();
    $ua = get_user_agent();
    $meta_json = $meta ? json_encode($meta) : null;

    $ins = db()->prepare('INSERT INTO audit_logs(user_id, event_id, action, ip, user_agent, meta) VALUES(?,?,?,?,?,?)');
    $ins->execute([$user_id, $event_id, $action, $ip, $ua, $meta_json]);
  }
}
