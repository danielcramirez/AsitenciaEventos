<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/db.php';

class DesignSettingsModel {
  private static bool $tableChecked = false;

  public static function defaults(): array {
    return [
      'primary_color' => '#006838',
      'primary_hover_color' => '#0D9A49',
      'logo_path' => null,
      'favicon_path' => null,
      'menu_button_bg' => '#F5EB28',
      'menu_button_text' => '#111111',
      'menu_button_hover_bg' => '#F89621',
      'menu_button_hover_text' => '#111111',
      'menu_secondary_bg' => '#FFFFFF',
      'menu_secondary_text' => '#111111',
      'menu_secondary_hover_bg' => '#E9ECEF',
      'menu_secondary_hover_text' => '#111111',
      'menu_show_admin_eventos' => 1,
      'menu_show_verificar_qr' => 1,
      'menu_show_mis_referidos' => 1,
      'menu_show_registro' => 1,
      'menu_show_login' => 1,
    ];
  }

  public static function get(): array {
    $defaults = self::defaults();

    try {
      self::ensureTable();
      $st = db()->query('SELECT * FROM design_settings WHERE id = 1 LIMIT 1');
      $row = $st->fetch();
      if (!$row) {
        return $defaults;
      }
      return array_merge($defaults, $row);
    } catch (Throwable $e) {
      // Si la tabla aún no existe, se usan valores por defecto.
      return $defaults;
    }
  }

  public static function save(array $data): void {
    self::ensureTable();
    $current = self::get();
    $merged = array_merge($current, $data);

    $sql = "INSERT INTO design_settings
      (id, primary_color, primary_hover_color, logo_path, favicon_path, menu_button_bg, menu_button_text, menu_button_hover_bg, menu_button_hover_text, menu_secondary_bg, menu_secondary_text, menu_secondary_hover_bg, menu_secondary_hover_text, menu_show_admin_eventos, menu_show_verificar_qr, menu_show_mis_referidos, menu_show_registro, menu_show_login)
      VALUES
      (1, :primary_color, :primary_hover_color, :logo_path, :favicon_path, :menu_button_bg, :menu_button_text, :menu_button_hover_bg, :menu_button_hover_text, :menu_secondary_bg, :menu_secondary_text, :menu_secondary_hover_bg, :menu_secondary_hover_text, :menu_show_admin_eventos, :menu_show_verificar_qr, :menu_show_mis_referidos, :menu_show_registro, :menu_show_login)
      ON DUPLICATE KEY UPDATE
      primary_color = VALUES(primary_color),
      primary_hover_color = VALUES(primary_hover_color),
      logo_path = VALUES(logo_path),
      favicon_path = VALUES(favicon_path),
      menu_button_bg = VALUES(menu_button_bg),
      menu_button_text = VALUES(menu_button_text),
      menu_button_hover_bg = VALUES(menu_button_hover_bg),
      menu_button_hover_text = VALUES(menu_button_hover_text),
      menu_secondary_bg = VALUES(menu_secondary_bg),
      menu_secondary_text = VALUES(menu_secondary_text),
      menu_secondary_hover_bg = VALUES(menu_secondary_hover_bg),
      menu_secondary_hover_text = VALUES(menu_secondary_hover_text),
      menu_show_admin_eventos = VALUES(menu_show_admin_eventos),
      menu_show_verificar_qr = VALUES(menu_show_verificar_qr),
      menu_show_mis_referidos = VALUES(menu_show_mis_referidos),
      menu_show_registro = VALUES(menu_show_registro),
      menu_show_login = VALUES(menu_show_login)";

    $st = db()->prepare($sql);
    $st->execute([
      'primary_color' => (string)$merged['primary_color'],
      'primary_hover_color' => (string)$merged['primary_hover_color'],
      'logo_path' => $merged['logo_path'] !== null && $merged['logo_path'] !== '' ? (string)$merged['logo_path'] : null,
      'favicon_path' => $merged['favicon_path'] !== null && $merged['favicon_path'] !== '' ? (string)$merged['favicon_path'] : null,
      'menu_button_bg' => (string)$merged['menu_button_bg'],
      'menu_button_text' => (string)$merged['menu_button_text'],
      'menu_button_hover_bg' => (string)$merged['menu_button_hover_bg'],
      'menu_button_hover_text' => (string)$merged['menu_button_hover_text'],
      'menu_secondary_bg' => (string)$merged['menu_secondary_bg'],
      'menu_secondary_text' => (string)$merged['menu_secondary_text'],
      'menu_secondary_hover_bg' => (string)$merged['menu_secondary_hover_bg'],
      'menu_secondary_hover_text' => (string)$merged['menu_secondary_hover_text'],
      'menu_show_admin_eventos' => (int)$merged['menu_show_admin_eventos'],
      'menu_show_verificar_qr' => (int)$merged['menu_show_verificar_qr'],
      'menu_show_mis_referidos' => (int)$merged['menu_show_mis_referidos'],
      'menu_show_registro' => (int)$merged['menu_show_registro'],
      'menu_show_login' => (int)$merged['menu_show_login'],
    ]);
  }

  private static function ensureTable(): void {
    if (self::$tableChecked) {
      return;
    }

    $sql = "CREATE TABLE IF NOT EXISTS design_settings (
      id TINYINT NOT NULL PRIMARY KEY,
      primary_color CHAR(7) NOT NULL DEFAULT '#006838',
      primary_hover_color CHAR(7) NOT NULL DEFAULT '#0D9A49',
      logo_path VARCHAR(255) NULL,
      favicon_path VARCHAR(255) NULL,
      menu_button_bg CHAR(7) NOT NULL DEFAULT '#F5EB28',
      menu_button_text CHAR(7) NOT NULL DEFAULT '#111111',
      menu_button_hover_bg CHAR(7) NOT NULL DEFAULT '#F89621',
      menu_button_hover_text CHAR(7) NOT NULL DEFAULT '#111111',
      menu_secondary_bg CHAR(7) NOT NULL DEFAULT '#FFFFFF',
      menu_secondary_text CHAR(7) NOT NULL DEFAULT '#111111',
      menu_secondary_hover_bg CHAR(7) NOT NULL DEFAULT '#E9ECEF',
      menu_secondary_hover_text CHAR(7) NOT NULL DEFAULT '#111111',
      menu_show_admin_eventos TINYINT(1) NOT NULL DEFAULT 1,
      menu_show_verificar_qr TINYINT(1) NOT NULL DEFAULT 1,
      menu_show_mis_referidos TINYINT(1) NOT NULL DEFAULT 1,
      menu_show_registro TINYINT(1) NOT NULL DEFAULT 1,
      menu_show_login TINYINT(1) NOT NULL DEFAULT 1,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    db()->exec($sql);
    self::ensureColumn('primary_hover_color', "ALTER TABLE design_settings ADD COLUMN primary_hover_color CHAR(7) NOT NULL DEFAULT '#0D9A49'");
    self::ensureColumn('favicon_path', "ALTER TABLE design_settings ADD COLUMN favicon_path VARCHAR(255) NULL");
    self::ensureColumn('menu_button_bg', "ALTER TABLE design_settings ADD COLUMN menu_button_bg CHAR(7) NOT NULL DEFAULT '#F5EB28'");
    self::ensureColumn('menu_button_text', "ALTER TABLE design_settings ADD COLUMN menu_button_text CHAR(7) NOT NULL DEFAULT '#111111'");
    self::ensureColumn('menu_button_hover_bg', "ALTER TABLE design_settings ADD COLUMN menu_button_hover_bg CHAR(7) NOT NULL DEFAULT '#F89621'");
    self::ensureColumn('menu_button_hover_text', "ALTER TABLE design_settings ADD COLUMN menu_button_hover_text CHAR(7) NOT NULL DEFAULT '#111111'");
    self::ensureColumn('menu_secondary_bg', "ALTER TABLE design_settings ADD COLUMN menu_secondary_bg CHAR(7) NOT NULL DEFAULT '#FFFFFF'");
    self::ensureColumn('menu_secondary_text', "ALTER TABLE design_settings ADD COLUMN menu_secondary_text CHAR(7) NOT NULL DEFAULT '#111111'");
    self::ensureColumn('menu_secondary_hover_bg', "ALTER TABLE design_settings ADD COLUMN menu_secondary_hover_bg CHAR(7) NOT NULL DEFAULT '#E9ECEF'");
    self::ensureColumn('menu_secondary_hover_text', "ALTER TABLE design_settings ADD COLUMN menu_secondary_hover_text CHAR(7) NOT NULL DEFAULT '#111111'");
    db()->exec("INSERT INTO design_settings (id, primary_color, primary_hover_color) VALUES (1, '#006838', '#0D9A49') ON DUPLICATE KEY UPDATE id = id");

    self::$tableChecked = true;
  }

  private static function ensureColumn(string $columnName, string $alterSql): void {
    $st = db()->prepare(
      "SELECT COUNT(*) FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'design_settings'
       AND COLUMN_NAME = ?"
    );
    $st->execute([$columnName]);
    $exists = (int)$st->fetchColumn() > 0;
    if (!$exists) {
      db()->exec($alterSql);
    }
  }
}
