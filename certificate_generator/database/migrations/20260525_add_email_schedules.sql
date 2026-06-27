CREATE TABLE IF NOT EXISTS email_schedules (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conference_id INT UNSIGNED NOT NULL,
    certificate_type_id INT UNSIGNED NULL,
    template_key VARCHAR(120) NOT NULL,
    subject_template VARCHAR(255) NOT NULL,
    message_template TEXT NOT NULL,
    cc_template TEXT NULL,
    bcc_template TEXT NULL,
    status ENUM('active', 'paused') NOT NULL DEFAULT 'active',
    interval_seconds INT NOT NULL DEFAULT 60,
    last_sent_at DATETIME NULL,
    last_participant_id INT UNSIGNED NULL,
    sent_count INT UNSIGNED NOT NULL DEFAULT 0,
    failed_count INT UNSIGNED NOT NULL DEFAULT 0,
    created_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    INDEX idx_email_schedules_status (status),
    INDEX idx_email_schedules_conf_type (conference_id, certificate_type_id),
    CONSTRAINT fk_email_schedules_conference FOREIGN KEY (conference_id) REFERENCES conferences(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_email_schedules_type FOREIGN KEY (certificate_type_id) REFERENCES certificate_types(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_email_schedules_created_by FOREIGN KEY (created_by) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;
