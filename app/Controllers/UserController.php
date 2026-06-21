<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use App\Models\{User, Role};

class UserController extends Controller
{
    protected array $availableApps = [
        'academic'      => 'Academic Registry',
        'academic_summary' => 'Academic Summary',
        'hr'            => 'HR Directory',
        'access_control'=> 'Access Control',
        'finance'       => 'Finance & Fees',
        'medical'       => 'Medical Logs',
        'transport'     => 'Transport & Bus',
        'file_manager'  => 'File Manager',
        'games'         => 'Learning Games',
        'config'        => 'System Config',
    ];

    public function index(): string
    {
        $search  = $this->request->get('search', '');
        $roleId  = $this->request->get('role_id', '');
        $page    = (int) $this->request->get('page', 1);

        $conditions = [];
        $params = [];

        if ($search !== '') {
            $conditions[] = "(name LIKE ? OR email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($roleId !== '') {
            $conditions[] = "id IN (SELECT user_id FROM user_roles WHERE role_id = ?)";
            $params[] = (int) $roleId;
        }

        $conditionStr = implode(' AND ', $conditions);

        $result  = User::paginate($page, 15, $conditionStr, $params);
        $roles   = Role::allWithPermissionCount();

        return $this->view('users/index', array_merge($result, [
            'search' => $search,
            'role_id' => $roleId,
            'roles' => $roles
        ]));
    }

    public function create(): string
    {
        $roles    = Role::all('sort_order');
        $schools  = \Core\Application::$app->db->select("SELECT id, name FROM schools WHERE tenant_id = ? AND is_active = 1 AND deleted_at IS NULL", [\Core\Database::getTenantId()]);
        $branches = \Core\Application::$app->db->select("SELECT id, name FROM branches WHERE tenant_id = ? AND is_active = 1 AND deleted_at IS NULL", [\Core\Database::getTenantId()]);
        $apps     = $this->availableApps;
        return $this->view('users/create', compact('roles', 'schools', 'branches', 'apps'));
    }

    public function store(): string
    {
        $data  = $this->request->getBody();
        $roles = $data['roles'] ?? [];
        $assignedApps = $data['apps'] ?? [];
        unset($data['roles'], $data['apps'], $data['_csrf']);

        $rules = [
            'name'      => 'required|min:2',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|min:8|confirmed',
            'school_id' => 'required',
            'branch_id' => 'required',
        ];

        $validator = new \Core\Validator($data, $rules);
        if ($validator->fails()) {
            \Core\Session::flash('errors', $validator->errors());
            \Core\Session::flash('old', array_merge($data, ['apps' => $assignedApps, 'roles' => $roles]));
            return $this->redirect('/users/create');
        }

        $lectureTime = !empty($data['lecture_time']) ? $data['lecture_time'] : null;
        $gracePeriod = isset($data['grace_period']) && $data['grace_period'] !== '' ? (int) $data['grace_period'] : 5;

        $data['uuid']         = str_uuid();
        $data['tenant_id']    = \Core\Database::getTenantId();
        $data['password']     = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        $data['created_by']   = auth_id();
        $data['lecture_time'] = $lectureTime;
        $data['grace_period'] = $gracePeriod;
        unset($data['password_confirmation']);

        $userId = (int) User::create($data);

        if ($roles) {
            User::syncRoles($userId, array_map('intval', $roles));
        }

        $db = \Core\Application::$app->db;
        foreach ($assignedApps as $app) {
            $db->insert('user_apps', ['user_id' => $userId, 'app_name' => $app]);
        }

        \App\Models\ActivityLog::log('user_created', auth_id(), ['user_id' => $userId]);
        $this->flash('success', 'User created successfully.');
        return $this->redirect('/users');
    }

    public function edit(string $id): string
    {
        $user     = User::withRoles((int) $id);
        $roles    = Role::all('sort_order');
        $schools  = \Core\Application::$app->db->select("SELECT id, name FROM schools WHERE tenant_id = ? AND deleted_at IS NULL", [\Core\Database::getTenantId()]);
        $branches = \Core\Application::$app->db->select("SELECT id, name FROM branches WHERE tenant_id = ? AND deleted_at IS NULL", [\Core\Database::getTenantId()]);
        $apps     = $this->availableApps;

        $db = \Core\Application::$app->db;
        $userApps = $db->select("SELECT app_name FROM user_apps WHERE user_id = ?", [$user['id']]);
        $user['assigned_apps'] = array_column($userApps, 'app_name');

        return $this->view('users/edit', compact('user', 'roles', 'schools', 'branches', 'apps'));
    }

    public function update(string $id): string
    {
        $data  = $this->request->getBody();
        $roles = $data['roles'] ?? [];
        $assignedApps = $data['apps'] ?? [];
        unset($data['roles'], $data['apps'], $data['_csrf'], $data['_method'], $data['password'], $data['password_confirmation']);

        $lectureTime = !empty($data['lecture_time']) ? $data['lecture_time'] : null;
        $gracePeriod = isset($data['grace_period']) && $data['grace_period'] !== '' ? (int) $data['grace_period'] : 5;

        $data['lecture_time'] = $lectureTime;
        $data['grace_period'] = $gracePeriod;
        $data['updated_by']   = auth_id();

        User::update((int) $id, $data);
        User::syncRoles((int) $id, array_map('intval', $roles));

        $db = \Core\Application::$app->db;
        $db->query("DELETE FROM user_apps WHERE user_id = ?", [$id]);
        foreach ($assignedApps as $app) {
            $db->insert('user_apps', ['user_id' => (int) $id, 'app_name' => $app]);
        }

        \App\Models\ActivityLog::log('user_updated', auth_id(), ['user_id' => $id]);
        $this->flash('success', 'User updated.');
        return $this->redirect('/users');
    }

    public function destroy(string $id): string
    {
        User::delete((int) $id);
        \App\Models\ActivityLog::log('user_deleted', auth_id(), ['user_id' => $id]);

        if ($this->request->wantsJson()) {
            return $this->successResponse('User deleted.');
        }
        $this->flash('success', 'User deleted.');
        return $this->redirect('/users');
    }

    public function attendanceLog(): string
    {
        $db = \Core\Application::$app->db;
        $search = $this->request->get('search', '');

        $clause = "";
        $params = [];
        if ($search) {
            $clause = "WHERE u.name LIKE ? OR u.email LIKE ? OR ta.status LIKE ?";
            $params = ["%$search%", "%$search%", "%$search%"];
        }

        $records = $db->select("
            SELECT ta.*, u.name as teacher_name, u.email as teacher_email
            FROM teacher_attendance ta
            JOIN users u ON ta.user_id = u.id
            $clause
            ORDER BY ta.attendance_date DESC, ta.opened_at DESC
        ", $params);

        return $this->view('users/attendance_log', compact('records', 'search'));
    }
}
