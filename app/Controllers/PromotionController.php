<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;
use Core\Session;

class PromotionController extends Controller
{
    private function db()
    {
        return Application::$app->db;
    }

    public function index(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        $years = $db->select("SELECT * FROM academic_years WHERE tenant_id = ? ORDER BY year_name DESC", [$tenantId]);
        $groups = $db->select("SELECT * FROM main_groups WHERE tenant_id = ? AND is_active = 1", [$tenantId]);
        $classes = $db->select("SELECT * FROM classes WHERE tenant_id = ?", [$tenantId]);

        // Filters
        $srcYearId = (int)$this->request->input('src_year_id');
        $srcGroupId = (int)$this->request->input('src_group_id');
        $srcClassId = (int)$this->request->input('src_class_id');

        $students = [];
        if ($srcClassId) {
            $students = $db->select("
                SELECT * FROM students 
                WHERE class_id = ? AND tenant_id = ? AND deleted_at IS NULL
                ORDER BY first_name ASC
            ", [$srcClassId, $tenantId]);
        }

        return $this->view('academics/promotion_wizard', compact(
            'years', 'groups', 'classes', 'srcYearId', 'srcGroupId', 'srcClassId', 'students'
        ));
    }

    public function promoteStudents(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        $srcClassId = (int)$this->request->input('src_class_id');
        $destYearId = (int)$this->request->input('dest_year_id');
        $destGroupId = (int)$this->request->input('dest_group_id');
        $destClassId = (int)$this->request->input('dest_class_id');

        $studentActions = $this->request->input('actions', []); // [student_id => 'promote' | 'stay']

        if (!$destClassId || !$destYearId || !$destGroupId) {
            Session::flash('error', 'Destination Year, Main Group, and Class are required.');
            return $this->redirect("/academics/promotion?src_class_id={$srcClassId}");
        }

        $destClassRow = $db->selectOne("SELECT * FROM classes WHERE id = ?", [$destClassId]);
        $destYearRow = $db->selectOne("SELECT * FROM academic_years WHERE id = ?", [$destYearId]);
        $destGroupRow = $db->selectOne("SELECT * FROM main_groups WHERE id = ?", [$destGroupId]);

        if (!$destClassRow || !$destYearRow || !$destGroupRow) {
            Session::flash('error', 'Invalid destination parameters selected.');
            return $this->redirect("/academics/promotion?src_class_id={$srcClassId}");
        }

        $count = 0;
        foreach ($studentActions as $studentId => $action) {
            if ($action === 'promote') {
                $db->update('students', [
                    'class_id' => $destClassId,
                    'main_group_id' => $destGroupId,
                    'class' => $destClassRow['name'],
                    'section' => $destClassRow['section'],
                    'academic_year' => $destYearRow['year_name']
                ], 'id = ? AND tenant_id = ?', [(int)$studentId, $tenantId]);
                $count++;
            }
        }

        if ($count > 0) {
            // Auto generate roll numbers for destination class
            \App\Models\Student::autoGenerateRollNumbers($destClassId);
            
            // Auto generate roll numbers for source class (since students left it)
            if ($srcClassId) {
                \App\Models\Student::autoGenerateRollNumbers($srcClassId);
            }
        }

        Session::flash('success', "Promotion wizard completed! Promoted {$count} students to {$destClassRow['name']} for {$destYearRow['year_name']}.");
        return $this->redirect('/academics/promotion');
    }
}
