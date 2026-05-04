CREATE TABLE IF NOT EXISTS api_access_tokens (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  patient_id INT NOT NULL,
  name VARCHAR(120) NOT NULL,
  token_hash CHAR(64) NOT NULL,
  abilities VARCHAR(255) NULL,
  last_used_at DATETIME NULL,
  expires_at DATETIME NOT NULL,
  revoked_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  UNIQUE KEY uq_api_access_tokens_hash (token_hash),
  KEY idx_api_access_tokens_user (user_id),
  KEY idx_api_access_tokens_patient (patient_id),
  KEY idx_api_access_tokens_expires (expires_at),
  CONSTRAINT fk_api_access_tokens_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_api_access_tokens_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS patient_devices (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  patient_id INT NOT NULL,
  user_id INT NOT NULL,
  platform ENUM('android','ios') NOT NULL,
  device_name VARCHAR(160) NOT NULL,
  device_identifier VARCHAR(191) NOT NULL,
  push_token VARCHAR(255) NULL,
  app_version VARCHAR(80) NULL,
  locale VARCHAR(20) NULL,
  last_seen_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  UNIQUE KEY uq_patient_devices_identifier (device_identifier),
  KEY idx_patient_devices_patient (patient_id),
  KEY idx_patient_devices_user (user_id),
  CONSTRAINT fk_patient_devices_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
  CONSTRAINT fk_patient_devices_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS app_analytics_events (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  patient_id INT NOT NULL,
  user_id INT NOT NULL,
  platform VARCHAR(20) NOT NULL,
  event_name VARCHAR(120) NOT NULL,
  event_context VARCHAR(120) NULL,
  payload_json JSON NULL,
  created_at DATETIME NOT NULL,
  KEY idx_app_analytics_patient (patient_id),
  KEY idx_app_analytics_user (user_id),
  KEY idx_app_analytics_name (event_name),
  KEY idx_app_analytics_created (created_at),
  CONSTRAINT fk_app_analytics_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
  CONSTRAINT fk_app_analytics_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS patient_app_cycles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  patient_id INT NOT NULL,
  module_name VARCHAR(60) NOT NULL,
  scope_key VARCHAR(120) NOT NULL DEFAULT 'default',
  drawn_ids_json JSON NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  UNIQUE KEY uq_patient_app_cycles (patient_id, module_name, scope_key),
  KEY idx_patient_app_cycles_patient (patient_id),
  CONSTRAINT fk_patient_app_cycles_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;