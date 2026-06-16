<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

class Guardian extends Model
{
    protected static string $table      = 'guardians';
    protected static bool   $tenantScope = false; // tenant_id already filtered manually

    public static function forStudent(int $studentId): array
    {
        return static::db()->select(
            "SELECT g.*, gs.is_primary, gs.can_pickup
             FROM guardians g
             JOIN guardian_student gs ON gs.guardian_id = g.id
             WHERE gs.student_id = ? AND g.deleted_at IS NULL
             ORDER BY gs.is_primary DESC",
            [$studentId]
        );
    }
}
