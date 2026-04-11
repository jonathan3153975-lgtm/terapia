ALTER TABLE patients
  ADD COLUMN review_status ENUM('pending_review','approved') DEFAULT 'approved' AFTER main_complaint,
  ADD COLUMN approved_at DATETIME NULL AFTER review_status;

CREATE TABLE IF NOT EXISTS patient_signup_links (
  id INT AUTO_INCREMENT PRIMARY KEY,
  therapist_id INT NOT NULL,
  token VARCHAR(120) NOT NULL UNIQUE,
  recipient_email VARCHAR(150) NULL,
  expires_at DATETIME NOT NULL,
  used_count INT NOT NULL DEFAULT 0,
  max_uses INT NOT NULL DEFAULT 30,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_patient_signup_links_therapist (therapist_id),
  CONSTRAINT fk_patient_signup_links_therapist FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE
);
