<?php

class MakeTeacherAttendanceColumnsNullable
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function up(): void
    {
        $this->db->query("
            ALTER TABLE `teacher_attendance`
            MODIFY COLUMN `lecture_time` TIME NULL DEFAULT NULL,
            MODIFY COLUMN `grace_period` INT UNSIGNED NULL DEFAULT 0
        ");
    }

    public function down(): void
    {
        $this->db->query("
            ALTER TABLE `teacher_attendance`
            MODIFY COLUMN `lecture_time` TIME NOT NULL,
            MODIFY COLUMN `grace_period` INT UNSIGNED NOT NULL
        ");
    }
}
