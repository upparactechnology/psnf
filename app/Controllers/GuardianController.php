<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Models\Guardian;
use Core\Session;

class GuardianController extends Controller
{
    public function index(): string
    {
        $db = \Core\Application::$app->db;
        $tenantId = Database::getTenantId();
        
        // Fetch all students (with at least one guardian, or just all active students)
        $students = $db->select("
            SELECT id, first_name, last_name, admission_number, photo, class, section
            FROM students
            WHERE tenant_id = ? AND deleted_at IS NULL
            ORDER BY first_name ASC
        ", [$tenantId]);

        // Fetch all guardians and their links
        $guardians = $db->select("
            SELECT g.*, gs.student_id
            FROM guardians g
            JOIN guardian_student gs ON gs.guardian_id = g.id
            WHERE g.tenant_id = ? AND g.deleted_at IS NULL
        ", [$tenantId]);

        // Fetch unlinked guardians
        $unlinkedGuardians = $db->select("
            SELECT g.* 
            FROM guardians g
            LEFT JOIN guardian_student gs ON gs.guardian_id = g.id
            WHERE g.tenant_id = ? AND g.deleted_at IS NULL AND gs.student_id IS NULL
        ", [$tenantId]);

        // Group guardians by student_id
        $studentGuardians = [];
        foreach ($guardians as $g) {
            $studentGuardians[$g['student_id']][] = $g;
        }

        return $this->view('academics/guardians/index', [
            'title' => 'Parents Directory',
            'students' => $students,
            'studentGuardians' => $studentGuardians,
            'unlinkedGuardians' => $unlinkedGuardians
        ]);
    }

    public function create(): string
    {
        $db = \Core\Application::$app->db;
        $students = $db->select("SELECT id, first_name, last_name, admission_number FROM students WHERE tenant_id = ? AND deleted_at IS NULL ORDER BY first_name ASC", [Database::getTenantId()]);

        return $this->view('academics/guardians/create', [
            'title' => 'Add Parent / Guardian',
            'students' => $students
        ]);
    }

    public function store(): string
    {
        $data = $this->request->getBody();
        $rules = [
            'name'  => 'required|min:2',
            'phone' => 'required',
            'email' => 'nullable|email'
        ];

        $validator = new \Core\Validator($data, $rules);
        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::flash('old', $data);
            return $this->redirect(url('academics/parents/create'));
        }

        $studentIds = $data['students'] ?? [];
        unset($data['students'], $data['_csrf']);

        $data['tenant_id'] = Database::getTenantId();
        $data['created_by'] = auth_id();

        $guardianId = (int) Guardian::create($data);

        // Auto-create Parent User for Portal Login
        $db = \Core\Application::$app->db;
        $userEmail = !empty($data['email']) ? $data['email'] : 'parent_' . $data['phone'] . '@psnf.edu';
        $existingUser = $db->selectOne("SELECT id FROM users WHERE email = ? OR phone = ? LIMIT 1", [$userEmail, $data['phone']]);
        
        $userId = null;
        if ($existingUser) {
            $userId = (int)$existingUser['id'];
        } else {
            $userData = [
                'uuid' => str_uuid(),
                'tenant_id' => Database::getTenantId(),
                'name' => $data['name'],
                'email' => $userEmail,
                'phone' => $data['phone'],
                'password' => password_hash($data['phone'], PASSWORD_BCRYPT, ['cost' => 12]),
                'first_login' => 1,
                'created_by' => auth_id()
            ];
            $userId = (int) \App\Models\User::create($userData);
            
            $parentRole = $db->selectOne("SELECT id FROM roles WHERE slug = 'parent' LIMIT 1");
            if ($parentRole) {
                \App\Models\User::syncRoles($userId, [(int)$parentRole['id']]);
            }
        }
        Guardian::update($guardianId, ['user_id' => $userId]);

        // Link students
        if (!empty($studentIds)) {
            $db = \Core\Application::$app->db;
            foreach ($studentIds as $sId) {
                $db->insert('guardian_student', [
                    'guardian_id' => $guardianId,
                    'student_id'  => (int)$sId,
                    'is_primary'  => 1,
                    'can_pickup'  => 1
                ]);
            }
        }

        Session::flash('success', 'Parent added and linked successfully.');
        return $this->redirect(url('academics/parents'));
    }

    public function edit(string $id): string
    {
        $guardian = Guardian::find((int)$id);
        if (!$guardian || $guardian['tenant_id'] !== Database::getTenantId()) {
            Session::flash('error', 'Guardian not found.');
            return $this->redirect(url('academics/parents'));
        }

        $db = \Core\Application::$app->db;
        $students = $db->select("SELECT id, first_name, last_name, admission_number FROM students WHERE tenant_id = ? AND deleted_at IS NULL ORDER BY first_name ASC", [Database::getTenantId()]);
        
        $linked = $db->select("SELECT student_id FROM guardian_student WHERE guardian_id = ?", [(int)$id]);
        $linkedStudentIds = array_column($linked, 'student_id');

        return $this->view('academics/guardians/edit', [
            'title' => 'Edit Parent',
            'guardian' => $guardian,
            'students' => $students,
            'linkedStudentIds' => $linkedStudentIds
        ]);
    }

    public function update(string $id): string
    {
        $guardian = Guardian::find((int)$id);
        if (!$guardian || $guardian['tenant_id'] !== Database::getTenantId()) {
            Session::flash('error', 'Guardian not found.');
            return $this->redirect(url('academics/parents'));
        }

        $data = $this->request->getBody();
        $rules = [
            'name'  => 'required|min:2',
            'phone' => 'required',
            'email' => 'nullable|email'
        ];

        $validator = new \Core\Validator($data, $rules);
        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            return $this->redirect(url("academics/parents/{$id}/edit"));
        }

        $studentIds = $data['students'] ?? [];
        unset($data['students'], $data['_csrf'], $data['_method']);

        $data['updated_by'] = auth_id();

        Guardian::update((int)$id, $data);

        // Auto-create Parent User for Portal Login
        $db = \Core\Application::$app->db;
        $userEmail = !empty($data['email']) ? $data['email'] : 'parent_' . $data['phone'] . '@psnf.edu';
        $existingUser = $db->selectOne("SELECT id FROM users WHERE email = ? OR phone = ? LIMIT 1", [$userEmail, $data['phone']]);
        
        $userId = null;
        if ($existingUser) {
            $userId = (int)$existingUser['id'];
        } else {
            $userData = [
                'uuid' => str_uuid(),
                'tenant_id' => Database::getTenantId(),
                'name' => $data['name'],
                'email' => $userEmail,
                'phone' => $data['phone'],
                'password' => password_hash($data['phone'], PASSWORD_BCRYPT, ['cost' => 12]),
                'first_login' => 1,
                'created_by' => auth_id()
            ];
            $userId = (int) \App\Models\User::create($userData);
            
            $parentRole = $db->selectOne("SELECT id FROM roles WHERE slug = 'parent' LIMIT 1");
            if ($parentRole) {
                \App\Models\User::syncRoles($userId, [(int)$parentRole['id']]);
            }
        }
        Guardian::update((int)$id, ['user_id' => $userId]);

        // Update linked students
        $db = \Core\Application::$app->db;
        $db->query("DELETE FROM guardian_student WHERE guardian_id = ?", [(int)$id]);
        if (!empty($studentIds)) {
            foreach ($studentIds as $sId) {
                $db->insert('guardian_student', [
                    'guardian_id' => (int)$id,
                    'student_id'  => (int)$sId,
                    'is_primary'  => 1,
                    'can_pickup'  => 1
                ]);
            }
        }

        Session::flash('success', 'Parent updated and linked successfully.');
        return $this->redirect(url('academics/parents'));
    }

    public function destroy(string $id): string
    {
        $guardian = Guardian::find((int)$id);
        if ($guardian && $guardian['tenant_id'] === Database::getTenantId()) {
            Guardian::delete((int)$id);
            Session::flash('success', 'Parent deleted.');
        }
        return $this->redirect(url('academics/parents'));
    }
}
