<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;
use Core\Session;

class FeeStructureController extends Controller
{
    private function db()
    {
        return Application::$app->db;
    }

    public function index(): string
    {
        $structures = $this->db()->select("
            SELECT fs.*, ay.year_name, mg.name as main_group_name
            FROM fee_structures fs
            LEFT JOIN academic_years ay ON fs.academic_year_id = ay.id
            LEFT JOIN main_groups mg ON fs.main_group_id = mg.id
            ORDER BY fs.id DESC
        ");
        
        $academicYears = $this->db()->select("SELECT * FROM academic_years ORDER BY id DESC");
        $mainGroups = $this->db()->select("SELECT * FROM main_groups ORDER BY name ASC");
        
        return $this->view('fees/structures', compact('structures', 'academicYears', 'mainGroups'));
    }

    public function store(): string
    {
        $data = $this->request->getBody();
        
        $rules = [
            'name' => 'required',
            'academic_year_id' => 'required',
            'main_group_id' => 'required'
        ];
        
        $validator = new \Core\Validator($data, $rules);
        if ($validator->fails()) {
            Session::flash('error', 'Name, Academic Year, and Main Group are required.');
            return $this->redirect('/fees/structures');
        }

        $id = $this->db()->insert('fee_structures', [
            'name' => $data['name'],
            'academic_year_id' => (int)$data['academic_year_id'],
            'main_group_id' => (int)$data['main_group_id'],
            'installment_type' => $data['installment_type'] ?? 'one_time',
            'is_active' => isset($data['is_active']) ? 1 : 0
        ]);
        
        Session::flash('success', 'Fee structure created. You can now add fee items to it.');
        return $this->redirect("/fees/structures/{$id}");
    }

    public function show(string $id): string
    {
        $structure = $this->db()->selectOne("
            SELECT fs.*, ay.year_name, mg.name as main_group_name
            FROM fee_structures fs
            LEFT JOIN academic_years ay ON fs.academic_year_id = ay.id
            LEFT JOIN main_groups mg ON fs.main_group_id = mg.id
            WHERE fs.id = ?
        ", [(int)$id]);
        
        if (!$structure) {
            Session::flash('error', 'Structure not found.');
            return $this->redirect('/fees/structures');
        }
        
        $items = $this->db()->select("
            SELECT fsi.*, fc.name as category_name
            FROM fee_structure_items fsi
            JOIN fee_categories fc ON fsi.fee_category_id = fc.id
            WHERE fsi.fee_structure_id = ?
            ORDER BY fsi.id ASC
        ", [(int)$id]);
        
        $categories = $this->db()->select("SELECT * FROM fee_categories WHERE is_active = 1 ORDER BY display_order ASC");
        $policies = $this->db()->select("SELECT * FROM late_fee_policies ORDER BY name ASC");
        
        return $this->view('fees/structure_items', compact('structure', 'items', 'categories', 'policies'));
    }

    public function addItem(string $id): string
    {
        $data = $this->request->getBody();
        
        $this->db()->insert('fee_structure_items', [
            'fee_structure_id' => (int)$id,
            'fee_category_id' => (int)$data['fee_category_id'],
            'amount' => (float)$data['amount'],
            'due_date' => $data['due_date'] ?: null,
            'late_fee_policy_id' => !empty($data['late_fee_policy_id']) ? (int)$data['late_fee_policy_id'] : null,
            'is_mandatory' => isset($data['is_mandatory']) ? 1 : 0
        ]);
        
        Session::flash('success', 'Item added to structure.');
        return $this->redirect("/fees/structures/{$id}");
    }

    public function destroyItem(string $id, string $itemId): string
    {
        $this->db()->query("DELETE FROM fee_structure_items WHERE id = ? AND fee_structure_id = ?", [(int)$itemId, (int)$id]);
        Session::flash('success', 'Item removed.');
        return $this->redirect("/fees/structures/{$id}");
    }
}
