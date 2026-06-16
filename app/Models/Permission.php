<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

class Permission extends Model
{
    protected static string $table     = 'permissions';
    protected static bool   $softDelete = false;

    public static function allGroupedByModule(): array
    {
        $perms  = static::all('module, slug');
        $groups = [];
        foreach ($perms as $perm) {
            $groups[$perm['module']][] = $perm;
        }
        return $groups;
    }

    public static function forRole(int $roleId): array
    {
        return static::db()->select(
            "SELECT p.* FROM permissions p
             JOIN role_permissions rp ON rp.permission_id = p.id
             WHERE rp.role_id = ?",
            [$roleId]
        );
    }
}
