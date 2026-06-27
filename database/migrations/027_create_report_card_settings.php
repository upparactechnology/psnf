<?php

declare(strict_types=1);

class CreateReportCardSettings
{
    public function __construct(private \Core\Database $db) {}

    public function up(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `report_card_settings` (
                `tenant_id`            INT UNSIGNED PRIMARY KEY,
                `school_name`          VARCHAR(255) DEFAULT 'Pearl Special Needs Foundation',
                `school_subtitle`      VARCHAR(255) DEFAULT 'Center for Special Education & Care',
                `school_address`       TEXT DEFAULT NULL,
                `stamp_text`           VARCHAR(255) DEFAULT 'Pearl Special Needs Foundation',
                `pdf_font`             VARCHAR(50) DEFAULT 'Inter',
                `primary_color`        VARCHAR(10) DEFAULT '#0d3827',
                `class_teacher_name`   VARCHAR(255) DEFAULT 'Class Teacher',
                `class_teacher_sig`    LONGTEXT DEFAULT NULL,
                `trustees_config`      JSON DEFAULT NULL,
                `created_at`           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at`           TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->db->query("DROP TABLE IF EXISTS `report_card_settings`");
    }
}
