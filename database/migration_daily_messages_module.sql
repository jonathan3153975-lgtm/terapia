-- Migration: modulo de mensagens diarias (terapeuta e paciente)
-- Data: 2026-04-10

USE terapia;

CREATE TABLE IF NOT EXISTS daily_messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  therapist_id INT NOT NULL,
  category ENUM('dores','reflexivas','cura','motivacionais','conflitos') NOT NULL,
  message_text TEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_daily_messages_therapist (therapist_id),
  INDEX idx_daily_messages_category (category),
  FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS patient_message_entries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  therapist_id INT NOT NULL,
  patient_id INT NOT NULL,
  message_id INT NULL,
  message_category ENUM('dores','reflexivas','cura','motivacionais','conflitos') NOT NULL,
  message_text TEXT NOT NULL,
  patient_note LONGTEXT NOT NULL,
  share_with_therapist TINYINT(1) NOT NULL DEFAULT 0,
  drawn_at DATETIME NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_pme_therapist (therapist_id),
  INDEX idx_pme_patient (patient_id),
  INDEX idx_pme_drawn_at (drawn_at),
  FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
  FOREIGN KEY (message_id) REFERENCES daily_messages(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
