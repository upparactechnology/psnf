<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\View;
use Core\Session;
use Core\Application;
use App\Models\{Attendance, Timetable, Homework, ExamResult, Announcement, Certificate, FeeInvoice, FeePayment, TransportRoute, CommunicationMessage, Student, StudentMedical, EmergencyContact};

class ParentPortalController extends Controller
{
    private function db()
    {
        return Application::$app->db;
    }

    /**
     * Helper to resolve active student context, verify ownership, and load multi-child list.
     */
    private function getContext(?int $requestedId = null): array
    {
        $userId = auth_id();
        if (!$userId) {
            Application::$app->response->redirect('/login');
            exit();
        }

        $guardian = null;
        $isAdminOrStaff = has_role('super_admin') || has_role('school_admin') || has_role('manager') || has_role('teacher');

        if ($isAdminOrStaff) {
            $sessGuardianId = Session::get('parent.impersonated_guardian_id');
            if ($sessGuardianId) {
                $guardian = $this->db()->selectOne(
                    "SELECT * FROM guardians WHERE id = ? AND deleted_at IS NULL LIMIT 1",
                    [(int)$sessGuardianId]
                );
            }
            if (!$guardian) {
                // Default to the first available guardian in the database
                $guardian = $this->db()->selectOne(
                    "SELECT * FROM guardians WHERE deleted_at IS NULL ORDER BY id ASC LIMIT 1"
                );
                if ($guardian) {
                    Session::set('parent.impersonated_guardian_id', $guardian['id']);
                }
            }
        } else {
            // Find the guardian record linked to user_id
            $guardian = $this->db()->selectOne(
                "SELECT * FROM guardians WHERE user_id = ? AND deleted_at IS NULL LIMIT 1",
                [$userId]
            );
        }

        if (!$guardian) {
            Application::$app->response->abort(403, "No parent/guardian profiles found in the system.");
            exit();
        }

        // Find all students mapped to this guardian
        $students = $this->db()->select(
            "SELECT s.* FROM students s
             JOIN guardian_student gs ON gs.student_id = s.id
             WHERE gs.guardian_id = ? AND s.deleted_at IS NULL",
            [$guardian['id']]
        );

        if (empty($students)) {
            return [
                'guardian'       => $guardian,
                'all_students'   => [],
                'active_student' => null,
            ];
        }

        $activeStudent = null;

        if ($requestedId !== null) {
            foreach ($students as $s) {
                if ((int)$s['id'] === $requestedId) {
                    $activeStudent = $s;
                    break;
                }
            }
            if (!$activeStudent) {
                Application::$app->response->abort(403);
                exit();
            }
            Session::set('parent.active_student_id', $requestedId);
        } else {
            $sessId = Session::get('parent.active_student_id');
            if ($sessId) {
                foreach ($students as $s) {
                    if ((int)$s['id'] === (int)$sessId) {
                        $activeStudent = $s;
                        break;
                    }
                }
            }
            if (!$activeStudent) {
                $activeStudent = $students[0];
                Session::set('parent.active_student_id', $activeStudent['id']);
            }
        }

        return [
            'guardian'       => $guardian,
            'all_students'   => $students,
            'active_student' => $activeStudent,
        ];
    }

    public function impersonate(string $id): void
    {
        $isAdminOrStaff = has_role('super_admin') || has_role('school_admin') || has_role('manager') || has_role('teacher');
        if (!$isAdminOrStaff) {
            Application::$app->response->abort(403);
            exit();
        }

        $guardian = $this->db()->selectOne(
            "SELECT id FROM guardians WHERE id = ? AND deleted_at IS NULL LIMIT 1",
            [(int)$id]
        );

        if ($guardian) {
            Session::set('parent.impersonated_guardian_id', $guardian['id']);
            Session::remove('parent.active_student_id'); // reset active student context so it recalculates
            Session::flash('success', "Switched view to parent: " . $id);
        }

        Application::$app->response->redirect('/parent/dashboard');
        exit();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // WEB CONTROLLER METHODS
    // ─────────────────────────────────────────────────────────────────────────

    public function dashboard(): string
    {
        $childId = Application::$app->request->get('child_id');
        $context = $this->getContext($childId !== null ? (int)$childId : null);
        $student = $context['active_student'];

        if (!$student) {
            return View::render('parent/dashboard', array_merge($context, [
                'title'             => 'Parent Dashboard',
                'attendanceRate'    => 0,
                'recentAttendance'  => [],
                'announcements'     => [],
                'homeworks'         => [],
                'unpaidTotal'       => 0.0,
                'transport'         => null,
                'recentMessages'    => [],
            ]));
        }

        // 1. Fetch attendance summary
        $totalAtt = (int)($this->db()->selectOne(
            "SELECT COUNT(*) as cnt FROM attendance WHERE student_id = ?",
            [$student['id']]
        )['cnt'] ?? 0);

        $presentAtt = (int)($this->db()->selectOne(
            "SELECT COUNT(*) as cnt FROM attendance WHERE student_id = ? AND status = 'present'",
            [$student['id']]
        )['cnt'] ?? 0);

        $attendanceRate = $totalAtt > 0 ? (int)round(($presentAtt / $totalAtt) * 100) : 100;

        // 2. Fetch recent attendance (last 5 records)
        $recentAttendance = $this->db()->select(
            "SELECT * FROM attendance WHERE student_id = ? ORDER BY date DESC LIMIT 5",
            [$student['id']]
        );

        // 3. Fetch announcements (last 3)
        $announcements = $this->db()->select(
            "SELECT * FROM announcements WHERE target_audience IN ('all', 'parents') ORDER BY published_at DESC LIMIT 3"
        );

        // 4. Fetch pending homeworks
        $homeworks = $this->db()->select(
            "SELECT * FROM homeworks WHERE class = ? AND due_date >= CURRENT_DATE ORDER BY due_date ASC LIMIT 3",
            [$student['class']]
        );

        // 5. Fetch fee invoice totals
        $unpaidFees = $this->db()->selectOne(
            "SELECT SUM(amount - paid_amount) as total FROM fee_invoices WHERE student_id = ? AND status != 'paid'",
            [$student['id']]
        );
        $unpaidTotal = (float)($unpaidFees['total'] ?? 0.00);

        // 6. Active bus tracking info
        $transport = $this->db()->selectOne(
            "SELECT e.*, CONCAT(e.first_name, ' ', e.last_name) as name, st.pickup_point, st.pickup_time,
             (SELECT ts.status FROM trip_students ts JOIN driver_trips dt ON dt.id = ts.trip_id WHERE ts.student_id = st.student_id AND dt.status = 'active' LIMIT 1) as trip_status
             FROM student_transport st
             JOIN employees e ON e.id = st.driver_id
             WHERE st.student_id = ? LIMIT 1",
            [$student['id']]
        );

        // 7. Recent messages (last 2)
        $recentMessages = $this->db()->select(
            "SELECT cm.*, u.name as sender_name
             FROM communication_messages cm
             JOIN users u ON u.id = cm.sender_id
             WHERE cm.sender_id = ? OR cm.receiver_id = ?
             ORDER BY cm.created_at DESC LIMIT 2",
            [$context['guardian']['user_id'], $context['guardian']['user_id']]
        );

        // 8. Fetch recent exam results
        $exams = $this->db()->select(
            "SELECT * FROM exam_results WHERE student_id = ? ORDER BY date_published DESC LIMIT 3",
            [$student['id']]
        );

        // 9. Fetch today's timetable
        $todayDay = date('l');
        $timetable = $this->db()->select(
            "SELECT * FROM timetables WHERE class = ? AND COALESCE(section, '') = COALESCE(?, '') AND day_of_week = ? ORDER BY start_time ASC LIMIT 5",
            [$student['class'], $student['section'], $todayDay]
        );

        return View::render('parent/dashboard', array_merge($context, [
            'title'             => 'Parent Dashboard',
            'attendanceRate'    => $attendanceRate,
            'recentAttendance'  => $recentAttendance,
            'announcements'     => $announcements,
            'homeworks'         => $homeworks,
            'unpaidTotal'       => $unpaidTotal,
            'transport'         => $transport,
            'recentMessages'    => $recentMessages,
            'exams'             => $exams,
            'timetable'         => $timetable,
        ]));
    }

    public function attendance(string $id): string
    {
        $context = $this->getContext((int)$id);
        $student = $context['active_student'];

        // Get logs for the current month/year
        $month = Application::$app->request->get('month', date('m'));
        $year  = Application::$app->request->get('year', date('Y'));

        $logs = $this->db()->select(
            "SELECT * FROM attendance
             WHERE student_id = ? AND MONTH(date) = ? AND YEAR(date) = ?
             ORDER BY date DESC",
            [$student['id'], $month, $year]
        );

        // Calculate summary
        $summary = [
            'present'  => 0,
            'absent'   => 0,
            'late'     => 0,
            'half_day' => 0,
        ];
        foreach ($logs as $log) {
            if (isset($summary[$log['status']])) {
                $summary[$log['status']]++;
            }
        }

        // Tomorrow's Attendance context
        $tomorrowDate = date('Y-m-d', strtotime('+1 day'));
        $tomorrowAtt  = $this->db()->selectOne(
            "SELECT * FROM attendance WHERE student_id = ? AND date = ? LIMIT 1",
            [$student['id'], $tomorrowDate]
        );

                // Leave Applications History
        $leaveHistory = $this->db()->select(
            "SELECT * FROM leave_applications WHERE student_id = ? ORDER BY created_at DESC",
            [$student['id']]
        );

        return View::render('parent/attendance', array_merge($context, [
            'title'        => 'Child Attendance',
            'logs'         => $logs,
            'summary'      => $summary,
            'month'        => $month,
            'year'         => $year,
            'leaveHistory' => $leaveHistory,
            'tomorrowDate' => $tomorrowDate,
            'tomorrowAtt'  => $tomorrowAtt,
        ]));
    }

        public function markAbsent(string $id): void
    {
        $context = $this->getContext((int)$id);
        $student = $context['active_student'];

        $date    = \Core\Application::$app->request->input('absence_date');
        $remarks = \Core\Application::$app->request->input('remarks', '');
        $date    = $date ?: date('Y-m-d');
        
        // Validate date
        $timestamp = strtotime($date);
        if (!$timestamp) {
            echo '<div class="p-3 mb-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-600 dark:text-red-400 text-xs font-semibold">Invalid date selected.</div>';
            return;
        }

        $dateStr = date('Y-m-d', $timestamp);

        // Check if date is today or in the past
        if ($dateStr <= date('Y-m-d')) {
            echo '<div class="p-3 mb-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-600 dark:text-red-400 text-xs font-semibold">You can only mark absence for future dates (Tomorrow onwards).</div>';
            return;
        }

        // Check if record exists
        $existing = $this->db()->selectOne(
            "SELECT id FROM attendance WHERE student_id = ? AND date = ?",
            [$student['id'], $dateStr]
        );

        if ($existing) {
            $this->db()->update('attendance', [
                'status' => 'absent',
                'remarks' => $remarks,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => auth_id()
            ], "id = ?", [$existing['id']]);
        } else {
            $this->db()->insert('attendance', [
                'uuid' => str_uuid(),
                'tenant_id' => $student['tenant_id'],
                'school_id' => $student['school_id'],
                'branch_id' => $student['branch_id'],
                'student_id' => $student['id'],
                'date' => $dateStr,
                'status' => 'absent',
                'remarks' => $remarks,
                'is_medical' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => auth_id()
            ]);
        }

        \Core\Session::flash('success', "Absence marked successfully for " . date('d M Y', $timestamp));
        echo '<div class="p-3 mb-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs font-semibold flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Absence recorded successfully.</div>';
        echo '<script>setTimeout(() => { window.location.reload(); }, 1500);</script>';
    }

        public function requestLeave(string $id): void
    {
        $context = $this->getContext((int)$id);
        $student = $context['active_student'];

        $startDate = \Core\Application::$app->request->input('start_date');
        $endDate = \Core\Application::$app->request->input('end_date');
        $leaveType = \Core\Application::$app->request->input('leave_type', 'Other');
        $reason = \Core\Application::$app->request->input('reason', '');

        $startTs = strtotime($startDate);
        $endTs = strtotime($endDate);

        if (!$startTs || !$endTs || $startTs > $endTs) {
            \Core\Session::flash('error', 'Invalid date range.');
            \Core\Application::$app->response->redirect("/parent/students/{$id}/attendance");
            return;
        }

        $diffDays = round(($endTs - $startTs) / 86400);
        $medicalCertPath = null;

        if ($diffDays >= 1) {
            if (!isset($_FILES['medical_certificate']) || $_FILES['medical_certificate']['error'] !== UPLOAD_ERR_OK) {
                \Core\Session::flash('error', 'A supporting document is required for multi-day leaves.');
                \Core\Application::$app->response->redirect("/parent/students/{$id}/attendance");
                return;
            }

            $uploadDir = dirname(dirname(__DIR__)) . '/public/uploads/absences/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileInfo = pathinfo($_FILES['medical_certificate']['name']);
            $ext = strtolower($fileInfo['extension']);
            $allowedExts = ['pdf', 'jpg', 'jpeg', 'png'];

            if (!in_array($ext, $allowedExts)) {
                \Core\Session::flash('error', 'Invalid file type. Only PDF and images are allowed.');
                \Core\Application::$app->response->redirect("/parent/students/{$id}/attendance");
                return;
            }

            $fileName = $student['id'] . '_' . time() . '_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['medical_certificate']['tmp_name'], $uploadDir . $fileName)) {
                $medicalCertPath = $fileName;
            } else {
                \Core\Session::flash('error', 'Failed to upload document.');
                \Core\Application::$app->response->redirect("/parent/students/{$id}/attendance");
                return;
            }
        }

        $this->db()->insert('leave_applications', [
            'tenant_id' => $student['tenant_id'],
            'school_id' => $student['school_id'],
            'branch_id' => $student['branch_id'],
            'student_id' => $student['id'],
            'start_date' => date('Y-m-d', $startTs),
            'end_date' => date('Y-m-d', $endTs),
            'leave_type' => $leaveType,
            'reason' => $reason,
            'medical_certificate' => $medicalCertPath,
            'status' => 'Pending',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        \Core\Session::flash('success', 'Leave request submitted successfully.');
        \Core\Application::$app->response->redirect("/parent/students/{$id}/attendance");
    }

    public function submitTomorrowAttendance(string $id): void
    {
        $context = $this->getContext((int)$id);
        $student = $context['active_student'];

        $date         = Application::$app->request->input('date');
        $date         = $date ?: date('Y-m-d', strtotime('+1 day'));
        $status       = Application::$app->request->input('status');
        $remarks      = Application::$app->request->input('remarks', '');
        $isMedical    = Application::$app->request->input('is_medical', '0');

        $timestamp = strtotime($date);
        if (!$timestamp) {
            Session::flash('error', 'Invalid date selected.');
            Application::$app->response->redirect("/parent/students/{$id}/attendance");
            exit();
        }

        $dateStr = date('Y-m-d', $timestamp);

        // Check if date is in the past
        if ($dateStr < date('Y-m-d')) {
            Session::flash('error', 'You cannot declare attendance for past dates.');
            Application::$app->response->redirect("/parent/students/{$id}/attendance");
            exit();
        }

        if (empty($status) || !in_array($status, ['present', 'absent'])) {
            Session::flash('error', 'Please select either Present or Absent.');
            Application::$app->response->redirect("/parent/students/{$id}/attendance");
            exit();
        }

        if ($status === 'absent' && empty(trim($remarks))) {
            Session::flash('error', 'Please provide a reason for the absence.');
            Application::$app->response->redirect("/parent/students/{$id}/attendance");
            exit();
        }

        // Force is_medical to '1' if the word 'medical' is used in the remarks (case-insensitive)
        if ($status === 'absent' && stripos($remarks, 'medical') !== false) {
            $isMedical = '1';
        }

        $exists = $this->db()->selectOne(
            "SELECT * FROM attendance WHERE student_id = ? AND date = ? LIMIT 1",
            [$student['id'], $dateStr]
        );

        // Enforce the 11:00 PM day prior deadline
        $targetDateTimestamp = strtotime($dateStr);
        $deadlineTimestamp = strtotime(date('Y-m-d', $targetDateTimestamp) . ' -1 day 23:00:00');
        if (time() >= $deadlineTimestamp) {
            Session::flash('error', 'The deadline to declare or modify attendance for this date has passed (11:00 PM on the day prior).');
            Application::$app->response->redirect("/parent/students/{$id}/attendance");
            exit();
        }

        if ($exists) {
            if (empty($exists['created_by']) || (int)$exists['created_by'] !== auth_id()) {
                Session::flash('error', 'This attendance record is managed by the school and cannot be modified.');
                Application::$app->response->redirect("/parent/students/{$id}/attendance");
                exit();
            }
        }

        $storedName = null;
        if ($status === 'absent' && $isMedical === '1') {
            $file = Application::$app->request->file('medical_certificate');
            if ($file && $file['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['pdf', 'png', 'jpg', 'jpeg'])) {
                    Session::flash('error', 'Medical certificate must be a PDF, PNG, JPG, or JPEG file.');
                    Application::$app->response->redirect("/parent/students/{$id}/attendance");
                    exit();
                }
                if ($file['size'] > 5 * 1024 * 1024) {
                    Session::flash('error', 'Medical certificate size must be less than 5MB.');
                    Application::$app->response->redirect("/parent/students/{$id}/attendance");
                    exit();
                }

                $uploadDir = storage_path('uploads/attendance/' . $student['id']);
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $storedName = bin2hex(random_bytes(16)) . '.' . $ext;
                move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $storedName);
            } else {
                // Check if we already have a medical certificate stored
                if ($exists && !empty($exists['medical_certificate'])) {
                    $storedName = $exists['medical_certificate'];
                } else {
                    $errorMsg = (stripos($remarks, 'medical') !== false)
                        ? 'Please upload a medical certificate (required since the reason contains the word "medical").'
                        : 'Please upload a medical certificate for medical issues.';
                    Session::flash('error', $errorMsg);
                    Application::$app->response->redirect("/parent/students/{$id}/attendance");
                    exit();
                }
            }
        }

        $useBusTransport = ($status === 'present') ? (int)Application::$app->request->input('use_bus_transport', '0') : 0;
        $medCert = ($status === 'absent' && $isMedical === '1') ? $storedName : null;
        $remarksVal = ($status === 'absent') ? $remarks : null;

        if ($exists) {
            $this->db()->query(
                "UPDATE attendance SET status = ?, remarks = ?, medical_certificate = ?, use_bus_transport = ?, updated_at = NOW() WHERE id = ?",
                [$status, $remarksVal, $medCert, $useBusTransport, $exists['id']]
            );
        } else {
            $this->db()->query(
                "INSERT INTO attendance (tenant_id, school_id, branch_id, student_id, date, status, remarks, medical_certificate, use_bus_transport, created_by, created_at, updated_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
                [
                    $student['tenant_id'],
                    $student['school_id'],
                    $student['branch_id'],
                    $student['id'],
                    $dateStr,
                    $status,
                    $remarksVal,
                    $medCert,
                    $useBusTransport,
                    auth_id()
                ]
            );
        }

        \App\Models\StudentTimeline::logEvent(
            (int)$student['id'],
            'attendance_declaration',
            "Parent declared attendance for {$dateStr} as: " . ucfirst($status),
            ['status' => $status, 'date' => $dateStr],
            auth_id(),
            $status === 'present' ? 'green' : 'red',
            'calendar'
        );

        Session::flash('success', "Attendance declaration for " . date('l, d M Y', strtotime($dateStr)) . " has been saved successfully.");
        Application::$app->response->redirect("/parent/students/{$id}/attendance");
        exit();
    }

    public function checkDateAttendance(string $id): string
    {
        $context = $this->getContext((int)$id);
        $student = $context['active_student'];

        $date = Application::$app->request->get('date', date('Y-m-d', strtotime('+1 day')));

        // Parse date to ensure it is valid
        $timestamp = strtotime($date);
        if (!$timestamp) {
            return "<div class='text-rose-400 text-xs font-semibold p-4'>Invalid date selected.</div>";
        }

        $dateStr = date('Y-m-d', $timestamp);

        // Past dates check
        $isPast = ($dateStr < date('Y-m-d'));

        $tomorrowAtt = $this->db()->selectOne(
            "SELECT * FROM attendance WHERE student_id = ? AND date = ? LIMIT 1",
            [$student['id'], $dateStr]
        );

        $targetDateTimestamp = strtotime($dateStr);
        $deadlineTimestamp = strtotime(date('Y-m-d', $targetDateTimestamp) . ' -1 day 23:00:00');
        $canEdit = (time() < $deadlineTimestamp);
        $remainingSeconds = max(0, $deadlineTimestamp - time());
        $lockedReason = '';

        if ($isPast) {
            $canEdit = false;
            $lockedReason = 'past_date';
        } elseif (!$canEdit) {
            $lockedReason = 'time_expired';
        } elseif ($tomorrowAtt) {
            if (empty($tomorrowAtt['created_by']) || (int)$tomorrowAtt['created_by'] !== auth_id()) {
                $canEdit = false;
                $lockedReason = 'school_managed';
            }
        }

        return View::render('parent/attendance_form', [
            'active_student'   => $student,
            'tomorrowAtt'      => $tomorrowAtt,
            'canEdit'          => $canEdit,
            'remainingSeconds' => $remainingSeconds,
            'lockedReason'     => $lockedReason,
            'date'             => $dateStr,
        ]);
    }

    public function timetable(string $id): string
    {
        $context = $this->getContext((int)$id);
        $student = $context['active_student'];

        // Fetch timetable grouped by day
        $rows = $this->db()->select(
            "SELECT * FROM timetables
             WHERE class = ? AND COALESCE(section, '') = COALESCE(?, '')
             ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), start_time ASC",
            [$student['class'], $student['section']]
        );

        $timetableByDay = [];
        foreach ($rows as $row) {
            $timetableByDay[$row['day_of_week']][] = $row;
        }

        return View::render('parent/timetable', array_merge($context, [
            'title'          => 'Class Timetable',
            'timetableByDay' => $timetableByDay,
        ]));
    }

    public function homework(string $id): string
    {
        $context = $this->getContext((int)$id);
        $student = $context['active_student'];

        $homeworks = $this->db()->select(
            "SELECT * FROM homeworks
             WHERE class = ?
             ORDER BY due_date ASC",
            [$student['class']]
        );

        return View::render('parent/homework', array_merge($context, [
            'title'     => 'Homework Assignments',
            'homeworks' => $homeworks,
        ]));
    }

    public function exams(string $id): string
    {
        $context = $this->getContext((int)$id);
        $student = $context['active_student'];

        $results = $this->db()->select(
            "SELECT * FROM exam_results
             WHERE student_id = ?
             ORDER BY date_published DESC, subject ASC",
            [$student['id']]
        );

        // Fetch generated certificates from the sub-app module
        $certificates = $this->db()->select("
            SELECT 
                gc.id,
                ct.name AS title,
                ct.name AS certificate_type,
                gc.pdf_path AS file_path,
                gc.generated_at AS issued_at,
                'generated' AS source,
                p.id AS participant_id
            FROM generated_certificates gc
            JOIN participants p ON p.id = gc.participant_id
            JOIN certificate_types ct ON ct.id = p.certificate_type_id
            WHERE p.student_id = ?
            ORDER BY issued_at DESC
        ", [$student['id']]);

        return View::render('parent/exams', array_merge($context, [
            'title'        => 'Evaluations & Certificates',
            'results'      => $results,
            'certificates' => $certificates,
        ]));
    }

    public function certificates(string $id): string
    {
        $context = $this->getContext((int)$id);
        $student = $context['active_student'];

        if (!$student) {
            return View::render('parent/certificates', array_merge($context, [
                'title'        => 'Student Certificates',
                'certificates' => [],
            ]));
        }

        $fullName = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));

        // 1. Fetch generated certificates from sub-app module
        $genCertificates = $this->db()->select("
            SELECT 
                gc.id,
                ct.name AS title,
                ct.name AS certificate_type,
                gc.pdf_path AS file_path,
                gc.jpg_path AS jpg_path,
                gc.generated_at AS issued_at,
                'generated' AS source,
                p.id AS participant_id
            FROM generated_certificates gc
            JOIN participants p ON p.id = gc.participant_id
            JOIN certificate_types ct ON ct.id = p.certificate_type_id
            WHERE p.student_id = ? OR (p.name IS NOT NULL AND TRIM(p.name) = ?)
            ORDER BY gc.generated_at DESC
        ", [$student['id'], $fullName]);

        // 2. Fetch certificates from main system table
        $hasCertTable = $this->db()->selectOne("SHOW TABLES LIKE 'certificates'");
        $mainCertificates = [];
        if ($hasCertTable) {
            $mainCertificates = $this->db()->select("
                SELECT 
                    c.id,
                    c.title,
                    c.certificate_type,
                    c.file_path,
                    c.file_path AS jpg_path,
                    c.issued_at,
                    'main' AS source,
                    NULL AS participant_id
                FROM certificates c
                WHERE c.student_id = ?
                ORDER BY c.issued_at DESC
            ", [$student['id']]);
        }

        $certificates = array_merge($genCertificates, $mainCertificates);

        return View::render('parent/certificates', array_merge($context, [
            'title'        => 'Student Certificates',
            'certificates' => $certificates,
        ]));
    }

    public function downloadCertificate(string $id): void
    {
        $format = (string) (Application::$app->request->get('format', 'pdf'));
        $format = in_array($format, ['pdf', 'jpg'], true) ? $format : 'pdf';
        $disposition = (string) (Application::$app->request->get('disposition', 'inline'));
        $disposition = in_array($disposition, ['attachment', 'inline'], true) ? $disposition : 'inline';

        $certId = (int) $id;

        // 1. Try fetching from generated_certificates joined with participants
        $cert = $this->db()->selectOne("
            SELECT 
                gc.id,
                gc.participant_id,
                gc.pdf_path,
                gc.jpg_path,
                p.name AS recipient_name,
                p.student_id,
                ct.name AS title
            FROM generated_certificates gc
            JOIN participants p ON p.id = gc.participant_id
            JOIN certificate_types ct ON ct.id = p.certificate_type_id
            WHERE p.id = ? OR gc.id = ?
            LIMIT 1
        ", [$certId, $certId]);

        if ($cert) {
            $relative = $format === 'pdf' ? (string)($cert['pdf_path'] ?? '') : (string)($cert['jpg_path'] ?? '');
            if (empty($relative)) {
                $relative = (string)($cert['pdf_path'] ?? '');
            }

            $cleanRel = ltrim(str_replace('\\', '/', $relative), '/');
            
            $possiblePaths = [
                ROOT_PATH . '/public/certificate_generator/' . $cleanRel,
                ROOT_PATH . '/public/' . $cleanRel,
                ROOT_PATH . '/' . $cleanRel,
                STORAGE_PATH . '/' . $cleanRel,
            ];

            $foundFile = null;
            foreach ($possiblePaths as $path) {
                if (is_file($path)) {
                    $foundFile = $path;
                    break;
                }
            }

            if ($foundFile !== null) {
                try {
                    $this->db()->query(
                        "UPDATE generated_certificates SET download_count = COALESCE(download_count, 0) + 1 WHERE id = ?",
                        [$cert['id']]
                    );
                } catch (\Throwable $e) {}

                $contentType = ($format === 'pdf') ? 'application/pdf' : 'image/jpeg';
                $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', ($cert['recipient_name'] ?? 'Certificate') . '_' . ($cert['title'] ?? 'Document')) . '.' . $format;

                header('Content-Type: ' . $contentType);
                header('Content-Disposition: ' . $disposition . '; filename="' . $safeName . '"');
                header('Content-Length: ' . filesize($foundFile));
                header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
                header('Pragma: no-cache');
                readfile($foundFile);
                exit();
            }
        }

        // 2. Check main certificates table
        $mainCert = $this->db()->selectOne("SELECT * FROM certificates WHERE id = ?", [$certId]);
        if ($mainCert) {
            $filePath = (string) $mainCert['file_path'];
            $cleanPath = ltrim(str_replace('\\', '/', $filePath), '/');

            $possiblePaths = [
                STORAGE_PATH . '/uploads/certificates/' . basename($cleanPath),
                ROOT_PATH . '/' . $cleanPath,
                ROOT_PATH . '/public/' . $cleanPath,
            ];

            $foundFile = null;
            foreach ($possiblePaths as $path) {
                if (is_file($path)) {
                    $foundFile = $path;
                    break;
                }
            }

            if ($foundFile !== null) {
                $contentType = 'application/pdf';
                $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $mainCert['title'] ?? 'Certificate') . '.pdf';

                header('Content-Type: ' . $contentType);
                header('Content-Disposition: ' . $disposition . '; filename="' . $safeName . '"');
                header('Content-Length: ' . filesize($foundFile));
                readfile($foundFile);
                exit();
            }
        }

        Application::$app->response->abort(404, "Certificate document file not found on disk.");
        exit();
    }

    public function medical(string $id): string
    {
        $context = $this->getContext((int)$id);
        $student = $context['active_student'];

        $medical = [];

        return View::render('parent/medical', array_merge($context, [
            'title'   => 'Medical Summary',
            'medical' => $medical ?: [],
        ]));
    }

    public function transport(string $id): string
    {
        $context = $this->getContext((int)$id);
        $student = $context['active_student'];

        $transport = $this->db()->selectOne(
            "SELECT e.*, CONCAT(e.first_name, ' ', e.last_name) as name, st.pickup_point, st.pickup_time, st.pickup_lat, st.pickup_lng,
             (SELECT ts.status FROM trip_students ts JOIN driver_trips dt ON dt.id = ts.trip_id WHERE ts.student_id = st.student_id AND dt.status = 'active' LIMIT 1) as trip_status
             FROM student_transport st
             JOIN employees e ON e.id = st.driver_id
             WHERE st.student_id = ? LIMIT 1",
            [$student['id']]
        );

        // Fetch dynamic campus location
        $campusLatRow = $this->db()->selectOne("SELECT value FROM settings WHERE `key` = 'campus_lat'");
        $campusLngRow = $this->db()->selectOne("SELECT value FROM settings WHERE `key` = 'campus_lng'");
        $campusLat = $campusLatRow ? (float)$campusLatRow['value'] : 23.0225;
        $campusLng = $campusLngRow ? (float)$campusLngRow['value'] : 72.5714;

        return View::render('parent/transport', array_merge($context, [
            'title'     => 'Bus Transport Tracking',
            'transport' => $transport,
            'campusLat' => $campusLat,
            'campusLng' => $campusLng,
        ]));
    }

    public function fees(string $id): string
    {
        $context = $this->getContext((int)$id);
        $student = $context['active_student'];

        $invoices = $this->db()->select(
            "SELECT * FROM fee_invoices
             WHERE student_id = ?
             ORDER BY due_date DESC",
            [$student['id']]
        );

        $payments = $this->db()->select(
            "SELECT fp.*, fi.title as invoice_title, fi.invoice_number
             FROM fee_payments fp
             JOIN fee_invoices fi ON fi.id = fp.invoice_id
             WHERE fi.student_id = ?
             ORDER BY fp.paid_at DESC",
            [$student['id']]
        );

        return View::render('parent/fees', array_merge($context, [
            'title'    => 'Fee Payments & Invoices',
            'invoices' => $invoices,
            'payments' => $payments,
        ]));
    }

    public function payFee(string $studentId, string $invoiceId): string
    {
        $context = $this->getContext((int)$studentId);
        $student = $context['active_student'];

        $invoice = $this->db()->selectOne(
            "SELECT * FROM fee_invoices WHERE id = ? AND student_id = ? LIMIT 1",
            [(int)$invoiceId, $student['id']]
        );

        if (!$invoice || $invoice['status'] === 'paid') {
            if (Application::$app->request->isHtmx()) {
                return '<div class="text-red-400 font-medium">Invalid or already paid invoice.</div>';
            }
            Session::flash('error', 'Invalid or already paid invoice.');
            Application::$app->response->redirect("/parent/students/{$student['id']}/fees");
            exit();
        }

        $method = Application::$app->request->post('payment_method', 'Credit Card');
        $ref    = 'SIM-' . strtoupper(substr(md5(uniqid()), 0, 10));
        $amount = (float)$invoice['amount'];

        // Mark invoice paid
        $this->db()->update('fee_invoices', [
            'status'      => 'paid',
            'paid_amount' => $amount,
            'paid_at'     => now(),
        ], 'id = ?', [$invoice['id']]);

        // Insert payment details
        $paymentId = $this->db()->insert('fee_payments', [
            'tenant_id'      => $student['tenant_id'],
            'school_id'      => $student['school_id'],
            'branch_id'      => $student['branch_id'],
            'invoice_id'     => $invoice['id'],
            'amount'         => $amount,
            'payment_method' => $method,
            'payment_ref'    => $ref,
            'paid_at'        => now(),
            'payment_source' => 'parent_online',
        ]);

        // Record to Student Ledger
        $currentBalance = (float)($this->db()->selectOne("SELECT balance FROM student_ledgers WHERE student_id = ? ORDER BY id DESC LIMIT 1", [$student['id']])['balance'] ?? 0);
        $newBalance = $currentBalance - $amount;
        $this->db()->insert('student_ledgers', [
            'student_id'   => $student['id'],
            'entry_type'   => 'payment',
            'debit'        => 0.00,
            'credit'       => $amount,
            'balance'      => $newBalance,
            'reference_id' => $paymentId,
            'description'  => "Online Payment: Received {$amount} via {$method} for '{$invoice['title']}'"
        ]);

        // WhatsApp Notification
        \App\Services\NotificationService::notifyPaymentReceived($student, $invoice, $amount);

        // Log to Student Timeline
        $this->db()->insert('student_timeline', [
            'student_id'  => $student['id'],
            'event_type'  => 'fee_payment',
            'title'       => 'Fee Payment Successful',
            'description' => "Paid {$amount} INR for '{$invoice['title']}' via {$method}. Ref: {$ref}.",
            'color'       => 'green',
            'icon'        => 'cash',
            'actor_name'  => $context['guardian']['name'],
            'occurred_at' => now(),
        ]);

        if (Application::$app->request->isHtmx()) {
            return '<div class="bg-green-950/60 border border-green-800/40 text-green-300 p-4 rounded-xl text-sm font-medium">
                        ✓ Payment successful! Ref: ' . $ref . '. Reloading page...
                        <script>setTimeout(() => window.location.reload(), 1500)</script>
                    </div>';
        }

        Session::flash('success', 'Fee paid successfully!');
        Application::$app->response->redirect("/parent/students/{$student['id']}/fees");
        exit();
    }

    public function updateEmergency(string $studentId): string
    {
        $context = $this->getContext((int)$studentId);
        $student = $context['active_student'];

        $name     = Application::$app->request->post('name');
        $relation = Application::$app->request->post('relationship');
        $phone    = Application::$app->request->post('phone');
        $phoneAlt = Application::$app->request->post('phone_alt');
        $email    = Application::$app->request->post('email');

        if (!$name || !$relation || !$phone) {
            Session::flash('error', 'Name, Relationship, and Phone are required.');
            Application::$app->response->redirect("/parent/students/{$student['id']}/attendance"); // Fallback redirect
            exit();
        }

        // Try to update existing primary contact, else insert
        $existing = $this->db()->selectOne(
            "SELECT id FROM emergency_contacts WHERE student_id = ? LIMIT 1",
            [$student['id']]
        );

        $data = [
            'tenant_id'    => $student['tenant_id'],
            'school_id'    => $student['school_id'],
            'branch_id'    => $student['branch_id'],
            'student_id'   => $student['id'],
            'name'         => $name,
            'relationship' => $relation,
            'phone'        => $phone,
            'phone_alt'    => $phoneAlt,
            'email'        => $email,
            'is_primary'   => 1,
        ];

        if ($existing) {
            $this->db()->update('emergency_contacts', $data, 'id = ?', [$existing['id']]);
        } else {
            $this->db()->insert('emergency_contacts', $data);
        }

        // Log event
        $this->db()->insert('student_timeline', [
            'student_id'  => $student['id'],
            'event_type'  => 'profile_update',
            'title'       => 'Emergency Contact Updated',
            'description' => "Emergency contact set to {$name} ({$relation}) by Parent.",
            'color'       => 'blue',
            'icon'        => 'user',
            'actor_name'  => $context['guardian']['name'],
            'occurred_at' => now(),
        ]);

        if (Application::$app->request->isHtmx()) {
            return '<div class="bg-green-950/60 border border-green-800/40 text-green-300 p-4 rounded-xl text-sm font-medium">
                        ✓ Contact updated successfully!
                    </div>';
        }

        Session::flash('success', 'Emergency contact updated successfully!');
        Application::$app->response->redirect('/parent/dashboard');
        exit();
    }

    public function announcements(): string
    {
        $context = $this->getContext();
        $announcements = $this->db()->select(
            "SELECT * FROM announcements
             WHERE target_audience IN ('all', 'parents')
             ORDER BY published_at DESC"
        );

        return View::render('parent/announcements', array_merge($context, [
            'title'         => 'School Announcements',
            'announcements' => $announcements,
        ]));
    }

    public function communication(): string
    {
        $context = $this->getContext();
        $parentUserId = (int)$context['guardian']['user_id'];

        // Fetch direct message log with the school staff (we default staff to admin@psnf.edu / ID 1)
        $staffUser = $this->db()->selectOne("SELECT id, name FROM users WHERE email = 'admin@psnf.edu' LIMIT 1");
        $staffId = $staffUser ? (int)$staffUser['id'] : 1;

        $messages = $this->db()->select(
            "SELECT cm.*, sender.name as sender_name, receiver.name as receiver_name
             FROM communication_messages cm
             JOIN users sender ON sender.id = cm.sender_id
             JOIN users receiver ON receiver.id = cm.receiver_id
             WHERE (cm.sender_id = ? AND cm.receiver_id = ?)
                OR (cm.sender_id = ? AND cm.receiver_id = ?)
             ORDER BY cm.created_at ASC",
            [$parentUserId, $staffId, $staffId, $parentUserId]
        );

        // Mark incoming messages as read
        $this->db()->query(
            "UPDATE communication_messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?",
            [$staffId, $parentUserId]
        );

        return View::render('parent/communication', array_merge($context, [
            'title'    => 'Parent-Teacher Messages',
            'messages' => $messages,
            'staff'    => $staffUser ?: ['id' => 1, 'name' => 'School Admin'],
        ]));
    }

    public function sendMessage(): string
    {
        $context = $this->getContext();
        $parentUserId = (int)$context['guardian']['user_id'];
        $message = Application::$app->request->post('message');

        $staffUser = $this->db()->selectOne("SELECT id FROM users WHERE email = 'admin@psnf.edu' LIMIT 1");
        $staffId = $staffUser ? (int)$staffUser['id'] : 1;

        if ($message) {
            $activeStudent = $context['active_student'];
            $tenantId = $activeStudent ? $activeStudent['tenant_id'] : $context['guardian']['tenant_id'];
            $schoolId = $activeStudent ? $activeStudent['school_id'] : 1;
            $branchId = $activeStudent ? $activeStudent['branch_id'] : 1;

            $this->db()->insert('communication_messages', [
                'tenant_id'   => $tenantId,
                'school_id'   => $schoolId,
                'branch_id'   => $branchId,
                'sender_id'   => $parentUserId,
                'receiver_id' => $staffId,
                'subject'     => 'Parent Portal Inquiry',
                'message'     => $message,
                'is_read'     => 0,
                'created_at'  => now(),
            ]);
        }

        if (Application::$app->request->isHtmx()) {
            // Re-render communication chat list directly
            $messages = $this->db()->select(
                "SELECT cm.*, sender.name as sender_name, receiver.name as receiver_name
                 FROM communication_messages cm
                 JOIN users sender ON sender.id = cm.sender_id
                 JOIN users receiver ON receiver.id = cm.receiver_id
                 WHERE (cm.sender_id = ? AND cm.receiver_id = ?)
                    OR (cm.sender_id = ? AND cm.receiver_id = ?)
                 ORDER BY cm.created_at ASC",
                [$parentUserId, $staffId, $staffId, $parentUserId]
            );

            // Output message bubbles fragment directly
            $html = '';
            foreach ($messages as $msg) {
                $isMe = (int)$msg['sender_id'] === $parentUserId;
                $align = $isMe ? 'justify-end' : 'justify-start';
                $color = $isMe ? 'bg-indigo-600 text-white rounded-br-none' : 'bg-slate-800 text-slate-100 rounded-bl-none';
                $html .= '<div class="flex ' . $align . ' gap-3 mb-4">
                            <div class="max-w-[70%] p-3.5 rounded-2xl shadow-md ' . $color . '">
                                <p class="text-sm">' . nl2br(htmlspecialchars($msg['message'])) . '</p>
                                <span class="block text-[10px] text-right mt-1 opacity-70">' . date('h:i A', strtotime($msg['created_at'])) . '</span>
                            </div>
                         </div>';
            }
            return $html;
        }

        Application::$app->response->redirect('/parent/communication');
        exit();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // JSON API CONTROLLER METHODS (FOR RETURNING DATA)
    // ─────────────────────────────────────────────────────────────────────────

    private function respondJson(array $data, int $status = 200): string
    {
        http_response_code($status);
        header('Content-Type: application/json');
        return json_encode($data);
    }

    public function apiStudents(): string
    {
        $context = $this->getContext();
        return $this->respondJson([
            'success'  => true,
            'students' => $context['all_students']
        ]);
    }

    public function apiStudentProfile(string $id): string
    {
        $context = $this->getContext((int)$id);
        $student['medical'] = [];
        $student['emergency_contacts'] = $this->db()->select("SELECT * FROM emergency_contacts WHERE student_id = ?", [$student['id']]);

        return $this->respondJson([
            'success' => true,
            'student' => $student
        ]);
    }

    public function apiAttendance(string $id): string
    {
        $context = $this->getContext((int)$id);
        $student = $context['active_student'];
        $logs = $this->db()->select("SELECT * FROM attendance WHERE student_id = ? ORDER BY date DESC", [$student['id']]);

        return $this->respondJson([
            'success'    => true,
            'attendance' => $logs
        ]);
    }

    public function apiTimetable(string $id): string
    {
        $context = $this->getContext((int)$id);
        $student = $context['active_student'];
        $rows = $this->db()->select("SELECT * FROM timetables WHERE class = ? ORDER BY day_of_week, start_time ASC", [$student['class']]);

        return $this->respondJson([
            'success'   => true,
            'timetable' => $rows
        ]);
    }

    public function apiHomework(string $id): string
    {
        $context = $this->getContext((int)$id);
        $student = $context['active_student'];
        $homeworks = $this->db()->select("SELECT * FROM homeworks WHERE class = ? ORDER BY due_date ASC", [$student['class']]);

        return $this->respondJson([
            'success'   => true,
            'homeworks' => $homeworks
        ]);
    }

    public function apiExams(string $id): string
    {
        $context = $this->getContext((int)$id);
        $student = $context['active_student'];
        $results = $this->db()->select("SELECT * FROM exam_results WHERE student_id = ? ORDER BY date_published DESC", [$student['id']]);

        return $this->respondJson([
            'success' => true,
            'exams'   => $results
        ]);
    }

    public function apiFees(string $id): string
    {
        $context = $this->getContext((int)$id);
        $student = $context['active_student'];
        $invoices = $this->db()->select("SELECT * FROM fee_invoices WHERE student_id = ? ORDER BY due_date DESC", [$student['id']]);

        return $this->respondJson([
            'success'  => true,
            'invoices' => $invoices
        ]);
    }

    public function apiPayFee(string $studentId, string $invoiceId): string
    {
        $context = $this->getContext((int)$studentId);
        $student = $context['active_student'];

        $invoice = $this->db()->selectOne(
            "SELECT * FROM fee_invoices WHERE id = ? AND student_id = ? LIMIT 1",
            [(int)$invoiceId, $student['id']]
        );

        if (!$invoice || $invoice['status'] === 'paid') {
            return $this->respondJson(['success' => false, 'message' => 'Invalid or paid invoice'], 400);
        }

        $amount = (float)$invoice['amount'];
        $ref    = 'API-' . strtoupper(substr(md5(uniqid()), 0, 10));

        $this->db()->update('fee_invoices', [
            'status'      => 'paid',
            'paid_amount' => $amount,
            'paid_at'     => now(),
        ], 'id = ?', [$invoice['id']]);

        $this->db()->insert('fee_payments', [
            'tenant_id'      => $student['tenant_id'],
            'school_id'      => $student['school_id'],
            'branch_id'      => $student['branch_id'],
            'invoice_id'     => $invoice['id'],
            'amount'         => $amount,
            'payment_method' => 'API Gateway',
            'payment_ref'    => $ref,
            'paid_at'        => now(),
        ]);

        return $this->respondJson([
            'success'       => true,
            'transaction'   => $ref,
            'amount_paid'   => $amount,
            'message'       => 'Payment processed successfully'
        ]);
    }

    public function apiUpdateEmergency(string $studentId): string
    {
        $context = $this->getContext((int)$studentId);
        $student = $context['active_student'];

        // Read JSON payload
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $name     = $input['name'] ?? null;
        $relation = $input['relationship'] ?? null;
        $phone    = $input['phone'] ?? null;

        if (!$name || !$relation || !$phone) {
            return $this->respondJson(['success' => false, 'message' => 'Missing fields'], 400);
        }

        $existing = $this->db()->selectOne(
            "SELECT id FROM emergency_contacts WHERE student_id = ? LIMIT 1",
            [$student['id']]
        );

        $data = [
            'tenant_id'    => $student['tenant_id'],
            'school_id'    => $student['school_id'],
            'branch_id'    => $student['branch_id'],
            'student_id'   => $student['id'],
            'name'         => $name,
            'relationship' => $relation,
            'phone'        => $phone,
            'is_primary'   => 1,
        ];

        if ($existing) {
            $this->db()->update('emergency_contacts', $data, 'id = ?', [$existing['id']]);
        } else {
            $this->db()->insert('emergency_contacts', $data);
        }

        return $this->respondJson([
            'success' => true,
            'message' => 'Emergency contact updated successfully'
        ]);
    }

    public function apiAnnouncements(): string
    {
        $announcements = $this->db()->select(
            "SELECT * FROM announcements WHERE target_audience IN ('all', 'parents') ORDER BY published_at DESC"
        );
        return $this->respondJson([
            'success'       => true,
            'announcements' => $announcements
        ]);
    }

    public function apiMessages(): string
    {
        $context = $this->getContext();
        $parentUserId = (int)$context['guardian']['user_id'];

        $messages = $this->db()->select(
            "SELECT cm.*, sender.name as sender_name, receiver.name as receiver_name
             FROM communication_messages cm
             JOIN users sender ON sender.id = cm.sender_id
             JOIN users receiver ON receiver.id = cm.receiver_id
             WHERE cm.sender_id = ? OR cm.receiver_id = ?
             ORDER BY cm.created_at ASC",
            [$parentUserId, $parentUserId]
        );

        return $this->respondJson([
            'success'  => true,
            'messages' => $messages
        ]);
    }

    public function apiSendMessage(): string
    {
        $context = $this->getContext();
        $parentUserId = (int)$context['guardian']['user_id'];

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $message = $input['message'] ?? null;

        $staffUser = $this->db()->selectOne("SELECT id FROM users WHERE email = 'admin@psnf.edu' LIMIT 1");
        $staffId = $staffUser ? (int)$staffUser['id'] : 1;

        if (!$message) {
            return $this->respondJson(['success' => false, 'message' => 'Empty message content'], 400);
        }

        $activeStudent = $context['active_student'];
        $tenantId = $activeStudent ? $activeStudent['tenant_id'] : $context['guardian']['tenant_id'];
        $schoolId = $activeStudent ? $activeStudent['school_id'] : 1;
        $branchId = $activeStudent ? $activeStudent['branch_id'] : 1;

        $this->db()->insert('communication_messages', [
            'tenant_id'   => $tenantId,
            'school_id'   => $schoolId,
            'branch_id'   => $branchId,
            'sender_id'   => $parentUserId,
            'receiver_id' => $staffId,
            'subject'     => 'API message',
            'message'     => $message,
            'is_read'     => 0,
            'created_at'  => now(),
        ]);

        return $this->respondJson([
            'success' => true,
            'message' => 'Message sent successfully'
        ]);
    }

    public function addStudent(): string
    {
        $context = $this->getContext();
        $db = $this->db();
        $tenantId = (int)$context['guardian']['tenant_id'];

        $schools  = $db->select("SELECT id, name FROM schools WHERE tenant_id = ? AND is_active = 1 AND deleted_at IS NULL", [$tenantId]);
        $branches = $db->select("SELECT id, name, school_id FROM branches WHERE tenant_id = ? AND is_active = 1 AND deleted_at IS NULL", [$tenantId]);

        $errors = Session::getFlash('errors') ?? [];
        $old    = Session::getFlash('old') ?? [];

        return View::render('parent/add_student', array_merge($context, [
            'title'     => 'Register Student Details',
            'schools'   => $schools,
            'branches'  => $branches,
            'errors'    => $errors,
            'old'       => $old,
        ]));
    }

    public function storeStudent(): void
    {
        $userId = auth_id();
        $guardian = $this->db()->selectOne(
            "SELECT * FROM guardians WHERE user_id = ? AND deleted_at IS NULL LIMIT 1",
            [$userId]
        );

        if (!$guardian) {
            Application::$app->response->abort(403);
            exit();
        }

        $data = $this->request->getBody();

        $rules = [
            'first_name'            => 'required|min:2|max:100',
            'last_name'             => 'required|min:2|max:100',
            'gender'                => 'required|in:male,female,other',
            'dob'                   => 'required|date',
            'blood_group'           => 'nullable|in:Unknown,A+,A-,B+,B-,AB+,AB-,O+,O-',
            'aadhar_number'         => 'nullable|max:20',
            'mother_tongue'         => 'nullable|max:100',
            'address'               => 'nullable',
            'disability_detail'     => 'nullable',
            'care_instructions'     => 'nullable',
            'special_needs_summary' => 'nullable',
            'school_id'             => 'required|exists:schools,id',
            'branch_id'             => 'required|exists:branches,id',
        ];

        $validator = new \Core\Validator($data, $rules);
        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::flash('old', $data);
            Application::$app->response->redirect('/parent/students/add');
            exit();
        }

        $validated = $validator->validated();

        // Build Student Data
        $studentData = [
            'uuid'                  => str_uuid(),
            'tenant_id'             => (int)$guardian['tenant_id'],
            'school_id'             => (int)$validated['school_id'],
            'branch_id'             => (int)$validated['branch_id'],
            'admission_number'      => admission_number((int)$validated['school_id']),
            'gr_number'             => gr_number((int)$validated['school_id']),
            'first_name'            => $validated['first_name'],
            'middle_name'           => '',
            'last_name'             => $validated['last_name'],
            'gender'                => $validated['gender'],
            'dob'                   => $validated['dob'],
            'blood_group'           => $validated['blood_group'] ?? 'Unknown',
            'nationality'           => 'Indian',
            'mother_tongue'         => $validated['mother_tongue'] ?? null,
            'aadhar_number'         => $validated['aadhar_number'] ?? null,
            'disability_detail'     => $validated['disability_detail'] ?? null,
            'care_instructions'     => $validated['care_instructions'] ?? null,
            'special_needs_summary' => $validated['special_needs_summary'] ?? null,
            'address'               => $validated['address'] ?? null,
            'admission_status'      => 'applied',
            'is_active'             => 1,
            'created_by'            => $userId,
        ];

        try {
            $this->db()->transaction(function ($db) use ($studentData, $guardian, $userId) {
                // 1. Create Student
                $studentId = (int) Student::create($studentData);

                // 2. Create medical record
                StudentMedical::create([
                    'student_id'          => $studentId,
                    'allergies'           => null,
                    'triggers'            => null,
                    'current_medications' => null,
                    'care_instructions'   => $studentData['care_instructions'] ?? null,
                    'created_by'          => $userId,
                ]);

                // 3. Link guardian to student
                $this->db()->insert('guardian_student', [
                    'guardian_id' => $guardian['id'],
                    'student_id'  => $studentId,
                    'is_primary'  => 1,
                    'can_pickup'  => 1,
                ]);

                // 4. Log Timeline
                \App\Models\StudentTimeline::logEvent(
                    $studentId,
                    'admission',
                    'Application submitted by Parent',
                    ['status' => 'applied'],
                    $userId,
                    'purple',
                    'user-plus'
                );

                // 5. Log Activity
                \App\Models\ActivityLog::log('student_created', $userId, ['student_id' => $studentId]);
            });

            Session::flash('success', 'Student details added and linked successfully! Your application has been sent for admin review.');
            Application::$app->response->redirect('/parent/dashboard');
            exit();
        } catch (\Throwable $e) {
            Session::flash('error', 'Failed to save student details: ' . $e->getMessage());
            Session::flash('old', $data);
            Application::$app->response->redirect('/parent/students/add');
            exit();
        }
    }
}
