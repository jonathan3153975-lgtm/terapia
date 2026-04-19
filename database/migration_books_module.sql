-- Migration: modulo Livros
-- Data: 2026-04-18

USE terapia;

CREATE TABLE IF NOT EXISTS books (
  id INT AUTO_INCREMENT PRIMARY KEY,
  therapist_id INT NOT NULL,
  title VARCHAR(180) NOT NULL,
  pdf_original_name VARCHAR(255) NOT NULL,
  pdf_path VARCHAR(500) NOT NULL,
  pdf_mime_type VARCHAR(120) NOT NULL DEFAULT 'application/pdf',
  pdf_size BIGINT NOT NULL DEFAULT 0,
  is_published TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_books_therapist (therapist_id),
  INDEX idx_books_published (therapist_id, is_published),
  FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS patient_book_favorites (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT NOT NULL,
  book_id INT NOT NULL,
  therapist_id INT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_patient_book_favorite (patient_id, book_id),
  INDEX idx_pbf_book (book_id),
  INDEX idx_pbf_therapist (therapist_id),
  FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
  FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
  FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;