-- Migration: Adicionar novos campos clínicos à tabela de pacientes
-- Execute este script no banco de dados existente

ALTER TABLE patients
    ADD COLUMN IF NOT EXISTS marital_status VARCHAR(30) NULL AFTER observations,
    ADD COLUMN IF NOT EXISTS children TEXT NULL AFTER marital_status,
    ADD COLUMN IF NOT EXISTS depression TINYINT(1) DEFAULT 0 AFTER children,
    ADD COLUMN IF NOT EXISTS anxiety TINYINT(1) DEFAULT 0 AFTER depression,
    ADD COLUMN IF NOT EXISTS medications TEXT NULL AFTER anxiety,
    ADD COLUMN IF NOT EXISTS bowel TEXT NULL AFTER medications,
    ADD COLUMN IF NOT EXISTS menstruation TEXT NULL AFTER bowel,
    ADD COLUMN IF NOT EXISTS had_therapy TINYINT(1) DEFAULT 0 AFTER menstruation,
    ADD COLUMN IF NOT EXISTS therapy_duration VARCHAR(100) NULL AFTER had_therapy,
    ADD COLUMN IF NOT EXISTS therapy_reason TEXT NULL AFTER therapy_duration;
