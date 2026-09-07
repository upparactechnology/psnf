<?php

class AddMainGroupIdToTimetables
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function up(): void
    {
        try {
            $this->db->query("ALTER TABLE `timetables` ADD COLUMN `main_group_id` INT UNSIGNED NULL AFTER `class_id`");
        } catch (\Throwable $e) {}

        // Migrate existing timetable data: set main_group_id from class -> main_groups
        $this->db->query("
            UPDATE timetables t
            JOIN classes c ON t.class_id = c.id
            SET t.main_group_id = c.main_group_id
            WHERE t.class_id IS NOT NULL AND c.main_group_id IS NOT NULL
        ");
    }
}
