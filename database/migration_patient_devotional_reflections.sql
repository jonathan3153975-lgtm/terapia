-- Migration: reflexoes do paciente no Devocional
-- Data: 20/04/2026

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
