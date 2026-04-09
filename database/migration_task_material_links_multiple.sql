-- Migration: vincular multiplos materiais por tarefa
-- Data: 2026-04-09

USE terapia;

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

INSERT IGNORE INTO task_material_links (task_id, material_id, created_at)
SELECT id, material_id, CURRENT_TIMESTAMP
FROM tasks
WHERE material_id IS NOT NULL;