<?php
class RolesPermissionsSeeder
{
    public function __construct(private \Core\Database $db) {}

    public function run(): void
    {
        $this->seedPlans();
        $this->seedTenant();
        $this->seedSchoolAndBranch();
        $this->seedRoles();
        $this->seedPermissions();
        $this->seedSuperAdmin();
    }

    private function seedPlans(): void
    {
        $plans = [
            ['name' => 'Starter',    'slug' => 'starter',    'price' => 0,      'billing_cycle' => 'monthly', 'max_users' => 10,  'max_students' => 100,  'max_branches' => 1],
            ['name' => 'School',     'slug' => 'school',     'price' => 2999,   'billing_cycle' => 'monthly', 'max_users' => 50,  'max_students' => 500,  'max_branches' => 3],
            ['name' => 'Enterprise', 'slug' => 'enterprise', 'price' => 7999,   'billing_cycle' => 'monthly', 'max_users' => 500, 'max_students' => 5000, 'max_branches' => 20],
        ];
        foreach ($plans as $plan) {
            $exists = $this->db->selectOne("SELECT id FROM plans WHERE slug = ?", [$plan['slug']]);
            if (!$exists) {
                $this->db->insert('plans', $plan);
            }
        }
    }

    private function seedTenant(): void
    {
        $exists = $this->db->selectOne("SELECT id FROM tenants WHERE slug = 'psnf'");
        if (!$exists) {
            $this->db->insert('tenants', [
                'uuid'    => str_uuid(),
                'name'    => 'Pearl Special Needs Foundation',
                'slug'    => 'psnf',
                'email'   => 'admin@psnf.edu',
                'phone'   => '+91-9000000000',
                'country' => 'India',
                'is_active' => 1,
                'created_at' => now(),
            ]);
        }
    }

    private function seedSchoolAndBranch(): void
    {
        $tenant = $this->db->selectOne("SELECT id FROM tenants WHERE slug = 'psnf'");
        if (!$tenant) return;
        $tid = $tenant['id'];

        $school = $this->db->selectOne("SELECT id FROM schools WHERE tenant_id = ? LIMIT 1", [$tid]);
        if (!$school) {
            $sid = $this->db->insert('schools', [
                'tenant_id' => $tid,
                'name'      => 'Pearl Special Needs School - Main',
                'code'      => 'PSNF-MAIN',
                'is_active' => 1,
                'created_at'=> now(),
            ]);
        } else {
            $sid = $school['id'];
        }

        $branch = $this->db->selectOne("SELECT id FROM branches WHERE school_id = ? LIMIT 1", [$sid]);
        if (!$branch) {
            $this->db->insert('branches', [
                'tenant_id' => $tid,
                'school_id' => $sid,
                'name'      => 'Main Branch',
                'code'      => 'MAIN',
                'is_main'   => 1,
                'is_active' => 1,
                'created_at'=> now(),
            ]);
        }
    }

    private function seedRoles(): void
    {
        $roles = [
            ['name' => 'Super Admin',   'slug' => 'super_admin',   'description' => 'Full system access',          'is_system' => 1, 'sort_order' => 1],
            ['name' => 'School Admin',  'slug' => 'school_admin',  'description' => 'School level administrator',  'is_system' => 1, 'sort_order' => 2],
            ['name' => 'Manager',       'slug' => 'manager',       'description' => 'Branch manager',              'is_system' => 1, 'sort_order' => 3],
            ['name' => 'Teacher',       'slug' => 'teacher',       'description' => 'Classroom teacher',           'is_system' => 1, 'sort_order' => 4],
            ['name' => 'Therapist',     'slug' => 'therapist',     'description' => 'Therapy specialist',          'is_system' => 1, 'sort_order' => 5],
            ['name' => 'Staff',         'slug' => 'staff',         'description' => 'General staff member',        'is_system' => 1, 'sort_order' => 6],
            ['name' => 'Driver',        'slug' => 'driver',        'description' => 'Transport driver',            'is_system' => 1, 'sort_order' => 7],
            ['name' => 'Parent',        'slug' => 'parent',        'description' => 'Parent / guardian',           'is_system' => 1, 'sort_order' => 8],
            ['name' => 'Student',       'slug' => 'student',       'description' => 'Student user',                'is_system' => 1, 'sort_order' => 9],
        ];

        foreach ($roles as $role) {
            $exists = $this->db->selectOne("SELECT id FROM roles WHERE slug = ? AND tenant_id IS NULL", [$role['slug']]);
            if (!$exists) {
                $this->db->insert('roles', array_merge($role, ['created_at' => now()]));
            }
        }
    }

    private function seedPermissions(): void
    {
        $modules = [
            'auth'        => ['login','logout','manage_sessions'],
            'users'       => ['view_users','create_users','edit_users','delete_users','assign_roles'],
            'roles'       => ['view_roles','create_roles','edit_roles','delete_roles'],
            'permissions' => ['view_permissions','assign_permissions'],
            'tenants'     => ['view_tenants','create_tenants','edit_tenants','delete_tenants'],
            'schools'     => ['view_schools','create_schools','edit_schools','delete_schools'],
            'branches'    => ['view_branches','create_branches','edit_branches','delete_branches'],
            'students'    => ['view_students','create_students','edit_students','delete_students','approve_admissions'],
            'documents'   => ['view_documents','upload_documents','delete_documents','verify_documents'],
            'reports'     => ['view_reports','export_reports'],
            'audit'       => ['view_audit_logs'],
            'settings'    => ['view_settings','edit_settings'],
            'portal_access'=> [
                'teacher_portal','parents_portal','driver_app','teacher_staff_app',
                'see_student_name','see_student_medical','see_student_guardian','access_exams_app',
                'teacher_app_student_details','teacher_app_half_leave_notification','teacher_app_timetables',
                'staff_app_student_details','staff_app_half_leave_notification','staff_app_half_leave_details'
            ],
        ];

        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $slug = $action;
                $name = ucfirst(str_replace('_', ' ', $action));
                $exists = $this->db->selectOne("SELECT id FROM permissions WHERE slug = ?", [$slug]);
                if (!$exists) {
                    $this->db->insert('permissions', [
                        'name'       => $name,
                        'slug'       => $slug,
                        'module'     => $module,
                        'created_at' => now(),
                    ]);
                }
            }
        }

        // Give super_admin all permissions
        $superAdminRole = $this->db->selectOne("SELECT id FROM roles WHERE slug = 'super_admin' AND tenant_id IS NULL");
        if ($superAdminRole) {
            $allPerms = $this->db->select("SELECT id FROM permissions");
            foreach ($allPerms as $perm) {
                $exists = $this->db->selectOne("SELECT 1 FROM role_permissions WHERE role_id = ? AND permission_id = ?", [$superAdminRole['id'], $perm['id']]);
                if (!$exists) {
                    $this->db->insert('role_permissions', ['role_id' => $superAdminRole['id'], 'permission_id' => $perm['id']]);
                }
            }
        }

        // Seed default presets for Teacher role
        $teacherRole = $this->db->selectOne("SELECT id FROM roles WHERE slug = 'teacher' AND tenant_id IS NULL");
        if ($teacherRole) {
            $teacherPerms = [
                'teacher_portal',
                'teacher_staff_app',
                'see_student_name',
                'see_student_medical',
                'see_student_guardian',
                'access_exams_app',
                'teacher_app_student_details',
                'teacher_app_half_leave_notification',
                'teacher_app_timetables'
            ];
            foreach ($teacherPerms as $slug) {
                $perm = $this->db->selectOne("SELECT id FROM permissions WHERE slug = ?", [$slug]);
                if ($perm) {
                    $exists = $this->db->selectOne("SELECT 1 FROM role_permissions WHERE role_id = ? AND permission_id = ?", [$teacherRole['id'], $perm['id']]);
                    if (!$exists) {
                        $this->db->insert('role_permissions', ['role_id' => $teacherRole['id'], 'permission_id' => $perm['id']]);
                    }
                }
            }
        }

        // Seed default presets for Staff role
        $staffRole = $this->db->selectOne("SELECT id FROM roles WHERE slug = 'staff' AND tenant_id IS NULL");
        if ($staffRole) {
            $staffPerms = [
                'teacher_staff_app',
                'staff_app_student_details',
                'staff_app_half_leave_notification',
                'staff_app_half_leave_details',
                'see_student_name',
                'see_student_medical',
                'see_student_guardian'
            ];
            foreach ($staffPerms as $slug) {
                $perm = $this->db->selectOne("SELECT id FROM permissions WHERE slug = ?", [$slug]);
                if ($perm) {
                    $exists = $this->db->selectOne("SELECT 1 FROM role_permissions WHERE role_id = ? AND permission_id = ?", [$staffRole['id'], $perm['id']]);
                    if (!$exists) {
                        $this->db->insert('role_permissions', ['role_id' => $staffRole['id'], 'permission_id' => $perm['id']]);
                    }
                }
            }
        }
    }

    private function seedSuperAdmin(): void
    {
        $tenant = $this->db->selectOne("SELECT id FROM tenants WHERE slug = 'psnf'");
        $school = $this->db->selectOne("SELECT id FROM schools WHERE tenant_id = ? LIMIT 1", [$tenant['id']]);
        $branch = $this->db->selectOne("SELECT id FROM branches WHERE school_id = ? LIMIT 1", [$school['id']]);

        $exists = $this->db->selectOne("SELECT id FROM users WHERE email = 'admin@psnf.edu'");
        if (!$exists) {
            $userId = $this->db->insert('users', [
                'uuid'       => str_uuid(),
                'tenant_id'  => $tenant['id'],
                'school_id'  => $school['id'],
                'branch_id'  => $branch['id'],
                'name'       => 'Super Admin',
                'email'      => 'admin@psnf.edu',
                'password'   => password_hash('Admin@1234', PASSWORD_BCRYPT, ['cost' => 12]),
                'is_active'  => 1,
                'email_verified_at' => now(),
                'created_at' => now(),
            ]);

            $superRole = $this->db->selectOne("SELECT id FROM roles WHERE slug = 'super_admin' AND tenant_id IS NULL");
            if ($superRole) {
                $this->db->insert('user_roles', ['user_id' => $userId, 'role_id' => $superRole['id']]);
            }

            echo "\033[36mℹ  Super Admin: admin@psnf.edu / Admin@1234\033[0m\n";
        }
    }
}
