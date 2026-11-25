<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Works out break time from a day's face-kiosk scans.
 *
 * Scans are read in time order:
 *   first scan = clock in, last scan = clock out,
 *   scans in between are paired as (break out, break in).
 *   4 scans = one break, 6 scans = two breaks, ...
 *   An odd number of middle scans leaves the last one unmatched.
 *
 * Breaks are PAID: working hours stay clock_out - clock_in. Breaks are only
 * tracked and flagged when the day's total exceeds the allowed minutes.
 */
class StaffAttendanceCalculator
{
    private static ?int $globalAllowedCache = null;

    /** Global allowed break minutes from the shared policy row (see ShiftSettings::current()). */
    public static function globalAllowedMinutes(): int
    {
        if (self::$globalAllowedCache !== null) {
            return self::$globalAllowedCache;
        }
        $value = 30;
        try {
            // Same policy row as payroll / Staff Settings (current academic year's template)
            $row = ShiftSettings::current();
            if (isset($row['break_allowed_minutes']) && $row['break_allowed_minutes'] !== null) {
                $value = (int) $row['break_allowed_minutes'];
            }
        } catch (\Throwable $e) {
            // Column not migrated yet — fall back to default
        }
        return self::$globalAllowedCache = $value;
    }

    /** Allowed minutes for an employee: their override if set, else global. */
    public static function allowedMinutes($employeeOverride): int
    {
        if ($employeeOverride !== null && $employeeOverride !== '') {
            return (int) $employeeOverride;
        }
        return self::globalAllowedMinutes();
    }

    /**
     * @param string[]    $scanTimes   datetime strings of the day's scans (any order)
     * @param int         $allowed     allowed break minutes for the day
     * @param string|null $shiftEnd    'H:i:s' shift end, used for the live "on break" hint
     * @param string|null $date        'Y-m-d' of the scans (live hint only applies to today)
     */
    public static function summarizeBreaks(array $scanTimes, int $allowed, ?string $shiftEnd = null, ?string $date = null): array
    {
        $ts = array_values(array_filter(array_map(fn($t) => $t ? strtotime((string)$t) : false, $scanTimes)));
        sort($ts);
        $count = count($ts);

        $result = [
            'breaks'              => [],
            'total_break_minutes' => 0,
            'allowed_minutes'     => $allowed,
            'exceeded'            => false,
            'exceeded_by'         => 0,
            'unmatched'           => false,
            'on_break_since'      => null,
        ];

        // Live hint: today, even scan count, latest scan before shift end
        // -> the latest scan is most likely a break-out, not a clock-out.
        $isToday = $date !== null && $date === date('Y-m-d');
        if ($isToday && $count >= 2 && $count % 2 === 0 && $shiftEnd) {
            $last = $ts[$count - 1];
            if (date('H:i:s', $last) < $shiftEnd && date('H:i:s') < $shiftEnd) {
                // Latest scan is the open break-out; completed breaks are the
                // pairs between the first and that latest scan (handled below).
                $result['on_break_since'] = date('h:i A', $last);
            }
        }

        if ($count < 3) {
            return $result;
        }

        $middle = array_slice($ts, 1, $count - 2);
        return self::pairBreaks($middle, $result);
    }

    private static function pairBreaks(array $middle, array $result): array
    {
        $n = count($middle);
        for ($i = 0; $i + 1 < $n; $i += 2) {
            $mins = (int) round(($middle[$i + 1] - $middle[$i]) / 60);
            $result['breaks'][] = [
                'out'     => date('h:i A', $middle[$i]),
                'in'      => date('h:i A', $middle[$i + 1]),
                'minutes' => $mins,
            ];
            $result['total_break_minutes'] += $mins;
        }
        if ($n % 2 === 1) {
            $result['unmatched'] = true;
        }
        return self::applyLimit($result);
    }

    /** Break summary for a manual entry where admin typed break minutes. */
    public static function summarizeManual($breakMinutes, int $allowed): array
    {
        $mins = ($breakMinutes === null || $breakMinutes === '') ? 0 : (int) $breakMinutes;
        return self::applyLimit([
            'breaks'              => [],
            'total_break_minutes' => $mins,
            'allowed_minutes'     => $allowed,
            'exceeded'            => false,
            'exceeded_by'         => 0,
            'unmatched'           => false,
            'on_break_since'      => null,
        ]);
    }

    private static function applyLimit(array $result): array
    {
        $over = $result['total_break_minutes'] - $result['allowed_minutes'];
        $result['exceeded']    = $over > 0;
        $result['exceeded_by'] = max(0, $over);
        return $result;
    }
}
