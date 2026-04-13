-- Migration: Adicionar suporte a tarefas dinâmicas (virtual tasks)
-- Data: 13/04/2026

-- Esta migration foi escrita para ser reexecutável (idempotente).
-- Pode ser rodada múltiplas vezes sem quebrar por "coluna já existe".

-- Estender tabela tasks com suporte a tarefas dinâmicas (apenas se necessário)
SET @sql = (
  SELECT IF(
    EXISTS (
      SELECT 1
      FROM information_schema.columns
      WHERE table_schema = DATABASE()
        AND table_name = 'tasks'
        AND column_name = 'task_type'
    ),
    'SELECT 1',
    "ALTER TABLE tasks ADD COLUMN task_type VARCHAR(50) DEFAULT 'regular' COMMENT 'Tipo de tarefa: regular, virtual_tree_of_life'"
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
  SELECT IF(
    EXISTS (
      SELECT 1
      FROM information_schema.columns
      WHERE table_schema = DATABASE()
        AND table_name = 'tasks'
        AND column_name = 'content_json'
    ),
    'SELECT 1',
    "ALTER TABLE tasks ADD COLUMN content_json LONGTEXT COMMENT 'Estrutura JSON para tarefas dinamicas (perguntas, configuracao)'"
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
  SELECT IF(
    EXISTS (
      SELECT 1
      FROM information_schema.columns
      WHERE table_schema = DATABASE()
        AND table_name = 'tasks'
        AND column_name = 'is_active'
    ),
    'SELECT 1',
    "ALTER TABLE tasks ADD COLUMN is_active TINYINT(1) DEFAULT 1 COMMENT 'Se a tarefa dinamica esta ativa para o paciente'"
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Tabela para respostas de tarefas dinâmicas (respostas por seção)
CREATE TABLE IF NOT EXISTS virtual_task_responses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  therapist_id INT NOT NULL,
  patient_id INT NOT NULL,
  task_id INT NOT NULL,
  section_name VARCHAR(100) NOT NULL COMMENT 'Nome da seção (Tempestades, Folhas, etc)',
  answers_json LONGTEXT COMMENT 'Respostas em JSON para a seção',
  reflection_html LONGTEXT COMMENT 'Reflexão final em HTML (Quill)',
  completed_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
  FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
  UNIQUE KEY uniq_virtual_task_section (task_id, section_name),
  INDEX (therapist_id),
  INDEX (patient_id),
  INDEX (task_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela para templates de tarefas dinâmicas (reutilizáveis)
CREATE TABLE IF NOT EXISTS virtual_task_templates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  therapist_id INT NOT NULL,
  name VARCHAR(255) NOT NULL COMMENT 'Nome do template',
  task_type VARCHAR(50) NOT NULL COMMENT 'Tipo: virtual_tree_of_life, etc',
  structure_json LONGTEXT COMMENT 'Estrutura completa do template em JSON',
  description TEXT,
  is_global TINYINT(1) DEFAULT 0 COMMENT 'Se é template padrão do sistema',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX (therapist_id, task_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
