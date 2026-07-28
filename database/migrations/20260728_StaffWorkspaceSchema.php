<?php

declare(strict_types=1);

use Core\Application;

class Migration_20260728_StaffWorkspaceSchema
{
    public function up(): void
    {
        $db = Application::$app->db;

        $db->query("SET FOREIGN_KEY_CHECKS = 0;");
        $db->query("DROP TABLE IF EXISTS `payroll_items`, `payroll_runs`, `salary_structures`, `leave_requests`, `leave_types`, `staff_attendance_logs`, `employee_profiles`, `employees`, `shift_templates`, `designations`, `departments`;");

        // 1. Departments Table
        $db->query("
            CREATE TABLE IF NOT EXISTS `departments` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
                `name` VARCHAR(100) NOT NULL,
                `code` VARCHAR(20) NOT NULL,
                `description` TEXT NULL,
                `head_id` INT UNSIGNED NULL,
                `is_active` TINYINT(1) DEFAULT 1,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 2. Designations Table
        $db->query("
            CREATE TABLE IF NOT EXISTS `designations` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
                `title` VARCHAR(100) NOT NULL,
                `code` VARCHAR(20) NOT NULL,
                `description` TEXT NULL,
                `is_active` TINYINT(1) DEFAULT 1,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 3. Shift Templates Table
        $db->query("
            CREATE TABLE IF NOT EXISTS `shift_templates` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
                `name` VARCHAR(100) NOT NULL,
                `start_time` TIME NOT NULL DEFAULT '09:00:00',
                `end_time` TIME NOT NULL DEFAULT '17:00:00',
                `grace_minutes` INT NOT NULL DEFAULT 15,
                `late_after` TIME NOT NULL DEFAULT '09:16:00',
                `half_day_after` TIME NOT NULL DEFAULT '12:00:00',
                `absent_after` TIME NOT NULL DEFAULT '14:00:00',
                `min_hours` DECIMAL(4,2) DEFAULT 8.00,
                `max_hours` DECIMAL(4,2) DEFAULT 10.00,
                `is_overtime_enabled` TINYINT(1) DEFAULT 1,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 4. Employees Table
        $db->query("DROP TABLE IF EXISTS `employees`;");
        $db->query("
            CREATE TABLE `employees` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `tenant_id` INT DEFAULT 1,
                `user_id` INT NULL,
                `emp_code` VARCHAR(30) NOT NULL,
                `first_name` VARCHAR(50) NOT NULL,
                `last_name` VARCHAR(50) NOT NULL,
                `email` VARCHAR(100) NOT NULL,
                `phone` VARCHAR(30) NULL,
                `department_id` INT NULL,
                `designation_id` INT NULL,
                `branch_id` INT DEFAULT 1,
                `shift_template_id` INT NULL,
                `min_clock_in` TIME DEFAULT '09:00:00',
                `max_clock_out` TIME DEFAULT '17:00:00',
                `employment_type` VARCHAR(30) DEFAULT 'full_time',
                `joining_date` DATE NOT NULL,
                `salary_basic` DECIMAL(10,2) DEFAULT 0.00,
                `status` VARCHAR(30) DEFAULT 'active',
                `photo` VARCHAR(255) NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // 5. Employee Profiles Table
        $db->query("
            CREATE TABLE IF NOT EXISTS `employee_profiles` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `employee_id` INT UNSIGNED NOT NULL,
                `dob` DATE NULL,
                `gender` ENUM('male', 'female', 'other') DEFAULT 'male',
                `emergency_contact` VARCHAR(50) NULL,
                `address` TEXT NULL,
                `bank_name` VARCHAR(100) NULL,
                `bank_acc_no` VARCHAR(50) NULL,
                `pan_no` VARCHAR(30) NULL,
                `aadhaar_no` VARCHAR(30) NULL,
                `reporting_manager_id` INT UNSIGNED NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 6. Staff Attendance Logs Table
        $db->query("
            CREATE TABLE IF NOT EXISTS `staff_attendance_logs` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
                `employee_id` INT UNSIGNED NOT NULL,
                `date` DATE NOT NULL,
                `clock_in` TIME NULL,
                `clock_out` TIME NULL,
                `working_hours` DECIMAL(4,2) DEFAULT 0.00,
                `late_minutes` INT DEFAULT 0,
                `status` ENUM('present', 'late', 'half_day', 'absent', 'on_leave', 'holiday', 'wfh') DEFAULT 'present',
                `device_type` VARCHAR(50) DEFAULT 'web_kiosk',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY `uq_emp_date` (`employee_id`, `date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 7. Leave Types Table
        $db->query("
            CREATE TABLE IF NOT EXISTS `leave_types` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
                `name` VARCHAR(50) NOT NULL,
                `code` VARCHAR(20) NOT NULL,
                `annual_allowance` INT DEFAULT 12,
                `is_paid` TINYINT(1) DEFAULT 1
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 8. Leave Requests Table
        $db->query("
            CREATE TABLE IF NOT EXISTS `leave_requests` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
                `employee_id` INT UNSIGNED NOT NULL,
                `leave_type_id` INT UNSIGNED NOT NULL,
                `start_date` DATE NOT NULL,
                `end_date` DATE NOT NULL,
                `days` DECIMAL(3,1) NOT NULL DEFAULT 1.0,
                `reason` TEXT NULL,
                `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 9. Salary Structures Table
        $db->query("
            CREATE TABLE IF NOT EXISTS `salary_structures` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
                `employee_id` INT UNSIGNED NOT NULL,
                `basic` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `hra` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `medical_allowance` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `transport_allowance` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `special_allowance` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `pf_deduction` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `esi_deduction` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `tax_deduction` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY `uq_sal_emp` (`employee_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 10. Payroll Runs Table
        $db->query("
            CREATE TABLE IF NOT EXISTS `payroll_runs` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
                `month_year` VARCHAR(20) NOT NULL,
                `total_gross` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                `total_deductions` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                `total_net` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                `status` ENUM('draft', 'approved', 'locked') DEFAULT 'draft',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 11. Payroll Items Table
        $db->query("
            CREATE TABLE IF NOT EXISTS `payroll_items` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `payroll_run_id` INT UNSIGNED NOT NULL,
                `employee_id` INT UNSIGNED NOT NULL,
                `working_days` INT DEFAULT 26,
                `present_days` INT DEFAULT 26,
                `late_days` INT DEFAULT 0,
                `half_days` INT DEFAULT 0,
                `absent_days` INT DEFAULT 0,
                `gross_salary` DECIMAL(10,2) DEFAULT 0.00,
                `late_deduction` DECIMAL(10,2) DEFAULT 0.00,
                `absent_deduction` DECIMAL(10,2) DEFAULT 0.00,
                `net_salary` DECIMAL(10,2) DEFAULT 0.00
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Seed Default Departments
        $db->query("
            INSERT IGNORE INTO `departments` (`id`, `tenant_id`, `name`, `code`, `description`) VALUES
            (1, 1, 'Teaching & Academic', 'DEP-ACAD', 'Core Teaching Faculty'),
            (2, 1, 'Administration', 'DEP-ADM', 'School Administration & Operations'),
            (3, 1, 'Therapy & Special Ed', 'DEP-THRP', 'Speech, Occupational & IEP Therapists'),
            (4, 1, 'Finance & Accounting', 'DEP-FIN', 'Billing & Accounts'),
            (5, 1, 'Transport & Logistics', 'DEP-TRN', 'Bus Drivers & Safety Officers');
        ");

        // Seed Default Designations
        $db->query("
            INSERT IGNORE INTO `designations` (`id`, `tenant_id`, `title`, `code`, `description`) VALUES
            (1, 1, 'Principal', 'DES-PRN', 'Head of School'),
            (2, 1, 'Senior Educator / Teacher', 'DES-TCH', 'Academic Subject Teacher'),
            (3, 1, 'Occupational Therapist', 'DES-OT', 'Sensory Integration Specialist'),
            (4, 1, 'Speech Therapist', 'DES-ST', 'Speech & Language Pathologist'),
            (5, 1, 'Accountant', 'DES-ACC', 'Accounts & Fees Executive'),
            (6, 1, 'School Bus Driver', 'DES-DRV', 'Transport Driver');
        ");

        // Seed Default Shift Template
        $db->query("
            INSERT IGNORE INTO `shift_templates` (`id`, `tenant_id`, `name`, `start_time`, `end_time`, `grace_minutes`, `late_after`, `half_day_after`, `absent_after`, `min_hours`) VALUES
            (1, 1, 'General Staff Shift', '09:00:00', '17:00:00', 15, '09:16:00', '12:00:00', '14:00:00', 8.00);
        ");

        // Seed Sample Staff Employees
        $db->query("
            INSERT IGNORE INTO `employees` (`id`, `tenant_id`, `emp_code`, `first_name`, `last_name`, `email`, `phone`, `department_id`, `designation_id`, `employment_type`, `joining_date`, `salary_basic`, `status`) VALUES
            (1, 1, 'EMP-101', 'Sarah', 'Jenkins', 'teacher@psnf.edu', '9876543210', 1, 2, 'full_time', '2024-01-15', 45000.00, 'active'),
            (2, 1, 'EMP-102', 'Priya', 'Mehta', 'priya@psnf.edu', '9876543211', 3, 3, 'full_time', '2024-02-01', 48000.00, 'active'),
            (3, 1, 'EMP-103', 'John', 'Driver', 'driver@psnf.edu', '9876543212', 5, 6, 'full_time', '2024-03-10', 25000.00, 'active');
        ");

        $db->query("SET FOREIGN_KEY_CHECKS = 1;");
    }

    public function down(): void
    {
        $db = Application::$app->db;
        $db->query("DROP TABLE IF EXISTS `payroll_items`, `payroll_runs`, `salary_structures`, `leave_requests`, `leave_types`, `staff_attendance_logs`, `employee_profiles`, `employees`, `shift_templates`, `designations`, `departments`;");
    }
}
