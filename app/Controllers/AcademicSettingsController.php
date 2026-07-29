<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;
use Core\Session;

class AcademicSettingsController extends Controller
{
    private function db()
    {
        return Application::$app->db;
    }

    private function checkAndInitializeAcademicSettings(): void
    {
        $db = $this->db();
        
        $db->query("
            CREATE TABLE IF NOT EXISTS `academic_years` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id` INT UNSIGNED NOT NULL,
                `year_name` VARCHAR(50) NOT NULL,
                `status` ENUM('current', 'unlocked', 'locked', 'archived') DEFAULT 'unlocked',
                `start_date` DATE NULL,
                `end_date` DATE NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY `uq_year_tenant` (`tenant_id`, `year_name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $db->query("
            CREATE TABLE IF NOT EXISTS `academic_semesters` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id` INT UNSIGNED NOT NULL,
                `academic_year_id` INT UNSIGNED NOT NULL,
                `name` VARCHAR(50) NOT NULL,
                `status` ENUM('OPEN', 'LOCKED') DEFAULT 'OPEN',
                `date_range` VARCHAR(100) NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Safely add status column if table existed previously without status column
        try {
            $db->query("ALTER TABLE `academic_years` ADD COLUMN `status` ENUM('current', 'unlocked', 'locked', 'archived') DEFAULT 'unlocked'");
        } catch (\Throwable $e) {
            // Column already exists
        }

        // Seed default academic years if empty
        $tenantId = \Core\Database::getTenantId() ?: 1;
        $count = $db->selectOne("SELECT COUNT(*) as c FROM academic_years WHERE tenant_id = ?", [$tenantId])['c'] ?? 0;
        if ($count == 0) {
            $db->insert('academic_years', ['tenant_id' => $tenantId, 'year_name' => '2024-25', 'status' => 'archived']);
            $db->insert('academic_years', ['tenant_id' => $tenantId, 'year_name' => '2025-26', 'status' => 'unlocked']);
            $db->insert('academic_years', ['tenant_id' => $tenantId, 'year_name' => '2026-27', 'status' => 'current']);
        }
    }

    public function index(): string
    {
        $this->checkAndInitializeAcademicSettings();
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        $years = $db->select("SELECT * FROM academic_years WHERE tenant_id = ? ORDER BY id DESC", [$tenantId]);
        $semesters = $db->select("SELECT * FROM academic_semesters WHERE tenant_id = ? ORDER BY id ASC", [$tenantId]);
        $students = $db->select("SELECT id, first_name, last_name, admission_number, class, section FROM students WHERE tenant_id = ? AND deleted_at IS NULL ORDER BY first_name ASC", [$tenantId]);
        $subjects = $db->select("SELECT * FROM subjects WHERE tenant_id = ? ORDER BY name ASC", [$tenantId]);

        $shift = $db->selectOne("SELECT lec_grace_minutes FROM shift_templates WHERE id = 1");
        $lecGraceMinutes = $shift['lec_grace_minutes'] ?? 5;

        return $this->view('academic/settings', compact('years', 'semesters', 'students', 'subjects', 'lecGraceMinutes'));
    }

    public function saveAttendanceSettings(): string
    {
        $db = $this->db();
        $lecGraceMinutes = (int) $this->request->input('lec_grace_minutes', 5);

        $db->update('shift_templates', [
            'lec_grace_minutes' => $lecGraceMinutes
        ], 'id = 1');

        Session::flash('success', 'Lecture-wise attendance settings updated.');
        return $this->redirect('/academics/settings?tab=lock');
    }

    public function storeYear(): string
    {
        $this->checkAndInitializeAcademicSettings();
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        $yearName = trim($this->request->input('year_name', ''));
        $tab = $this->request->input('tab', 'years');

        if (empty($yearName)) {
            Session::flash('error', 'Academic Year Name is required.');
            return $this->redirect('/academics/settings?tab=' . $tab);
        }

        try {
            $db->insert('academic_years', [
                'tenant_id' => $tenantId,
                'year_name' => $yearName,
                'status' => 'unlocked'
            ]);
            Session::flash('success', "Academic Year '{$yearName}' created successfully.");
        } catch (\Throwable $e) {
            Session::flash('error', 'Academic year already exists.');
        }

        return $this->redirect('/academics/settings?tab=' . $tab);
    }

    public function lockYear(string $id): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();
        $tab = $this->request->input('tab', 'lock');

        $db->query("UPDATE academic_years SET status = 'locked' WHERE id = ? AND tenant_id = ?", [(int)$id, $tenantId]);
        Session::flash('success', "Academic Year locked successfully. Mark & Attendance entry frozen.");

        return $this->redirect('/academics/settings?tab=' . $tab);
    }

    public function runYearClosingWizard(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();
        $promotions = $this->request->input('promotions', []); // [student_id => 'promote' | 'stay']
        $subjectActions = $this->request->input('subjects', []); // [subject_id => 'continue' | 'remove' | 'edit']

        // 1. Update Student Class Promotions
        foreach ($promotions as $studentId => $action) {
            if ($action === 'promote') {
                $st = $db->selectOne("SELECT class FROM students WHERE id = ?", [(int)$studentId]);
                if ($st && !empty($st['class'])) {
                    // Auto-increment class number if numerical (e.g. Class 1 -> Class 2)
                    $nextClass = preg_replace_callback('/\d+/', fn($m) => ((int)$m[0] + 1), $st['class']);
                    $db->query("UPDATE students SET class = ? WHERE id = ?", [$nextClass, (int)$studentId]);
                }
            }
        }

        // 2. Process Subjects Rollover
        foreach ($subjectActions as $subjectId => $action) {
            if ($action === 'remove') {
                $db->query("DELETE FROM subjects WHERE id = ? AND tenant_id = ?", [(int)$subjectId, $tenantId]);
            }
        }

        // 3. Auto-copy existing subject assignments to new year/semester if unselected
        $db->query("UPDATE academic_years SET status = 'archived' WHERE status = 'current' AND tenant_id = ?", [$tenantId]);
        $db->query("UPDATE academic_years SET status = 'current' WHERE status = 'unlocked' AND tenant_id = ? LIMIT 1", [$tenantId]);

        Session::flash('success', "Year Closing Wizard executed! Student promotions & subject rollovers applied successfully.");

        return $this->redirect('/academics/settings?tab=wizard');
    }

    public function lectureAttendance(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();
        
        $logs = $db->select("
            SELECT tla.*, u.name as teacher_name, u.email as teacher_email
            FROM teacher_attendance tla
            JOIN users u ON tla.user_id = u.id
            WHERE tla.tenant_id = ?
            ORDER BY tla.attendance_date DESC, tla.opened_at DESC
        ", [$tenantId]);

        return $this->view('academic/lecture_attendance', compact('logs'));
    }
}
