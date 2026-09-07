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
        $selectedYearId = (int) $this->request->input('academic_year_id');
        $selectedGroupId = (int) $this->request->input('main_group_id');

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
                        'assessment_type' => $sec['assessment_type'] ?? 'Grade',
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

        if (is_year_locked($yearId)) {
            Session::flash('error', 'This academic year is locked. Only administrators can edit it.');
            return $this->redirect("/academics/curriculum?academic_year_id={$yearId}&main_group_id={$groupId}");
        }

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
        $assessmentType = trim($this->request->input('assessment_type', 'Grade'));
        $yearId = (int) $this->request->input('academic_year_id');
        $groupId = (int) $this->request->input('main_group_id');

        if (is_year_locked($yearId)) {
            Session::flash('error', 'This academic year is locked. Only administrators can edit it.');
            return $this->redirect("/academics/curriculum?academic_year_id={$yearId}&main_group_id={$groupId}&template_id={$templateId}");
        }

        if (empty($sectionName)) {
            Session::flash('error', 'Section Name is required.');
            return $this->redirect("/academics/curriculum?academic_year_id={$yearId}&main_group_id={$groupId}&template_id={$templateId}");
        }

        $maxOrder = $db->selectOne("SELECT MAX(sort_order) as max_ord FROM curriculum_sections WHERE curriculum_template_id = ?", [(int)$templateId]);
        $nextOrder = ($maxOrder['max_ord'] ?? 0) + 1;

        $db->insert('curriculum_sections', [
            'curriculum_template_id' => (int)$templateId,
            'section_name' => $sectionName,
            'sort_order' => $nextOrder,
            'assessment_type' => $assessmentType
        ]);

        Session::flash('success', 'Curriculum Section added.');
        return $this->redirect("/academics/curriculum?academic_year_id={$yearId}&main_group_id={$groupId}&template_id={$templateId}");
    }

    public function updateSection(): string
    {
        $db = $this->db();
        $id = (int)$this->request->input('section_id');
        $sectionName = trim($this->request->input('section_name', ''));
        $assessmentType = trim($this->request->input('assessment_type', 'Grade'));
        $yearId = (int) $this->request->input('academic_year_id');
        $groupId = $this->request->input('main_group_id');

        $sec = $db->selectOne("SELECT curriculum_template_id FROM curriculum_sections WHERE id = ?", [$id]);
        $templateId = $sec['curriculum_template_id'] ?? '';

        if (is_year_locked($yearId)) {
            Session::flash('error', 'This academic year is locked. Only administrators can edit it.');
            return $this->redirect("/academics/curriculum?academic_year_id={$yearId}&main_group_id={$groupId}&template_id={$templateId}");
        }

        if (empty($sectionName)) {
            Session::flash('error', 'Section Name is required.');
            return $this->redirect("/academics/curriculum?academic_year_id={$yearId}&main_group_id={$groupId}&template_id={$templateId}");
        }

        try {
            $db->update('curriculum_sections', [
                'section_name' => $sectionName,
                'assessment_type' => $assessmentType
            ], 'id = ?', [(int)$id]);

            Session::flash('success', 'Curriculum Section updated successfully.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Failed to update section: ' . $e->getMessage());
        }

        return $this->redirect("/academics/curriculum?academic_year_id={$yearId}&main_group_id={$groupId}&template_id={$templateId}");
    }

    public function storeCurriculumSubject(): string
    {
        $db = $this->db();
        $sectionId = (int)$this->request->input('section_id');
        $subjectIdsRaw = $this->request->input('subject_ids', '');
        $isRequired = (int)$this->request->input('is_required', 1);
        $yearId = (int) $this->request->input('academic_year_id');
        $groupId = $this->request->input('main_group_id');

        $subjectIds = array_filter(array_map('intval', explode(',', $subjectIdsRaw)));

        if (empty($subjectIds)) {
            Session::flash('error', 'Please select at least one subject.');
            return $this->redirect("/academics/curriculum?academic_year_id={$yearId}&main_group_id={$groupId}");
        }

        $sec = $db->selectOne("SELECT curriculum_template_id, assessment_type FROM curriculum_sections WHERE id = ?", [$sectionId]);
        $templateId = $sec['curriculum_template_id'] ?? '';
        $assessmentType = $sec['assessment_type'] ?? 'Grade';

        if (is_year_locked($yearId)) {
            Session::flash('error', 'This academic year is locked. Only administrators can edit it.');
            return $this->redirect("/academics/curriculum?academic_year_id={$yearId}&main_group_id={$groupId}&template_id={$templateId}");
        }

        $maxSeq = $db->selectOne("SELECT MAX(sequence) as max_seq FROM curriculum_subjects WHERE curriculum_section_id = ?", [(int)$sectionId]);
        $nextSeq = ($maxSeq['max_seq'] ?? 0);

        $added = 0;
        $skipped = 0;

        foreach ($subjectIds as $subjectId) {
            $existing = $db->selectOne("
                SELECT id FROM curriculum_subjects 
                WHERE curriculum_section_id = ? AND subject_id = ?
            ", [(int)$sectionId, $subjectId]);

            if ($existing) {
                $skipped++;
                continue;
            }

            $nextSeq++;
            $db->insert('curriculum_subjects', [
                'curriculum_section_id' => (int)$sectionId,
                'subject_id' => $subjectId,
                'is_required' => $isRequired,
                'default_grade' => null,
                'visible' => 1,
                'sequence' => $nextSeq,
                'assessment_type' => $assessmentType
            ]);
            $added++;
        }

        $msg = "{$added} subject(s) added to Curriculum Section.";
        if ($skipped > 0) {
            $msg .= " {$skipped} already existed and were skipped.";
        }
        Session::flash('success', $msg);
        return $this->redirect("/academics/curriculum?academic_year_id={$yearId}&main_group_id={$groupId}&template_id={$templateId}");
    }

    public function destroyCurriculumSubject(string $id): string
    {
        $db = $this->db();
        $yearId = (int) $this->request->input('academic_year_id');
        $groupId = $this->request->input('main_group_id');

        if (is_year_locked($yearId)) {
            Session::flash('error', 'This academic year is locked. Only administrators can edit it.');
            return $this->redirect("/academics/curriculum?academic_year_id={$yearId}&main_group_id={$groupId}");
        }

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
        $yearId = (int) $this->request->input('academic_year_id');
        $groupId = $this->request->input('main_group_id');

        if (is_year_locked($yearId)) {
            Session::flash('error', 'This academic year is locked. Only administrators can edit it.');
            return $this->redirect("/academics/curriculum?academic_year_id={$yearId}&main_group_id={$groupId}");
        }

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

    public function destroyTemplate(string $id): string
    {
        $db = $this->db();
        $template = $db->selectOne("SELECT id, academic_year_id, main_group_id FROM curriculum_templates WHERE id = ?", [(int)$id]);

        if (!$template) {
            Session::flash('error', 'Template not found.');
            return $this->redirect("/academics/curriculum");
        }

        $yearId = (int)$template['academic_year_id'];
        $groupId = $template['main_group_id'];

        if (is_year_locked($yearId)) {
            Session::flash('error', 'This academic year is locked. Only administrators can edit it.');
            return $this->redirect("/academics/curriculum?academic_year_id={$yearId}&main_group_id={$groupId}");
        }

        $sectionIds = array_column(
            $db->select("SELECT id FROM curriculum_sections WHERE curriculum_template_id = ?", [(int)$id]),
            'id'
        );

        if (!empty($sectionIds)) {
            $placeholders = implode(',', array_fill(0, count($sectionIds), '?'));
            $db->query("DELETE FROM curriculum_subjects WHERE curriculum_section_id IN ({$placeholders})", $sectionIds);
            $db->query("DELETE FROM curriculum_sections WHERE curriculum_template_id = ?", [(int)$id]);
        }

        $db->query("DELETE FROM curriculum_templates WHERE id = ?", [(int)$id]);

        Session::flash('success', 'Curriculum template deleted.');
        return $this->redirect("/academics/curriculum?academic_year_id={$yearId}&main_group_id={$groupId}");
    }
}
