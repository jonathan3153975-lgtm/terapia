CREATE DATABASE IF NOT EXISTS terapia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE terapia;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  cpf VARCHAR(14) NULL,
  phone VARCHAR(20) NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('super_admin','therapist','patient') NOT NULL,
  therapist_id INT NULL,
  patient_id INT NULL,
  plan_type VARCHAR(30) NULL,
  status ENUM('active','inactive') DEFAULT 'active',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_users_role(role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS plans (
  id INT AUTO_INCREMENT PRIMARY KEY,
  target ENUM('therapist','patient') NOT NULL,
  name VARCHAR(100) NOT NULL,
  billing_cycle ENUM('mensal','anual') NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS therapists (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  plan_id INT NULL,
  commission_percent DECIMAL(5,2) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS patients (
  id INT AUTO_INCREMENT PRIMARY KEY,
  therapist_id INT NOT NULL,
  name VARCHAR(150) NOT NULL,
  cpf VARCHAR(14) NOT NULL,
  birth_date DATE NULL,
  phone VARCHAR(20) NOT NULL,
  email VARCHAR(150) NULL,
  marital_status VARCHAR(50) NULL,
  children VARCHAR(255) NULL,
  cep VARCHAR(9) NULL,
  address VARCHAR(255) NULL,
  neighborhood VARCHAR(100) NULL,
  city VARCHAR(100) NULL,
  state CHAR(2) NULL,
  depression TINYINT(1) DEFAULT 0,
  anxiety TINYINT(1) DEFAULT 0,
  medical_treatment TEXT NULL,
  alcoholism TINYINT(1) DEFAULT 0,
  drugs TINYINT(1) DEFAULT 0,
  convulsions TINYINT(1) DEFAULT 0,
  smoker TINYINT(1) DEFAULT 0,
  hepatitis TINYINT(1) DEFAULT 0,
  hypertension TINYINT(1) DEFAULT 0,
  diabetes TINYINT(1) DEFAULT 0,
  addictions_json JSON NULL,
  had_therapy TINYINT(1) DEFAULT 0,
  therapy_description TEXT NULL,
  treatment_start_date DATE NULL,
  menstruation TEXT NULL,
  bowel TEXT NULL,
  main_complaint TEXT NULL,
  status ENUM('active','inactive') DEFAULT 'active',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_patients_therapist(therapist_id),
  FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS appointments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  therapist_id INT NOT NULL,
  patient_id INT NULL,
  guest_patient_name VARCHAR(150) NULL,
  session_date DATETIME NOT NULL,
  description VARCHAR(255) NULL,
  history LONGTEXT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_appt_therapist(therapist_id),
  INDEX idx_appt_patient(patient_id),
  FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  therapist_id INT NOT NULL,
  patient_id INT NOT NULL,
  material_id INT NULL,
  due_date DATE NOT NULL,
  title VARCHAR(255) NOT NULL,
  description LONGTEXT NOT NULL,
  patient_response_html LONGTEXT NULL,
  responded_at DATETIME NULL,
  send_to_patient TINYINT(1) DEFAULT 0,
  delivery_kind ENUM('task','material') DEFAULT 'task',
  status ENUM('pending','done') DEFAULT 'pending',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_tasks_therapist(therapist_id),
  INDEX idx_tasks_patient(patient_id),
  INDEX idx_tasks_material(material_id),
  FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS files (
  id INT AUTO_INCREMENT PRIMARY KEY,
  therapist_id INT NOT NULL,
  patient_id INT NULL,
  task_id INT NULL,
  source_role ENUM('therapist','patient') DEFAULT 'therapist',
  file_name VARCHAR(255) NOT NULL,
  file_path VARCHAR(500) NOT NULL,
  file_type ENUM('pdf','image','link','other') DEFAULT 'other',
  file_size BIGINT DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_files_therapist(therapist_id),
  FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE SET NULL,
  FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  therapist_id INT NOT NULL,
  appointment_id INT NULL,
  patient_id INT NULL,
  plan_id INT NULL,
  amount DECIMAL(10,2) NOT NULL,
  provider VARCHAR(50) DEFAULT 'mercado_pago',
  provider_reference VARCHAR(100) NULL,
  status ENUM('pending','paid','failed','canceled') DEFAULT 'pending',
  paid_at DATETIME NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_payments_therapist(therapist_id),
  INDEX idx_payments_appointment(appointment_id),
  FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
  FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE SET NULL,
  FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS commissions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  therapist_id INT NOT NULL,
  patient_id INT NOT NULL,
  month_ref CHAR(7) NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  status ENUM('pending','paid') DEFAULT 'pending',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_comm_therapist(therapist_id),
  FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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

CREATE TABLE IF NOT EXISTS task_material_links (
  id INT AUTO_INCREMENT PRIMARY KEY,
  task_id INT NOT NULL,
  material_id INT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_task_material(task_id, material_id),
  INDEX idx_task_material_links_task(task_id),
  INDEX idx_task_material_links_material(material_id),
  FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
  FOREIGN KEY (material_id) REFERENCES materials(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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

ALTER TABLE tasks
  ADD CONSTRAINT fk_tasks_material
  FOREIGN KEY (material_id) REFERENCES materials(id) ON DELETE SET NULL;

INSERT INTO plans (target, name, billing_cycle, price) VALUES
('therapist','Plano Terapeuta Mensal','mensal',99.90),
('therapist','Plano Terapeuta Anual','anual',999.00),
('patient','Plano Paciente Mensal','mensal',29.90),
('patient','Plano Paciente Anual','anual',299.00)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Senha padrao: 123456
INSERT INTO users (name, email, password, role, status) VALUES
('Admin Master','admin@teste.com','$2y$12$vM76NPXqrc6Qt9Zg6rGQOeTpDOaYavnj8kRjMAh0FgFGkxNHUgtsq','super_admin','active'),
('Terapeuta Teste','terapeuta@teste.com','$2y$12$vM76NPXqrc6Qt9Zg6rGQOeTpDOaYavnj8kRjMAh0FgFGkxNHUgtsq','therapist','active')
ON DUPLICATE KEY UPDATE
  password = VALUES(password),
  role = VALUES(role),
  status = VALUES(status);

INSERT INTO patients (therapist_id, name, cpf, phone, email, status)
SELECT u.id, 'Paciente Teste', '12345678901', '11999999999', 'paciente@teste.com', 'active'
FROM users u WHERE u.email = 'terapeuta@teste.com'
ON DUPLICATE KEY UPDATE status = VALUES(status);

INSERT INTO users (name, email, password, role, therapist_id, patient_id, status)
SELECT 'Paciente Teste', 'paciente@teste.com', '$2y$12$vM76NPXqrc6Qt9Zg6rGQOeTpDOaYavnj8kRjMAh0FgFGkxNHUgtsq', 'patient', p.therapist_id, p.id, 'active'
FROM patients p WHERE p.email = 'paciente@teste.com'
ON DUPLICATE KEY UPDATE
  password = VALUES(password),
  role = VALUES(role),
  therapist_id = VALUES(therapist_id),
  patient_id = VALUES(patient_id),
  status = VALUES(status);
