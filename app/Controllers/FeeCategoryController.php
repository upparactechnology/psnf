<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;
use Core\Session;

class FeeCategoryController extends Controller
{
    private function db()
    {
        return Application::$app->db;
    }

    public function index(): string
    {
        $categories = $this->db()->select("SELECT * FROM fee_categories ORDER BY display_order ASC");
        return $this->view('fees/categories', compact('categories'));
    }

    public function store(): string
    {
        $data = $this->request->getBody();
        
        $rules = [
            'name' => 'required',
            'code' => 'required'
        ];
        
        $validator = new \Core\Validator($data, $rules);
        if ($validator->fails()) {
            Session::flash('error', 'Name and Code are required.');
            return $this->redirect('/fees/categories');
        }

        try {
            $this->db()->insert('fee_categories', [
                'name' => $data['name'],
                'code' => strtoupper($data['code']),
                'description' => $data['description'] ?? '',
                'tax' => (float)($data['tax'] ?? 0.00),
                'is_refundable' => isset($data['is_refundable']) ? 1 : 0,
                'is_active' => isset($data['is_active']) ? 1 : 0,
                'display_order' => (int)($data['display_order'] ?? 0)
            ]);
            Session::flash('success', 'Fee category created successfully.');
        } catch (\Exception $e) {
            Session::flash('error', 'Could not create category. The code might already exist.');
        }
        
        return $this->redirect('/fees/categories');
    }

    public function update(string $id): string
    {
        $data = $this->request->getBody();
        
        try {
            $this->db()->update('fee_categories', [
                'name' => $data['name'],
                'code' => strtoupper($data['code']),
                'description' => $data['description'] ?? '',
                'tax' => (float)($data['tax'] ?? 0.00),
                'is_refundable' => isset($data['is_refundable']) ? 1 : 0,
                'is_active' => isset($data['is_active']) ? 1 : 0,
                'display_order' => (int)($data['display_order'] ?? 0)
            ], 'id = ?', [(int)$id]);
            Session::flash('success', 'Fee category updated successfully.');
        } catch (\Exception $e) {
            Session::flash('error', 'Could not update category. The code might be in use.');
        }

        return $this->redirect('/fees/categories');
    }

    public function destroy(string $id): string
    {
        $inUse = $this->db()->selectOne("SELECT id FROM fee_structure_items WHERE fee_category_id = ? LIMIT 1", [(int)$id]);
        
        if ($inUse) {
            Session::flash('error', 'Cannot delete this category because it is used in a fee structure.');
            return $this->redirect('/fees/categories');
        }

        $this->db()->query("DELETE FROM fee_categories WHERE id = ?", [(int)$id]);
        Session::flash('success', 'Fee category deleted.');
        
        return $this->redirect('/fees/categories');
    }
}
