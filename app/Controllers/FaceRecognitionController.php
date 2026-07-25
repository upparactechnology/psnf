<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;

class FaceRecognitionController extends Controller
{
    private function db()
    {
        return Application::$app->db;
    }

    public function index(): string
    {
        $db = $this->db();
        
        // Fetch stats
        $totalEmployees = $db->selectOne("SELECT COUNT(*) as cnt FROM employees WHERE status = 'active'")['cnt'] ?? 0;
        $registeredFaces = $db->selectOne("SELECT COUNT(DISTINCT employee_id) as cnt FROM face_embeddings")['cnt'] ?? 0;
        $todayAttendance = $db->selectOne("SELECT COUNT(*) as cnt FROM attendance WHERE attendance_date = CURDATE()")['cnt'] ?? 0;

        // Fetch recent attendance logs
        $recentLogs = $db->select("
            SELECT a.*, e.employee_code, e.name, e.department
            FROM attendance a
            JOIN employees e ON a.employee_id = e.id
            ORDER BY a.check_in DESC
            LIMIT 10
        ") ?: [];

        return $this->render('attendance/dashboard', [
            'totalEmployees' => $totalEmployees,
            'registeredFaces' => $registeredFaces,
            'todayAttendance' => $todayAttendance,
            'recentLogs' => $recentLogs
        ]);
    }

    public function registerView(): string
    {
        return $this->render('attendance/register');
    }

    public function verifyView(): string
    {
        return $this->render('attendance/verify');
    }

    public function historyView(): string
    {
        $db = $this->db();
        $logs = $db->select("
            SELECT a.*, e.employee_code, e.name, e.department
            FROM attendance a
            JOIN employees e ON a.employee_id = e.id
            ORDER BY a.check_in DESC
            LIMIT 100
        ") ?: [];

        return $this->render('attendance/history', [
            'logs' => $logs
        ]);
    }
}
