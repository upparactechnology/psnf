<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;
use App\Models\{User, ActivityLog};

class TeacherPortalController extends Controller
{
    private function db()
    {
        return Application::$app->db;
    }

    public function dashboard(): string
    {
        $sessionUser = $this->auth();
        if (!$sessionUser) {
            Application::$app->response->redirect('/login');
            exit();
        }

        $db = $this->db();

        // Load fresh user data to get updated settings
        $user = User::find($sessionUser['id']);
        if (!$user) {
            Application::$app->response->redirect('/login');
            exit();
        }
        $tenantId = $user['tenant_id'];

        $teacherAttendance = null;
        $lectureTime = $user['lecture_time'] ?? null;
        if ($lectureTime) {
            $today = date('Y-m-d');
            $teacherAttendance = $db->selectOne(
                "SELECT * FROM teacher_attendance WHERE user_id = ? AND attendance_date = ?",
                [$user['id'], $today]
            );

            if (!$teacherAttendance) {
                $openedAt = date('Y-m-d H:i:s');
                $grace = (int) ($user['grace_period'] ?? 5);

                // Compute cutoff time
                $lectureTimestamp = strtotime($today . ' ' . $lectureTime);
                $cutoffTimestamp = $lectureTimestamp + ($grace * 60);
                $currentTimestamp = time();

                $status = ($currentTimestamp <= $cutoffTimestamp) ? 'on_time' : 'late';

                $db->insert('teacher_attendance', [
                    'tenant_id'       => $user['tenant_id'],
                    'school_id'       => $user['school_id'],
                    'branch_id'       => $user['branch_id'],
                    'user_id'         => $user['id'],
                    'attendance_date' => $today,
                    'opened_at'       => $openedAt,
                    'status'          => $status,
                    'lecture_time'    => $lectureTime,
                    'grace_period'    => $grace
                ]);

                $teacherAttendance = $db->selectOne(
                    "SELECT * FROM teacher_attendance WHERE user_id = ? AND attendance_date = ?",
                    [$user['id'], $today]
                );

                ActivityLog::log('teacher_attendance_checkin', $user['id'], [
                    'status'    => $status,
                    'opened_at' => $openedAt
                ]);
            }
        }

        // Determine assigned apps
        $assignedApps = [];
        $hasAppAccessRecords = $db->selectOne("SELECT 1 FROM user_apps WHERE user_id = ?", [$user['id']]);
        
        if ($hasAppAccessRecords) {
            $userApps = $db->select("SELECT app_name FROM user_apps WHERE user_id = ?", [$user['id']]);
            $assignedApps = array_column($userApps, 'app_name');
        }

        $stats = [
            'total_students'  => (int) ($db->selectOne("SELECT COUNT(*) as c FROM students WHERE tenant_id = ? AND deleted_at IS NULL", [$tenantId])['c'] ?? 0),
            'total_users'     => (int) ($db->selectOne("SELECT COUNT(*) as c FROM users WHERE tenant_id = ? AND deleted_at IS NULL", [$tenantId])['c'] ?? 0),
        ];

        // Fetch teacher's timetable schedule for today
        $todayDay = date('l'); // e.g. Monday
        $teacherSchedule = $db->select(
            "SELECT * FROM timetables WHERE teacher_name = ? AND day_of_week = ? ORDER BY start_time ASC",
            [$user['name'], $todayDay]
        );

        // Fetch announcements
        $announcements = $db->select(
            "SELECT * FROM announcements WHERE tenant_id = ? AND target_audience IN ('all', 'teachers', 'staff') ORDER BY published_at DESC LIMIT 5",
            [$tenantId]
        );

        return $this->view('teacher/dashboard', compact(
            'user', 'teacherAttendance', 'assignedApps', 'stats', 'teacherSchedule', 'announcements'
        ));
    }
}
