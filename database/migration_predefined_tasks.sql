CREATE TABLE IF NOT EXISTS predefined_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    therapist_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description MEDIUMTEXT NOT NULL,
    cover_image_path VARCHAR(500) NULL,
    cover_image_name VARCHAR(255) NULL,
    delivery_kind ENUM('task', 'material') NOT NULL DEFAULT 'task',
    status ENUM('pending', 'done') NOT NULL DEFAULT 'pending',
    send_to_patient TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_predefined_tasks_therapist (therapist_id),
    CONSTRAINT fk_predefined_tasks_therapist
        FOREIGN KEY (therapist_id) REFERENCES users(id)
        ON DELETE CASCADE
);
