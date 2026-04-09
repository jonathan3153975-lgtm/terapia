-- Migration: Agenda do terapeuta com compromisso para paciente sem cadastro
-- Data: 2026-04-09

USE terapia;

ALTER TABLE appointments
  MODIFY COLUMN patient_id INT NULL;

ALTER TABLE appointments
  ADD COLUMN guest_patient_name VARCHAR(150) NULL AFTER patient_id;

-- Rollback
-- ALTER TABLE appointments DROP COLUMN guest_patient_name;
-- ALTER TABLE appointments MODIFY COLUMN patient_id INT NOT NULL;
