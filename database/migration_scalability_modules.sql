-- Migration: Escalabilidade por modulos (Administrador Geral, Terapeuta e Paciente)
-- Execute este script no banco existente

-- 1) Usuarios e papeis
ALTER TABLE users
    MODIFY COLUMN role ENUM('admin', 'super_admin', 'therapist', 'patient') DEFAULT 'patient',
    ADD COLUMN IF NOT EXISTS therapist_id INT NULL AFTER role,
    ADD COLUMN IF NOT EXISTS patient_id INT NULL AFTER therapist_id;

ALTER TABLE users
    ADD INDEX IF NOT EXISTS idx_users_role (role),
    ADD INDEX IF NOT EXISTS idx_users_therapist_id (therapist_id),
    ADD INDEX IF NOT EXISTS idx_users_patient_id (patient_id);

-- 2) Escopo por terapeuta em entidades existentes
ALTER TABLE patients
    ADD COLUMN IF NOT EXISTS therapist_id INT NULL AFTER id,
    ADD INDEX IF NOT EXISTS idx_patients_therapist_id (therapist_id);

ALTER TABLE patient_records
    ADD COLUMN IF NOT EXISTS therapist_id INT NULL AFTER patient_id,
    ADD COLUMN IF NOT EXISTS description VARCHAR(255) NULL AFTER record_date,
    ADD INDEX IF NOT EXISTS idx_records_therapist_id (therapist_id);

ALTER TABLE appointments
    ADD COLUMN IF NOT EXISTS therapist_id INT NULL AFTER patient_id,
    ADD INDEX IF NOT EXISTS idx_appointments_therapist_id (therapist_id);

ALTER TABLE payments
    ADD COLUMN IF NOT EXISTS therapist_id INT NULL AFTER patient_id,
    ADD INDEX IF NOT EXISTS idx_payments_therapist_id (therapist_id);

-- 3) Tarefas do paciente
CREATE TABLE IF NOT EXISTS patient_tasks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT NOT NULL,
    therapist_id INT NOT NULL,
    due_date DATE NOT NULL,
    title VARCHAR(255) NOT NULL,
    description LONGTEXT NOT NULL,
    sent_to_patient TINYINT(1) DEFAULT 0,
    sent_at DATETIME NULL,
    status ENUM('pending', 'done') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_patient_tasks_patient (patient_id),
    INDEX idx_patient_tasks_therapist (therapist_id),
    INDEX idx_patient_tasks_status (status),
    CONSTRAINT fk_patient_tasks_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    CONSTRAINT fk_patient_tasks_therapist FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4) Anexos de tarefas (arquivo e link)
CREATE TABLE IF NOT EXISTS task_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    task_id INT NOT NULL,
    therapist_id INT NOT NULL,
    type ENUM('file', 'link') DEFAULT 'file',
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size BIGINT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_task_attachments_task (task_id),
    INDEX idx_task_attachments_therapist (therapist_id),
    CONSTRAINT fk_task_attachments_task FOREIGN KEY (task_id) REFERENCES patient_tasks(id) ON DELETE CASCADE,
    CONSTRAINT fk_task_attachments_therapist FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5) Materiais armazenados pelos terapeutas
CREATE TABLE IF NOT EXISTS therapist_files (
    id INT PRIMARY KEY AUTO_INCREMENT,
    therapist_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size BIGINT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_therapist_files_therapist (therapist_id),
    CONSTRAINT fk_therapist_files_therapist FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6) Mensagens armazenadas (reserva para modulo interacao)
CREATE TABLE IF NOT EXISTS patient_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT NOT NULL,
    therapist_id INT NOT NULL,
    message LONGTEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_patient_messages_patient (patient_id),
    INDEX idx_patient_messages_therapist (therapist_id),
    CONSTRAINT fk_patient_messages_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    CONSTRAINT fk_patient_messages_therapist FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7) Chaves estrangeiras novas em tabelas existentes
ALTER TABLE users
    ADD CONSTRAINT fk_users_therapist FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_users_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE SET NULL;

ALTER TABLE patients
    ADD CONSTRAINT fk_patients_therapist FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE SET NULL;

ALTER TABLE patient_records
    ADD CONSTRAINT fk_records_therapist FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE SET NULL;

ALTER TABLE appointments
    ADD CONSTRAINT fk_appointments_therapist FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE SET NULL;

ALTER TABLE payments
    ADD CONSTRAINT fk_payments_therapist FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE SET NULL;

-- 8) Converte admin legado para super_admin
UPDATE users SET role = 'super_admin' WHERE role = 'admin';
