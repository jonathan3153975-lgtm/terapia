-- Migration: modulo Oracoes
-- Data: 2026-04-11

USE terapia;

CREATE TABLE IF NOT EXISTS prayers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  therapist_id INT NOT NULL,
  title VARCHAR(180) NOT NULL,
  reference_image_name VARCHAR(255) NULL,
  reference_image_path VARCHAR(500) NULL,
  audio_name VARCHAR(255) NOT NULL,
  audio_path VARCHAR(500) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_prayers_therapist (therapist_id),
  FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS patient_prayer_entries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  therapist_id INT NOT NULL,
  patient_id INT NOT NULL,
  prayer_id INT NOT NULL,
  patient_note LONGTEXT NOT NULL,
  share_with_therapist TINYINT(1) NOT NULL DEFAULT 0,
  listened_at DATETIME NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_ppe_therapist (therapist_id),
  INDEX idx_ppe_patient (patient_id),
  INDEX idx_ppe_prayer (prayer_id),
  FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
  FOREIGN KEY (prayer_id) REFERENCES prayers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
