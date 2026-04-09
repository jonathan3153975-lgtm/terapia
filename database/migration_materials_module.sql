-- Migration: módulo de materiais do terapeuta
-- Data: 2026-04-09

USE terapia;

CREATE TABLE IF NOT EXISTS materials (
  id INT AUTO_INCREMENT PRIMARY KEY,
  therapist_id INT NOT NULL,
  title VARCHAR(180) NOT NULL,
  type ENUM('support','exercise') NOT NULL,
  description_html LONGTEXT NULL,
  custom_html LONGTEXT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_materials_therapist(therapist_id),
  FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS material_assets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  material_id INT NOT NULL,
  asset_type ENUM('pdf','image','video','url') NOT NULL,
  file_name VARCHAR(255) NULL,
  file_path VARCHAR(500) NULL,
  file_url VARCHAR(500) NULL,
  mime_type VARCHAR(120) NULL,
  file_size BIGINT DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_material_assets_material(material_id),
  FOREIGN KEY (material_id) REFERENCES materials(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS material_deliveries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  material_id INT NOT NULL,
  therapist_id INT NOT NULL,
  patient_id INT NOT NULL,
  message TEXT NULL,
  status ENUM('sent','viewed') DEFAULT 'sent',
  sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_material_deliveries_material(material_id),
  INDEX idx_material_deliveries_patient(patient_id),
  FOREIGN KEY (material_id) REFERENCES materials(id) ON DELETE CASCADE,
  FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
