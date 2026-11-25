<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;
use Core\Session;
use App\Services\ShiftSettings;

class StaffAppController extends Controller
{
    private function db()
    {
        return Application::$app->db;
    }

    private function respondJson(array $data, int $status = 200): string
    {
        http_response_code($status);
        header('Content-Type: application/json');
        return json_encode($data);
    }

    private function getAuthenticatedUserId(): ?int
    {
        return auth_id();
    }

    /** Roles allowed to use the staff app API. */
    private const STAFF_ROLES = ['teacher', 'staff', 'therapist', 'school_admin', 'super_admin'];

    /**
     * Guard for every staff-app action: 401 when not logged in, 403 unless the user
     * holds a staff role (checked against the session/JWT roles and user_roles).
     * Returns a JSON response to send, or null when allowed.
     */
    private function denyUnlessStaff(): ?string
    {
        $userId = $this->getAuthenticatedUserId();
        if (!$userId) {
            return $this->respondJson(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $norm = static fn($r) => str_replace([' ', '-'], '_', strtolower(trim((string) $r)));
        $roles = array_map($norm, (array) (auth()['roles'] ?? []));
        try {
            $dbRoles = $this->db()->select(
                "SELECT r.slug FROM user_roles ur JOIN roles r ON r.id = ur.role_id WHERE ur.user_id = ?",
                [$userId]
            );
            $roles = array_merge($roles, array_map($norm, array_column($dbRoles, 'slug')));
        } catch (\Throwable $e) {
        }

        if (!array_intersect(self::STAFF_ROLES, $roles)) {
            return $this->respondJson(['success' => false, 'message' => 'Forbidden: staff access only.'], 403);
        }
        return null;
    }

    private function userTenantId(int $userId): ?int
    {
        $u = $this->db()->selectOne("SELECT tenant_id FROM users WHERE id = ? LIMIT 1", [$userId]);
        return $u ? (int) $u['tenant_id'] : null;
    }

    /** Student row when it belongs to the caller's tenant, else null. */
    private function tenantStudent(int $userId, int $studentId): ?array
    {
        if ($studentId <= 0) return null;
        $tenantId = $this->userTenantId($userId);
        $stu = $this->db()->selectOne(
            "SELECT id, tenant_id, school_id, branch_id FROM students WHERE id = ? AND tenant_id = ? AND deleted_at IS NULL LIMIT 1",
            [$studentId, $tenantId]
        );
        return $stu ?: null;
    }

    /**
     * Shift rules for a login user, identical to payroll: the linked employee record
     * (employees.user_id, else users.employee_id = employees.emp_code) + users.grace_period
     * + the current academic year's shift template.
     */
    private function shiftRulesForUser(array $user): array
    {
        $employee = $this->db()->selectOne(
            "SELECT e.*, u.grace_period AS user_grace_period FROM employees e LEFT JOIN users u ON u.id = e.user_id WHERE e.user_id = ? LIMIT 1",
            [(int) $user['id']]
        );
        if (!$employee && !empty($user['employee_id'])) {
            $employee = $this->db()->selectOne("SELECT * FROM employees WHERE emp_code = ? LIMIT 1", [$user['employee_id']]);
        }
        $base = $employee ?: [];
        $base['user_grace_period'] = $user['grace_period'] ?? ($base['user_grace_period'] ?? null);
        return ShiftSettings::rulesFor($base);
    }


    public function getTodayAttendance(): string
    {
        if ($deny = $this->denyUnlessStaff()) {
            return $deny;
        }
        $userId = (int) $this->getAuthenticatedUserId();

        $today = date('Y-m-d');
        $record = $this->db()->selectOne(
            "SELECT * FROM `teacher_attendance` WHERE `user_id` = ? AND `attendance_date` = ? LIMIT 1",
            [$userId, $today]
        );

        if (!$record) {
            $faceRecords = $this->db()->select(
                "SELECT * FROM `attendance` WHERE `user_id` = ? AND (DATE(check_in) = ? OR `attendance_date` = ?) ORDER BY check_in ASC",
                [$userId, $today, $today]
            );

            if (!empty($faceRecords)) {
                $first = $faceRecords[0];
                $last = count($faceRecords) > 1 ? $faceRecords[count($faceRecords)-1] : null;
                // Same shift rules as payroll
                $user = $this->db()->selectOne("SELECT * FROM users WHERE id = ? LIMIT 1", [$userId]) ?: ['id' => $userId];
                $rules = $this->shiftRulesForUser($user);
                $cls = ShiftSettings::classifyCheckIn((string) $first['check_in'], $rules);
                $record = [
                    'opened_at' => $first['check_in'],
                    'checkout_at' => $last ? $last['check_in'] : null,
                    'status' => $cls === 'present' ? 'on_time' : $cls,
                    'lecture_time' => $rules['start']
                ];
            } else {
                return $this->respondJson([
                    'success' => true,
                    'checked_in' => false,
                    'check_in_time' => null,
                    'check_out_time' => null,
                    'total_working' => "--:--",
                    'status' => "Not Checked In",
                    'face_attendance_time' => null,
                    'is_late' => false,
                    'late_minutes' => 0,
                    'warning_message' => null
                ]);
            }
        }

        $checkInTime = date('h:i A', strtotime($record['opened_at']));
        $checkOutTime = $record['checkout_at'] ? date('h:i A', strtotime($record['checkout_at'])) : null;
        $faceTime = date('h:i:s A', strtotime($record['opened_at']));
        
        // Calculate working hours
        $totalWorking = "--:--";
        if ($record['opened_at']) {
            $start = strtotime($record['opened_at']);
            $end = $record['checkout_at'] ? strtotime($record['checkout_at']) : time();
            $diffSeconds = $end - $start;
            if ($diffSeconds > 0) {
                $hours = floor($diffSeconds / 3600);
                $minutes = floor(($diffSeconds % 3600) / 60);
                $totalWorking = sprintf('%02d:%02d', $hours, $minutes);
            }
        }

        $isLate = ($record['status'] === 'late');
        $statusText = $isLate ? 'Late' : ($record['status'] === 'half_day' ? 'Half Day' : 'Present');
        
        $lectureTimeStr = $record['lecture_time'] ?: '09:00:00';
        $lectureTimestamp = strtotime(date('Y-m-d ') . $lectureTimeStr);
        $checkInTimestamp = strtotime($record['opened_at']);
        $lateSeconds = max(0, $checkInTimestamp - $lectureTimestamp);
        $lateMinutes = (int)ceil($lateSeconds / 60);

        $warningMessage = null;
        if ($isLate || $record['status'] === 'half_day') {
            $warningMessage = "Late Clock-In Warning: Recorded at $checkInTime ($lateMinutes mins past scheduled " . date('h:i A', $lectureTimestamp) . " start time).";
        }

        return $this->respondJson([
            'success' => true,
            'checked_in' => true,
            'check_in_time' => $checkInTime,
            'check_out_time' => $checkOutTime,
            'total_working' => $totalWorking,
            'status' => $statusText,
            'face_attendance_time' => "Face Verified at $faceTime",
            'is_late' => $isLate,
            'late_minutes' => $lateMinutes,
            'warning_message' => $warningMessage
        ]);
    }

    public function checkIn(): string
    {
        if ($deny = $this->denyUnlessStaff()) {
            return $deny;
        }
        $userId = (int) $this->getAuthenticatedUserId();

        $today = date('Y-m-d');
        $existing = $this->db()->selectOne(
            "SELECT * FROM `teacher_attendance` WHERE `user_id` = ? AND `attendance_date` = ? LIMIT 1",
            [$userId, $today]
        );

        if ($existing) {
            return $this->getTodayAttendance();
        }

        $user = $this->db()->selectOne(
            "SELECT * FROM `users` WHERE `id` = ? LIMIT 1",
            [$userId]
        );

        if (!$user) {
            return $this->respondJson(['success' => false, 'message' => 'User not found'], 404);
        }

        // Same shift rules as payroll / attendance screens
        $rules = $this->shiftRulesForUser($user);
        $empShiftStart = $rules['start'];
        $gracePeriod = $rules['grace'];
        $cls = ShiftSettings::classifyCheckIn(date('H:i:s'), $rules);
        $status = $cls === 'present' ? 'on_time' : $cls; // on_time | late | half_day

        $this->db()->insert('teacher_attendance', [
            'tenant_id' => $user['tenant_id'],
            'school_id' => $user['school_id'],
            'branch_id' => $user['branch_id'],
            'user_id' => $userId,
            'attendance_date' => $today,
            'opened_at' => now(),
            'status' => $status,
            'lecture_time' => $empShiftStart,
            'grace_period' => $gracePeriod
        ]);

        return $this->getTodayAttendance();
    }

    public function checkOut(): string
    {
        if ($deny = $this->denyUnlessStaff()) {
            return $deny;
        }
        $userId = (int) $this->getAuthenticatedUserId();

                $today = date('Y-m-d');
        $record = $this->db()->selectOne(
            "SELECT id, opened_at FROM `teacher_attendance` WHERE `user_id` = ? AND `attendance_date` = ? LIMIT 1",
            [$userId, $today]
        );

        if (!$record) {
            return $this->respondJson(['success' => false, 'message' => 'Please check in first before checking out.'], 400);
        }

        if (strtotime($record['opened_at']) + (10 * 60) > time()) {
            return $this->respondJson(['success' => false, 'message' => 'You can only check out 10 minutes after checking in.'], 400);
        }

        $this->db()->update('teacher_attendance', [
            'checkout_at' => now()
        ], 'id = ?', [$record['id']]);

        return $this->getTodayAttendance();
    }

    public function getAttendanceHistory(): string
    {
        if ($deny = $this->denyUnlessStaff()) {
            return $deny;
        }
        $userId = (int) $this->getAuthenticatedUserId();

        $records = $this->db()->select(
            "SELECT * FROM `teacher_attendance` WHERE `user_id` = ? ORDER BY `attendance_date` DESC LIMIT 30",
            [$userId]
        );

        $faceRecordsRaw = $this->db()->select(
            "SELECT *, DATE(check_in) as a_date FROM `attendance` WHERE `user_id` = ? AND check_in IS NOT NULL ORDER BY check_in ASC",
            [$userId]
        );

        $faceGrouped = [];
        foreach ($faceRecordsRaw as $f) {
            $date = $f['attendance_date'] ?: $f['a_date'];
            if (!isset($faceGrouped[$date])) {
                $faceGrouped[$date] = ['min' => $f['check_in'], 'max' => $f['check_in']];
            } else {
                $faceGrouped[$date]['max'] = $f['check_in'];
            }
        }

        $mergedData = [];
        foreach ($records as $r) {
            $mergedData[$r['attendance_date']] = [
                'opened_at' => $r['opened_at'],
                'checkout_at' => $r['checkout_at'],
                'status' => $r['status']
            ];
        }

        $rules = null;
        foreach ($faceGrouped as $date => $f) {
            if (!isset($mergedData[$date])) {
                if ($rules === null) {
                    $user = $this->db()->selectOne("SELECT * FROM users WHERE id = ? LIMIT 1", [$userId]) ?: ['id' => $userId];
                    $rules = $this->shiftRulesForUser($user);
                }
                $cls = ShiftSettings::classifyCheckIn((string) $f['min'], $rules);
                $mergedData[$date] = [
                    'opened_at' => $f['min'],
                    'checkout_at' => $f['max'] != $f['min'] ? $f['max'] : null,
                    'status' => $cls === 'present' ? 'on_time' : $cls
                ];
            }
        }

        krsort($mergedData); // Sort descending by date
        $mergedData = array_slice($mergedData, 0, 30); // Limit to 30

        $history = [];
        foreach ($mergedData as $date => $r) {
            $dateFormatted = date('d M Y (D)', strtotime($date));
            $checkInTime = date('h:i A', strtotime($r['opened_at']));
            $checkOutTime = $r['checkout_at'] ? date('h:i A', strtotime($r['checkout_at'])) : '--:--';
            
            $totalWorking = '--:--';
            if ($r['opened_at']) {
                $start = strtotime($r['opened_at']);
                $end = $r['checkout_at'] ? strtotime($r['checkout_at']) : time();
                $diff = $end - $start;
                if ($diff > 0) {
                    $hours = floor($diff / 3600);
                    $minutes = floor(($diff % 3600) / 60);
                    $totalWorking = sprintf('%02d:%02d', $hours, $minutes);
                }
            }

            $statusText = $r['status'] === 'late' ? 'Late' : ($r['status'] === 'half_day' ? 'Half Day' : 'Present');

            $history[] = [
                'date' => $dateFormatted,
                'raw_date' => $date,
                'check_in' => $checkInTime,
                'check_out' => $checkOutTime,
                'total_working' => $totalWorking,
                'status' => $statusText,
                'is_late' => $r['status'] === 'late',
            ];
        }

        // Mock data removed for strict accurate database records
        return $this->respondJson([
            'success' => true,
            'history' => $history
        ]);
    }

        public function getClassAttendance(): string
    {
        if ($deny = $this->denyUnlessStaff()) {
            return $deny;
        }
        $userId = (int) $this->getAuthenticatedUserId();

        $today = date('Y-m-d');

        // Get classes assigned to this teacher (as class teacher)
        $assignedClasses = $this->db()->select(
            "SELECT name, section FROM classes WHERE class_teacher_id = ?",
            [$userId]
        );

        // Build class name list for filtering (e.g. "3-A", "4-B")
        $classNames = [];
        foreach ($assignedClasses as $ac) {
            $name = trim(($ac['name'] ?? '') . ' ' . ($ac['section'] ?? ''));
            if ($name !== '') {
                $classNames[] = $name;
            }
        }

        // If teacher has no assigned classes, return empty list
        if (empty($classNames)) {
            return $this->respondJson([
                'success' => true,
                'classes' => [],
                'students' => [],
                'assigned_classes' => []
            ]);
        }

        // Fetch students only from assigned classes, left join with today's attendance
        $placeholders = implode(',', array_fill(0, count($classNames), '?'));
        $records = $this->db()->select("
            SELECT s.id, s.first_name, s.last_name, s.photo, s.class, 
                   COALESCE(a.status, 'pending') as status
            FROM students s 
            LEFT JOIN attendance a ON a.student_id = s.id AND a.date = ?
            WHERE s.deleted_at IS NULL AND s.is_active = 1 AND s.class IN ($placeholders)
            ORDER BY s.class ASC, s.first_name ASC
        ", array_merge([$today], $classNames));

        $students = [];
        $classes = [];
        
        foreach ($records as $r) {
            $className = $r['class'] ?: 'Unassigned';
            if (!in_array($className, $classes)) {
                $classes[] = $className;
            }
            
            $students[] = [
                'id' => $r['id'],
                'name' => $r['first_name'] . ' ' . $r['last_name'],
                'class' => $className,
                'status' => $r['status'],
                'photo' => $r['photo'] ? url('uploads/' . $r['photo']) : null
            ];
        }

        return $this->respondJson([
            'success' => true,
            'classes' => $classes,
            'students' => $students,
            'assigned_classes' => $classNames
        ]);
    }

    public function markStudentAttendance(): string
    {
        if ($deny = $this->denyUnlessStaff()) {
            return $deny;
        }
        $userId = (int) $this->getAuthenticatedUserId();

        $input = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($input)) {
            $input = $this->request->getBody();
        }
        $studentId = (int) ($input['student_id'] ?? 0);
        $status = (string) ($input['status'] ?? ''); // present | absent | late | half_day

        if (!$studentId || $status === '') {
            return $this->respondJson(['success' => false, 'message' => 'Missing parameters'], 400);
        }
        if (!in_array($status, ['present', 'absent', 'late', 'half_day'], true)) {
            return $this->respondJson(['success' => false, 'message' => 'Invalid status'], 422);
        }

        // The student must belong to the caller's school (tenant)
        $stu = $this->tenantStudent($userId, $studentId);
        if (!$stu) {
            return $this->respondJson(['success' => false, 'message' => 'Student not found'], 404);
        }

        $today = date('Y-m-d');
        $existing = $this->db()->selectOne("SELECT id FROM attendance WHERE student_id = ? AND date = ?", [$studentId, $today]);

        if ($existing) {
            $this->db()->update('attendance', [
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ], "id = ?", [$existing['id']]);
        } else {
            $this->db()->insert('attendance', [
                'tenant_id' => $stu['tenant_id'],
                'school_id' => $stu['school_id'],
                'branch_id' => $stu['branch_id'],
                'student_id' => $studentId,
                'date' => $today,
                'status' => $status,
                'remarks' => 'Marked by teacher',
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $userId
            ]);
        }

        return $this->respondJson(['success' => true]);
    }

    public function getSummary(): string
    {
        if ($deny = $this->denyUnlessStaff()) {
            return $deny;
        }
        $userId = (int) $this->getAuthenticatedUserId();

        $tenantId = $this->userTenantId($userId) ?? 0;
        $today = date('Y-m-d');

        $totalCount = (int)$this->db()->selectOne("SELECT COUNT(*) as c FROM students WHERE tenant_id = ? AND deleted_at IS NULL", [$tenantId])['c'];
        $presentCount = (int)$this->db()->selectOne("SELECT COUNT(*) as c FROM attendance WHERE tenant_id = ? AND date = ? AND student_id IS NOT NULL AND status IN ('present', 'late', 'half_day')", [$tenantId, $today])['c'];
        $absentCount = (int)$this->db()->selectOne("SELECT COUNT(*) as c FROM attendance WHERE tenant_id = ? AND date = ? AND student_id IS NOT NULL AND status = 'absent'", [$tenantId, $today])['c'];

        return $this->respondJson([
            'success' => true,
            'summary' => [
                'total' => $totalCount,
                'present' => $presentCount,
                'absent' => $absentCount
            ]
        ]);
    }

    /**
     * Guardians of the caller's school, optionally for one student (?student_id=).
     * Aadhaar numbers are never returned in full: only a masked form (last 4 digits)
     * so a teacher can still match an ID card at early pickup.
     */
    public function getGuardians(): string
    {
        if ($deny = $this->denyUnlessStaff()) {
            return $deny;
        }
        $userId = (int) $this->getAuthenticatedUserId();

        $tenantId = $this->userTenantId($userId) ?? 0;
        $studentId = (int) $this->request->get('student_id', 0);

        $sql = "
            SELECT g.id as guardian_id, g.name as g_name, g.phone, g.relationship, g.aadhar, g.photo,
                   gs.can_pickup,
                   s.id as student_id, s.first_name as s_first, s.last_name as s_last, s.class
            FROM guardians g
            JOIN guardian_student gs ON g.id = gs.guardian_id
            JOIN students s ON gs.student_id = s.id AND s.tenant_id = g.tenant_id AND s.deleted_at IS NULL
            WHERE g.tenant_id = ? AND g.deleted_at IS NULL";
        $params = [$tenantId];
        if ($studentId > 0) {
            $sql .= " AND s.id = ?";
            $params[] = $studentId;
        }
        $records = $this->db()->select($sql . " ORDER BY g.name ASC", $params);

        // Real documents on file per guardian (parent_documents.parent_id = guardians.id)
        $docs = [];
        $gIds = array_values(array_unique(array_map('intval', array_column($records, 'guardian_id'))));
        if ($gIds) {
            try {
                $ph = implode(',', array_fill(0, count($gIds), '?'));
                foreach ($this->db()->select(
                    "SELECT parent_id, COALESCE(NULLIF(title, ''), type) AS label FROM parent_documents
                     WHERE parent_id IN ($ph) AND deleted_at IS NULL AND status <> 'rejected' ORDER BY id ASC",
                    $gIds
                ) as $d) {
                    $docs[(int) $d['parent_id']][] = $d['label'];
                }
            } catch (\Throwable $e) {
            }
        }

        $guardians = [];
        foreach ($records as $r) {
            $aadhar = preg_replace('/\D/', '', (string) ($r['aadhar'] ?? ''));
            $guardians[] = [
                'name' => $r['g_name'],
                'phone' => $r['phone'] ?: 'N/A',
                'relationship' => $r['relationship'] ?: 'Guardian',
                'can_pickup' => (bool) $r['can_pickup'],
                'student_id' => $r['student_id'],
                'student_name' => trim($r['s_first'] . ' ' . $r['s_last']),
                'student_class' => 'Class: ' . ($r['class'] ?: 'N/A'),
                'aadhar' => strlen($aadhar) >= 4 ? 'XXXX-XXXX-' . substr($aadhar, -4) : null,
                'photo' => $r['photo'] ? url('uploads/' . $r['photo']) : null,
                'documents' => implode(', ', array_unique($docs[(int) $r['guardian_id']] ?? [])),
            ];
        }

        return $this->respondJson([
            'success' => true,
            'guardians' => $guardians
        ]);
    }

    private const EARLY_PICKUP_PREFIX = 'Early pickup';

    public function approveEarlyPickup(): string
    {
        if ($deny = $this->denyUnlessStaff()) {
            return $deny;
        }
        $userId = (int) $this->getAuthenticatedUserId();

        $body = $this->request->getBody();
        $studentId = isset($body['student_id']) ? (int)$body['student_id'] : 0;
        $guardianName = trim((string) ($body['guardian_name'] ?? '')) ?: 'Authorized Guardian';
        $relationship = trim((string) ($body['relationship'] ?? '')) ?: 'Guardian';
        $notes = trim((string) ($body['notes'] ?? '')) ?: 'Teacher verified guardian in person.';

        if (!$studentId) {
            return $this->respondJson(['success' => false, 'message' => 'Student ID is required'], 400);
        }

        $stu = $this->tenantStudent($userId, $studentId);
        if (!$stu) {
            return $this->respondJson(['success' => false, 'message' => 'Student not found'], 404);
        }

        $today = date('Y-m-d');
        $remarks = self::EARLY_PICKUP_PREFIX . ' at ' . date('h:i A') . " by $guardianName ($relationship). Notes: $notes";

        // 1. Today's attendance row: keep the attendance status (the child was in school),
        //    stop the bus drop-off and record the pickup in remarks. (The attendance table
        //    has no checkout_at column and no 'early_departure' status.)
        $attendance = $this->db()->selectOne(
            "SELECT id FROM attendance WHERE student_id = ? AND date = ? LIMIT 1",
            [$studentId, $today]
        );

        if ($attendance) {
            $this->db()->update('attendance', [
                'use_bus_transport' => 0,
                'remarks'           => $remarks,
                'updated_at'        => date('Y-m-d H:i:s'),
            ], 'id = ?', [$attendance['id']]);
        } else {
            $this->db()->insert('attendance', [
                'tenant_id' => $stu['tenant_id'],
                'school_id' => $stu['school_id'],
                'branch_id' => $stu['branch_id'],
                'student_id' => $studentId,
                'date' => $today,
                'status' => 'present',
                'use_bus_transport' => 0,
                'remarks' => $remarks,
                'created_by' => $userId,
            ]);
        }

        // 2. Take the student off active driver trips (trip_students.status enum has no
        //    'EarlyPickup'; 'Skipped' is the existing value for a stop that is not served).
        try {
            $this->db()->query("
                UPDATE trip_students
                SET status = 'Skipped'
                WHERE student_id = ? AND status = 'Waiting' AND trip_id IN (
                    SELECT id FROM driver_trips WHERE status = 'active'
                )
            ", [$studentId]);
        } catch (\Throwable $e) {
        }

        return $this->respondJson([
            'success' => true,
            'message' => "Early pickup approved for student. Removed from driver's drop route."
        ]);
    }

    /** GET /api/v1/staff/early-pickup — today's approved early pickups in the caller's school. */
    public function earlyPickupList(): string
    {
        if ($deny = $this->denyUnlessStaff()) {
            return $deny;
        }
        $userId = (int) $this->getAuthenticatedUserId();
        $tenantId = $this->userTenantId($userId) ?? 0;

        $rows = $this->db()->select("
            SELECT a.id, a.student_id, a.remarks, a.updated_at, a.created_at,
                   s.first_name, s.last_name, s.class, s.section, s.photo
            FROM attendance a
            JOIN students s ON s.id = a.student_id AND s.tenant_id = ?
            WHERE a.tenant_id = ? AND a.date = ? AND a.student_id IS NOT NULL
              AND a.remarks LIKE ?
            ORDER BY a.updated_at DESC, a.id DESC
        ", [$tenantId, $tenantId, date('Y-m-d'), self::EARLY_PICKUP_PREFIX . '%']);

        $students = [];
        foreach ($rows as $r) {
            $students[] = [
                'id' => (int) $r['student_id'],
                'student_id' => (int) $r['student_id'],
                'name' => trim($r['first_name'] . ' ' . $r['last_name']),
                'class' => trim(($r['class'] ?? '') . ' ' . ($r['section'] ?? '')),
                'photo' => $r['photo'] ? url('uploads/' . $r['photo']) : null,
                'remarks' => $r['remarks'],
                'approved_at' => $r['updated_at'] ?: $r['created_at'],
            ];
        }

        return $this->respondJson(['success' => true, 'students' => $students, 'count' => count($students)]);
    }
}
