-- ClinicDesk Database Setup
-- Run this file in phpMyAdmin or via: mysql -u root -p < clinicdesk_db.sql

CREATE DATABASE IF NOT EXISTS clinicdesk_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

USE clinicdesk_db;

-- -------------------------------------------------------
-- Table 1: users
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(120)  NOT NULL,
    email      VARCHAR(180)  NOT NULL UNIQUE,
    password   VARCHAR(255)  NOT NULL,
    role       ENUM('admin','doctor','patient') NOT NULL DEFAULT 'patient',
    phone      VARCHAR(20)   DEFAULT NULL,
    avatar     VARCHAR(255)  DEFAULT NULL,
    is_active  TINYINT(1)    NOT NULL DEFAULT 1,
    created_at TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin seed (password: Admin@1234)
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@clinic.local',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- -------------------------------------------------------
-- Table 2: specializations
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS specializations (
    id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO specializations (name) VALUES
('General Practice'), ('Cardiology'), ('Dermatology'),
('Pediatrics'), ('Orthopedics'), ('Neurology'),
('Ophthalmology'), ('ENT'), ('Psychiatry');

-- -------------------------------------------------------
-- Table 3: doctors
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS doctors (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id           INT UNSIGNED NOT NULL UNIQUE,
    specialization_id INT UNSIGNED NOT NULL,
    bio               TEXT          DEFAULT NULL,
    consultation_fee  DECIMAL(8,2)  NOT NULL DEFAULT 0.00,
    available_days    VARCHAR(50)   NOT NULL DEFAULT 'Sun,Mon,Tue,Wed,Thu',
    FOREIGN KEY (user_id)           REFERENCES users(id)           ON DELETE CASCADE,
    FOREIGN KEY (specialization_id) REFERENCES specializations(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- Table 4: appointments
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS appointments (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id   INT UNSIGNED NOT NULL,
    doctor_id    INT UNSIGNED NOT NULL,
    appt_date    DATE         NOT NULL,
    appt_time    TIME         NOT NULL,
    status       ENUM('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
    reason       VARCHAR(255) DEFAULT NULL,
    doctor_notes TEXT         DEFAULT NULL,
    created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY no_double_booking (doctor_id, appt_date, appt_time),
    FOREIGN KEY (patient_id) REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (doctor_id)  REFERENCES doctors(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- Table 5: prescriptions
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS prescriptions (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT UNSIGNED NOT NULL UNIQUE,
    diagnosis      TEXT         NOT NULL,
    medications    TEXT         NOT NULL,
    notes          TEXT         DEFAULT NULL,
    file_path      VARCHAR(255) DEFAULT NULL,
    created_at     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- Sample data for testing
-- -------------------------------------------------------

-- Doctor user (password: Doctor@1234)
INSERT INTO users (name, email, password, role, phone) VALUES
('Dr. Sarah Ahmed',  'doctor@clinic.local',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor', '0591234567'),
('Dr. Omar Khalil',  'doctor2@clinic.local',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor', '0597654321');

-- Doctor records
INSERT INTO doctors (user_id, specialization_id, bio, consultation_fee, available_days) VALUES
((SELECT id FROM users WHERE email='doctor@clinic.local'),
 2, 'Experienced cardiologist with 10 years of practice.', 80.00, 'Sun,Mon,Tue,Wed,Thu'),
((SELECT id FROM users WHERE email='doctor2@clinic.local'),
 4, 'Pediatrics specialist, gentle with children.', 60.00, 'Mon,Tue,Wed,Thu');

-- Patient user (password: Patient@1234)
INSERT INTO users (name, email, password, role, phone) VALUES
('Ahmed Al-Nabulsi', 'patient@clinic.local',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient', '0599999999');

-- NOTE: All sample passwords use the hash for "password"
-- Change them immediately after first login!
-- To generate a proper hash: password_hash('YourPassword', PASSWORD_BCRYPT)
