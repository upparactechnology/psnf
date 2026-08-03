<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;
use Core\Session;

class ClassesController extends Controller
{
    private function db()
    {
        return Application::$app->db;
    }

    public function index(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        // Fetch classes with main groups and teachers
        $classes = $db->select("
            SELECT c.*, mg.name as group_name, mg.color as group_color, mg.icon as group_icon, 
                   u.name as teacher_name, ay.year_name, ct.name as curriculum_name,
                   (SELECT COUNT(*) FROM students s WHERE s.class_id = c.id AND s.deleted_at IS NULL) as student_count
            FROM classes c
            LEFT JOIN main_groups mg ON c.main_group_id = mg.id
            LEFT JOIN users u ON c.class_teacher_id = u.id
            LEFT JOIN academic_years ay ON c.academic_year_id = ay.id
            LEFT JOIN curriculum_templates ct ON c.curriculum_template_id = ct.id
            WHERE c.tenant_id = ?
            ORDER BY mg.name ASC, c.name ASC
        ", [$tenantId]);

        $years = $db->select("SELECT * FROM academic_years WHERE tenant_id = ? ORDER BY year_name DESC", [$tenantId]);
        $groups = $db->select("SELECT * FROM main_groups WHERE tenant_id = ? AND is_active = 1", [$tenantId]);
        $curriculums = $db->select("SELECT id, name, main_group_id, academic_year_id FROM curriculum_templates WHERE tenant_id = ? AND is_active = 1 ORDER BY name ASC", [$tenantId]);
        
        // Fetch teachers
        $teachers = $db->select("
            SELECT u.id, u.name 
            FROM users u
            JOIN user_roles ur ON u.id = ur.user_id
            JOIN roles r ON ur.role_id = r.id
            WHERE u.tenant_id = ? AND r.slug = 'teacher'
            ORDER BY u.name ASC
        ", [$tenantId]);

        return $this->view('classes/index', compact('classes', 'years', 'groups', 'teachers', 'curriculums'));
    }

    public function store(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();
        
        $className = trim($this->request->input('class', ''));
        $sectionName = trim($this->request->input('section', ''));
        $yearId = (int)$this->request->input('academic_year_id');
        $groupId = (int)$this->request->input('main_group_id');
        $curriculumTemplateId = $this->request->input('curriculum_template_id') ? (int)$this->request->input('curriculum_template_id') : null;
        $teacherId = (int)$this->request->input('class_teacher_id');

        if (empty($className)) {
            Session::flash('error', 'Class name is required.');
            return $this->redirect('/academics/classes');
        }

        if ($curriculumTemplateId) {
            $currCheck = $db->selectOne("SELECT id FROM curriculum_templates WHERE id = ? AND main_group_id = ? AND academic_year_id = ? AND tenant_id = ?", [$curriculumTemplateId, $groupId, $yearId, $tenantId]);
            if (!$currCheck) {
                Session::flash('error', 'The selected curriculum template does not match the selected Main Group or Academic Year.');
                return $this->redirect('/academics/classes');
            }
        }

        // Insert into classes
        $db->insert('classes', [
            'tenant_id' => $tenantId,
            'school_id' => 1,
            'branch_id' => 1,
            'name' => $className,
            'section' => $sectionName,
            'academic_year_id' => $yearId,
            'main_group_id' => $groupId,
            'curriculum_template_id' => $curriculumTemplateId,
            'class_teacher_id' => $teacherId ?: null
        ]);

        Session::flash('success', "Class '{$className}' created successfully.");
        return $this->redirect('/academics/classes');
    }

    public function show(string $id): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        $class = $db->selectOne("
            SELECT c.*, mg.name as group_name, mg.color as group_color, mg.icon as group_icon, 
                   u.name as teacher_name, ay.year_name
            FROM classes c
            LEFT JOIN main_groups mg ON c.main_group_id = mg.id
            LEFT JOIN users u ON c.class_teacher_id = u.id
            LEFT JOIN academic_years ay ON c.academic_year_id = ay.id
            WHERE c.id = ? AND c.tenant_id = ?
        ", [(int)$id, $tenantId]);

        if (!$class) {
            Session::flash('error', 'Class not found.');
            return $this->redirect('/academics/classes');
        }

        // Resolve curriculum template
        $curriculum = null;
        if (!empty($class['curriculum_template_id'])) {
            $curriculum = $db->selectOne("
                SELECT * FROM curriculum_templates 
                WHERE id = ? AND tenant_id = ?
            ", [(int)$class['curriculum_template_id'], $tenantId]);
        }
        if (!$curriculum) {
            $curriculum = $db->selectOne("
                SELECT * FROM curriculum_templates 
                WHERE academic_year_id = ? AND main_group_id = ? AND tenant_id = ? LIMIT 1
            ", [$class['academic_year_id'], $class['main_group_id'], $tenantId]);
        }

        $subjects = [];
        if ($curriculum) {
            $subjects = $db->select("
                SELECT s.name, s.code, s.category, cs.assessment_type
                FROM curriculum_subjects cs
                JOIN subjects s ON cs.subject_id = s.id
                JOIN curriculum_sections sec ON cs.curriculum_section_id = sec.id
                WHERE sec.curriculum_template_id = ?
                ORDER BY sec.sort_order ASC, cs.sequence ASC
            ", [$curriculum['id']]);
        }

        // Fetch students in this class
        $students = $db->select("
            SELECT * FROM students 
            WHERE class_id = ? AND tenant_id = ? AND deleted_at IS NULL
            ORDER BY first_name ASC
        ", [(int)$id, $tenantId]);

        $years = $db->select("SELECT * FROM academic_years WHERE tenant_id = ? ORDER BY year_name DESC", [$tenantId]);
        $groups = $db->select("SELECT * FROM main_groups WHERE tenant_id = ? AND is_active = 1", [$tenantId]);
        $curriculums = $db->select("SELECT id, name, main_group_id, academic_year_id FROM curriculum_templates WHERE tenant_id = ? AND is_active = 1 ORDER BY name ASC", [$tenantId]);
        $unassignedStudents = $db->select("
            SELECT id, first_name, last_name, admission_number 
            FROM students 
            WHERE tenant_id = ? AND class_id IS NULL AND deleted_at IS NULL AND admission_status = 'enrolled'
            ORDER BY first_name ASC
        ", [$tenantId]);

        $teachers = $db->select("
            SELECT u.id, u.name 
            FROM users u
            JOIN user_roles ur ON u.id = ur.user_id
            JOIN roles r ON ur.role_id = r.id
            WHERE u.tenant_id = ? AND r.slug = 'teacher'
            ORDER BY u.name ASC
        ", [$tenantId]);

        return $this->view('classes/show', compact('class', 'curriculum', 'subjects', 'students', 'years', 'groups', 'teachers', 'curriculums', 'unassignedStudents'));
    }

    public function update(string $id): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();
        
        $className = trim($this->request->input('class', ''));
        $sectionName = trim($this->request->input('section', ''));
        $yearId = (int)$this->request->input('academic_year_id');
        $groupId = (int)$this->request->input('main_group_id');
        $curriculumTemplateId = $this->request->input('curriculum_template_id') ? (int)$this->request->input('curriculum_template_id') : null;
        $teacherId = (int)$this->request->input('class_teacher_id');

        if (empty($className)) {
            Session::flash('error', 'Class name is required.');
            return $this->redirect('/academics/classes/' . $id);
        }

        if ($curriculumTemplateId) {
            $currCheck = $db->selectOne("SELECT id FROM curriculum_templates WHERE id = ? AND main_group_id = ? AND academic_year_id = ? AND tenant_id = ?", [$curriculumTemplateId, $groupId, $yearId, $tenantId]);
            if (!$currCheck) {
                Session::flash('error', 'The selected curriculum template does not match the selected Main Group or Academic Year.');
                return $this->redirect('/academics/classes/' . $id);
            }
        }

        $db->update('classes', [
            'name' => $className,
            'section' => $sectionName,
            'academic_year_id' => $yearId,
            'main_group_id' => $groupId,
            'curriculum_template_id' => $curriculumTemplateId,
            'class_teacher_id' => $teacherId ?: null
        ], 'id = ? AND tenant_id = ?', [(int)$id, $tenantId]);

        Session::flash('success', "Class '{$className}' updated successfully.");
        return $this->redirect('/academics/classes/' . $id);
    }

    public function destroy(string $id): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        // Unassign students from this class
        $db->query("UPDATE students SET class_id = NULL, class = NULL, section = NULL WHERE class_id = ? AND tenant_id = ?", [(int)$id, $tenantId]);
        $db->query("DELETE FROM classes WHERE id = ? AND tenant_id = ?", [(int)$id, $tenantId]);

        Session::flash('success', 'Class deleted successfully.');
        return $this->redirect('/academics/classes');
    }

    public function enrollStudents(string $id): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();
        
        $class = $db->selectOne("SELECT * FROM classes WHERE id = ? AND tenant_id = ?", [(int)$id, $tenantId]);
        if (!$class) {
            Session::flash('error', 'Class not found.');
            return $this->redirect('/academics/classes');
        }

        $studentIds = $this->request->input('student_ids', []);
        if (!empty($studentIds)) {
            $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
            $db->query(
                "UPDATE students 
                 SET class_id = ?, class = ?, section = ? 
                 WHERE id IN ($placeholders) AND tenant_id = ?",
                array_merge([(int)$id, $class['name'], $class['section']], array_map('intval', $studentIds), [$tenantId])
            );
            Session::flash('success', count($studentIds) . ' students enrolled successfully.');
        } else {
            Session::flash('error', 'No students selected.');
        }

        return $this->redirect('/academics/classes/' . $id);
    }

    public function removeStudent(string $classId, string $studentId): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        $db->query(
            "UPDATE students 
             SET class_id = NULL, class = NULL, section = NULL 
             WHERE id = ? AND class_id = ? AND tenant_id = ?",
            [(int)$studentId, (int)$classId, $tenantId]
        );

        Session::flash('success', 'Student removed from class.');
        return $this->redirect('/academics/classes/' . $classId);
    }
}
