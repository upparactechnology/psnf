<?php

declare(strict_types=1);

namespace App\Services;

use Core\Application;
use App\Models\User;
use App\Models\Role;

class EmployeeSyncService
{
    private static array $employeeRoles = [
        'super_admin',
        'school_admin',
        'manager',
        'teacher',
        'therapist',
        'staff',
        'driver'
    ];

    /**
     * Look up existing departments and designations by code.
     * Returns an array mapping codes to IDs (only for records that exist).
     */
    public static function ensureDefaultDepartmentsAndDesignations(): array
    {
        $db = Application::$app->db;
        
        $deptCodes = ['ACAD', 'ADMIN', 'SUPP'];
        $deptIds = [];
        foreach ($deptCodes as $code) {
            $existing = $db->selectOne("SELECT id FROM departments WHERE code = ? LIMIT 1", [$code]);
            if ($existing) {
                $deptIds[$code] = (int) $existing['id'];
            }
        }

        $desigCodes = ['TCH', 'ADMIN', 'STAFF', 'DRV', 'THER'];
        $desigIds = [];
        foreach ($desigCodes as $code) {
            $existing = $db->selectOne("SELECT id FROM designations WHERE code = ? LIMIT 1", [$code]);
            if ($existing) {
                $desigIds[$code] = (int) $existing['id'];
            }
        }

        return [
            'departments'  => $deptIds,
            'designations' => $desigIds
        ];
    }

    /**
     * Sync a User to an Employee record
     */
    public static function syncUserToEmployee(int $userId): void
    {
        $db = Application::$app->db;
        $user = User::withRoles($userId);
        if (!$user) {
            return;
        }

        // Check if user has an employee role
        $userRoles = $user['roles'] ?? [];
        $employeeRolesIntersect = array_intersect($userRoles, self::$employeeRoles);

        if (empty($employeeRolesIntersect)) {
            // User does not have an employee role. If they have an employee record, set it inactive
            $existingEmp = $db->selectOne("SELECT id FROM employees WHERE user_id = ? OR email = ? LIMIT 1", [$userId, $user['email']]);
            if ($existingEmp) {
                $db->update('employees', ['status' => 'inactive', 'user_id' => null], 'id = ?', [$existingEmp['id']]);
            }
            return;
        }

        // Parse Name
        $parts = explode(' ', trim($user['name']), 2);
        $firstName = $parts[0] !== '' ? $parts[0] : 'Staff';
        $lastName = $parts[1] ?? '';

        // Find appropriate default department and designation based on roles
        $meta = self::ensureDefaultDepartmentsAndDesignations();
        $deptId = null;
        $desigId = null;

        if (in_array('teacher', $userRoles)) {
            $deptId = $meta['departments']['ACAD'] ?? null;
            $desigId = $meta['designations']['TCH'] ?? null;
        } elseif (in_array('therapist', $userRoles)) {
            $deptId = $meta['departments']['ACAD'] ?? null;
            $desigId = $meta['designations']['THER'] ?? null;
        } elseif (in_array('driver', $userRoles)) {
            $deptId = $meta['departments']['SUPP'] ?? null;
            $desigId = $meta['designations']['DRV'] ?? null;
        } elseif (in_array('super_admin', $userRoles) || in_array('school_admin', $userRoles) || in_array('manager', $userRoles)) {
            $deptId = $meta['departments']['ADMIN'] ?? null;
            $desigId = $meta['designations']['ADMIN'] ?? null;
        } else {
            $deptId = $meta['departments']['ADMIN'] ?? null;
            $desigId = $meta['designations']['STAFF'] ?? null;
        }

        $deptName = null;
        if ($deptId) {
            $d = $db->selectOne("SELECT name FROM departments WHERE id = ?", [$deptId]);
            if ($d) $deptName = $d['name'];
        }
        $desigTitle = null;
        if ($desigId) {
            $des = $db->selectOne("SELECT title FROM designations WHERE id = ?", [$desigId]);
            if ($des) $desigTitle = $des['title'];
        }

        // Check if employee record already exists
        $existingEmp = $db->selectOne("SELECT id, emp_code FROM employees WHERE user_id = ? OR email = ? LIMIT 1", [$userId, $user['email']]);

        $status = $user['is_active'] ? 'active' : 'inactive';

        if ($existingEmp) {
            $db->update('employees', [
                'user_id'        => $userId,
                'first_name'     => $firstName,
                'last_name'      => $lastName,
                'email'          => $user['email'],
                'phone'          => $user['phone'] ?? null,
                'status'         => $status,
                'branch_id'      => $user['branch_id'] ?? 1,
                'updated_at'     => now()
            ], 'id = ?', [$existingEmp['id']]);

            // Sync employee_id back to users table if missing
            if (empty($user['employee_id'])) {
                $db->update('users', ['employee_id' => $existingEmp['emp_code']], 'id = ?', [$userId]);
            }
        } else {
            // Create a new employee code
            $codeCount = (int) ($db->selectOne("SELECT COUNT(*) as cnt FROM employees")['cnt'] ?? 0) + 101;
            $empCode = 'EMP-' . $codeCount;

            $db->insert('employees', [
                'tenant_id'      => $user['tenant_id'] ?? 1,
                'user_id'        => $userId,
                'emp_code'       => $empCode,
                'first_name'     => $firstName,
                'last_name'      => $lastName,
                'email'          => $user['email'],
                'phone'          => $user['phone'] ?? null,
                'department_id'  => $deptId,
                'designation_id' => $desigId,
                'branch_id'      => $user['branch_id'] ?? 1,
                'joining_date'   => date('Y-m-d'),
                'salary_basic'   => 35000.00,
                'status'         => $status,
                'created_at'     => now()
            ]);

            // Update user with employee_id
            $db->update('users', ['employee_id' => $empCode], 'id = ?', [$userId]);
        }
    }

    /**
     * Sync an Employee record to a User record
     */
    public static function syncEmployeeToUser(int $employeeId): void
    {
        $db = Application::$app->db;
        $emp = $db->selectOne("SELECT * FROM employees WHERE id = ?", [$employeeId]);
        if (!$emp) {
            return;
        }

        // Try to find the user
        $user = null;
        if (!empty($emp['user_id'])) {
            $user = User::find((int) $emp['user_id']);
        }
        if (!$user) {
            $user = User::findByEmailGlobal($emp['email']);
        }

        $fullName = trim($emp['first_name'] . ' ' . $emp['last_name']);
        $isActive = ($emp['status'] === 'active') ? 1 : 0;

        if ($user) {
            // Update User fields
            $db->update('users', [
                'name'        => $fullName,
                'email'       => $emp['email'],
                'phone'       => $emp['phone'] ?? null,
                'is_active'   => $isActive,
                'employee_id' => $emp['emp_code'],
                'branch_id'   => $emp['branch_id'] ?? 1,
                'updated_at'  => now()
            ], 'id = ?', [$user['id']]);

            // Ensure employee has user_id set correctly
            if (empty($emp['user_id'])) {
                $db->update('employees', ['user_id' => $user['id']], 'id = ?', [$employeeId]);
            }
        } else {
            // Create user
            $uuid = str_uuid();
            $defaultPassword = password_hash('Password123!', PASSWORD_BCRYPT, ['cost' => 12]);

            $userId = $db->insert('users', [
                'uuid'        => $uuid,
                'tenant_id'   => $emp['tenant_id'] ?? 1,
                'school_id'   => 1,
                'branch_id'   => $emp['branch_id'] ?? 1,
                'name'        => $fullName,
                'email'       => $emp['email'],
                'phone'       => $emp['phone'] ?? null,
                'password'    => $defaultPassword,
                'is_active'   => $isActive,
                'employee_id' => $emp['emp_code'],
                'created_at'  => now()
            ]);

            // Assign role based on designation
            $roleSlug = 'staff';
            if ($emp['designation_id']) {
                $desig = $db->selectOne("SELECT code FROM designations WHERE id = ?", [$emp['designation_id']]);
                if ($desig) {
                    if ($desig['code'] === 'TCH') {
                        $roleSlug = 'teacher';
                    } elseif ($desig['code'] === 'DRV') {
                        $roleSlug = 'driver';
                    } elseif ($desig['code'] === 'THER') {
                        $roleSlug = 'therapist';
                    }
                }
            }

            $role = $db->selectOne("SELECT id FROM roles WHERE slug = ?", [$roleSlug]);
            if ($role) {
                User::assignRole((int) $userId, (int) $role['id']);
            }

            // Update employee with user_id
            $db->update('employees', ['user_id' => $userId], 'id = ?', [$employeeId]);
        }
    }
}
