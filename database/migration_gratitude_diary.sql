CREATE TABLE IF NOT EXISTS patient_gratitude_entries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  therapist_id INT NOT NULL,
  patient_id INT NOT NULL,
  cycle_number INT NOT NULL,
  day_number INT NOT NULL,
  content_html LONGTEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_patient_cycle_day (patient_id, cycle_number, day_number),
  INDEX idx_patient_gratitude_patient (patient_id),
  INDEX idx_patient_gratitude_therapist (therapist_id),
  CONSTRAINT fk_patient_gratitude_therapist FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_patient_gratitude_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS patient_gratitude_entries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  therapist_id INT NOT NULL,
  patient_id INT NOT NULL,
  cycle_number INT NOT NULL,
  day_number INT NOT NULL,
  content_html LONGTEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_patient_cycle_day (patient_id, cycle_number, day_number),
  INDEX idx_patient_gratitude_patient (patient_id),
  INDEX idx_patient_gratitude_therapist (therapist_id),
  CONSTRAINT fk_patient_gratitude_therapist FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_patient_gratitude_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
);
