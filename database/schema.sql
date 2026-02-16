-- Database schema for Event Management System with QR codes
-- Requires MySQL 8.0+

CREATE DATABASE IF NOT EXISTS asistencia_eventos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE asistencia_eventos;

-- Users table with role-based access
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('Administrador', 'Operador', 'Asistente') NOT NULL DEFAULT 'Asistente',
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB;

-- Events table
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    location VARCHAR(200),
    event_date DATETIME NOT NULL,
    max_capacity INT NOT NULL,
    current_registrations INT DEFAULT 0,
    created_by INT NOT NULL,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_event_date (event_date),
    INDEX idx_created_by (created_by)
) ENGINE=InnoDB;

-- Registrations table (attendees per event)
CREATE TABLE IF NOT EXISTS registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    attendee_name VARCHAR(100) NOT NULL,
    attendee_email VARCHAR(100) NOT NULL,
    attendee_phone VARCHAR(20),
    qr_token VARCHAR(64) NOT NULL UNIQUE,
    registered_by INT NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (registered_by) REFERENCES users(id),
    UNIQUE KEY unique_attendee_event (event_id, attendee_email),
    INDEX idx_qr_token (qr_token),
    INDEX idx_event_id (event_id),
    INDEX idx_attendee_email (attendee_email)
) ENGINE=InnoDB;

-- Check-ins table (unique check-in per person per event)
CREATE TABLE IF NOT EXISTS checkins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    registration_id INT NOT NULL,
    event_id INT NOT NULL,
    checked_in_by INT NOT NULL,
    checkin_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (registration_id) REFERENCES registrations(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (checked_in_by) REFERENCES users(id),
    UNIQUE KEY unique_checkin (registration_id),
    INDEX idx_event_id (event_id),
    INDEX idx_checkin_time (checkin_time)
) ENGINE=InnoDB;

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password_hash, full_name, role) VALUES 
('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador Sistema', 'Administrador');

-- Insert sample users for testing
INSERT INTO users (username, email, password_hash, full_name, role) VALUES 
('operador1', 'operador1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Operador Uno', 'Operador'),
('asistente1', 'asistente1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Asistente Uno', 'Asistente');
