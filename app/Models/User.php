<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

class User extends Model
{
    protected static string $table      = 'users';
    protected static bool   $tenantScope = true;

    public static function findByEmail(string $email, int $tenantId): array|false
    {
        return static::db()->selectOne(
            "SELECT * FROM users WHERE email = ? AND tenant_id = ? AND deleted_at IS NULL LIMIT 1",
            [$email, $tenantId]
        );
    }

    public static function findByEmailGlobal(string $email): array|false
    {
        return static::db()->selectOne(
            "SELECT * FROM users WHERE email = ? AND deleted_at IS NULL LIMIT 1",
            [$email]
        );
    }

    public static function withRoles(int $userId): array
    {
        $user = static::find($userId);
        if (!$user) return [];

        $roles = static::db()->select(
            "SELECT r.* FROM roles r
             JOIN user_roles ur ON ur.role_id = r.id
             WHERE ur.user_id = ? AND r.deleted_at IS NULL",
            [$userId]
        );

        $permissions = static::db()->select(
            "SELECT DISTINCT p.slug FROM permissions p
             JOIN role_permissions rp ON rp.permission_id = p.id
             JOIN user_roles ur ON ur.role_id = rp.role_id
             WHERE ur.user_id = ?",
            [$userId]
        );

        $user['roles']       = array_column($roles, 'slug');
        $user['role_names']  = array_column($roles, 'name');
        $user['permissions'] = array_column($permissions, 'slug');

        return $user;
    }

    public static function getRoles(int $userId): array
    {
        return static::db()->select(
            "SELECT r.* FROM roles r
             JOIN user_roles ur ON ur.role_id = r.id
             WHERE ur.user_id = ? AND r.deleted_at IS NULL",
            [$userId]
        );
    }

    public static function assignRole(int $userId, int $roleId): void
    {
        $exists = static::db()->selectOne(
            "SELECT 1 FROM user_roles WHERE user_id = ? AND role_id = ?",
            [$userId, $roleId]
        );
        if (!$exists) {
            static::db()->insert('user_roles', ['user_id' => $userId, 'role_id' => $roleId]);
        }
    }

    public static function revokeRole(int $userId, int $roleId): void
    {
        static::db()->query("DELETE FROM user_roles WHERE user_id = ? AND role_id = ?", [$userId, $roleId]);
    }

    public static function syncRoles(int $userId, array $roleIds): void
    {
        static::db()->query("DELETE FROM user_roles WHERE user_id = ?", [$userId]);
        foreach ($roleIds as $roleId) {
            static::db()->insert('user_roles', ['user_id' => $userId, 'role_id' => $roleId]);
        }
    }

    public static function incrementLoginAttempts(int $userId): void
    {
        static::db()->query("UPDATE users SET login_attempts = login_attempts + 1 WHERE id = ?", [$userId]);
    }

    public static function lockAccount(int $userId, int $minutes = 15): void
    {
        $lockedUntil = date('Y-m-d H:i:s', strtotime("+$minutes minutes"));
        static::db()->update('users', ['locked_until' => $lockedUntil, 'login_attempts' => 0], 'id = ?', [$userId]);
    }

    public static function resetLoginAttempts(int $userId): void
    {
        static::db()->update('users', ['login_attempts' => 0, 'locked_until' => null], 'id = ?', [$userId]);
    }

    public static function recordLogin(int $userId, string $ip): void
    {
        static::db()->update('users', [
            'last_login_at'      => now(),
            'last_login_ip'      => $ip,
            'login_attempts'     => 0,
            'locked_until'       => null,
        ], 'id = ?', [$userId]);
    }

    public static function isLocked(array $user): bool
    {
        return $user['locked_until'] && strtotime($user['locked_until']) > time();
    }

    public static function search(string $query, int $limit = 20): array
    {
        $like = "%$query%";
        [$tWhere, $tParams] = static::tenantWhere();
        $tWhere[] = "(name LIKE ? OR email LIKE ? OR phone LIKE ?)";
        $tParams  = array_merge($tParams, [$like, $like, $like]);
        $clause   = 'WHERE ' . implode(' AND ', $tWhere);
        return static::db()->select(
            "SELECT id, uuid, name, email, phone, avatar, is_active FROM users $clause LIMIT $limit",
            $tParams
        );
    }
}
