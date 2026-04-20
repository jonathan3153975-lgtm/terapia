-- Migration: modulo teraTube
-- Data: 2026-04-20

USE terapia;

CREATE TABLE IF NOT EXISTS teratube_videos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  therapist_id INT NOT NULL,
  title VARCHAR(180) NOT NULL,
  description_text TEXT NULL,
  keywords VARCHAR(500) NULL,
  source_type ENUM('upload', 'youtube') NOT NULL,
  youtube_url VARCHAR(500) NULL,
  youtube_video_id VARCHAR(40) NULL,
  video_original_name VARCHAR(255) NULL,
  video_path VARCHAR(500) NULL,
  video_mime_type VARCHAR(120) NULL,
  video_size BIGINT NOT NULL DEFAULT 0,
  is_published TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_teratube_therapist (therapist_id),
  INDEX idx_teratube_published (therapist_id, is_published),
  INDEX idx_teratube_youtube (youtube_video_id),
  FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS patient_video_favorites (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT NOT NULL,
  video_id INT NOT NULL,
  therapist_id INT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_patient_video_favorite (patient_id, video_id),
  INDEX idx_pvf_video (video_id),
  INDEX idx_pvf_therapist (therapist_id),
  FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
  FOREIGN KEY (video_id) REFERENCES teratube_videos(id) ON DELETE CASCADE,
  FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS patient_video_ratings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT NOT NULL,
  video_id INT NOT NULL,
  therapist_id INT NOT NULL,
  rating TINYINT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_patient_video_rating (patient_id, video_id),
  INDEX idx_pvr_video (video_id),
  INDEX idx_pvr_therapist (therapist_id),
  FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
  FOREIGN KEY (video_id) REFERENCES teratube_videos(id) ON DELETE CASCADE,
  FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS patient_video_comments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT NOT NULL,
  video_id INT NOT NULL,
  therapist_id INT NOT NULL,
  comment_text TEXT NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_pvc_video (video_id),
  INDEX idx_pvc_therapist (therapist_id),
  FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
  FOREIGN KEY (video_id) REFERENCES teratube_videos(id) ON DELETE CASCADE,
  FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS patient_video_comment_ratings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  comment_id INT NOT NULL,
  patient_id INT NOT NULL,
  rating TINYINT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_patient_comment_rating (comment_id, patient_id),
  INDEX idx_pvcr_comment (comment_id),
  INDEX idx_pvcr_patient (patient_id),
  FOREIGN KEY (comment_id) REFERENCES patient_video_comments(id) ON DELETE CASCADE,
  FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
