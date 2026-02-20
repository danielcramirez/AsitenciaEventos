<?php
declare(strict_types=1);

class ReferralModel {
  public static function getSummary(int $enlace_user_id): array {
    $st = db()->prepare("SELECT COUNT(*) AS total FROM users WHERE role='ELECTOR' AND referred_by_user_id=?");
    $st->execute([$enlace_user_id]);
    $total = (int)($st->fetch()['total'] ?? 0);

    $st2 = db()->prepare("
      SELECT COUNT(DISTINCT r.id) AS total
      FROM users u
      JOIN user_person up ON up.user_id=u.id
      JOIN persons p ON p.id=up.person_id
      LEFT JOIN registrations r ON r.person_id=p.id AND r.status='ACTIVE'
      WHERE u.role='ELECTOR' AND u.referred_by_user_id=?
    ");
    $st2->execute([$enlace_user_id]);
    $registered = (int)($st2->fetch()['total'] ?? 0);

    $st3 = db()->prepare("
      SELECT COUNT(DISTINCT c.id) AS total
      FROM users u
      JOIN user_person up ON up.user_id=u.id
      JOIN persons p ON p.id=up.person_id
      JOIN registrations r ON r.person_id=p.id AND r.status='ACTIVE'
      JOIN checkins c ON c.registration_id=r.id
      WHERE u.role='ELECTOR' AND u.referred_by_user_id=?
    ");
    $st3->execute([$enlace_user_id]);
    $attended = (int)($st3->fetch()['total'] ?? 0);

    return [
      'total_referidos' => $total,
      'total_registros_evento' => $registered,
      'total_asistencias' => $attended,
    ];
  }

  public static function getElectorsByEnlace(int $enlace_user_id): array {
    $sql = "
      SELECT
        u.id AS user_id,
        u.email,
        u.created_at AS fecha_registro_usuario,
        p.cedula,
        p.nombres,
        p.apellidos,
        p.celular,
        COUNT(DISTINCT r.id) AS eventos_registrados,
        COUNT(DISTINCT c.id) AS eventos_asistidos,
        MAX(r.created_at) AS ultima_inscripcion,
        MAX(c.checkin_at) AS ultima_asistencia
      FROM users u
      JOIN user_person up ON up.user_id=u.id
      JOIN persons p ON p.id=up.person_id
      LEFT JOIN registrations r ON r.person_id=p.id AND r.status='ACTIVE'
      LEFT JOIN checkins c ON c.registration_id=r.id
      WHERE u.role='ELECTOR' AND u.referred_by_user_id=?
      GROUP BY u.id, u.email, u.created_at, p.cedula, p.nombres, p.apellidos, p.celular
      ORDER BY u.created_at DESC
    ";

    $st = db()->prepare($sql);
    $st->execute([$enlace_user_id]);
    return $st->fetchAll();
  }

  public static function getGlobalSummary(): array {
    $st = db()->query("
      SELECT COUNT(*) AS total
      FROM users
      WHERE role = 'ENLACE' AND active = 1
    ");
    $totalEnlaces = (int)($st->fetch()['total'] ?? 0);

    $st2 = db()->query("
      SELECT COUNT(DISTINCT referred_by_user_id) AS total
      FROM users
      WHERE role = 'ELECTOR' AND referred_by_user_id IS NOT NULL
    ");
    $enlacesConReferidos = (int)($st2->fetch()['total'] ?? 0);

    $st3 = db()->query("
      SELECT COUNT(*) AS total
      FROM users u
      JOIN users e ON e.id = u.referred_by_user_id AND e.role = 'ENLACE'
      WHERE u.role = 'ELECTOR'
    ");
    $totalReferidos = (int)($st3->fetch()['total'] ?? 0);

    $st4 = db()->query("
      SELECT COUNT(DISTINCT r.id) AS total
      FROM users u
      JOIN users e ON e.id = u.referred_by_user_id AND e.role = 'ENLACE'
      JOIN user_person up ON up.user_id = u.id
      JOIN persons p ON p.id = up.person_id
      LEFT JOIN registrations r ON r.person_id = p.id AND r.status = 'ACTIVE'
      WHERE u.role = 'ELECTOR'
    ");
    $totalRegistros = (int)($st4->fetch()['total'] ?? 0);

    $st5 = db()->query("
      SELECT COUNT(DISTINCT c.id) AS total
      FROM users u
      JOIN users e ON e.id = u.referred_by_user_id AND e.role = 'ENLACE'
      JOIN user_person up ON up.user_id = u.id
      JOIN persons p ON p.id = up.person_id
      JOIN registrations r ON r.person_id = p.id AND r.status = 'ACTIVE'
      JOIN checkins c ON c.registration_id = r.id
      WHERE u.role = 'ELECTOR'
    ");
    $totalAsistencias = (int)($st5->fetch()['total'] ?? 0);

    return [
      'total_enlaces' => $totalEnlaces,
      'enlaces_con_referidos' => $enlacesConReferidos,
      'total_referidos' => $totalReferidos,
      'total_registros_evento' => $totalRegistros,
      'total_asistencias' => $totalAsistencias,
    ];
  }

  public static function getSummaryByEnlace(): array {
    $sql = "
      SELECT
        e.id AS enlace_user_id,
        e.email AS enlace_email,
        e.referral_code,
        COUNT(DISTINCT u.id) AS total_referidos,
        COUNT(DISTINCT r.id) AS total_registros_evento,
        COUNT(DISTINCT c.id) AS total_asistencias
      FROM users e
      LEFT JOIN users u ON u.referred_by_user_id = e.id AND u.role = 'ELECTOR'
      LEFT JOIN user_person up ON up.user_id = u.id
      LEFT JOIN persons p ON p.id = up.person_id
      LEFT JOIN registrations r ON r.person_id = p.id AND r.status = 'ACTIVE'
      LEFT JOIN checkins c ON c.registration_id = r.id
      WHERE e.role = 'ENLACE' AND e.active = 1
      GROUP BY e.id, e.email, e.referral_code
      ORDER BY total_referidos DESC, e.id ASC
    ";

    $st = db()->query($sql);
    return $st->fetchAll();
  }

  public static function getAllReferredElectors(): array {
    $sql = "
      SELECT
        e.id AS enlace_user_id,
        e.email AS enlace_email,
        e.referral_code,
        u.id AS user_id,
        u.email,
        u.created_at AS fecha_registro_usuario,
        p.cedula,
        p.nombres,
        p.apellidos,
        p.celular,
        COUNT(DISTINCT r.id) AS eventos_registrados,
        COUNT(DISTINCT c.id) AS eventos_asistidos,
        MAX(r.created_at) AS ultima_inscripcion,
        MAX(c.checkin_at) AS ultima_asistencia
      FROM users u
      JOIN users e ON e.id = u.referred_by_user_id AND e.role = 'ENLACE'
      LEFT JOIN user_person up ON up.user_id = u.id
      LEFT JOIN persons p ON p.id = up.person_id
      LEFT JOIN registrations r ON r.person_id = p.id AND r.status = 'ACTIVE'
      LEFT JOIN checkins c ON c.registration_id = r.id
      WHERE u.role = 'ELECTOR'
      GROUP BY
        e.id, e.email, e.referral_code,
        u.id, u.email, u.created_at,
        p.cedula, p.nombres, p.apellidos, p.celular
      ORDER BY u.created_at DESC
    ";

    $st = db()->query($sql);
    return $st->fetchAll();
  }
}
