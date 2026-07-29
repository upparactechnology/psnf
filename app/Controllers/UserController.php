<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use App\Models\{User, Role};
use App\Services\EmployeeSyncService;

class UserController extends Controller
{
    protected array $availableApps = [
        'driver_app'        => 'Driver App',
        'staff_dashboard'   => 'Staff Dashboard',
        'teacher_app'       => 'Staff and Teacher App',
        'parents_dashboard' => 'Parents Dashboard',
    ];

    public function index(): string
    {
        $search  = $this->request->get('search', '');
        $roleId  = $this->request->get('role_id', '');
        $page    = (int) $this->request->get('page', 1);

        $currentPath = \Core\Application::$app->request->getPath();
        $isTeachersOnly = ($currentPath === '/academics/teachers');

        $conditions = [];
        $params = [];

        if ($isTeachersOnly) {
            $conditions[] = "id IN (SELECT ur.user_id FROM user_roles ur JOIN roles r ON ur.role_id = r.id WHERE r.slug = 'teacher')";
        } else {
            // Only show users with the selected roles: Teacher, Staff, Driver, Parent, Super Admin
            $conditions[] = "id IN (SELECT ur.user_id FROM user_roles ur JOIN roles r ON ur.role_id = r.id WHERE r.slug IN ('super_admin', 'teacher', 'staff', 'driver', 'parent'))";
        }

        if ($search !== '') {
            $conditions[] = "(name LIKE ? OR email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($roleId !== '' && !$isTeachersOnly) {
            $conditions[] = "id IN (SELECT user_id FROM user_roles WHERE role_id = ?)";
            $params[] = (int) $roleId;
        }

        $conditionStr = implode(' AND ', $conditions);

        $result  = User::paginate($page, 15, $conditionStr, $params);

        if ($isTeachersOnly) {
            $db = \Core\Application::$app->db;
            foreach ($result['data'] as &$u) {
                $emp = $db->selectOne("
                    SELECT e.id as emp_id, e.emp_code, e.salary_basic, e.joining_date, d.name as department_name, des.title as designation_title
                    FROM employees e
                    LEFT JOIN departments d ON e.department_id = d.id
                    LEFT JOIN designations des ON e.designation_id = des.id
                    WHERE e.user_id = ? OR e.email = ?
                    LIMIT 1
                ", [$u['id'], $u['email']]);
                $u['employee'] = $emp ?: null;
            }
        }

        $roles   = Role::allWithPermissionCount();
        $roles   = array_filter($roles, function($role) {
            return in_array($role['slug'], ['super_admin', 'teacher', 'staff', 'driver', 'parent']);
        });

        return $this->view('users/index', array_merge($result, [
            'search' => $search,
            'role_id' => $roleId,
            'roles' => $roles,
            'isTeachersOnly' => $isTeachersOnly
        ]));
    }

    public function create(): string
    {
        $roles    = Role::all('sort_order');
        $roles    = array_filter($roles, function($role) {
            return in_array($role['slug'], ['super_admin', 'teacher', 'staff', 'driver', 'parent']);
        });
        $schools  = \Core\Application::$app->db->select("SELECT id, name FROM schools WHERE tenant_id = ? AND is_active = 1 AND deleted_at IS NULL", [\Core\Database::getTenantId()]);
        $branches = \Core\Application::$app->db->select("SELECT id, name FROM branches WHERE tenant_id = ? AND is_active = 1 AND deleted_at IS NULL", [\Core\Database::getTenantId()]);
        $apps     = $this->availableApps;
        $departments = \Core\Application::$app->db->select("SELECT * FROM departments WHERE is_active = 1");
        $designations = \Core\Application::$app->db->select("SELECT * FROM designations WHERE is_active = 1");

        return $this->view('users/create', compact('roles', 'schools', 'branches', 'apps', 'departments', 'designations'));
    }

    public function store(): string
    {
        $data  = $this->request->getBody();
        $roles = $data['roles'] ?? [];
        $assignedApps = $data['apps'] ?? [];
        $redirectTo = $this->request->input('redirect_to', '/users');
        unset($data['roles'], $data['apps'], $data['_csrf'], $data['redirect_to']);

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
            return $this->redirect('/users/create?redirect_to=' . urlencode($redirectTo));
        }

        $lectureTime = !empty($data['lecture_time']) ? $data['lecture_time'] : null;
        $gracePeriod = isset($data['grace_period']) && $data['grace_period'] !== '' ? (int) $data['grace_period'] : 5;
        
        $salaryBasic = isset($data['salary_basic']) && $data['salary_basic'] !== '' ? (float) $data['salary_basic'] : 0.00;
        $deptId = !empty($data['department_id']) ? (int) $data['department_id'] : null;
        $desigId = !empty($data['designation_id']) ? (int) $data['designation_id'] : null;
        $minClockIn = !empty($data['min_clock_in']) ? $data['min_clock_in'] : null;
        $maxClockOut = !empty($data['max_clock_out']) ? $data['max_clock_out'] : null;

        $data['uuid']         = str_uuid();
        $data['tenant_id']    = \Core\Database::getTenantId();
        $data['password']     = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        $data['created_by']   = auth_id();
        $data['lecture_time'] = $lectureTime;
        $data['grace_period'] = $gracePeriod;
        unset($data['password_confirmation'], $data['salary_basic'], $data['department_id'], $data['designation_id'], $data['min_clock_in'], $data['max_clock_out']);

        $userId = (int) User::create($data);

        if ($roles) {
            User::syncRoles($userId, array_map('intval', $roles));
        }

        EmployeeSyncService::syncUserToEmployee($userId);

        if ($salaryBasic > 0 || $deptId || $desigId || $minClockIn || $maxClockOut) {
            $db = \Core\Application::$app->db;
            $updateData = [];
            if ($salaryBasic > 0) $updateData['salary_basic'] = $salaryBasic;
            if ($deptId) $updateData['department_id'] = $deptId;
            if ($desigId) $updateData['designation_id'] = $desigId;
            if ($minClockIn) $updateData['min_clock_in'] = $minClockIn;
            if ($maxClockOut) $updateData['max_clock_out'] = $maxClockOut;
            
            if (!empty($updateData)) {
                $db->update('employees', $updateData, 'user_id = ?', [$userId]);
            }
        }

        $db = \Core\Application::$app->db;
        foreach ($assignedApps as $app) {
            $db->insert('user_apps', ['user_id' => $userId, 'app_name' => $app]);
        }

        \App\Models\ActivityLog::log('user_created', auth_id(), ['user_id' => $userId]);
        $this->flash('success', 'User created successfully.');
        return $this->redirect($redirectTo);
    }

    public function edit(string $id): string
    {
        $user     = User::withRoles((int) $id);
        $roles    = Role::all('sort_order');
        $roles    = array_filter($roles, function($role) {
            return in_array($role['slug'], ['super_admin', 'teacher', 'staff', 'driver', 'parent']);
        });
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
        $redirectTo = $this->request->input('redirect_to', '/users');
        unset($data['roles'], $data['apps'], $data['_csrf'], $data['_method'], $data['password'], $data['password_confirmation'], $data['redirect_to']);

        $lectureTime = !empty($data['lecture_time']) ? $data['lecture_time'] : null;
        $gracePeriod = isset($data['grace_period']) && $data['grace_period'] !== '' ? (int) $data['grace_period'] : 5;

        $data['lecture_time'] = $lectureTime;
        $data['grace_period'] = $gracePeriod;
        $data['updated_by']   = auth_id();

        User::update((int) $id, $data);
        User::syncRoles((int) $id, array_map('intval', $roles));

        EmployeeSyncService::syncUserToEmployee((int) $id);

        $db = \Core\Application::$app->db;
        $db->query("DELETE FROM user_apps WHERE user_id = ?", [$id]);
        foreach ($assignedApps as $app) {
            $db->insert('user_apps', ['user_id' => (int) $id, 'app_name' => $app]);
        }

        \App\Models\ActivityLog::log('user_updated', auth_id(), ['user_id' => $id]);
        $this->flash('success', 'User updated.');
        return $this->redirect($redirectTo);
    }

    public function destroy(string $id): string
    {
        User::delete((int) $id);
        \App\Models\ActivityLog::log('user_deleted', auth_id(), ['user_id' => $id]);

        // Attempt to remove face embeddings from Python backend
        try {
            $ch = curl_init("http://127.0.0.1:8000/api/employees/delete?id=" . $id);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2);
            curl_exec($ch);
            curl_close($ch);
        } catch (\Throwable $e) {
            // Ignore if backend is down
        }

        $redirectTo = $this->request->input('redirect_to', '/users');

        if ($this->request->wantsJson()) {
            return $this->successResponse('User deleted.');
        }
        $this->flash('success', 'User deleted.');
        return $this->redirect($redirectTo);
    }

    public function staffAttendanceLog(): string
    {
        $db = \Core\Application::$app->db;
        $search = trim($this->request->get('search', ''));
        $date = trim($this->request->get('date', ''));
        $status = trim($this->request->get('status', ''));
        $employee_id = trim($this->request->get('employee_id', ''));

        $conditions = [];
        $params = [];

        if ($search !== '') {
            $conditions[] = "(e.name LIKE ? OR e.first_name LIKE ? OR e.last_name LIKE ? OR a.status LIKE ? OR e.employee_code LIKE ? OR e.employee_id LIKE ? OR e.email LIKE ?)";
            $params = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%", "%$search%", "%$search%", "%$search%"]);
        }

        if ($date !== '') {
            $conditions[] = "(DATE(a.check_in) = ? OR a.attendance_date = ?)";
            $params[] = $date;
            $params[] = $date;
        }

        if ($status !== '') {
            $conditions[] = "LOWER(a.status) = LOWER(?)";
            $params[] = $status;
        }

        if ($employee_id !== '') {
            $conditions[] = "a.employee_id = ?";
            $params[] = $employee_id;
        }

        $clause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

        $records = $db->select("
            SELECT 
                a.id,
                a.employee_id,
                a.check_in as clock_time,
                'CHECK_IN' as clock_type,
                COALESCE(a.status, 'present') as status,
                COALESCE(a.ip_address, 'Face Kiosk') as device_id,
                COALESCE(e.name, NULLIF(TRIM(CONCAT(IFNULL(e.first_name,''), ' ', IFNULL(e.last_name,''))), ''), 'Staff Member') as first_name,
                '' as last_name,
                COALESCE(e.employee_code, e.employee_id, CONCAT('EMP-', e.id)) as emp_code,
                COALESCE(e.email, '') as emp_email
            FROM attendance a
            JOIN employees e ON a.employee_id = e.id
            $clause
            ORDER BY a.check_in DESC, a.id DESC
        ", $params);

        $employeesList = $db->select("SELECT id, name, employee_code FROM employees ORDER BY name ASC");

        return $this->view('users/attendance_log', compact('records', 'search', 'date', 'status', 'employee_id', 'employeesList'));
    }
}
