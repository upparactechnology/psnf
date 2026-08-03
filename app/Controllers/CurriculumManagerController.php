<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;
use Core\Session;

class CurriculumManagerController extends Controller
{
    private function db()
    {
        return Application::$app->db;
    }

    public function index(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        // Get years and main groups for selection
        $years = $db->select("SELECT * FROM academic_years WHERE tenant_id = ? ORDER BY year_name DESC", [$tenantId]);
        $mainGroups = $db->select("SELECT * FROM main_groups WHERE tenant_id = ? AND is_active = 1", [$tenantId]);

        // Selected filter state
        $selectedYearId = $this->request->input('academic_year_id');
        $selectedGroupId = $this->request->input('main_group_id');

        if (!$selectedYearId && !empty($years)) {
            // Find current active year
            foreach ($years as $y) {
                if ($y['status'] === 'current') {
                    $selectedYearId = $y['id'];
                    break;
                }
            }
            if (!$selectedYearId) {
                $selectedYearId = $years[0]['id'];
            }
        }

        if (!$selectedGroupId && !empty($mainGroups)) {
            $selectedGroupId = $mainGroups[0]['id'];
        }

        // Get all Curriculum Templates for selected Year & Main Group
        $templates = [];
        $template = null;
        $sections = [];
        $subjectsMaster = $db->select("SELECT * FROM subjects WHERE tenant_id = ? ORDER BY name ASC", [$tenantId]);

        if ($selectedYearId && $selectedGroupId) {
            $templates = $db->select("
                SELECT id, name FROM curriculum_templates 
                WHERE academic_year_id = ? AND main_group_id = ? AND tenant_id = ?
                ORDER BY name ASC
            ", [(int)$selectedYearId, (int)$selectedGroupId, $tenantId]);

            $selectedTemplateId = $this->request->input('template_id');
            if ($selectedTemplateId) {
                $template = $db->selectOne("
                    SELECT * FROM curriculum_templates 
                    WHERE id = ? AND tenant_id = ?
                ", [(int)$selectedTemplateId, $tenantId]);
            }
            if (!$template && !empty($templates)) {
                $template = $db->selectOne("
                    SELECT * FROM curriculum_templates 
                    WHERE id = ? AND tenant_id = ?
                ", [(int)$templates[0]['id'], $tenantId]);
            }

            if ($template) {
                $sectionsList = $db->select("
                    SELECT * FROM curriculum_sections 
                    WHERE curriculum_template_id = ? 
                    ORDER BY sort_order ASC
                ", [$template['id']]);

                foreach ($sectionsList as $sec) {
                    $subjects = $db->select("
                        SELECT cs.*, s.name as subject_name, s.code as subject_code, s.category as subject_category
                        FROM curriculum_subjects cs
                        JOIN subjects s ON cs.subject_id = s.id
                        WHERE cs.curriculum_section_id = ?
                        ORDER BY cs.sequence ASC
                    ", [$sec['id']]);

                    $sections[] = [
                        'id' => $sec['id'],
                        'section_name' => $sec['section_name'],
                        'sort_order' => $sec['sort_order'],
                        'subjects' => $subjects
                    ];
                }
            }
        }

        return $this->view('academics/curriculum_manager', compact(
            'years', 'mainGroups', 'selectedYearId', 'selectedGroupId',
            'templates', 'template', 'sections', 'subjectsMaster'
        ));
    }

    public function storeTemplate(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        $yearId = (int)$this->request->input('academic_year_id');
        $groupId = (int)$this->request->input('main_group_id');
        $name = trim($this->request->input('name', ''));
        $description = trim($this->request->input('description', ''));

        if (empty($name)) {
            Session::flash('error', 'Curriculum Name is required.');
            return $this->redirect("/academics/curriculum?academic_year_id={$yearId}&main_group_id={$groupId}");
        }

        $templateId = $db->insert('curriculum_templates', [
            'tenant_id' => $tenantId,
            'academic_year_id' => $yearId,
            'main_group_id' => $groupId,
            'name' => $name,
            'version' => 1,
            'description' => $description,
            'is_active' => 1
        ]);

        // Auto-create default sections: Academic, Life Skills, Co-Curricular, Vocational
        $defaultSections = ['Academic', 'Life Skills', 'Co-Curricular', 'Vocational'];
        foreach ($defaultSections as $index => $secName) {
            $db->insert('curriculum_sections', [
                'curriculum_template_id' => $templateId,
                'section_name' => $secName,
                'sort_order' => $index + 1
            ]);
        }

        Session::flash('success', 'Curriculum Template and default sections created successfully.');
        return $this->redirect("/academics/curriculum?academic_year_id={$yearId}&main_group_id={$groupId}&template_id={$templateId}");
    }

    public function storeSection(string $templateId): string
    {
        $db = $this->db();
        $sectionName = trim($this->request->input('section_name', ''));
        $yearId = $this->request->input('academic_year_id');
        $groupId = $this->request->input('main_group_id');

        if (empty($sectionName)) {
            Session::flash('error', 'Section Name is required.');
            return $this->redirect("/academics/curriculum?academic_year_id={$yearId}&main_group_id={$groupId}&template_id={$templateId}");
        }

        $maxOrder = $db->selectOne("SELECT MAX(sort_order) as max_ord FROM curriculum_sections WHERE curriculum_template_id = ?", [(int)$templateId]);
        $nextOrder = ($maxOrder['max_ord'] ?? 0) + 1;

        $db->insert('curriculum_sections', [
            'curriculum_template_id' => (int)$templateId,
            'section_name' => $sectionName,
            'sort_order' => $nextOrder
        ]);

        Session::flash('success', 'Curriculum Section added.');
        return $this->redirect("/academics/curriculum?academic_year_id={$yearId}&main_group_id={$groupId}&template_id={$templateId}");
    }

    public function storeCurriculumSubject(): string
    {
        $db = $this->db();
        $sectionId = (int)$this->request->input('section_id');
        $subjectId = (int)$this->request->input('subject_id');
        $assessmentType = $this->request->input('assessment_type', 'Marks');
        $isRequired = (int)$this->request->input('is_required', 1);
        $yearId = $this->request->input('academic_year_id');
        $groupId = $this->request->input('main_group_id');

        $sec = $db->selectOne("SELECT curriculum_template_id FROM curriculum_sections WHERE id = ?", [$sectionId]);
        $templateId = $sec['curriculum_template_id'] ?? '';

        // Check if subject is already in the section
        $existing = $db->selectOne("
            SELECT id FROM curriculum_subjects 
            WHERE curriculum_section_id = ? AND subject_id = ?
        ", [(int)$sectionId, $subjectId]);

        if ($existing) {
            Session::flash('error', 'Subject is already added to this curriculum section.');
            return $this->redirect("/academics/curriculum?academic_year_id={$yearId}&main_group_id={$groupId}&template_id={$templateId}");
        }

        $maxSeq = $db->selectOne("SELECT MAX(sequence) as max_seq FROM curriculum_subjects WHERE curriculum_section_id = ?", [(int)$sectionId]);
        $nextSeq = ($maxSeq['max_seq'] ?? 0) + 1;

        $db->insert('curriculum_subjects', [
            'curriculum_section_id' => (int)$sectionId,
            'subject_id' => $subjectId,
            'is_required' => $isRequired,
            'default_grade' => null,
            'visible' => 1,
            'sequence' => $nextSeq,
            'assessment_type' => $assessmentType
        ]);

        Session::flash('success', 'Subject added to Curriculum Section.');
        return $this->redirect("/academics/curriculum?academic_year_id={$yearId}&main_group_id={$groupId}&template_id={$templateId}");
    }

    public function destroyCurriculumSubject(string $id): string
    {
        $db = $this->db();
        $yearId = $this->request->input('academic_year_id');
        $groupId = $this->request->input('main_group_id');

        $cs = $db->selectOne("
            SELECT sec.curriculum_template_id 
            FROM curriculum_subjects cs
            JOIN curriculum_sections sec ON cs.curriculum_section_id = sec.id
            WHERE cs.id = ?
        ", [(int)$id]);
        $templateId = $cs['curriculum_template_id'] ?? '';

        $db->query("DELETE FROM curriculum_subjects WHERE id = ?", [(int)$id]);

        Session::flash('success', 'Subject removed from Curriculum Template.');
        return $this->redirect("/academics/curriculum?academic_year_id={$yearId}&main_group_id={$groupId}&template_id={$templateId}");
    }

    public function reorderSubjects(): string
    {
        $db = $this->db();
        $orders = $this->request->input('sequence', []);
        $yearId = $this->request->input('academic_year_id');
        $groupId = $this->request->input('main_group_id');

        $templateId = '';
        if (!empty($orders)) {
            $firstId = key($orders);
            $cs = $db->selectOne("
                SELECT sec.curriculum_template_id 
                FROM curriculum_subjects cs
                JOIN curriculum_sections sec ON cs.curriculum_section_id = sec.id
                WHERE cs.id = ?
            ", [(int)$firstId]);
            $templateId = $cs['curriculum_template_id'] ?? '';
        }

        foreach ($orders as $id => $seq) {
            $db->update('curriculum_subjects', [
                'sequence' => (int)$seq
            ], 'id = ?', [(int)$id]);
        }

        Session::flash('success', 'Curriculum sequence updated.');
        return $this->redirect("/academics/curriculum?academic_year_id={$yearId}&main_group_id={$groupId}&template_id={$templateId}");
    }
}
