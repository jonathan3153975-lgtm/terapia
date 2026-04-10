-- Migration: expandir categorias do mensageiro
-- Data: 2026-04-10

USE terapia;

ALTER TABLE daily_messages
  MODIFY COLUMN category ENUM('dores','reflexivas','cura','motivacionais','conflitos') NOT NULL;

ALTER TABLE patient_message_entries
  MODIFY COLUMN message_category ENUM('dores','reflexivas','cura','motivacionais','conflitos') NOT NULL;
