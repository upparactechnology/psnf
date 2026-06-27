SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS email_logs;
DROP TABLE IF EXISTS email_schedules;
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS generated_certificates;
DROP TABLE IF EXISTS participants;
DROP TABLE IF EXISTS field_mappings;
DROP TABLE IF EXISTS certificate_types;
DROP TABLE IF EXISTS conference_admins;
DROP TABLE IF EXISTS conferences;
DROP TABLE IF EXISTS cert_settings;
DROP TABLE IF EXISTS cert_users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE cert_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin', 'sub_admin') NOT NULL DEFAULT 'admin',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL
) ENGINE=InnoDB;

CREATE TABLE cert_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(120) NOT NULL UNIQUE,
    setting_value TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE conferences (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    year INT NOT NULL,
    description TEXT NULL,
    created_by INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    UNIQUE KEY uq_conference_name_year (name, year),
    CONSTRAINT fk_conferences_created_by FOREIGN KEY (created_by) REFERENCES cert_users(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE conference_admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conference_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL,
    UNIQUE KEY uq_conference_admin (conference_id, user_id),
    CONSTRAINT fk_conference_admins_conference FOREIGN KEY (conference_id) REFERENCES conferences(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_conference_admins_user FOREIGN KEY (user_id) REFERENCES cert_users(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE certificate_types (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conference_id INT UNSIGNED NOT NULL,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(150) NOT NULL,
    template_path VARCHAR(255) NULL,
    is_custom TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    UNIQUE KEY uq_certificate_type_slug (conference_id, slug),
    CONSTRAINT fk_certificate_types_conference FOREIGN KEY (conference_id) REFERENCES conferences(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE field_mappings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    certificate_type_id INT UNSIGNED NOT NULL,
    field_key VARCHAR(80) NOT NULL,
    label TEXT NOT NULL,
    options_json TEXT NULL,
    x_pos INT NOT NULL,
    y_pos INT NOT NULL,
    font_size INT NOT NULL DEFAULT 32,
    align ENUM('left', 'center') NOT NULL DEFAULT 'left',
    color_hex CHAR(7) NOT NULL DEFAULT '#000000',
    max_width INT NOT NULL DEFAULT 900,
    line_height INT NOT NULL DEFAULT 42,
    underline TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    INDEX idx_field_mappings_type_sort (certificate_type_id, sort_order),
    CONSTRAINT fk_field_mappings_type FOREIGN KEY (certificate_type_id) REFERENCES certificate_types(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE participants (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conference_id INT UNSIGNED NOT NULL,
    certificate_type_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(190) NULL,
    institute VARCHAR(255) NULL,
    title TEXT NULL,
    issued_date DATE NULL,
    extra_json JSON NULL,
    verify_code VARCHAR(40) NOT NULL UNIQUE,
    status ENUM('pending', 'generated', 'emailed', 'failed') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    INDEX idx_participants_lookup (conference_id, certificate_type_id, status),
    CONSTRAINT fk_participants_conference FOREIGN KEY (conference_id) REFERENCES conferences(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_participants_type FOREIGN KEY (certificate_type_id) REFERENCES certificate_types(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE generated_certificates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    participant_id INT UNSIGNED NOT NULL,
    jpg_path VARCHAR(255) NULL,
    pdf_path VARCHAR(255) NULL,
    generated_at DATETIME NOT NULL,
    download_count INT NOT NULL DEFAULT 0,
    UNIQUE KEY uq_generated_participant (participant_id),
    CONSTRAINT fk_generated_participant FOREIGN KEY (participant_id) REFERENCES participants(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE email_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    participant_id INT UNSIGNED NOT NULL,
    to_email VARCHAR(190) NOT NULL,
    subject_text VARCHAR(255) NOT NULL,
    message_text TEXT NULL,
    status ENUM('sent', 'failed') NOT NULL,
    error_message TEXT NULL,
    sent_at DATETIME NOT NULL,
    INDEX idx_email_logs_participant (participant_id),
    INDEX idx_email_logs_status (status),
    CONSTRAINT fk_email_logs_participant FOREIGN KEY (participant_id) REFERENCES participants(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE email_schedules (
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
    CONSTRAINT fk_email_schedules_created_by FOREIGN KEY (created_by) REFERENCES cert_users(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE contact_messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conference_id INT UNSIGNED NULL,
    contact_name VARCHAR(190) NOT NULL,
    contact_email VARCHAR(190) NULL,
    mobile_number VARCHAR(30) NULL,
    category VARCHAR(190) NOT NULL,
    message_text TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_contact_messages_created_at (created_at),
    INDEX idx_contact_messages_conference (conference_id),
    CONSTRAINT fk_contact_messages_conference FOREIGN KEY (conference_id) REFERENCES conferences(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO cert_users (full_name, email, password_hash, role, is_active, created_at)
VALUES
('Super Admin', 'superadmin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', 1, NOW());

INSERT INTO cert_settings (setting_key, setting_value)
VALUES
('default_font_path', 'C:/Windows/Fonts/arial.ttf'),
('file_name_format', '{conference}_{year}_{category}_{name}_{id}'),
('download_format', 'pdf'),
('smtp_host', ''),
('smtp_port', '587'),
('smtp_secure', 'tls'),
('smtp_username', ''),
('smtp_password', ''),
('smtp_from_email', 'no-reply@example.com'),
('smtp_from_name', 'Certificate Team'),
('email_subject_template', 'Your {{certificate_type}} Certificate - {{conference}} {{year}}'),
('email_message_template', 'Dear {{name}},\n\nPlease find attached your {{certificate_type}} certificate for {{conference}} {{year}}.\n\nRegards,\nConference Team'),
('bulk_email_delay_min_seconds', '1'),
('bulk_email_delay_max_seconds', '3')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
