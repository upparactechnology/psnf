<?php

declare(strict_types=1);

return new class {
    public function up(): void
    {
        $db = \Core\Application::$app->db;
        
        // Check if columns already exist
        $columns = $db->select("SHOW COLUMNS FROM timetables LIKE 'shared_at'");
        if (empty($columns)) {
            $db->query("ALTER TABLE timetables ADD COLUMN `shared_at` TIMESTAMP NULL AFTER `updated_at`");
        }
        
        $columns = $db->select("SHOW COLUMNS FROM timetables LIKE 'shared_by'");
        if (empty($columns)) {
            $db->query("ALTER TABLE timetables ADD COLUMN `shared_by` INT UNSIGNED NULL AFTER `shared_at`");
        }
    }

    public function down(): void
    {
        $db = \Core\Application::$app->db;
        $db->query("ALTER TABLE timetables DROP COLUMN IF EXISTS `shared_at`");
        $db->query("ALTER TABLE timetables DROP COLUMN IF EXISTS `shared_by`");
    }
};
