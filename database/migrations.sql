-- Create login_attempts table
CREATE TABLE IF NOT EXISTS login_attempts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(120) NOT NULL,
  ip VARCHAR(45) NOT NULL,
  attempts INT NOT NULL DEFAULT 0,
  first_attempt_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  last_attempt_at TIMESTAMP NULL DEFAULT NULL,
  blocked_until TIMESTAMP NULL DEFAULT NULL,
  UNIQUE KEY uq_login_attempt (email, ip)
);

-- Create rate_limits table
CREATE TABLE IF NOT EXISTS rate_limits (
  id INT AUTO_INCREMENT PRIMARY KEY,
  key_hash CHAR(64) NOT NULL UNIQUE,
  attempts INT NOT NULL DEFAULT 0,
  window_start TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Create audit_logs table
CREATE TABLE IF NOT EXISTS audit_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  event_id INT NULL,
  action VARCHAR(50) NOT NULL,
  ip VARCHAR(45) NOT NULL,
  user_agent VARCHAR(255) NULL,
  meta TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_action_created (action, created_at),
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (event_id) REFERENCES events(id)
);

-- Roles nuevos y campos de referidos
SET @role_def_exists := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'users'
    AND COLUMN_NAME = 'role'
    AND COLUMN_TYPE LIKE "%'ENLACE'%"
);
SET @sql_role := IF(@role_def_exists = 0,
  "ALTER TABLE users MODIFY role ENUM('ADMIN','OPERATOR','ENLACE','ELECTOR','ATTENDEE') NOT NULL",
  "SELECT 1");
PREPARE stmt_role FROM @sql_role;
EXECUTE stmt_role;
DEALLOCATE PREPARE stmt_role;

ALTER TABLE users
  ADD COLUMN IF NOT EXISTS referred_by_user_id INT NULL,
  ADD COLUMN IF NOT EXISTS referral_code VARCHAR(24) NULL UNIQUE;

SET @fk_exists := (
  SELECT COUNT(*)
  FROM information_schema.REFERENTIAL_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND CONSTRAINT_NAME = 'fk_users_referred_by'
);
SET @sql_fk := IF(@fk_exists = 0,
  "ALTER TABLE users ADD CONSTRAINT fk_users_referred_by FOREIGN KEY (referred_by_user_id) REFERENCES users(id)",
  "SELECT 1");
PREPARE stmt_fk FROM @sql_fk;
EXECUTE stmt_fk;
DEALLOCATE PREPARE stmt_fk;

-- Parametrización de diseño
CREATE TABLE IF NOT EXISTS design_settings (
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
);

INSERT INTO design_settings (
  id, primary_color, primary_hover_color, menu_show_admin_eventos, menu_show_verificar_qr,
  menu_show_mis_referidos, menu_show_registro, menu_show_login
) VALUES (1, '#006838', '#0D9A49', 1, 1, 1, 1, 1)
ON DUPLICATE KEY UPDATE id = id;
