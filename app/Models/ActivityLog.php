<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

class ActivityLog extends Model
{
    protected static string $table     = 'activity_logs';
    protected static bool   $softDelete = false;
    protected static bool   $timestamps = false;

    public static function log(
        string $event,
        ?int   $userId = null,
        array  $properties = [],
        ?string $model = null,
        ?string $modelId = null,
        ?string $description = null
    ): void {
        $request = \Core\Application::$app->request ?? null;

        static::db()->insert('activity_logs', [
            'tenant_id'   => \Core\Database::getTenantId() ?? session('user.tenant_id'),
            'school_id'   => \Core\Database::getSchoolId() ?? session('user.school_id'),
            'branch_id'   => \Core\Database::getBranchId() ?? session('user.branch_id'),
            'user_id'     => $userId ?? auth_id(),
            'event'       => $event,
            'model'       => $model,
            'model_id'    => $modelId,
            'description' => $description,
            'properties'  => json_encode($properties),
            'ip_address'  => $request?->ip() ?? ($_SERVER['REMOTE_ADDR'] ?? null),
            'user_agent'  => $request?->userAgent() ?? ($_SERVER['HTTP_USER_AGENT'] ?? null),
            'created_at'  => now(),
        ]);
    }

    public static function forUser(int $userId, int $limit = 50): array
    {
        return static::db()->select(
            "SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT $limit",
            [$userId]
        );
    }

    public static function recent(int $limit = 100): array
    {
        $tenantId = \Core\Database::getTenantId();
        $sql    = "SELECT al.*, u.name as user_name, u.email as user_email
                   FROM activity_logs al
                   LEFT JOIN users u ON u.id = al.user_id
                   WHERE al.tenant_id = ?
                   ORDER BY al.created_at DESC LIMIT $limit";
        return static::db()->select($sql, [$tenantId]);
    }
}
