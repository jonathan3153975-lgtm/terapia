-- Migration: modulos Meditacao guiada e Cartas de cura
-- Data: 2026-04-11

USE terapia;

CREATE TABLE IF NOT EXISTS guided_meditations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  therapist_id INT NOT NULL,
  title VARCHAR(180) NOT NULL,
  reference_image_name VARCHAR(255) NULL,
  reference_image_path VARCHAR(500) NULL,
  audio_name VARCHAR(255) NOT NULL,
  audio_path VARCHAR(500) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_guided_meditations_therapist (therapist_id),
  FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS healing_letters (
  id INT AUTO_INCREMENT PRIMARY KEY,
  therapist_id INT NOT NULL,
  category ENUM('dores','reflexivas','cura','motivacionais','conflitos') NOT NULL,
  message_text TEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_healing_letters_therapist (therapist_id),
  INDEX idx_healing_letters_category (category),
  FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS patient_guided_meditation_entries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  therapist_id INT NOT NULL,
  patient_id INT NOT NULL,
  meditation_id INT NOT NULL,
  letter_id INT NULL,
  letter_category ENUM('dores','reflexivas','cura','motivacionais','conflitos') NOT NULL,
  letter_text TEXT NOT NULL,
  patient_note LONGTEXT NOT NULL,
  share_with_therapist TINYINT(1) NOT NULL DEFAULT 0,
  listened_at DATETIME NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_pgme_therapist (therapist_id),
  INDEX idx_pgme_patient (patient_id),
  INDEX idx_pgme_meditation (meditation_id),
  FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
  FOREIGN KEY (meditation_id) REFERENCES guided_meditations(id) ON DELETE CASCADE,
  FOREIGN KEY (letter_id) REFERENCES healing_letters(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
