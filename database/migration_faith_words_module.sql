-- Migration: modulo Pai, fala comigo
-- Data: 2026-04-11

USE terapia;

CREATE TABLE IF NOT EXISTS faith_words (
  id INT AUTO_INCREMENT PRIMARY KEY,
  therapist_id INT NOT NULL,
  reference_text VARCHAR(120) NOT NULL,
  verse_text TEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_faith_words_therapist (therapist_id),
  FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS patient_faith_entries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  therapist_id INT NOT NULL,
  patient_id INT NOT NULL,
  word_id INT NULL,
  word_reference VARCHAR(120) NOT NULL,
  word_text TEXT NOT NULL,
  patient_note LONGTEXT NOT NULL,
  share_with_therapist TINYINT(1) NOT NULL DEFAULT 0,
  drawn_at DATETIME NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_pfe_therapist (therapist_id),
  INDEX idx_pfe_patient (patient_id),
  FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
  FOREIGN KEY (word_id) REFERENCES faith_words(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
