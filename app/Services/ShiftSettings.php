<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Single source of truth for the staff shift / payroll policy row (shift_templates).
 *
 * Staff Settings saves one template per academic year. Everything that reads the
 * policy must use the same row:
 *   1. the template of the 'current' academic year (status = 'current'),
 *   2. else the template of the newest academic year that has one,
 *   3. else template id = 1 (legacy global row),
 *   4. else the oldest template.
 */
class ShiftSettings
{
    /** @var array<string, array> */
    private static array $cache = [];

    public static function current(?int $tenantId = null): array
    {
        $tenantId = $tenantId ?? \Core\Database::getTenantId();
        $key = (string) ($tenantId ?? 'any');
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        $db = \Core\Application::$app->db;
        $row = false;
        try {
            $sql = "SELECT st.*
                    FROM shift_templates st
                    JOIN academic_years ay ON ay.id = st.academic_year_id
                    " . ($tenantId ? "WHERE st.tenant_id = ?" : "") . "
                    ORDER BY (ay.status = 'current') DESC, ay.id DESC, st.id DESC
                    LIMIT 1";
            $row = $db->selectOne($sql, $tenantId ? [$tenantId] : []);
        } catch (\Throwable $e) {
            $row = false; // academic_year_id column / academic_years table missing
        }
        if (!$row) {
            $row = $db->selectOne("SELECT * FROM shift_templates WHERE id = 1");
        }
        if (!$row) {
            $row = $db->selectOne("SELECT * FROM shift_templates ORDER BY id ASC LIMIT 1");
        }

        return self::$cache[$key] = self::withDefaults($row ?: []);
    }

    /** Forget cached rows (call after the settings are saved). */
    public static function flush(): void
    {
        self::$cache = [];
    }

    private static function withDefaults(array $row): array
    {
        return $row + [
            'id'                         => null,
            'start_time'                 => '09:00:00',
            'end_time'                   => '17:00:00',
            'grace_minutes'              => 15,
            'half_day_after'             => '12:00:00',
            'working_days_json'          => '{}',
            'late_limit_count'           => 3,
            'late_deduction_percent'     => 10.00,
            'half_day_deduction_percent' => 50.00,
            'break_allowed_minutes'      => 30,
        ];
    }

    /** Normalise 'H:i' / 'H:i:s' to 'H:i:s'. */
    public static function hms(?string $time, string $fallback): string
    {
        $time = trim((string) $time);
        if ($time === '') {
            return $fallback;
        }
        $ts = strtotime('1970-01-01 ' . $time . ' UTC');
        return $ts === false ? $fallback : gmdate('H:i:s', $ts);
    }

    /**
     * Shift rules for one employee. $employee needs min_clock_in, max_clock_out and
     * user_grace_period (users.grace_period, NULL when no linked user / not set).
     *
     *  start     = employees.min_clock_in if set, else template start_time
     *  grace     = users.grace_period if not NULL, else template grace_minutes
     *  late_after= start + grace (check-in strictly after this is late)
     *  half_day  = template half_day_after when start == template start,
     *              else start + (template half_day_after - template start_time)
     */
    public static function rulesFor(array $employee, ?array $shift = null): array
    {
        $shift = $shift ?? self::current();
        $tplStart = self::hms($shift['start_time'] ?? null, '09:00:00');
        $tplHalf  = self::hms($shift['half_day_after'] ?? null, '12:00:00');
        $tplEnd   = self::hms($shift['end_time'] ?? null, '17:00:00');

        $start = self::hms($employee['min_clock_in'] ?? null, $tplStart);
        $end   = self::hms($employee['max_clock_out'] ?? null, $tplEnd);

        $userGrace = $employee['user_grace_period'] ?? null;
        $grace = ($userGrace !== null && $userGrace !== '') ? (int) $userGrace : (int) ($shift['grace_minutes'] ?? 15);

        $toSecs = static fn(string $hms): int => (int) substr($hms, 0, 2) * 3600 + (int) substr($hms, 3, 2) * 60 + (int) substr($hms, 6, 2);
        $fmt    = static fn(int $s): string => sprintf('%02d:%02d:%02d', intdiv(max(0, min($s, 86399)), 3600), intdiv(max(0, min($s, 86399)) % 3600, 60), max(0, min($s, 86399)) % 60);

        if ($start === $tplStart) {
            $halfDay = $tplHalf;
        } else {
            $halfDay = $fmt($toSecs($start) + ($toSecs($tplHalf) - $toSecs($tplStart)));
        }

        return [
            'start'          => $start,
            'end'            => $end,
            'grace'          => $grace,
            'late_after'     => $fmt($toSecs($start) + $grace * 60),
            'half_day_after' => $halfDay,
        ];
    }

    /** 'present' | 'late' | 'half_day' for a check-in time ('H:i:s' or datetime). */
    public static function classifyCheckIn(string $checkIn, array $rules): string
    {
        $t = strlen($checkIn) > 8 ? date('H:i:s', strtotime($checkIn)) : self::hms($checkIn, '00:00:00');
        if ($t > $rules['half_day_after']) {
            return 'half_day';
        }
        if ($t > $rules['late_after']) {
            return 'late';
        }
        return 'present';
    }

    /** Minutes after the late threshold (0 when on time). */
    public static function lateMinutes(string $checkIn, array $rules): int
    {
        $t = strlen($checkIn) > 8 ? date('H:i:s', strtotime($checkIn)) : self::hms($checkIn, '00:00:00');
        if ($t <= $rules['late_after']) {
            return 0;
        }
        return (int) round((strtotime('1970-01-01 ' . $t) - strtotime('1970-01-01 ' . $rules['late_after'])) / 60);
    }

    /** Configured working days for a month (0 = not configured). */
    public static function configuredWorkingDays(int $month, ?array $shift = null): int
    {
        $shift = $shift ?? self::current();
        $map = json_decode((string) ($shift['working_days_json'] ?? '{}'), true) ?: [];
        return (int) ($map[sprintf('%02d', $month)] ?? 0);
    }
}
