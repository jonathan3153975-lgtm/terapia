-- Migration: vinculo financeiro por agendamento
-- Data: 2026-04-09

USE terapia;

ALTER TABLE payments
  ADD COLUMN appointment_id INT NULL AFTER therapist_id;

ALTER TABLE payments
  ADD INDEX idx_payments_appointment (appointment_id);

ALTER TABLE payments
  ADD CONSTRAINT fk_payments_appointment
    FOREIGN KEY (appointment_id) REFERENCES appointments(id)
    ON DELETE CASCADE;

-- Rollback
-- ALTER TABLE payments DROP FOREIGN KEY fk_payments_appointment;
-- ALTER TABLE payments DROP INDEX idx_payments_appointment;
-- ALTER TABLE payments DROP COLUMN appointment_id;
