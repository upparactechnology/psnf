<?php

declare(strict_types=1);

class Migration_20260815_PayrollAttendanceImprovements
{
    private \Core\Database $db;

    public function __construct(\Core\Database $db)
    {
        $this->db = $db;
    }

    public function up(): void
    {
        $db = $this->db;

        // 1. Create holidays table
        $db->query("
            CREATE TABLE IF NOT EXISTS `holidays` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `holiday_date` DATE NOT NULL,
                `name` VARCHAR(100) NOT NULL,
                `type` VARCHAR(50) DEFAULT 'public',
                `is_paid` TINYINT(1) DEFAULT 1,
                `status` VARCHAR(20) DEFAULT 'active',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `uq_holiday_date` (`holiday_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 2. Add columns to attendance table
        $db->query("ALTER TABLE `attendance` ADD COLUMN IF NOT EXISTS `late_exempted` TINYINT(1) DEFAULT 0;");
        $db->query("ALTER TABLE `attendance` ADD COLUMN IF NOT EXISTS `late_exemption_reason` TEXT NULL;");
        $db->query("ALTER TABLE `attendance` ADD COLUMN IF NOT EXISTS `late_exempted_by` INT UNSIGNED NULL;");
        $db->query("ALTER TABLE `attendance` ADD COLUMN IF NOT EXISTS `late_exempted_at` TIMESTAMP NULL;");
        $db->query("ALTER TABLE `attendance` ADD COLUMN IF NOT EXISTS `incomplete_log` TINYINT(1) DEFAULT 0;");

        // 3. Add columns to staff_attendance_logs table
        $db->query("ALTER TABLE `staff_attendance_logs` ADD COLUMN IF NOT EXISTS `late_exempted` TINYINT(1) DEFAULT 0;");
        $db->query("ALTER TABLE `staff_attendance_logs` ADD COLUMN IF NOT EXISTS `late_exemption_reason` TEXT NULL;");
        $db->query("ALTER TABLE `staff_attendance_logs` ADD COLUMN IF NOT EXISTS `late_exempted_by` INT UNSIGNED NULL;");
        $db->query("ALTER TABLE `staff_attendance_logs` ADD COLUMN IF NOT EXISTS `late_exempted_at` TIMESTAMP NULL;");
        $db->query("ALTER TABLE `staff_attendance_logs` ADD COLUMN IF NOT EXISTS `incomplete_log` TINYINT(1) DEFAULT 0;");

        // 4. Modify leave_requests status and add audit columns
        $db->query("ALTER TABLE `leave_requests` MODIFY COLUMN `status` VARCHAR(30) DEFAULT 'pending';");
        $db->query("ALTER TABLE `leave_requests` ADD COLUMN IF NOT EXISTS `recalled_at` TIMESTAMP NULL;");
        $db->query("ALTER TABLE `leave_requests` ADD COLUMN IF NOT EXISTS `recalled_by` INT UNSIGNED NULL;");
        $db->query("ALTER TABLE `leave_requests` ADD COLUMN IF NOT EXISTS `recalled_reason` TEXT NULL;");

        // 5. Modify payroll_runs status
        $db->query("ALTER TABLE `payroll_runs` MODIFY COLUMN `status` VARCHAR(30) DEFAULT 'draft';");

        // 6. Add columns to payroll_items
        $db->query("ALTER TABLE `payroll_items` ADD COLUMN IF NOT EXISTS `salary_basic` DECIMAL(10,2) DEFAULT 0.00;");
        $db->query("ALTER TABLE `payroll_items` ADD COLUMN IF NOT EXISTS `salary_days` INT DEFAULT 30;");
        $db->query("ALTER TABLE `payroll_items` ADD COLUMN IF NOT EXISTS `daily_salary` DECIMAL(10,2) DEFAULT 0.00;");
        $db->query("ALTER TABLE `payroll_items` ADD COLUMN IF NOT EXISTS `normal_half_days` INT DEFAULT 0;");
        $db->query("ALTER TABLE `payroll_items` ADD COLUMN IF NOT EXISTS `paid_leave_days` INT DEFAULT 0;");
        $db->query("ALTER TABLE `payroll_items` ADD COLUMN IF NOT EXISTS `unpaid_leave_days` INT DEFAULT 0;");
        $db->query("ALTER TABLE `payroll_items` ADD COLUMN IF NOT EXISTS `paid_holiday_days` INT DEFAULT 0;");
        $db->query("ALTER TABLE `payroll_items` ADD COLUMN IF NOT EXISTS `sunday_days` INT DEFAULT 0;");
        $db->query("ALTER TABLE `payroll_items` ADD COLUMN IF NOT EXISTS `late_count` INT DEFAULT 0;");
        $db->query("ALTER TABLE `payroll_items` ADD COLUMN IF NOT EXISTS `late_exempted_count` INT DEFAULT 0;");
        $db->query("ALTER TABLE `payroll_items` ADD COLUMN IF NOT EXISTS `late_penalty_half_days` INT DEFAULT 0;");
        $db->query("ALTER TABLE `payroll_items` ADD COLUMN IF NOT EXISTS `unpaid_leave_deduction` DECIMAL(10,2) DEFAULT 0.00;");
        $db->query("ALTER TABLE `payroll_items` ADD COLUMN IF NOT EXISTS `normal_half_day_deduction` DECIMAL(10,2) DEFAULT 0.00;");
        $db->query("ALTER TABLE `payroll_items` ADD COLUMN IF NOT EXISTS `late_penalty_deduction` DECIMAL(10,2) DEFAULT 0.00;");
        $db->query("ALTER TABLE `payroll_items` ADD COLUMN IF NOT EXISTS `total_deductions` DECIMAL(10,2) DEFAULT 0.00;");

        // 7. Create payroll_audit_logs table
        $db->query("
            CREATE TABLE IF NOT EXISTS `payroll_audit_logs` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT UNSIGNED NOT NULL,
                `action` VARCHAR(100) NOT NULL,
                `entity` VARCHAR(50) NOT NULL,
                `entity_id` INT UNSIGNED NOT NULL,
                `old_value` TEXT NULL,
                `new_value` TEXT NULL,
                `reason` TEXT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down(): void
    {
        // No destructive schema rollbacks automatically
    }
}
