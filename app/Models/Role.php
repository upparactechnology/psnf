<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

class Role extends Model
{
    protected static string $table = 'roles';

    public static function withPermissions(int $roleId): array|false
    {
        $role = static::find($roleId);
        if (!$role) return false;

        $perms = static::db()->select(
            "SELECT p.* FROM permissions p
             JOIN role_permissions rp ON rp.permission_id = p.id
             WHERE rp.role_id = ?
             ORDER BY p.module, p.slug",
            [$roleId]
        );

        $role['permissions']     = $perms;
        $role['permission_ids']  = array_column($perms, 'id');
        $role['permission_slugs']= array_column($perms, 'slug');

        return $role;
    }

    public static function syncPermissions(int $roleId, array $permissionIds): void
    {
        static::db()->query("DELETE FROM role_permissions WHERE role_id = ?", [$roleId]);
        foreach ($permissionIds as $permId) {
            static::db()->insert('role_permissions', ['role_id' => $roleId, 'permission_id' => $permId]);
        }
    }

    public static function allWithPermissionCount(): array
    {
        return static::db()->select(
            "SELECT r.*, COUNT(rp.permission_id) as permission_count
             FROM roles r
             LEFT JOIN role_permissions rp ON rp.role_id = r.id
             WHERE r.deleted_at IS NULL
             GROUP BY r.id
             ORDER BY r.sort_order, r.name"
        );
    }
}
