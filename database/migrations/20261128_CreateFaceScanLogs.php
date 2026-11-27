<?php

/**
 * face_scan_logs: one row per face-kiosk recognition attempt (success, cooldown or
 * failure) for the admin "Face Scan Logs" page. Written by the Python face service
 * (backend/database/models.py FaceScanLog — keep both schemas identical; the service
 * also creates the table itself on startup). Idempotent.
 */
class CreateFaceScanLogs
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function up(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `face_scan_logs` (
                `id`              BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `created_at`      DATETIME NULL,
                `tenant_id`       INT NULL,
                `outcome`         VARCHAR(20) NOT NULL,
                `message`         VARCHAR(255) NULL,
                `matched_user_id` INT NULL,
                `best_user_id`    INT NULL,
                `score`           FLOAT NULL,
                `second_score`    FLOAT NULL,
                `threshold`       FLOAT NULL,
                `blur_score`      FLOAT NULL,
                `blur_threshold`  FLOAT NULL,
                `brightness`      FLOAT NULL,
                `face_brightness` FLOAT NULL,
                `face_contrast`   FLOAT NULL,
                `face_width`      INT NULL,
                `det_score`       FLOAT NULL,
                `yaw`             FLOAT NULL,
                `pitch`           FLOAT NULL,
                `img_width`       INT NULL,
                `img_height`      INT NULL,
                `image_path`      VARCHAR(255) NULL,
                `session_id`      VARCHAR(40) NULL,
                `attempt`         INT NULL,
                `client_meta`     TEXT NULL,
                `processing_ms`   INT NULL,
                `ip_address`      VARCHAR(45) NULL,
                `user_agent`      VARCHAR(255) NULL,
                KEY `ix_face_scan_logs_created_at` (`created_at`),
                KEY `ix_face_scan_logs_tenant_id` (`tenant_id`),
                KEY `ix_face_scan_logs_outcome` (`outcome`),
                KEY `ix_face_scan_logs_matched_user_id` (`matched_user_id`),
                KEY `ix_face_scan_logs_session_id` (`session_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->db->query("DROP TABLE IF EXISTS `face_scan_logs`");
    }
}
