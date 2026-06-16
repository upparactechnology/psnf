<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

class StudentTimeline extends Model
{
    protected static string $table     = 'student_timeline';
    protected static bool   $softDelete = false;
    protected static bool   $timestamps = false;

    public static function logEvent(
        int    $studentId,
        string $eventType,
        string $title,
        array  $meta   = [],
        ?int   $actorId = null,
        string $color  = 'blue',
        string $icon   = 'circle'
    ): void {
        $user = auth();
        static::db()->insert('student_timeline', [
            'student_id'  => $studentId,
            'event_type'  => $eventType,
            'title'       => $title,
            'meta'        => json_encode($meta),
            'color'       => $color,
            'icon'        => $icon,
            'actor_id'    => $actorId ?? auth_id(),
            'actor_name'  => $user['name'] ?? 'System',
            'occurred_at' => now(),
            'created_at'  => now(),
        ]);
    }
}
