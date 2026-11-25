<?php

declare(strict_types=1);

return new class {
    public function up(): void
    {
        $db = \Core\Application::$app->db;

        // Add class_name column for class-specific announcements
        if (empty($db->select("SHOW COLUMNS FROM `announcements` LIKE 'class_name'"))) {
            $db->query("ALTER TABLE announcements ADD COLUMN class_name VARCHAR(50) DEFAULT NULL AFTER target_audience");
        }

        // Add priority column (normal, urgent, critical)
        if (empty($db->select("SHOW COLUMNS FROM `announcements` LIKE 'priority'"))) {
            $db->query("ALTER TABLE announcements ADD COLUMN priority ENUM('normal', 'urgent', 'critical') NOT NULL DEFAULT 'normal' AFTER class_name");
        }
    }

    public function down(): void
    {
        $pdo = \Core\Database::getInstance();
        $pdo->exec("ALTER TABLE announcements DROP COLUMN class_name");
        $pdo->exec("ALTER TABLE announcements DROP COLUMN priority");
    }
};
