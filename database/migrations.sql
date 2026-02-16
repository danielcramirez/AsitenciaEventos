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
