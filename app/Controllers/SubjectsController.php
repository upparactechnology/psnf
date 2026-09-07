<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;

class SubjectsController extends Controller
{
    private function db()
    {
        return Application::$app->db;
    }

    public function index(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        $subjects = $db->select("SELECT * FROM subjects WHERE tenant_id = ? ORDER BY name ASC", [$tenantId]);
        $subjectTypes = $db->select("SELECT * FROM subject_types WHERE tenant_id = ? AND is_active = 1 ORDER BY sort_order ASC", [$tenantId]);

        // Auto-generate next subject code
        $lastSubject = $db->selectOne("SELECT code FROM subjects WHERE tenant_id = ? ORDER BY id DESC LIMIT 1", [$tenantId]);
        $nextCode = 'SUB-101';
        if ($lastSubject && preg_match('/^SUB-(\d+)$/', $lastSubject['code'], $m)) {
            $nextCode = 'SUB-' . str_pad((string)($m[1] + 1), 3, '0', STR_PAD_LEFT);
        }

        return $this->view('subjects/index', compact('subjects', 'nextCode', 'subjectTypes'));
    }

    public function store(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        $name = trim($this->request->input('name', ''));
        $code = trim($this->request->input('code', ''));
        $type = trim($this->request->input('type', 'Academic'));
        $addNext = !empty($_POST['_add_next']);

        if (empty($name) || empty($code)) {
            \Core\Session::flash('error', 'Subject Name and Code are required.');
            return $this->redirect('/academics/subjects');
        }

        // Normalize type to a slug for the `type` column
        $dbType = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $type));
        $dbType = trim($dbType, '-');

        try {
            $db->insert('subjects', [
                'tenant_id' => $tenantId,
                'school_id' => 1,
                'code'      => strtoupper($code),
                'name'      => $name,
                'type'      => $dbType,
                'category'  => $type,
            ]);
            \Core\Session::flash('success', "Subject '{$name}' created successfully.");
        } catch (\Throwable $e) {
            \Core\Session::flash('error', 'Subject with this code already exists.');
        }

        if ($addNext) {
            return $this->redirect('/academics/subjects?add_next=1');
        }
        return $this->redirect('/academics/subjects');
    }

    public function destroy(string $id): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        $db->query("DELETE FROM subjects WHERE id = ? AND tenant_id = ?", [(int)$id, $tenantId]);
        \Core\Session::flash('success', "Subject deleted successfully.");

        return $this->redirect('/academics/subjects');
    }

    public function update(string $id): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        $name = trim($this->request->input('name', ''));
        $code = trim($this->request->input('code', ''));
        $type = trim($this->request->input('type', 'Academic'));

        if (empty($name) || empty($code)) {
            \Core\Session::flash('error', 'Subject Name and Code are required.');
            return $this->redirect('/academics/subjects');
        }

        // Normalize type to a slug for the `type` column
        $dbType = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $type));
        $dbType = trim($dbType, '-');

        try {
            $db->query("
                UPDATE subjects 
                SET name = ?, code = ?, type = ?, category = ?
                WHERE id = ? AND tenant_id = ?
            ", [$name, strtoupper($code), $dbType, $type, (int)$id, $tenantId]);
            \Core\Session::flash('success', "Subject '{$name}' updated successfully.");
        } catch (\Throwable $e) {
            \Core\Session::flash('error', 'Error updating subject.');
        }

        return $this->redirect('/academics/subjects');
    }
}
