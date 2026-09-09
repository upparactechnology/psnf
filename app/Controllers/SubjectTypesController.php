<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;

class SubjectTypesController extends Controller
{
    private function db()
    {
        return Application::$app->db;
    }

    public function store(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        $name = trim($this->request->input('name', ''));
        $color = trim($this->request->input('color', '#6366F1'));

        if (empty($name)) {
            \Core\Session::flash('error', 'Type name is required.');
            return $this->redirect(url('academics/subjects'));
        }

        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
        $slug = trim($slug, '-');

        // Get max sort_order
        $max = $db->selectOne("SELECT MAX(sort_order) as max_sort FROM subject_types WHERE tenant_id = ?", [$tenantId]);
        $sortOrder = ($max['max_sort'] ?? 0) + 1;

        try {
            $db->insert('subject_types', [
                'tenant_id'  => $tenantId,
                'name'       => $name,
                'slug'       => $slug,
                'color'      => $color,
                'sort_order' => $sortOrder,
                'is_active'  => 1,
            ]);
            \Core\Session::flash('success', "Subject type '{$name}' created.");
        } catch (\Throwable $e) {
            \Core\Session::flash('error', 'A type with this name already exists.');
        }

        return $this->redirect(url('academics/subjects'));
    }

    public function update(string $id): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        $name = trim($this->request->input('name', ''));
        $color = trim($this->request->input('color', '#6366F1'));

        if (empty($name)) {
            \Core\Session::flash('error', 'Type name is required.');
            return $this->redirect(url('academics/subjects'));
        }

        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
        $slug = trim($slug, '-');

        try {
            $db->update('subject_types', [
                'name'  => $name,
                'slug'  => $slug,
                'color' => $color,
            ], 'id = ? AND tenant_id = ?', [(int)$id, $tenantId]);
            \Core\Session::flash('success', "Subject type updated.");
        } catch (\Throwable $e) {
            \Core\Session::flash('error', 'Error updating type.');
        }

        return $this->redirect(url('academics/subjects'));
    }

    public function destroy(string $id): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        // Check if any subjects use this type
        $type = $db->selectOne("SELECT slug FROM subject_types WHERE id = ? AND tenant_id = ?", [(int)$id, $tenantId]);
        if ($type) {
            $count = $db->selectOne(
                "SELECT COUNT(*) as cnt FROM subjects WHERE tenant_id = ? AND category = ?",
                [$tenantId, $type['name']]
            );
            if (($count['cnt'] ?? 0) > 0) {
                \Core\Session::flash('error', "Cannot delete: {$count['cnt']} subject(s) use this type.");
                return $this->redirect(url('academics/subjects'));
            }
        }

        $db->query("DELETE FROM subject_types WHERE id = ? AND tenant_id = ?", [(int)$id, $tenantId]);
        \Core\Session::flash('success', "Subject type deleted.");

        return $this->redirect(url('academics/subjects'));
    }
}
