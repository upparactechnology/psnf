CREATE TABLE IF NOT EXISTS contact_messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conference_id INT UNSIGNED NULL,
    contact_name VARCHAR(190) NOT NULL,
    category VARCHAR(190) NOT NULL,
    message_text TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_contact_messages_created_at (created_at),
    INDEX idx_contact_messages_conference (conference_id),
    CONSTRAINT fk_contact_messages_conference FOREIGN KEY (conference_id) REFERENCES conferences(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
