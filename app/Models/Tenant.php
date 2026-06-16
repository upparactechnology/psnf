<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

class Tenant extends Model
{
    protected static string $table = 'tenants';

    public static function findBySlug(string $slug): array|false
    {
        return static::findBy('slug', $slug);
    }

    public static function withStats(int $tenantId): array|false
    {
        return static::db()->selectOne(
            "SELECT t.*,
                (SELECT COUNT(*) FROM schools   WHERE tenant_id = t.id AND deleted_at IS NULL) as school_count,
                (SELECT COUNT(*) FROM branches  WHERE tenant_id = t.id AND deleted_at IS NULL) as branch_count,
                (SELECT COUNT(*) FROM users     WHERE tenant_id = t.id AND deleted_at IS NULL) as user_count,
                (SELECT COUNT(*) FROM students  WHERE tenant_id = t.id AND deleted_at IS NULL) as student_count
             FROM tenants t WHERE t.id = ? AND t.deleted_at IS NULL",
            [$tenantId]
        );
    }
}
