CREATE DATABASE IF NOT EXISTS eventos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE eventos;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(120) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('ADMIN','OPERATOR','ATTENDEE') NOT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE persons (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cedula VARCHAR(30) NOT NULL UNIQUE,
  nombres VARCHAR(120) NOT NULL,
  apellidos VARCHAR(120) NOT NULL,
  celular VARCHAR(30) NULL,
  email VARCHAR(120) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE user_person (
  user_id INT NOT NULL UNIQUE,
  person_id INT NOT NULL UNIQUE,
  PRIMARY KEY(user_id, person_id),
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (person_id) REFERENCES persons(id)
);

CREATE TABLE events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(180) NOT NULL,
  lugar VARCHAR(180) NULL,
  fecha_inicio DATETIME NOT NULL,
  fecha_fin DATETIME NOT NULL,
  cupo INT NOT NULL DEFAULT 0,
  estado ENUM('DRAFT','PUBLISHED','CLOSED') NOT NULL DEFAULT 'DRAFT',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE registrations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT NOT NULL,
  person_id INT NOT NULL,
  status ENUM('ACTIVE','CANCELED') NOT NULL DEFAULT 'ACTIVE',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_event_person (event_id, person_id),
  FOREIGN KEY (event_id) REFERENCES events(id),
  FOREIGN KEY (person_id) REFERENCES persons(id)
);

CREATE TABLE qr_tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  registration_id INT NOT NULL UNIQUE,
  token_hash CHAR(64) NOT NULL UNIQUE,
  issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  revoked_at TIMESTAMP NULL DEFAULT NULL,
  FOREIGN KEY (registration_id) REFERENCES registrations(id)
);

CREATE TABLE checkins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT NOT NULL,
  registration_id INT NOT NULL,
  operator_user_id INT NOT NULL,
  checkin_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_event_registration (event_id, registration_id),
  FOREIGN KEY (event_id) REFERENCES events(id),
  FOREIGN KEY (registration_id) REFERENCES registrations(id),
  FOREIGN KEY (operator_user_id) REFERENCES users(id)
);

-- Usuario admin inicial (password: Admin123*)
INSERT INTO users(email,password_hash,role) VALUES
('admin@local', '$2y$10$r.GRXmkyeQEeFi.n.5lGkegyZjpxjZOILC3P.MB/UHQkbQ7f56vTO', 'ADMIN');
