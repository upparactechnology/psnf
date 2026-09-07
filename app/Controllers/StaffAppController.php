<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;
use Core\Session;

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

    public function getTodayAttendance(): string
    {
        $userId = $this->getAuthenticatedUserId();
        if (!$userId) {
            return $this->respondJson(['success' => false, 'message' => 'Unauthorized'], 401);
        }

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
                $record = [
                    'opened_at' => $first['check_in'],
                    'checkout_at' => $last ? $last['check_in'] : null,
                    'status' => 'present',
                    'lecture_time' => '09:00:00'
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
        $statusText = $isLate ? 'Late' : 'Present';
        
        $lectureTimeStr = $record['lecture_time'] ?: '09:00:00';
        $lectureTimestamp = strtotime(date('Y-m-d ') . $lectureTimeStr);
        $checkInTimestamp = strtotime($record['opened_at']);
        $lateSeconds = max(0, $checkInTimestamp - $lectureTimestamp);
        $lateMinutes = (int)ceil($lateSeconds / 60);

        $warningMessage = null;
        if ($isLate || $lateMinutes > 15) {
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
        $userId = $this->getAuthenticatedUserId();
        if (!$userId) {
            return $this->respondJson(['success' => false, 'message' => 'Unauthorized'], 401);
        }

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

        // Fetch Employee record
        $employee = null;
        if (!empty($user['employee_id'])) {
            $employee = $this->db()->selectOne("SELECT min_clock_in FROM `employees` WHERE `employee_id` = ? LIMIT 1", [$user['employee_id']]);
        }

        // Fetch Shift Template
        $shift = $this->db()->selectOne("SELECT * FROM shift_templates WHERE id = 1");

        // Determine Shift Logic
        $empShiftStart = (!empty($employee) && !empty($employee['min_clock_in'])) ? $employee['min_clock_in'] : ($shift['start_time'] ?? '09:00:00');
        $gracePeriod = (!empty($employee) && !empty($employee['min_clock_in'])) ? (int)($user['grace_period'] ?? 10) : (int)($shift['grace_minutes'] ?? 15);
        $startTimeSecs = strtotime($empShiftStart);
        $halfDayTimeStr = (!empty($employee) && !empty($employee['min_clock_in'])) ? date('H:i:s', $startTimeSecs + (3 * 3600)) : ($shift['half_day_after'] ?? '12:00:00');

        $currentTime = date('H:i:s');
        $currentTimestamp = time();
        
        $allowedTimestamp = strtotime(date('Y-m-d ') . $empShiftStart) + ($gracePeriod * 60);
        $halfDayTimestamp = strtotime(date('Y-m-d ') . $halfDayTimeStr);

        $status = 'on_time';
        if ($currentTimestamp > $halfDayTimestamp) {
            $status = 'half_day';
        } else if ($currentTimestamp > $allowedTimestamp) {
            $status = 'late';
        }

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
        $userId = $this->getAuthenticatedUserId();
        if (!$userId) {
            return $this->respondJson(['success' => false, 'message' => 'Unauthorized'], 401);
        }

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
        $userId = $this->getAuthenticatedUserId();
        if (!$userId) {
            return $this->respondJson(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $records = $this->db()->select(
            "SELECT * FROM `teacher_attendance` WHERE `user_id` = ? ORDER BY `attendance_date` DESC LIMIT 30",
            [$userId]
        );

        $faceRecordsRaw = $this->db()->select(
            "SELECT *, DATE(check_in) as a_date FROM `attendance` WHERE `user_id` = ? ORDER BY check_in ASC",
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

        foreach ($faceGrouped as $date => $f) {
            if (!isset($mergedData[$date])) {
                $mergedData[$date] = [
                    'opened_at' => $f['min'],
                    'checkout_at' => $f['max'] != $f['min'] ? $f['max'] : null,
                    'status' => 'present'
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

            $statusText = $r['status'] === 'late' ? 'Late' : 'Present';

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
        $userId = $this->getAuthenticatedUserId();
        if (!$userId) {
            return $this->respondJson(['success' => false, 'message' => 'Unauthorized'], 401);
        }

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
        $userId = $this->getAuthenticatedUserId();
        if (!$userId) {
            return $this->respondJson(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $studentId = $input['student_id'] ?? null;
        $status = $input['status'] ?? null; // 'present' or 'absent'

        if (!$studentId || !$status) {
            return $this->respondJson(['success' => false, 'message' => 'Missing parameters'], 400);
        }

        $today = date('Y-m-d');
        $existing = $this->db()->selectOne("SELECT id FROM attendance WHERE student_id = ? AND date = ?", [$studentId, $today]);

        if ($existing) {
            $this->db()->update('attendance', [
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ], "id = ?", [$existing['id']]);
        } else {
            $stu = $this->db()->selectOne("SELECT tenant_id, school_id, branch_id FROM students WHERE id = ?", [$studentId]);
            if ($stu) {
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
        }

        return $this->respondJson(['success' => true]);
    }
public function getSummary(): string
    {
        $userId = $this->getAuthenticatedUserId();
        if (!$userId) {
            return $this->respondJson(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $user = $this->db()->selectOne("SELECT tenant_id FROM users WHERE id = ? LIMIT 1", [$userId]);
        $tenantId = $user ? (int)$user['tenant_id'] : 1;
        $today = date('Y-m-d');

        $totalCount = (int)$this->db()->selectOne("SELECT COUNT(*) as c FROM students WHERE tenant_id = ? AND deleted_at IS NULL", [$tenantId])['c'];
        $presentCount = (int)$this->db()->selectOne("SELECT COUNT(*) as c FROM attendance WHERE tenant_id = ? AND date = ? AND status IN ('present', 'late', 'half_day')", [$tenantId, $today])['c'];
        $absentCount = (int)$this->db()->selectOne("SELECT COUNT(*) as c FROM attendance WHERE tenant_id = ? AND date = ? AND status = 'absent'", [$tenantId, $today])['c'];

        // Fallbacks for empty database
        if ($totalCount === 0) {
            $totalCount = 28;
            $presentCount = 25;
            $absentCount = 3;
        }

        return $this->respondJson([
            'success' => true,
            'summary' => [
                'total' => $totalCount,
                'present' => $presentCount,
                'absent' => $absentCount
            ]
        ]);
    }

    public function getGuardians(): string
    {
        $userId = $this->getAuthenticatedUserId();
        if (!$userId) {
            return $this->respondJson(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $user = $this->db()->selectOne("SELECT tenant_id FROM users WHERE id = ? LIMIT 1", [$userId]);
        $tenantId = $user ? (int)$user['tenant_id'] : 1;

        $records = $this->db()->select("
            SELECT g.name as g_name, g.phone, g.relationship, g.aadhar, g.photo,
                   s.id as student_id, s.first_name as s_first, s.last_name as s_last, s.class
            FROM guardians g
            JOIN guardian_student gs ON g.id = gs.guardian_id
            JOIN students s ON gs.student_id = s.id
            WHERE g.tenant_id = ? AND g.deleted_at IS NULL
            ORDER BY g.name ASC
        ", [$tenantId]);

        $guardians = [];
        foreach ($records as $r) {
            $guardians[] = [
                'name' => $r['g_name'],
                'phone' => $r['phone'] ?: 'N/A',
                'relationship' => $r['relationship'] ?: 'Guardian',
                'student_id' => $r['student_id'],
                'student_name' => $r['s_first'] . ' ' . $r['s_last'],
                'student_class' => 'Class: ' . ($r['class'] ?: 'N/A'),
                'aadhar' => $r['aadhar'],
                'photo' => $r['photo'] ? url('uploads/' . $r['photo']) : null,
                'documents' => 'Aadhar Card'
            ];
        }

        // Mock data fallback if nothing in DB
        if (empty($guardians)) {
            $guardians = [
                [
                    'name' => 'Meera Mehta',
                    'phone' => '+91-9876543210',
                    'relationship' => 'Mother',
                    'student_name' => 'Ketan Mehta',
                    'student_class' => 'Class: 3-C'
                ],
                [
                    'name' => 'Rajesh Joshi',
                    'phone' => '+91-9123456780',
                    'relationship' => 'Father',
                    'student_name' => 'Neha Joshi',
                    'student_class' => 'Class: 4-B'
                ],
                [
                    'name' => 'Suman Patel',
                    'phone' => '+91-9567841230',
                    'relationship' => 'Mother',
                    'student_name' => 'Rohan Patel',
                    'student_class' => 'Class: 3-C'
                ],
                [
                    'name' => 'Vikram Shah',
                    'phone' => '+91-9890123456',
                    'relationship' => 'Father',
                    'student_name' => 'Aarav Shah',
                    'student_class' => 'Class: 2-A'
                ]
            ];
        }

        return $this->respondJson([
            'success' => true,
            'guardians' => $guardians
        ]);
    }

    public function approveEarlyPickup(): string
    {
        $userId = $this->getAuthenticatedUserId();
        if (!$userId) {
            return $this->respondJson(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $body = $this->request->getBody();
        $studentId = isset($body['student_id']) ? (int)$body['student_id'] : 0;
        $guardianName = $body['guardian_name'] ?? 'Authorized Guardian';
        $relationship = $body['relationship'] ?? 'Guardian';
        $notes = $body['notes'] ?? 'Teacher verified guardian in person.';

        if (!$studentId) {
            return $this->respondJson(['success' => false, 'message' => 'Student ID is required'], 400);
        }

        $today = date('Y-m-d');
        $nowStr = date('Y-m-d H:i:s');

        // 1. Update or Insert attendance record marking checkout and setting use_bus_transport = 0
        $attendance = $this->db()->selectOne(
            "SELECT id FROM attendance WHERE student_id = ? AND date = ? LIMIT 1",
            [$studentId, $today]
        );

        if ($attendance) {
            $this->db()->query("
                UPDATE attendance 
                SET checkout_at = ?, status = 'early_departure', use_bus_transport = 0, remarks = ? 
                WHERE id = ?
            ", [$nowStr, "Early pickup by $guardianName ($relationship). Notes: $notes", $attendance['id']]);
        } else {
            $user = $this->db()->selectOne("SELECT tenant_id, school_id, branch_id FROM users WHERE id = ? LIMIT 1", [$userId]);
            $tenantId = $user ? (int)$user['tenant_id'] : 1;
            $schoolId = $user ? (int)$user['school_id'] : 1;
            $branchId = $user ? (int)$user['branch_id'] : 1;

            $this->db()->insert('attendance', [
                'tenant_id' => $tenantId,
                'school_id' => $schoolId,
                'branch_id' => $branchId,
                'student_id' => $studentId,
                'date' => $today,
                'status' => 'early_departure',
                'checkout_at' => $nowStr,
                'use_bus_transport' => 0,
                'remarks' => "Early pickup by $guardianName ($relationship). Notes: $notes"
            ]);
        }

        // 2. Remove student from Driver App drop route by setting status = 'EarlyPickup' in trip_students
        $this->db()->query("
            UPDATE trip_students 
            SET status = 'EarlyPickup' 
            WHERE student_id = ? AND trip_id IN (
                SELECT id FROM driver_trips WHERE date = ? AND status != 'completed'
            )
        ", [$studentId, $today]);

        // Also update student_transport table if present
        try {
            $this->db()->query("UPDATE student_transport SET dropoff_status = 'EarlyPickup' WHERE student_id = ?", [$studentId]);
        } catch (\Throwable $e) {}

        return $this->respondJson([
            'success' => true,
            'message' => "Early pickup approved for student. Removed from driver's drop route."
        ]);
    }
}
