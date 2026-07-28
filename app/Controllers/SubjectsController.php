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

    private function checkAndInitializeSubjectsTable(): void
    {
        $db = $this->db();
        $db->query("
            CREATE TABLE IF NOT EXISTS `subjects` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id` INT UNSIGNED NOT NULL,
                `code` VARCHAR(20) NOT NULL,
                `name` VARCHAR(100) NOT NULL,
                `type` VARCHAR(50) DEFAULT 'Academic',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `uq_subject_code` (`tenant_id`, `code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Seed default subjects if empty
        $count = $db->selectOne("SELECT COUNT(*) as c FROM subjects")['c'] ?? 0;
        if ($count == 0) {
            $tenantId = \Core\Database::getTenantId() ?: 1;
            $defaults = [
                ['code' => 'SUB-101', 'name' => 'Speech Therapy & Communication', 'type' => 'Therapy'],
                ['code' => 'SUB-102', 'name' => 'Sensory Integration & Occupational Skills', 'type' => 'Therapy'],
                ['code' => 'SUB-103', 'name' => 'Visual Arts & Creative Expression', 'type' => 'Skill'],
                ['code' => 'SUB-104', 'name' => 'Functional Numeracy & Cognitive Math', 'type' => 'Academic'],
                ['code' => 'SUB-105', 'name' => 'Physical Education & Motor Development', 'type' => 'Activity'],
            ];
            foreach ($defaults as $sub) {
                try {
                    $db->insert('subjects', array_merge(['tenant_id' => $tenantId], $sub));
                } catch (\Throwable $e) {}
            }
        }
    }

    public function index(): string
    {
        $this->checkAndInitializeSubjectsTable();
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        $subjects = $db->select("SELECT * FROM subjects WHERE tenant_id = ? ORDER BY name ASC", [$tenantId]);

        return $this->view('subjects/index', compact('subjects'));
    }

    public function store(): string
    {
        $this->checkAndInitializeSubjectsTable();
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        $name = trim($this->request->input('name', ''));
        $code = trim($this->request->input('code', ''));
        $type = trim($this->request->input('type', 'Academic'));

        if (empty($name) || empty($code)) {
            \Core\Session::flash('error', 'Subject Name and Code are required.');
            return $this->redirect('/academics/subjects');
        }

        try {
            $db->insert('subjects', [
                'tenant_id' => $tenantId,
                'name' => $name,
                'code' => strtoupper($code),
                'type' => $type
            ]);
            \Core\Session::flash('success', "Subject '{$name}' created successfully.");
        } catch (\Throwable $e) {
            \Core\Session::flash('error', 'Subject with this code already exists.');
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

        try {
            $db->query("
                UPDATE subjects 
                SET name = ?, code = ?, type = ?
                WHERE id = ? AND tenant_id = ?
            ", [$name, strtoupper($code), $type, (int)$id, $tenantId]);
            \Core\Session::flash('success', "Subject '{$name}' updated successfully.");
        } catch (\Throwable $e) {
            \Core\Session::flash('error', 'Error updating subject.');
        }

        return $this->redirect('/academics/subjects');
    }
}
