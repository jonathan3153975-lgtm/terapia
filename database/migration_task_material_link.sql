-- Migration: vinculo opcional de material em tarefas
-- Data: 2026-04-09

USE terapia;

ALTER TABLE tasks
  ADD COLUMN material_id INT NULL AFTER patient_id,
  ADD INDEX idx_tasks_material(material_id),
  ADD CONSTRAINT fk_tasks_material
    FOREIGN KEY (material_id) REFERENCES materials(id) ON DELETE SET NULL;