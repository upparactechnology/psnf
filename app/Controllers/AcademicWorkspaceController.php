<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;
use Core\Session;

class AcademicWorkspaceController extends Controller
{
    private function db()
    {
        return Application::$app->db;
    }

    public function index(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        // 1. Get Academic Years for dropdown/selector
        $years = $db->select("SELECT * FROM academic_years WHERE tenant_id = ? ORDER BY year_name DESC", [$tenantId]);
        
        // Active Year Selection
        $selectedYearId = (int) $this->request->get('academic_year_id');
        if (!$selectedYearId) {
            foreach ($years as $y) {
                if ($y['status'] === 'current') {
                    $selectedYearId = $y['id'];
                    break;
                }
            }
            if (!$selectedYearId && !empty($years)) {
                $selectedYearId = $years[0]['id'];
            }
        }
        $selectedYearRow = $db->selectOne("SELECT * FROM academic_years WHERE id = ?", [(int)$selectedYearId]);
        $selectedYearName = $selectedYearRow['year_name'] ?? '2026-27';

        // 2. Get Main Groups with dynamic stats
        $mainGroups = $db->select("
            SELECT mg.*,
                   (SELECT COUNT(*) FROM classes c WHERE c.main_group_id = mg.id AND c.academic_year_id = ?) as class_count,
                   (SELECT COUNT(*) FROM students s WHERE s.main_group_id = mg.id AND s.deleted_at IS NULL AND s.admission_status = 'enrolled') as student_count
            FROM main_groups mg
            WHERE mg.tenant_id = ? AND mg.is_active = 1
        ", [(int)$selectedYearId, $tenantId]);

        // Selected Main Group context (group-first UX)
        $selectedGroupId = $this->request->get('main_group_id');
        $groupContext = null;
        $workspaceData = [];

        if ($selectedGroupId) {
            $groupContext = $db->selectOne("SELECT * FROM main_groups WHERE id = ? AND tenant_id = ?", [(int)$selectedGroupId, $tenantId]);
            if ($groupContext) {
                // Fetch scoped Classes
                $classes = $db->select("
                    SELECT c.*, u.name as teacher_name,
                           (SELECT COUNT(*) FROM students s WHERE s.class_id = c.id AND s.deleted_at IS NULL) as student_count
                    FROM classes c
                    LEFT JOIN users u ON c.class_teacher_id = u.id
                    WHERE c.main_group_id = ? AND c.academic_year_id = ? AND c.tenant_id = ?
                ", [(int)$selectedGroupId, (int)$selectedYearId, $tenantId]);

                // Fetch scoped Students
                $students = $db->select("
                    SELECT s.*, c.name as class_name, c.section as class_section
                    FROM students s
                    LEFT JOIN classes c ON s.class_id = c.id
                    WHERE s.main_group_id = ? AND s.tenant_id = ? AND s.deleted_at IS NULL AND s.admission_status = 'enrolled'
                    ORDER BY s.first_name ASC
                ", [(int)$selectedGroupId, $tenantId]);

                // Fetch Teachers working in this Main Group (either class teacher or subject teacher in timetable)
                $teachers = $db->select("
                    SELECT DISTINCT u.id, u.name, u.email
                    FROM users u
                    JOIN user_roles ur ON u.id = ur.user_id
                    JOIN roles r ON ur.role_id = r.id
                    WHERE u.tenant_id = ? AND r.slug = 'teacher' AND (
                        u.id IN (SELECT class_teacher_id FROM classes WHERE main_group_id = ? AND academic_year_id = ?)
                        OR u.id IN (SELECT teacher_id FROM timetables WHERE class_id IN (SELECT id FROM classes WHERE main_group_id = ? AND academic_year_id = ?))
                    )
                ", [$tenantId, (int)$selectedGroupId, (int)$selectedYearId, (int)$selectedGroupId, (int)$selectedYearId]);

                // Resolved Curriculum Template
                $curriculum = $db->selectOne("
                    SELECT * FROM curriculum_templates 
                    WHERE academic_year_id = ? AND main_group_id = ? AND tenant_id = ? LIMIT 1
                ", [(int)$selectedYearId, (int)$selectedGroupId, $tenantId]);

                $curriculumSubjects = [];
                if ($curriculum) {
                    $curriculumSubjects = $db->select("
                        SELECT s.name, s.code, s.category, cs.assessment_type
                        FROM curriculum_subjects cs
                        JOIN subjects s ON cs.subject_id = s.id
                        JOIN curriculum_sections sec ON cs.curriculum_section_id = sec.id
                        WHERE sec.curriculum_template_id = ?
                        ORDER BY sec.sort_order ASC, cs.sequence ASC
                    ", [$curriculum['id']]);
                }

                // Scoped attendance statistic
                $attendanceCount = $db->selectOne("
                    SELECT COUNT(*) as total, 
                           SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present
                    FROM attendance 
                    WHERE date = ? AND student_id IN (SELECT id FROM students WHERE main_group_id = ? AND deleted_at IS NULL)
                ", [date('Y-m-d'), (int)$selectedGroupId]);
                $attendancePct = 100;
                if ($attendanceCount['total'] > 0) {
                    $attendancePct = round(($attendanceCount['present'] / $attendanceCount['total']) * 100);
                }

                // Scoped pending report cards
                $pendingReportCards = $db->selectOne("
                    SELECT COUNT(*) as cnt 
                    FROM students s
                    WHERE s.main_group_id = ? 
                      AND s.admission_status = 'enrolled' 
                      AND s.deleted_at IS NULL 
                      AND s.id NOT IN (SELECT DISTINCT student_id FROM student_report_cards WHERE academic_year = ?)
                ", [(int)$selectedGroupId, $selectedYearName])['cnt'] ?? 0;

                $workspaceData = [
                    'classes' => $classes,
                    'students' => $students,
                    'teachers' => $teachers,
                    'curriculum' => $curriculum,
                    'curriculumSubjects' => $curriculumSubjects,
                    'attendancePct' => $attendancePct,
                    'pendingReportCards' => $pendingReportCards
                ];
            }
        }

        // Global statistics for the year if no group selected
        $stats = [
            'years_count' => count($years),
            'groups_count' => count($mainGroups),
            'classes_count' => $db->selectOne("SELECT COUNT(*) as cnt FROM classes WHERE academic_year_id = ?", [(int)$selectedYearId])['cnt'] ?? 0,
            'students_count' => $db->selectOne("SELECT COUNT(*) as cnt FROM students WHERE tenant_id = ? AND deleted_at IS NULL AND admission_status = 'enrolled'", [$tenantId])['cnt'] ?? 0,
            'teachers_count' => $db->selectOne("SELECT COUNT(*) as cnt FROM users u JOIN user_roles ur ON u.id = ur.user_id JOIN roles r ON ur.role_id = r.id WHERE u.tenant_id = ? AND r.slug = 'teacher'", [$tenantId])['cnt'] ?? 0,
            'attendance_today' => '96%',
            'pending_rc' => $db->selectOne("
                SELECT COUNT(*) as cnt FROM students WHERE tenant_id = ? AND admission_status = 'enrolled' AND deleted_at IS NULL 
                AND id NOT IN (SELECT DISTINCT student_id FROM student_report_cards WHERE academic_year = ?)
            ", [$tenantId, $selectedYearName])['cnt'] ?? 0
        ];

        return $this->view('academics/workspace', compact(
            'years', 'selectedYearId', 'selectedYearName', 'mainGroups', 'selectedGroupId', 'groupContext', 'workspaceData', 'stats'
        ));
    }
}
