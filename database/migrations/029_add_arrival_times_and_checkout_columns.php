<?php

declare(strict_types=1);

class AddArrivalTimesAndCheckoutColumns
{
    public function __construct(private \Core\Database $db) {}

    public function up(): void
    {
        // 1. Add checkout_at column to teacher_attendance
        $columnsTeacher = $this->db->select("SHOW COLUMNS FROM `teacher_attendance` LIKE 'checkout_at'");
        if (empty($columnsTeacher)) {
            $this->db->query("ALTER TABLE `teacher_attendance` ADD COLUMN `checkout_at` DATETIME NULL AFTER `opened_at`");
        }

        // 2. Add arrival_time column to student attendance
        $columnsAttendance = $this->db->select("SHOW COLUMNS FROM `attendance` LIKE 'arrival_time'");
        if (empty($columnsAttendance)) {
            $this->db->query("ALTER TABLE `attendance` ADD COLUMN `arrival_time` TIME NULL AFTER `status`");
        }
    }

    public function down(): void
    {
        $columnsTeacher = $this->db->select("SHOW COLUMNS FROM `teacher_attendance` LIKE 'checkout_at'");
        if (!empty($columnsTeacher)) {
            $this->db->query("ALTER TABLE `teacher_attendance` DROP COLUMN `checkout_at`");
        }

        $columnsAttendance = $this->db->select("SHOW COLUMNS FROM `attendance` LIKE 'arrival_time'");
        if (!empty($columnsAttendance)) {
            $this->db->query("ALTER TABLE `attendance` DROP COLUMN `arrival_time`");
        }
    }
}
