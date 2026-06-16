<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

class Student extends Model
{
    protected static string $table      = 'students';
    protected static bool   $tenantScope = true;

    public static function withDetails(int $studentId): array|false
    {
        $student = static::find($studentId);
        if (!$student) return false;

        $student['medical']            = StudentMedical::findBy('student_id', $studentId) ?: [];
        $student['emergency_contacts'] = EmergencyContact::where('student_id = ?', [$studentId], 'priority ASC');
        $student['documents']          = StudentDocument::where('student_id = ?', [$studentId]);
        $student['guardians']          = static::getGuardians($studentId);
        $student['timeline']           = StudentTimeline::where('student_id = ?', [$studentId], 'occurred_at DESC');

        return $student;
    }

    public static function getGuardians(int $studentId): array
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

    public static function search(string $query, array $filters = [], int $perPage = 15, int $page = 1): array
    {
        $like   = "%$query%";
        [$tWhere, $tParams] = static::tenantWhere('s');

        if ($query) {
            $tWhere[] = "(s.first_name LIKE ? OR s.last_name LIKE ? OR s.admission_number LIKE ? OR s.gr_number LIKE ?)";
            $tParams  = array_merge($tParams, [$like, $like, $like, $like]);
        }

        if (!empty($filters['status'])) {
            $tWhere[] = 's.admission_status = ?';
            $tParams[] = $filters['status'];
        }

        if (!empty($filters['disability'])) {
            $tWhere[] = 's.disability_type = ?';
            $tParams[] = $filters['disability'];
        }

        $clause = $tWhere ? 'WHERE ' . implode(' AND ', $tWhere) : '';
        $offset = ($page - 1) * $perPage;

        $total = (int) (static::db()->selectOne(
            "SELECT COUNT(*) as cnt FROM students s $clause", $tParams
        )['cnt'] ?? 0);

        $data = static::db()->select(
            "SELECT s.*, b.name as branch_name, sc.name as school_name
             FROM students s
             LEFT JOIN branches b  ON b.id  = s.branch_id
             LEFT JOIN schools  sc ON sc.id = s.school_id
             $clause
             ORDER BY s.created_at DESC
             LIMIT $perPage OFFSET $offset",
            $tParams
        );

        return [
            'data'         => $data,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / $perPage),
        ];
    }

    public static function statusCounts(): array
    {
        $tid = \Core\Database::getTenantId();
        $rows = static::db()->select(
            "SELECT admission_status, COUNT(*) as cnt
             FROM students
             WHERE tenant_id = ? AND deleted_at IS NULL
             GROUP BY admission_status",
            [$tid]
        );
        $counts = [];
        foreach ($rows as $row) {
            $counts[$row['admission_status']] = (int) $row['cnt'];
        }
        return $counts;
    }

    public static function updateStatus(int $studentId, string $status, int $actorId): void
    {
        $student = static::find($studentId);
        if (!$student) return;

        static::update($studentId, [
            'admission_status' => $status,
            'updated_by'       => $actorId,
        ]);

        StudentTimeline::logEvent($studentId, 'status_change', "Status changed to " . ucfirst(str_replace('_', ' ', $status)), [
            'from' => $student['admission_status'],
            'to'   => $status,
        ], $actorId);
    }
}
