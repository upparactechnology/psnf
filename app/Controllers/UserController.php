<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use App\Models\{User, Role};
use App\Services\EmployeeSyncService;

class UserController extends Controller
{

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

                // Relational assignments
                $u['class_teacher_assignments'] = $db->select("
                    SELECT name, section FROM classes WHERE class_teacher_id = ?
                ", [$u['id']]);

                $u['subject_teacher_assignments'] = $db->select("
                    SELECT DISTINCT c.name as class_name, c.section, s.name as subject_name 
                    FROM timetables t 
                    JOIN classes c ON t.class_id = c.id 
                    JOIN subjects s ON t.subject_id = s.id 
                    WHERE t.teacher_id = ?
                ", [$u['id']]);
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
        $roles    = array_values($roles);
        $schools  = \Core\Application::$app->db->select("SELECT id, name FROM schools WHERE tenant_id = ? AND is_active = 1 AND deleted_at IS NULL", [\Core\Database::getTenantId()]);
        $branches = \Core\Application::$app->db->select("SELECT id, name FROM branches WHERE tenant_id = ? AND is_active = 1 AND deleted_at IS NULL", [\Core\Database::getTenantId()]);
        $departments = \Core\Application::$app->db->select("SELECT * FROM departments WHERE is_active = 1");
        $designations = \Core\Application::$app->db->select("SELECT * FROM designations WHERE is_active = 1");

        return $this->view('users/create', compact('roles', 'schools', 'branches', 'departments', 'designations'));
    }

    public function store(): string
    {
        $data  = $this->request->getBody();
        $roles = $data['roles'] ?? [];
        $redirectTo = safe_redirect_target($this->request->input('redirect_to', '/users'), '/users');
        unset($data['roles'], $data['_csrf'], $data['redirect_to']);

        $roleCheck = $this->sanitizeRoles((array) $roles, null);
        if (is_string($roleCheck)) {
            $this->flash('error', $roleCheck);
            \Core\Session::flash('old', array_merge($data, ['roles' => $roles, 'password' => '', 'password_confirmation' => '']));
            return $this->redirect(url('users/create?redirect_to=' . urlencode($redirectTo)));
        }
        $roles = $roleCheck;

        $rules = [
            'first_name'=> 'required|min:2',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|min:8|confirmed',
            'school_id' => 'required',
            'branch_id' => 'required',
        ];

        $validator = new \Core\Validator($data, $rules);
        if ($validator->fails()) {
            \Core\Session::flash('errors', $validator->errors());
            \Core\Session::flash('old', array_merge($data, ['roles' => $roles]));
            return $this->redirect(url('users/create?redirect_to=' . urlencode($redirectTo)));
        }

        if (!$this->validSchoolBranch($data)) {
            $this->flash('error', 'Invalid school or branch.');
            return $this->redirect(url('users/create?redirect_to=' . urlencode($redirectTo)));
        }

        $lectureTime = !empty($data['lecture_time']) ? $data['lecture_time'] : null;
        // Blank = follow the shift policy (Staff Settings); a number overrides it for this user.
        $gracePeriod = isset($data['grace_period']) && $data['grace_period'] !== '' ? max(0, (int) $data['grace_period']) : null;

        $salaryBasic = isset($data['salary_basic']) && $data['salary_basic'] !== '' ? (float) $data['salary_basic'] : 0.00;
        $deptId = !empty($data['department_id']) ? (int) $data['department_id'] : null;
        $desigId = !empty($data['designation_id']) ? (int) $data['designation_id'] : null;
        $minClockIn = !empty($data['min_clock_in']) ? $data['min_clock_in'] : null;
        $maxClockOut = !empty($data['max_clock_out']) ? $data['max_clock_out'] : null;
        $breakAllowed = (isset($data['break_allowed_minutes']) && $data['break_allowed_minutes'] !== '') ? max(0, (int) $data['break_allowed_minutes']) : null;

        $data['uuid']         = str_uuid();
        $data['tenant_id']    = \Core\Database::getTenantId();
        $data['password']     = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        $data['created_by']   = auth_id();
        $data['lecture_time'] = $lectureTime;
        $data['grace_period'] = $gracePeriod;
        
        $data['name'] = trim($data['first_name'] . ' ' . ($data['last_name'] ?? ''));
        unset($data['password_confirmation'], $data['salary_basic'], $data['department_id'], $data['designation_id'], $data['min_clock_in'], $data['max_clock_out'], $data['first_name'], $data['last_name']);

        // Mass-assignment protection: only whitelisted columns, plus server-set fields
        $data = array_merge(
            $this->onlyWritable($data),
            [
                'uuid'       => $data['uuid'],
                'tenant_id'  => $data['tenant_id'],
                'password'   => $data['password'],
                'created_by' => $data['created_by'],
            ]
        );
        $data['is_active'] = !empty($data['is_active']) ? 1 : 0;

        $userId = (int) User::create($data);

        if ($roles) {
            User::syncRoles($userId, $roles);
        }

        EmployeeSyncService::syncUserToEmployee($userId);

        if ($salaryBasic > 0 || $deptId || $desigId || $minClockIn || $maxClockOut || $breakAllowed !== null) {
            $db = \Core\Application::$app->db;
            $updateData = [];
            if ($breakAllowed !== null) $updateData['break_allowed_minutes'] = $breakAllowed;
            if ($salaryBasic > 0) $updateData['salary_basic'] = $salaryBasic;
            if ($deptId) $updateData['department_id'] = $deptId;
            if ($desigId) $updateData['designation_id'] = $desigId;
            if ($minClockIn) $updateData['min_clock_in'] = $minClockIn;
            if ($maxClockOut) $updateData['max_clock_out'] = $maxClockOut;
            
            if (!empty($updateData)) {
                $db->update('employees', $updateData, 'user_id = ?', [$userId]);
            }
        }

        \App\Models\ActivityLog::log('user_created', auth_id(), ['user_id' => $userId]);
        $this->flash('success', 'User created successfully.');
        return $this->redirect($redirectTo);
    }

    public function edit(string $id): string
    {
        if (!$this->targetInTenant((int) $id)) {
            $this->abort(404);
        }
        $user     = User::withRoles((int) $id);
        $roles    = Role::all('sort_order');
        $roles    = array_filter($roles, function($role) {
            return in_array($role['slug'], ['super_admin', 'teacher', 'staff', 'driver', 'parent']);
        });
        $roles    = array_values($roles);
        $schools  = \Core\Application::$app->db->select("SELECT id, name FROM schools WHERE tenant_id = ? AND deleted_at IS NULL", [\Core\Database::getTenantId()]);
        $branches = \Core\Application::$app->db->select("SELECT id, name FROM branches WHERE tenant_id = ? AND deleted_at IS NULL", [\Core\Database::getTenantId()]);
        $departments = \Core\Application::$app->db->select("SELECT * FROM departments WHERE is_active = 1");
        $designations = \Core\Application::$app->db->select("SELECT * FROM designations WHERE is_active = 1");

        $db = \Core\Application::$app->db;

        // Fetch linked employee record for salary/department/designation/shift data
        $emp = $db->selectOne("SELECT * FROM employees WHERE user_id = ? OR email = ? LIMIT 1", [$user['id'], $user['email']]);
        $user['employee'] = $emp ?: null;

        return $this->view('users/edit', compact('user', 'roles', 'schools', 'branches', 'departments', 'designations'));
    }

    public function update(string $id): string
    {
        $data  = $this->request->getBody();
        $roles = $data['roles'] ?? [];
        $redirectTo = safe_redirect_target($this->request->input('redirect_to', '/users'), '/users');
        $editUrl = url('users/' . (int) $id . '/edit?redirect_to=' . urlencode($redirectTo));

        if (!$this->targetInTenant((int) $id)) {
            $this->flash('error', 'User not found.');
            return $this->redirect($redirectTo);
        }
        if ($this->isProtectedTarget((int) $id)) {
            $this->flash('error', 'Only a super admin can modify a super admin account.');
            return $this->redirect($redirectTo);
        }

        $roleCheck = $this->sanitizeRoles((array) $roles, (int) $id);
        if (is_string($roleCheck)) {
            $this->flash('error', $roleCheck);
            return $this->redirect($editUrl);
        }
        $roles = $roleCheck;

        if ((isset($data['school_id']) || isset($data['branch_id'])) && !$this->validSchoolBranch($data)) {
            $this->flash('error', 'Invalid school or branch.');
            return $this->redirect($editUrl);
        }

        $newPassword        = (string) ($data['new_password'] ?? '');
        $newPasswordConfirm = (string) ($data['new_password_confirmation'] ?? '');
        unset($data['roles'], $data['_csrf'], $data['_method'], $data['password'], $data['password_confirmation'], $data['redirect_to'], $data['new_password'], $data['new_password_confirmation']);

        // Only admins may change another user's password
        if ($newPassword !== '') {
            if (!has_role('super_admin') && !has_role('school_admin')) {
                $this->flash('error', 'Only an admin can change passwords.');
                return $this->redirect(url('users/' . (int) $id . '/edit?redirect_to=' . urlencode($redirectTo)));
            }
            $pwError = null;
            if ($newPassword !== $newPasswordConfirm) {
                $pwError = 'Passwords do not match.';
            }
            if ($pwError) {
                \Core\Session::flash('errors', ['new_password' => $pwError]);
                return $this->redirect(url('users/' . (int) $id . '/edit?redirect_to=' . urlencode($redirectTo)));
            }
            $data['password'] = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        }

        $lectureTime = !empty($data['lecture_time']) ? $data['lecture_time'] : null;
        // Blank = follow the shift policy (Staff Settings); a number overrides it for this user.
        $gracePeriod = isset($data['grace_period']) && $data['grace_period'] !== '' ? max(0, (int) $data['grace_period']) : null;

        $salaryBasic = isset($data['salary_basic']) && $data['salary_basic'] !== '' ? (float) $data['salary_basic'] : null;
        $deptId = !empty($data['department_id']) ? (int) $data['department_id'] : null;
        $desigId = !empty($data['designation_id']) ? (int) $data['designation_id'] : null;
        $minClockIn = !empty($data['min_clock_in']) ? $data['min_clock_in'] : null;
        $maxClockOut = !empty($data['max_clock_out']) ? $data['max_clock_out'] : null;
        $hasBreakField = array_key_exists('break_allowed_minutes', $data);
        $breakAllowed  = ($hasBreakField && $data['break_allowed_minutes'] !== '') ? max(0, (int) $data['break_allowed_minutes']) : null;

        $data['lecture_time'] = $lectureTime;
        $data['grace_period'] = $gracePeriod;
        $data['updated_by']   = auth_id();

        // Build full name from first_name/last_name if provided
        if (!empty($data['first_name'])) {
            $data['name'] = trim($data['first_name'] . ' ' . ($data['last_name'] ?? ''));
        }
        unset($data['salary_basic'], $data['department_id'], $data['designation_id'], $data['min_clock_in'], $data['max_clock_out'], $data['first_name'], $data['last_name'], $data['break_allowed_minutes']);

        // Mass-assignment protection: whitelisted columns only (+ admin-set password hash)
        $hashedPassword = $data['password'] ?? null;
        $updatedBy      = $data['updated_by'];
        $data = $this->onlyWritable($data);
        $data['updated_by'] = $updatedBy;
        if ($hashedPassword !== null) {
            $data['password'] = $hashedPassword;
        }

        User::update((int) $id, $data);
        User::syncRoles((int) $id, $roles);

        EmployeeSyncService::syncUserToEmployee((int) $id);

        // Update employee record with salary/department/designation/shift data
        $db = \Core\Application::$app->db;
        $empUpdate = [];
        if ($salaryBasic !== null) $empUpdate['salary_basic'] = $salaryBasic;
        if ($deptId) $empUpdate['department_id'] = $deptId;
        if ($desigId) $empUpdate['designation_id'] = $desigId;
        if ($minClockIn) $empUpdate['min_clock_in'] = $minClockIn;
        if ($maxClockOut) $empUpdate['max_clock_out'] = $maxClockOut;
        if ($hasBreakField) $empUpdate['break_allowed_minutes'] = $breakAllowed; // NULL = use global
        if (!empty($empUpdate)) {
            $db->update('employees', $empUpdate, 'user_id = ?', [(int) $id]);
        }

        \App\Models\ActivityLog::log('user_updated', auth_id(), ['user_id' => $id]);
        if ($newPassword !== '') {
            \App\Models\ActivityLog::log('user_password_changed', auth_id(), ['user_id' => $id]);
        }
        $this->flash('success', 'User updated.');
        return $this->redirect($redirectTo);
    }

    public function destroy(string $id): string
    {
        $redirectTo = safe_redirect_target($this->request->input('redirect_to', '/users'), '/users');

        $denied = null;
        if (!$this->targetInTenant((int) $id)) {
            $denied = 'User not found.';
        } elseif ((int) $id === (int) auth_id()) {
            $denied = 'You cannot delete your own account.';
        } elseif ($this->isProtectedTarget((int) $id)) {
            $denied = 'Only a super admin can delete a super admin account.';
        }
        if ($denied !== null) {
            if ($this->request->wantsJson()) {
                return $this->errorResponse($denied, null, 403);
            }
            $this->flash('error', $denied);
            return $this->redirect($redirectTo);
        }

        User::delete((int) $id);
        \App\Models\ActivityLog::log('user_deleted', auth_id(), ['user_id' => $id]);

        // Attempt to remove face embeddings from Python backend
        try {
            $ch = curl_init("http://127.0.0.1:8000/api/employees/delete?" . http_build_query(['id' => (int) $id, 'tenant_id' => (int) \Core\Database::getTenantId()]));
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Internal-Key: ' . app_secret('FACE_API_KEY', 'face_api_key')]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2);
            curl_exec($ch);
            curl_close($ch);
        } catch (\Throwable $e) {
            // Ignore if backend is down
        }

        if ($this->request->wantsJson()) {
            return $this->successResponse('User deleted.');
        }
        $this->flash('success', 'User deleted.');
        return $this->redirect($redirectTo);
    }

    // ─── Security helpers ────────────────────────────────────────────────────

    /** Columns of `users` that may be written from the create/edit forms. */
    private const WRITABLE_COLUMNS = [
        'name', 'email', 'phone', 'school_id', 'branch_id', 'is_active',
        'lecture_time', 'grace_period', 'designation', 'employee_id', 'gender', 'dob',
    ];

    private function onlyWritable(array $data): array
    {
        return array_intersect_key($data, array_flip(self::WRITABLE_COLUMNS));
    }

    private function actorIsSuperAdmin(): bool
    {
        return in_array('super_admin', (array) (auth()['roles'] ?? []), true);
    }

    /** Target user exists, is not deleted, and belongs to the current tenant. */
    private function targetInTenant(int $id): bool
    {
        $row = \Core\Application::$app->db->selectOne(
            "SELECT tenant_id FROM users WHERE id = ? AND deleted_at IS NULL LIMIT 1",
            [$id]
        );
        if (!$row) return false;
        $tenantId = \Core\Database::getTenantId();
        return $tenantId === null || (int) $row['tenant_id'] === (int) $tenantId;
    }

    /** Super admin accounts can only be changed by a super admin. */
    private function isProtectedTarget(int $id): bool
    {
        if ($this->actorIsSuperAdmin()) return false;
        $row = \Core\Application::$app->db->selectOne(
            "SELECT 1 FROM user_roles ur JOIN roles r ON r.id = ur.role_id WHERE ur.user_id = ? AND r.slug = 'super_admin' LIMIT 1",
            [$id]
        );
        return (bool) $row;
    }

    /**
     * Validates requested role ids. Returns the clean int list, or an error string.
     * - only existing (non-deleted) roles
     * - only a super admin may grant super_admin
     * - a non-super-admin may not change their own roles
     */
    private function sanitizeRoles(array $roles, ?int $targetUserId): array|string
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $roles), fn($r) => $r > 0)));
        $db  = \Core\Application::$app->db;
        $isSuper = $this->actorIsSuperAdmin();

        $valid = [];
        if ($ids) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $rows = $db->select("SELECT id, slug FROM roles WHERE id IN ($placeholders) AND deleted_at IS NULL", $ids);
            if (count($rows) !== count($ids)) {
                return 'One or more selected roles are invalid.';
            }
            foreach ($rows as $r) {
                if ($r['slug'] === 'super_admin' && !$isSuper) {
                    return 'Only a super admin can assign the Super Admin role.';
                }
                $valid[] = (int) $r['id'];
            }
        }

        if ($targetUserId !== null && $targetUserId === (int) auth_id() && !$isSuper) {
            $current = array_map('intval', array_column(
                $db->select("SELECT role_id FROM user_roles WHERE user_id = ?", [$targetUserId]),
                'role_id'
            ));
            sort($current);
            $cmp = $valid;
            sort($cmp);
            if ($current !== $cmp) {
                return 'You cannot change your own roles.';
            }
        }

        return $valid;
    }

    /** school_id / branch_id (when given) must belong to the current tenant. */
    private function validSchoolBranch(array $data): bool
    {
        $db = \Core\Application::$app->db;
        $tenantId = \Core\Database::getTenantId();
        if (!empty($data['school_id'])) {
            $ok = $db->selectOne("SELECT 1 FROM schools WHERE id = ? AND tenant_id = ? LIMIT 1", [(int) $data['school_id'], $tenantId]);
            if (!$ok) return false;
        }
        if (!empty($data['branch_id'])) {
            $ok = $db->selectOne("SELECT 1 FROM branches WHERE id = ? AND tenant_id = ? LIMIT 1", [(int) $data['branch_id'], $tenantId]);
            if (!$ok) return false;
        }
        return true;
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
