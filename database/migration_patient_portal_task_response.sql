-- Migration: portal do paciente (tipo de envio, devolutiva de tarefa e origem de anexo)
-- Data: 2026-04-10

USE terapia;

ALTER TABLE tasks
  ADD COLUMN delivery_kind ENUM('task','material') NOT NULL DEFAULT 'task' AFTER send_to_patient,
  ADD COLUMN patient_response_html LONGTEXT NULL AFTER description,
  ADD COLUMN responded_at DATETIME NULL AFTER patient_response_html;

ALTER TABLE files
  ADD COLUMN source_role ENUM('therapist','patient') NOT NULL DEFAULT 'therapist' AFTER task_id;

UPDATE tasks
SET delivery_kind = 'task'
WHERE delivery_kind IS NULL;

UPDATE files
SET source_role = 'therapist'
WHERE source_role IS NULL;
