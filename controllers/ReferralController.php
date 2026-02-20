<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/bootstrap.php';
require_once __DIR__ . '/../models/ReferralModel.php';
require_once __DIR__ . '/../models/UserModel.php';

class ReferralController {
  public static function index(): void {
    require_auth();

    $u = current_user();
    if (!$u || !in_array($u['role'], [ROLE_ENLACE, ROLE_ADMIN], true)) {
      render_error('No autorizado', 403);
    }

    if ($u['role'] === ROLE_ADMIN) {
      $summary = ReferralModel::getGlobalSummary();
      $summaryByEnlace = ReferralModel::getSummaryByEnlace();
      $electors = ReferralModel::getAllReferredElectors();

      render_view('layout/header', ['title' => 'Dashboard de referidos']);
      render_view('referrals/index', [
        'is_admin_dashboard' => true,
        'summary' => $summary,
        'summary_by_enlace' => $summaryByEnlace,
        'electors' => $electors,
      ]);
      render_view('layout/footer');
      return;
    }

    $enlace_user_id = (int)$u['id'];
    $enlace = UserModel::findById($enlace_user_id);
    if (!$enlace || $enlace['role'] !== ROLE_ENLACE) {
      render_error('Enlace no encontrado', 404);
    }

    $code = UserModel::ensureReferralCode($enlace_user_id) ?? ($enlace['referral_code'] ?? '');
    $summary = ReferralModel::getSummary($enlace_user_id);
    $electors = ReferralModel::getElectorsByEnlace($enlace_user_id);

    render_view('layout/header', ['title' => 'Mis referidos']);
    render_view('referrals/index', [
      'is_admin_dashboard' => false,
      'enlace' => $enlace,
      'referral_code' => $code,
      'summary' => $summary,
      'electors' => $electors,
    ]);
    render_view('layout/footer');
  }
}
