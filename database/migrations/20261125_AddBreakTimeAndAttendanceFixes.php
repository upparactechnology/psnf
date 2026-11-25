<?php

/**
 * Break-time tracking for staff attendance + two schema fixes:
 *  - shift_templates.break_allowed_minutes   (global allowed break per day)
 *  - employees.break_allowed_minutes         (per-employee override, NULL = use global)
 *  - staff_attendance_logs.break_minutes     (break minutes for manual entries)
 *  - FIX: staff_attendance_logs lacked late_exempted / incomplete_log columns that
 *         PayrollController reads and writes (exemptLate, payroll calculation).
 *  - FIX: teacher_attendance.status enum lacked 'half_day', which check-in writes.
 */
class AddBreakTimeAndAttendanceFixes
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    private function hasColumn(string $table, string $column): bool
    {
        return !empty($this->db->select("SHOW COLUMNS FROM `$table` LIKE '$column'"));
    }

    public function up(): void
    {
        if (!$this->hasColumn('shift_templates', 'break_allowed_minutes')) {
            $this->db->query("ALTER TABLE `shift_templates` ADD COLUMN `break_allowed_minutes` INT UNSIGNED NOT NULL DEFAULT 30");
        }

        if (!$this->hasColumn('employees', 'break_allowed_minutes')) {
            $this->db->query("ALTER TABLE `employees` ADD COLUMN `break_allowed_minutes` INT UNSIGNED NULL DEFAULT NULL");
        }

        if (!$this->hasColumn('staff_attendance_logs', 'break_minutes')) {
            $this->db->query("ALTER TABLE `staff_attendance_logs` ADD COLUMN `break_minutes` INT UNSIGNED NULL DEFAULT NULL AFTER `working_hours`");
        }

        // Fix: columns PayrollController uses on manual logs
        $missing = [
            'late_exempted'         => "TINYINT(1) NULL DEFAULT 0",
            'late_exemption_reason' => "TEXT NULL",
            'late_exempted_by'      => "INT UNSIGNED NULL",
            'late_exempted_at'      => "TIMESTAMP NULL DEFAULT NULL",
            'incomplete_log'        => "TINYINT(1) NULL DEFAULT 0",
        ];
        foreach ($missing as $col => $def) {
            if (!$this->hasColumn('staff_attendance_logs', $col)) {
                $this->db->query("ALTER TABLE `staff_attendance_logs` ADD COLUMN `$col` $def");
            }
        }

        // Fix: allow 'half_day' status on teacher_attendance
        $statusCol = $this->db->selectOne("SHOW COLUMNS FROM `teacher_attendance` LIKE 'status'");
        if ($statusCol && stripos((string) $statusCol['Type'], "'half_day'") === false) {
            $this->db->query("ALTER TABLE `teacher_attendance` MODIFY `status` ENUM('on_time','late','half_day') NOT NULL");
        }
    }

    public function down(): void
    {
        $this->db->query("ALTER TABLE `shift_templates` DROP COLUMN IF EXISTS `break_allowed_minutes`");
        $this->db->query("ALTER TABLE `employees` DROP COLUMN IF EXISTS `break_allowed_minutes`");
        $this->db->query("ALTER TABLE `staff_attendance_logs` DROP COLUMN IF EXISTS `break_minutes`");
    }
}
