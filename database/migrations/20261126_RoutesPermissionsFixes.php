<?php

/**
 * Route / permission / schema fixes (idempotent):
 *  - Permissions referenced by routes but missing from `permissions`:
 *      view/create/edit/delete_student_parents, edit_payroll_attendance, edit_receipts_log,
 *      the Games module permissions of never-run migration 051, and the new
 *      view/create/edit/delete_scholarships. All granted to super_admin + school_admin.
 *  - Teacher role: revoke assign_roles and delete_certificates.
 *  - leave_applications.reviewed_by / reviewed_at (student leave approve/reject).
 *  - employees.last_updated_at (+ the live-tracking columns TransportController used to
 *    ALTER in at runtime) so driver GPS updates / live tracking work.
 */
class RoutesPermissionsFixes
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /** [name, slug, module] */
    private function permissions(): array
    {
        return [
            ['Student Parents – View',   'view_student_parents',   'Academic'],
            ['Student Parents – Create', 'create_student_parents', 'Academic'],
            ['Student Parents – Edit',   'edit_student_parents',   'Academic'],
            ['Student Parents – Delete', 'delete_student_parents', 'Academic'],
            ['Detailed Attendance – Edit', 'edit_payroll_attendance', 'Payroll'],
            ['Receipts Log – Edit',      'edit_receipts_log',      'Fees'],
            ['Scholarships – View',      'view_scholarships',      'Fees'],
            ['Scholarships – Create',    'create_scholarships',    'Fees'],
            ['Scholarships – Edit',      'edit_scholarships',      'Fees'],
            ['Scholarships – Delete',    'delete_scholarships',    'Fees'],
            // from 051_add_games_module_permissions (never applied)
            ['Games Overview – View',    'view_games_overview',    'Games'],
            ['Games – View',             'view_games',             'Games'],
            ['Games – Create',           'create_games',           'Games'],
            ['Games – Edit',             'edit_games',             'Games'],
            ['Games – Delete',           'delete_games',           'Games'],
        ];
    }

    private function hasColumn(string $table, string $column): bool
    {
        return !empty($this->db->select("SHOW COLUMNS FROM `$table` LIKE '$column'"));
    }

    private function hasTable(string $table): bool
    {
        return !empty($this->db->select("SHOW TABLES LIKE '$table'"));
    }

    public function up(): void
    {
        // 1. Permissions
        foreach ($this->permissions() as [$name, $slug, $module]) {
            $exists = $this->db->selectOne("SELECT id FROM permissions WHERE slug = ? LIMIT 1", [$slug]);
            if ($exists) {
                continue;
            }
            // `name` is UNIQUE too — make it unique if another permission already uses it
            $finalName = $name;
            if ($this->db->selectOne("SELECT id FROM permissions WHERE name = ? LIMIT 1", [$finalName])) {
                $finalName = $name . ' (' . $slug . ')';
            }
            $this->db->query(
                "INSERT INTO permissions (name, slug, module, created_at) VALUES (?, ?, ?, NOW())",
                [$finalName, $slug, $module]
            );
        }

        // 2. Grant to super_admin + school_admin
        $slugs = array_column($this->permissions(), 1);
        $ph = implode(',', array_fill(0, count($slugs), '?'));
        $permIds = $this->db->select("SELECT id FROM permissions WHERE slug IN ($ph)", $slugs);
        $roles = $this->db->select("SELECT id FROM roles WHERE slug IN ('super_admin', 'school_admin')");
        foreach ($roles as $role) {
            foreach ($permIds as $perm) {
                $this->db->query(
                    "INSERT IGNORE INTO role_permissions (role_id, permission_id, created_at) VALUES (?, ?, NOW())",
                    [(int) $role['id'], (int) $perm['id']]
                );
            }
        }

        // 3. Teacher role must not manage roles or delete certificates
        $this->db->query(
            "DELETE rp FROM role_permissions rp
             JOIN roles r ON r.id = rp.role_id
             JOIN permissions p ON p.id = rp.permission_id
             WHERE r.slug = 'teacher' AND p.slug IN ('assign_roles', 'delete_certificates')"
        );

        // 4. Leave approval audit columns
        if ($this->hasTable('leave_applications')) {
            if (!$this->hasColumn('leave_applications', 'reviewed_by')) {
                $this->db->query("ALTER TABLE `leave_applications` ADD COLUMN `reviewed_by` INT UNSIGNED NULL DEFAULT NULL AFTER `status`");
            }
            if (!$this->hasColumn('leave_applications', 'reviewed_at')) {
                $this->db->query("ALTER TABLE `leave_applications` ADD COLUMN `reviewed_at` DATETIME NULL DEFAULT NULL AFTER `reviewed_by`");
            }
        }

        // 5. Live-tracking columns on employees (drivers are employees)
        $cols = [
            'route_status'      => "VARCHAR(20) DEFAULT 'idle'",
            'current_latitude'  => 'DECIMAL(10,7) NULL',
            'current_longitude' => 'DECIMAL(10,7) NULL',
            'current_speed'     => 'DECIMAL(5,2) DEFAULT 0',
            'eta_minutes'       => 'INT DEFAULT 0',
            'remaining_km'      => 'DECIMAL(6,2) DEFAULT 0',
            'last_updated_at'   => 'TIMESTAMP NULL DEFAULT NULL',
        ];
        foreach ($cols as $col => $def) {
            if (!$this->hasColumn('employees', $col)) {
                $this->db->query("ALTER TABLE `employees` ADD COLUMN `$col` $def");
            }
        }
    }

    public function down(): void
    {
        // Permissions added here (only those not created by earlier migrations)
        $slugs = ['view_student_parents', 'create_student_parents', 'edit_student_parents', 'delete_student_parents',
                  'edit_payroll_attendance', 'edit_receipts_log',
                  'view_scholarships', 'create_scholarships', 'edit_scholarships', 'delete_scholarships'];
        $ph = implode(',', array_fill(0, count($slugs), '?'));
        $this->db->query("DELETE rp FROM role_permissions rp JOIN permissions p ON p.id = rp.permission_id WHERE p.slug IN ($ph)", $slugs);
        $this->db->query("DELETE FROM permissions WHERE slug IN ($ph)", $slugs);

        // Restore the teacher grants that up() revoked
        $teacher = $this->db->selectOne("SELECT id FROM roles WHERE slug = 'teacher' LIMIT 1");
        if ($teacher) {
            foreach ($this->db->select("SELECT id FROM permissions WHERE slug IN ('assign_roles', 'delete_certificates')") as $p) {
                $this->db->query("INSERT IGNORE INTO role_permissions (role_id, permission_id, created_at) VALUES (?, ?, NOW())", [(int) $teacher['id'], (int) $p['id']]);
            }
        }

        if ($this->hasTable('leave_applications')) {
            if ($this->hasColumn('leave_applications', 'reviewed_at')) {
                $this->db->query("ALTER TABLE `leave_applications` DROP COLUMN `reviewed_at`");
            }
            if ($this->hasColumn('leave_applications', 'reviewed_by')) {
                $this->db->query("ALTER TABLE `leave_applications` DROP COLUMN `reviewed_by`");
            }
        }
        if ($this->hasColumn('employees', 'last_updated_at')) {
            $this->db->query("ALTER TABLE `employees` DROP COLUMN `last_updated_at`");
        }
        // Games permissions and the other employees tracking columns predate this migration's
        // intent (051 / runtime ALTERs) and are left in place.
    }
}
