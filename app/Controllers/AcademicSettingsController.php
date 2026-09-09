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
                `start_date` DATE DEFAULT NULL,
                `end_date` DATE DEFAULT NULL,
                `total_working_days` INT DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Safely add status column if table existed previously without status column
        try {
            $db->query("ALTER TABLE `academic_years` ADD COLUMN `status` ENUM('current', 'unlocked', 'locked', 'archived') DEFAULT 'unlocked'");
        } catch (\Throwable $e) {
            // Column already exists
        }
    }

    public function index(): string
    {
        $this->checkAndInitializeAcademicSettings();
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        $years = $db->select("SELECT * FROM academic_years WHERE tenant_id = ? ORDER BY id DESC", [$tenantId]);

        // Semester year filter
        $selectedYearId = (int) $this->request->get('year_id', 0);
        if (!$selectedYearId && !empty($years)) {
            foreach ($years as $y) {
                if ($y['status'] === 'current') { $selectedYearId = (int) $y['id']; break; }
            }
            if (!$selectedYearId) $selectedYearId = (int) $years[0]['id'];
        }
        $semesters = $db->select("SELECT * FROM academic_semesters WHERE tenant_id = ? AND academic_year_id = ? ORDER BY id ASC", [$tenantId, $selectedYearId]);

        $students = $db->select("SELECT id, first_name, last_name, admission_number, class, section FROM students WHERE tenant_id = ? AND deleted_at IS NULL ORDER BY first_name ASC", [$tenantId]);
        $subjects = $db->select("SELECT * FROM subjects WHERE tenant_id = ? ORDER BY name ASC", [$tenantId]);

        $shift = $db->selectOne("SELECT lec_grace_minutes FROM shift_templates WHERE id = 1");
        $lecGraceMinutes = $shift['lec_grace_minutes'] ?? 5;

        // Selected year info for calendar
        $selectedYear = null;
        foreach ($years as $y) {
            if ((int)$y['id'] === $selectedYearId) { $selectedYear = $y; break; }
        }

        return $this->view('academics/settings', compact('years', 'semesters', 'students', 'subjects', 'lecGraceMinutes', 'selectedYearId', 'selectedYear'));
    }

    public function storeSemester(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();
        $yearId = (int) $this->request->input('academic_year_id');
        $name = trim($this->request->input('name', ''));
        $dateRange = trim($this->request->input('date_range', ''));
        $startDate = $this->request->input('start_date', '') ?: null;
        $endDate = $this->request->input('end_date', '') ?: null;
        $totalWorkingDays = $this->request->input('total_working_days', '') !== '' ? (int) $this->request->input('total_working_days') : null;

        if (empty($name) || !$yearId) {
            Session::flash('error', 'Semester name and academic year are required.');
            return $this->redirect(url('academics/settings?tab=semesters&year_id=' . $yearId));
        }

        try {
            $db->insert('academic_semesters', [
                'tenant_id' => $tenantId,
                'academic_year_id' => $yearId,
                'name' => $name,
                'status' => 'OPEN',
                'date_range' => $dateRange ?: null,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_working_days' => $totalWorkingDays,
            ]);
            Session::flash('success', "Semester '{$name}' created successfully.");
        } catch (\Throwable $e) {
            Session::flash('error', 'Failed to create semester: ' . $e->getMessage());
        }

        return $this->redirect(url('academics/settings?tab=semesters&year_id=' . $yearId));
    }

    public function deleteSemester(string $id): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();
        $yearId = (int) $this->request->input('academic_year_id');

        $db->query("DELETE FROM academic_semesters WHERE id = ? AND tenant_id = ?", [(int)$id, $tenantId]);
        Session::flash('success', "Semester deleted successfully.");

        return $this->redirect(url('academics/settings?tab=semesters&year_id=' . $yearId));
    }

    public function saveAttendanceSettings(): string
    {
        $db = $this->db();
        $lecGraceMinutes = (int) $this->request->input('lec_grace_minutes', 5);

        $db->update('shift_templates', [
            'lec_grace_minutes' => $lecGraceMinutes
        ], 'id = 1');

        Session::flash('success', 'Lecture-wise attendance settings updated.');
        return $this->redirect(url('academics/settings?tab=lock'));
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
            return $this->redirect(url('academics/settings?tab=' . $tab));
        }

        try {
            // Ensure year-scoped columns exist
            try { $db->query("ALTER TABLE `classes` ADD COLUMN `academic_year_id` INT UNSIGNED NULL"); } catch (\Throwable $e) {}
            try { $db->query("ALTER TABLE `classes` ADD COLUMN `main_group_id` INT UNSIGNED NULL"); } catch (\Throwable $e) {}
            try { $db->query("ALTER TABLE `shift_templates` ADD COLUMN `academic_year_id` INT UNSIGNED NULL AFTER `tenant_id`"); } catch (\Throwable $e) {}

            $newYearId = $db->insert('academic_years', [
                'tenant_id' => $tenantId,
                'year_name' => $yearName,
                'status' => 'unlocked'
            ]);

            // Find the most recent existing year to copy from
            $sourceYear = $db->selectOne("SELECT * FROM academic_years WHERE tenant_id = ? AND id != ? ORDER BY id DESC LIMIT 1", [$tenantId, $newYearId]);

            if ($sourceYear) {
                $copied = [];

                // 1. Copy Classes
                $classes = $db->select("SELECT * FROM classes WHERE tenant_id = ? AND (academic_year_id = ? OR academic_year_id IS NULL)", [$tenantId, (int)$sourceYear['id']]);
                foreach ($classes as $cls) {
                    $db->insert('classes', [
                        'tenant_id' => $tenantId,
                        'academic_year_id' => $newYearId,
                        'main_group_id' => $cls['main_group_id'] ?? null,
                        'name' => $cls['name'],
                        'section' => $cls['section'] ?? '',
                        'class_teacher_id' => null,
                    ]);
                }
                $copied[] = count($classes) . ' classes';

                // 2. Copy Shift Template (staff settings)
                $shift = $db->selectOne("SELECT * FROM shift_templates WHERE tenant_id = ? AND academic_year_id = ? ORDER BY id DESC LIMIT 1", [$tenantId, (int)$sourceYear['id']]);
                if (!$shift) $shift = $db->selectOne("SELECT * FROM shift_templates WHERE id = 1");
                if ($shift) {
                    $shiftData = $shift;
                    unset($shiftData['id']);
                    $shiftData['tenant_id'] = $tenantId;
                    $shiftData['academic_year_id'] = $newYearId;
                    $shiftData['name'] = $shiftData['name'] ?? 'Default Shift';
                    $db->insert('shift_templates', $shiftData);
                    $copied[] = 'shift template';
                }

                // 3. Copy Curriculum Templates + Sections + Subjects
                $templates = $db->select("SELECT * FROM curriculum_templates WHERE academic_year_id = ? AND tenant_id = ?", [(int)$sourceYear['id'], $tenantId]);
                foreach ($templates as $tpl) {
                    $newTplId = $db->insert('curriculum_templates', [
                        'tenant_id' => $tenantId,
                        'academic_year_id' => $newYearId,
                        'main_group_id' => $tpl['main_group_id'],
                        'name' => $tpl['name'],
                        'version' => 1,
                        'description' => $tpl['description'],
                        'is_active' => $tpl['is_active']
                    ]);
                    $sections = $db->select("SELECT * FROM curriculum_sections WHERE curriculum_template_id = ?", [$tpl['id']]);
                    foreach ($sections as $sec) {
                        $newSecId = $db->insert('curriculum_sections', [
                            'curriculum_template_id' => $newTplId,
                            'section_name' => $sec['section_name'],
                            'sort_order' => $sec['sort_order']
                        ]);
                        $subjects = $db->select("SELECT * FROM curriculum_subjects WHERE curriculum_section_id = ?", [$sec['id']]);
                        foreach ($subjects as $sub) {
                            $db->insert('curriculum_subjects', [
                                'curriculum_section_id' => $newSecId,
                                'subject_id' => $sub['subject_id'],
                                'is_required' => $sub['is_required'],
                                'default_grade' => $sub['default_grade'],
                                'visible' => $sub['visible'],
                                'sequence' => $sub['sequence'],
                                'assessment_type' => $sub['assessment_type']
                            ]);
                        }
                    }
                }
                $copied[] = count($templates) . ' curriculum templates';

                // 4. Copy Fee Structures
                $fees = $db->select("SELECT * FROM fee_structures WHERE academic_year_id = ?", [(int)$sourceYear['id']]);
                foreach ($fees as $fee) {
                    $feeData = $fee;
                    unset($feeData['id']);
                    $feeData['academic_year_id'] = $newYearId;
                    $feeData['version'] = 1;
                    $db->insert('fee_structures', $feeData);
                }
                $copied[] = count($fees) . ' fee structures';

                Session::flash('success', "Academic Year '{$yearName}' created. Copied: " . implode(', ', $copied) . ".");
            } else {
                Session::flash('success', "Academic Year '{$yearName}' created successfully.");
            }
        } catch (\Throwable $e) {
            Session::flash('error', 'Academic year already exists or error: ' . $e->getMessage());
        }

        return $this->redirect(url('academics/settings?tab=' . $tab));
    }

    public function lockYear(string $id): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();
        $tab = $this->request->input('tab', 'lock');

        $db->query("UPDATE academic_years SET status = 'locked' WHERE id = ? AND tenant_id = ?", [(int)$id, $tenantId]);
        Session::flash('success', "Academic Year locked successfully. Mark & Attendance entry frozen.");

        return $this->redirect(url('academics/settings?tab=' . $tab));
    }

    public function archiveYear(string $id): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();
        $tab = $this->request->input('tab', 'years');

        $db->query("UPDATE academic_years SET status = 'archived' WHERE id = ? AND tenant_id = ?", [(int)$id, $tenantId]);
        Session::flash('success', "Academic Year archived successfully.");

        return $this->redirect(url('academics/settings?tab=' . $tab));
    }

    public function deleteYear(string $id): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();
        $tab = $this->request->input('tab', 'years');

        $db->query("DELETE FROM academic_years WHERE id = ? AND tenant_id = ?", [(int)$id, $tenantId]);
        Session::flash('success', "Academic Year deleted successfully.");

        return $this->redirect(url('academics/settings?tab=' . $tab));
    }
    public function copyPreviousYear(string $id): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();
        $tab = $this->request->input('tab', 'years');

        // Find the previous academic year if exists
        $currentYear = $db->selectOne("SELECT * FROM academic_years WHERE id = ? AND tenant_id = ?", [(int)$id, $tenantId]);
        if (!$currentYear) {
            Session::flash('error', 'Academic Year not found.');
            return $this->redirect(url('academics/settings?tab=' . $tab));
        }

        $prevYear = $db->selectOne("SELECT * FROM academic_years WHERE id < ? AND tenant_id = ? ORDER BY id DESC LIMIT 1", [(int)$id, $tenantId]);
        if (!$prevYear) {
            Session::flash('error', 'No previous academic year found to copy from.');
            return $this->redirect(url('academics/settings?tab=' . $tab));
        }

        // Copy Curriculum Templates, Sections, and Subjects from previous year to current year
        $templates = $db->select("SELECT * FROM curriculum_templates WHERE academic_year_id = ? AND tenant_id = ?", [(int)$prevYear['id'], $tenantId]);
        foreach ($templates as $tpl) {
            $newTplId = $db->insert('curriculum_templates', [
                'tenant_id' => $tenantId,
                'academic_year_id' => $id,
                'main_group_id' => $tpl['main_group_id'],
                'name' => $tpl['name'] . ' (Copied)',
                'version' => 1,
                'description' => $tpl['description'],
                'is_active' => $tpl['is_active']
            ]);

            $sections = $db->select("SELECT * FROM curriculum_sections WHERE curriculum_template_id = ?", [$tpl['id']]);
            foreach ($sections as $sec) {
                $newSecId = $db->insert('curriculum_sections', [
                    'curriculum_template_id' => $newTplId,
                    'section_name' => $sec['section_name'],
                    'sort_order' => $sec['sort_order']
                ]);

                $subjects = $db->select("SELECT * FROM curriculum_subjects WHERE curriculum_section_id = ?", [$sec['id']]);
                foreach ($subjects as $sub) {
                    $db->insert('curriculum_subjects', [
                        'curriculum_section_id' => $newSecId,
                        'subject_id' => $sub['subject_id'],
                        'is_required' => $sub['is_required'],
                        'default_grade' => $sub['default_grade'],
                        'visible' => $sub['visible'],
                        'sequence' => $sub['sequence'],
                        'assessment_type' => $sub['assessment_type']
                    ]);
                }
            }
        }

        Session::flash('success', "Curriculum Templates copied from year '{$prevYear['year_name']}' successfully!");
        return $this->redirect(url('academics/settings?tab=' . $tab));
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

        return $this->redirect(url('academics/settings?tab=wizard'));
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

        return $this->view('academics/lecture_attendance', compact('logs'));
    }

    public function mainGroupsIndex(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();
        $groups = $db->select("SELECT * FROM main_groups WHERE tenant_id = ? ORDER BY id ASC", [$tenantId]);
        return $this->view('academics/main_groups', compact('groups'));
    }

    public function storeMainGroup(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();
        $name = trim($this->request->input('name', ''));
        $description = trim($this->request->input('description', ''));
        $age_range = trim($this->request->input('age_range', ''));
        $color = $this->request->input('color', '#6366f1');
        $icon = $this->request->input('icon', '🎓');
        $addNext = !empty($_POST['_add_next']);

        if (empty($name)) {
            Session::flash('error', 'Main Group Name is required.');
            return $this->redirect(url('academics/main-groups'));
        }

        $db->insert('main_groups', [
            'tenant_id' => $tenantId,
            'name' => $name,
            'description' => $description,
            'age_range' => $age_range,
            'is_active' => 1,
            'color' => $color,
            'icon' => $icon
        ]);

        Session::flash('success', "Main Group '{$name}' created successfully.");
        if ($addNext) {
            return $this->redirect(url('academics/main-groups?add_next=1'));
        }
        return $this->redirect(url('academics/main-groups'));
    }

    public function updateMainGroup(string $id): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();
        $name = trim($this->request->input('name', ''));
        $description = trim($this->request->input('description', ''));
        $age_range = trim($this->request->input('age_range', ''));
        $color = $this->request->input('color', '#6366f1');
        $icon = $this->request->input('icon', '🎓');
        $is_active = (int)$this->request->input('is_active', 1);

        if (empty($name)) {
            Session::flash('error', 'Main Group Name is required.');
            return $this->redirect(url('academics/main-groups'));
        }

        $db->update('main_groups', [
            'name' => $name,
            'description' => $description,
            'age_range' => $age_range,
            'is_active' => $is_active,
            'color' => $color,
            'icon' => $icon
        ], 'id = ? AND tenant_id = ?', [(int)$id, $tenantId]);

        Session::flash('success', "Main Group '{$name}' updated successfully.");
        return $this->redirect(url('academics/main-groups'));
    }

    public function subjectMasterIndex(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();
        $subjects = $db->select("SELECT * FROM subjects WHERE tenant_id = ? ORDER BY name ASC", [$tenantId]);
        $subjectTypes = $db->select("SELECT * FROM subject_types WHERE tenant_id = ? AND is_active = 1 ORDER BY sort_order ASC", [$tenantId]);
        return $this->view('academics/subject_master', compact('subjects', 'subjectTypes'));
    }

    public function storeSubject(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();
        $name = trim($this->request->input('name', ''));
        $code = trim($this->request->input('code', ''));
        $category = $this->request->input('category', 'Academic');
        $addNext = !empty($_POST['_add_next']);

        if (empty($name) || empty($code)) {
            Session::flash('error', 'Subject Name and Code are required.');
            return $this->redirect(url('academics/subject-master'));
        }

        $db->insert('subjects', [
            'tenant_id' => $tenantId,
            'school_id' => 1,
            'code' => $code,
            'name' => $name,
            'category' => $category,
            'is_active' => 1
        ]);

        Session::flash('success', "Subject '{$name}' added to Master successfully.");
        if ($addNext) {
            return $this->redirect(url('academics/subject-master?add_next=1'));
        }
        return $this->redirect(url('academics/subject-master'));
    }

    public function updateSubject(string $id): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();
        $name = trim($this->request->input('name', ''));
        $code = trim($this->request->input('code', ''));
        $category = $this->request->input('category', 'Academic');

        if (empty($name) || empty($code)) {
            Session::flash('error', 'Subject Name and Code are required.');
            return $this->redirect(url('academics/subject-master'));
        }

        $db->update('subjects', [
            'name' => $name,
            'code' => $code,
            'category' => $category
        ], 'id = ? AND tenant_id = ?', [(int)$id, $tenantId]);

        Session::flash('success', "Subject '{$name}' updated successfully.");
        return $this->redirect(url('academics/subject-master'));
    }

    public function destroySubject(string $id): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();
        $db->query("DELETE FROM subjects WHERE id = ? AND tenant_id = ?", [(int)$id, $tenantId]);
        Session::flash('success', "Subject removed from Master successfully.");
        return $this->redirect(url('academics/subject-master'));
    }
}
