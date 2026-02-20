<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/bootstrap.php';
require_once __DIR__ . '/../models/DesignSettingsModel.php';

class DesignSettingsController {
  public static function index(): void {
    require_auth();
    require_role(ROLE_ADMIN);

    $error = null;
    $success = null;
    $settings = DesignSettingsModel::get();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      csrf_check();

      $primaryColor = self::readHexColor('primary_color', $_POST);
      $primaryHoverColor = self::readHexColor('primary_hover_color', $_POST);
      $menuButtonBg = self::readHexColor('menu_button_bg', $_POST);
      $menuButtonText = self::readHexColor('menu_button_text', $_POST);
      $menuButtonHoverBg = self::readHexColor('menu_button_hover_bg', $_POST);
      $menuButtonHoverText = self::readHexColor('menu_button_hover_text', $_POST);
      $menuSecondaryBg = self::readHexColor('menu_secondary_bg', $_POST);
      $menuSecondaryText = self::readHexColor('menu_secondary_text', $_POST);
      $menuSecondaryHoverBg = self::readHexColor('menu_secondary_hover_bg', $_POST);
      $menuSecondaryHoverText = self::readHexColor('menu_secondary_hover_text', $_POST);
      $removeLogo = isset($_POST['remove_logo']) && $_POST['remove_logo'] === '1';
      $removeFavicon = isset($_POST['remove_favicon']) && $_POST['remove_favicon'] === '1';

      if ($primaryColor === null || $primaryHoverColor === null || $menuButtonBg === null || $menuButtonText === null || $menuButtonHoverBg === null || $menuButtonHoverText === null || $menuSecondaryBg === null || $menuSecondaryText === null || $menuSecondaryHoverBg === null || $menuSecondaryHoverText === null) {
        $error = 'Todos los colores deben usar el formato #RRGGBB.';
      } else {
        $payload = [
          'primary_color' => $primaryColor,
          'primary_hover_color' => $primaryHoverColor,
          'menu_button_bg' => $menuButtonBg,
          'menu_button_text' => $menuButtonText,
          'menu_button_hover_bg' => $menuButtonHoverBg,
          'menu_button_hover_text' => $menuButtonHoverText,
          'menu_secondary_bg' => $menuSecondaryBg,
          'menu_secondary_text' => $menuSecondaryText,
          'menu_secondary_hover_bg' => $menuSecondaryHoverBg,
          'menu_secondary_hover_text' => $menuSecondaryHoverText,
          'menu_show_admin_eventos' => isset($_POST['menu_show_admin_eventos']) ? 1 : 0,
          'menu_show_verificar_qr' => isset($_POST['menu_show_verificar_qr']) ? 1 : 0,
          'menu_show_mis_referidos' => isset($_POST['menu_show_mis_referidos']) ? 1 : 0,
          'menu_show_registro' => isset($_POST['menu_show_registro']) ? 1 : 0,
          'menu_show_login' => isset($_POST['menu_show_login']) ? 1 : 0,
        ];

        try {
          $logoPath = self::resolveLogoUpload($settings, $removeLogo);
          if ($logoPath !== null || $removeLogo) {
            $payload['logo_path'] = $logoPath;
          }
          $faviconPath = self::resolveFaviconUpload($settings, $removeFavicon);
          if ($faviconPath !== null || $removeFavicon) {
            $payload['favicon_path'] = $faviconPath;
          }

          DesignSettingsModel::save($payload);
          header('Location: ' . BASE_URL . '/parametrizacion_diseno?ok=1');
          exit;
        } catch (RuntimeException $e) {
          $error = $e->getMessage();
        }
      }
      $settings = array_merge($settings, $_POST);
    }

    if (isset($_GET['ok'])) {
      $success = 'Configuracion guardada correctamente.';
      $settings = DesignSettingsModel::get();
    }

    render_view('layout/header', ['title' => 'Parametrizacion de diseno']);
    render_view('settings/design', [
      'settings' => $settings,
      'error' => $error,
      'success' => $success,
    ]);
    render_view('layout/footer');
  }

  private static function resolveLogoUpload(array $settings, bool $removeLogo): ?string {
    $currentLogo = (string)($settings['logo_path'] ?? '');

    if ($removeLogo && $currentLogo !== '') {
      self::deleteBrandingFile($currentLogo);
      return null;
    }

    if (!isset($_FILES['logo']) || !is_array($_FILES['logo']) || (int)($_FILES['logo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
      return null;
    }

    $file = $_FILES['logo'];
    $errorCode = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($errorCode !== UPLOAD_ERR_OK) {
      throw new RuntimeException('No se pudo cargar el logo.');
    }

    $tmp = (string)($file['tmp_name'] ?? '');
    if ($tmp === '' || !is_uploaded_file($tmp)) {
      throw new RuntimeException('Archivo de logo invalido.');
    }

    $mime = (string)mime_content_type($tmp);
    $allowed = [
      'image/png' => 'png',
      'image/jpeg' => 'jpg',
      'image/webp' => 'webp',
    ];
    if (!isset($allowed[$mime])) {
      throw new RuntimeException('Logo invalido. Solo PNG, JPG o WEBP.');
    }

    $maxBytes = 2 * 1024 * 1024;
    if ((int)($file['size'] ?? 0) > $maxBytes) {
      throw new RuntimeException('El logo supera 2MB.');
    }

    $uploadDir = __DIR__ . '/../uploads/branding';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
      throw new RuntimeException('No se pudo crear carpeta de logos.');
    }

    $fileName = 'logo_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
    $target = $uploadDir . '/' . $fileName;

    if (!move_uploaded_file($tmp, $target)) {
      throw new RuntimeException('No se pudo guardar el archivo de logo.');
    }

    if ($currentLogo !== '') {
      self::deleteBrandingFile($currentLogo);
    }

    return BASE_URL . '/uploads/branding/' . $fileName;
  }

  private static function resolveFaviconUpload(array $settings, bool $removeFavicon): ?string {
    $currentFavicon = (string)($settings['favicon_path'] ?? '');

    if ($removeFavicon && $currentFavicon !== '') {
      self::deleteBrandingFile($currentFavicon);
      return null;
    }

    if (!isset($_FILES['favicon']) || !is_array($_FILES['favicon']) || (int)($_FILES['favicon']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
      return null;
    }

    $file = $_FILES['favicon'];
    $errorCode = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($errorCode !== UPLOAD_ERR_OK) {
      throw new RuntimeException('No se pudo cargar el favicon.');
    }

    $tmp = (string)($file['tmp_name'] ?? '');
    if ($tmp === '' || !is_uploaded_file($tmp)) {
      throw new RuntimeException('Archivo de favicon invalido.');
    }

    $mime = (string)mime_content_type($tmp);
    $allowed = [
      'image/png' => 'png',
      'image/x-icon' => 'ico',
      'image/vnd.microsoft.icon' => 'ico',
      'image/svg+xml' => 'svg',
    ];
    if (!isset($allowed[$mime])) {
      throw new RuntimeException('Favicon invalido. Solo PNG, ICO o SVG.');
    }

    $maxBytes = 1024 * 1024;
    if ((int)($file['size'] ?? 0) > $maxBytes) {
      throw new RuntimeException('El favicon supera 1MB.');
    }

    $uploadDir = __DIR__ . '/../uploads/branding';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
      throw new RuntimeException('No se pudo crear carpeta de branding.');
    }

    $fileName = 'favicon_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
    $target = $uploadDir . '/' . $fileName;

    if (!move_uploaded_file($tmp, $target)) {
      throw new RuntimeException('No se pudo guardar el favicon.');
    }

    if ($currentFavicon !== '') {
      self::deleteBrandingFile($currentFavicon);
    }

    return BASE_URL . '/uploads/branding/' . $fileName;
  }

  private static function deleteBrandingFile(string $assetPath): void {
    $prefix = BASE_URL . '/uploads/branding/';
    if (!str_starts_with($assetPath, $prefix)) {
      return;
    }
    $fileName = basename($assetPath);
    $fullPath = __DIR__ . '/../uploads/branding/' . $fileName;
    if (is_file($fullPath)) {
      @unlink($fullPath);
    }
  }

  private static function readHexColor(string $key, array $source): ?string {
    $value = strtoupper(trim((string)($source[$key] ?? '')));
    if (!preg_match('/^#[0-9A-F]{6}$/', $value)) {
      return null;
    }
    return $value;
  }
}
