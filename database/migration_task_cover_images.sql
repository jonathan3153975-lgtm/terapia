-- Migration: adicionar imagem de capa em tarefas e tarefas pre-definidas
-- Data: 20/04/2026

-- tasks.cover_image_path
SET @sql = (
  SELECT IF(
    EXISTS (
      SELECT 1
      FROM information_schema.columns
      WHERE table_schema = DATABASE()
        AND table_name = 'tasks'
        AND column_name = 'cover_image_path'
    ),
    'SELECT 1',
    "ALTER TABLE tasks ADD COLUMN cover_image_path VARCHAR(500) NULL AFTER description"
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- tasks.cover_image_name
SET @sql = (
  SELECT IF(
    EXISTS (
      SELECT 1
      FROM information_schema.columns
      WHERE table_schema = DATABASE()
        AND table_name = 'tasks'
        AND column_name = 'cover_image_name'
    ),
    'SELECT 1',
    "ALTER TABLE tasks ADD COLUMN cover_image_name VARCHAR(255) NULL AFTER cover_image_path"
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- predefined_tasks.cover_image_path
SET @sql = (
  SELECT IF(
    NOT EXISTS (
      SELECT 1
      FROM information_schema.tables
      WHERE table_schema = DATABASE()
        AND table_name = 'predefined_tasks'
    ),
    'SELECT 1',
    IF(
      EXISTS (
        SELECT 1
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = 'predefined_tasks'
          AND column_name = 'cover_image_path'
      ),
      'SELECT 1',
      "ALTER TABLE predefined_tasks ADD COLUMN cover_image_path VARCHAR(500) NULL AFTER description"
    )
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- predefined_tasks.cover_image_name
SET @sql = (
  SELECT IF(
    NOT EXISTS (
      SELECT 1
      FROM information_schema.tables
      WHERE table_schema = DATABASE()
        AND table_name = 'predefined_tasks'
    ),
    'SELECT 1',
    IF(
      EXISTS (
        SELECT 1
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = 'predefined_tasks'
          AND column_name = 'cover_image_name'
      ),
      'SELECT 1',
      "ALTER TABLE predefined_tasks ADD COLUMN cover_image_name VARCHAR(255) NULL AFTER cover_image_path"
    )
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
