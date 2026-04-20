-- Migration: modulo Devocional
-- Data: 20/04/2026

CREATE TABLE IF NOT EXISTS devotionals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  therapist_id INT NOT NULL,
  month_number TINYINT UNSIGNED NOT NULL,
  year_number SMALLINT UNSIGNED NOT NULL,
  theme VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  UNIQUE KEY uniq_devotional_month_year (therapist_id, month_number, year_number),
  INDEX idx_devotionals_therapist (therapist_id),
  FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS devotional_entries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  devotional_id INT NOT NULL,
  therapist_id INT NOT NULL,
  entry_date DATE NOT NULL,
  title VARCHAR(255) NOT NULL,
  word_of_god VARCHAR(255) NOT NULL,
  text_content LONGTEXT NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  UNIQUE KEY uniq_devotional_entry_date (devotional_id, entry_date),
  INDEX idx_devotional_entries_devotional (devotional_id),
  INDEX idx_devotional_entries_therapist (therapist_id),
  FOREIGN KEY (devotional_id) REFERENCES devotionals(id) ON DELETE CASCADE,
  FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS patient_devotional_reflections (
  id INT AUTO_INCREMENT PRIMARY KEY,
  devotional_id INT NOT NULL,
  devotional_entry_id INT NOT NULL,
  therapist_id INT NOT NULL,
  patient_id INT NOT NULL,
  reflection_html LONGTEXT NOT NULL,
  compiled_html LONGTEXT NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  UNIQUE KEY uniq_patient_devotional_entry (patient_id, devotional_entry_id),
  INDEX idx_patient_devotional_reflections_patient (patient_id),
  INDEX idx_patient_devotional_reflections_therapist (therapist_id),
  INDEX idx_patient_devotional_reflections_devotional (devotional_id),
  FOREIGN KEY (devotional_id) REFERENCES devotionals(id) ON DELETE CASCADE,
  FOREIGN KEY (devotional_entry_id) REFERENCES devotional_entries(id) ON DELETE CASCADE,
  FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
