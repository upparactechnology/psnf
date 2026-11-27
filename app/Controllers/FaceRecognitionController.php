<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;
use Core\View;

class FaceRecognitionController extends Controller
{
    private function db()
    {
        return Application::$app->db;
    }

    public function index(): string
    {
        return $this->redirect(url('attendance/face-kiosk'));
    }

    /**
     * Face attendance kiosk - embedded in ERP layout, no auth required.
     */
    public function kioskView(): string
    {
        return View::render('attendance/face_kiosk', []);
    }

    /**
     * Face registration - embedded in ERP layout, requires admin auth overlay.
     * Handles AJAX login/logout POST via the view itself.
     */
    public function registerView(): string
    {
        return View::render('attendance/face_register', []);
    }

    /**
     * Face Scan Logs — admin diagnostics for the face kiosk: every recognition attempt
     * with its score, closest match, quality metrics, camera info and (for failures)
     * the snapshot. Rows are written by the Python face service (face_scan_logs).
     */
    public function scanLogsView(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        $tableExists = (bool) $db->selectOne(
            "SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'face_scan_logs'"
        );

        $today = date('Y-m-d');
        $from = (string) ($_GET['from'] ?? date('Y-m-d', strtotime('-6 days')));
        $to   = (string) ($_GET['to'] ?? $today);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) $from = date('Y-m-d', strtotime('-6 days'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to))   $to = $today;
        if ($from > $to) [$from, $to] = [$to, $from];

        $outcomes = ['success', 'cooldown', 'not_recognized', 'blurry', 'no_face', 'multiple_faces',
                     'liveness', 'dark', 'overexposed', 'inactive_user', 'error'];
        $outcome = (string) ($_GET['outcome'] ?? '');
        if ($outcome !== '' && $outcome !== 'failures' && !in_array($outcome, $outcomes, true)) $outcome = '';
        $userFilter = (int) ($_GET['user'] ?? 0);
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 50;

        $data = [
            'tableExists' => $tableExists, 'from' => $from, 'to' => $to, 'outcome' => $outcome,
            'userFilter' => $userFilter, 'page' => $page, 'perPage' => $perPage, 'outcomes' => $outcomes,
            'totals' => [], 'reasons' => [], 'hours' => [], 'scoreHist' => ['success' => [], 'not_recognized' => []],
            'staff' => [], 'rows' => [], 'totalRows' => 0, 'sessions' => ['total' => 0, 'ok' => 0],
            'retrySaves' => 0, 'users' => [],
        ];

        // Registered staff + face profiles (works even before the first scan log exists)
        $tenantSql = $tenantId ? ' AND u.tenant_id = ?' : '';
        $tenantArg = $tenantId ? [$tenantId] : [];
        $staff = $db->select(
            "SELECT u.id, u.name, u.employee_id, u.designation, COUNT(f.id) AS vectors, MIN(f.created_at) AS registered_at,
                    (SELECT MAX(a.check_in) FROM attendance a WHERE a.user_id = u.id) AS last_check_in
             FROM face_embeddings f JOIN users u ON u.id = f.user_id
             WHERE u.deleted_at IS NULL {$tenantSql}
             GROUP BY u.id, u.name, u.employee_id, u.designation
             ORDER BY u.name",
            $tenantArg
        );
        $data['users'] = $staff;

        if (!$tableExists) {
            $data['staff'] = $staff;
            return View::render('attendance/face_scan_logs', $data);
        }

        // Shared WHERE (date range + tenant). Kiosk scans without a login have tenant NULL.
        $where = "l.created_at >= ? AND l.created_at < DATE_ADD(?, INTERVAL 1 DAY)";
        $args  = [$from . ' 00:00:00', $to];
        if ($tenantId) { $where .= " AND (l.tenant_id = ? OR l.tenant_id IS NULL)"; $args[] = $tenantId; }

        // KPIs
        $t = $db->selectOne(
            "SELECT COUNT(*) AS total,
                    SUM(outcome = 'success') AS success,
                    SUM(outcome = 'cooldown') AS cooldown,
                    SUM(outcome NOT IN ('success','cooldown')) AS failed,
                    AVG(CASE WHEN outcome IN ('success','cooldown') THEN score END) AS avg_ok_score,
                    AVG(processing_ms) AS avg_ms
             FROM face_scan_logs l WHERE $where",
            $args
        ) ?: [];
        $data['totals'] = $t;

        // Sessions = one person standing in front of the kiosk (groups automatic retries)
        $s = $db->selectOne(
            "SELECT COUNT(*) AS total, SUM(ok) AS ok, SUM(ok AND attempts > 1) AS saved_by_retry FROM (
                SELECT session_id, MAX(outcome IN ('success','cooldown')) AS ok, COUNT(*) AS attempts
                FROM face_scan_logs l WHERE $where AND session_id IS NOT NULL GROUP BY session_id
             ) x",
            $args
        ) ?: [];
        $data['sessions'] = ['total' => (int) ($s['total'] ?? 0), 'ok' => (int) ($s['ok'] ?? 0)];
        $data['retrySaves'] = (int) ($s['saved_by_retry'] ?? 0);

        // Failure reasons
        $data['reasons'] = $db->select(
            "SELECT outcome, COUNT(*) AS n FROM face_scan_logs l
             WHERE $where AND outcome NOT IN ('success','cooldown') GROUP BY outcome ORDER BY n DESC",
            $args
        );

        // By hour of day
        $data['hours'] = $db->select(
            "SELECT HOUR(created_at) AS h,
                    SUM(outcome IN ('success','cooldown')) AS ok,
                    SUM(outcome = 'blurry') AS blurry,
                    SUM(outcome = 'not_recognized') AS notrec,
                    SUM(outcome NOT IN ('success','cooldown','blurry','not_recognized')) AS other,
                    AVG(face_brightness) AS face_light
             FROM face_scan_logs l WHERE $where GROUP BY HOUR(created_at) ORDER BY h",
            $args
        );

        // Score distribution (0.05 buckets)
        foreach ($db->select(
            "SELECT CASE WHEN outcome IN ('success','cooldown') THEN 'success' ELSE 'not_recognized' END AS grp,
                    FLOOR(score * 20) / 20 AS b, COUNT(*) AS n
             FROM face_scan_logs l
             WHERE $where AND score IS NOT NULL AND outcome IN ('success','cooldown','not_recognized')
             GROUP BY grp, b ORDER BY b",
            $args
        ) as $r) {
            $data['scoreHist'][$r['grp']][number_format((float) $r['b'], 2)] = (int) $r['n'];
        }

        // Per registered person: successes, scores, near-misses where they were the closest match
        $per = [];
        foreach ($db->select(
            "SELECT COALESCE(matched_user_id, best_user_id) AS uid,
                    SUM(outcome IN ('success','cooldown')) AS ok,
                    AVG(CASE WHEN outcome IN ('success','cooldown') THEN score END) AS avg_score,
                    MIN(CASE WHEN outcome IN ('success','cooldown') THEN score END) AS min_score,
                    SUM(outcome = 'not_recognized' AND score >= 0.40) AS near_miss,
                    MAX(CASE WHEN outcome IN ('success','cooldown') THEN created_at END) AS last_ok
             FROM face_scan_logs l WHERE $where AND COALESCE(matched_user_id, best_user_id) IS NOT NULL
             GROUP BY uid",
            $args
        ) as $r) {
            $per[(int) $r['uid']] = $r;
        }
        foreach ($staff as &$st) {
            $st['stats'] = $per[(int) $st['id']] ?? null;
        }
        unset($st);
        $data['staff'] = $staff;

        // Attempt list (filtered)
        $listWhere = $where;
        $listArgs  = $args;
        if ($outcome === 'failures') {
            $listWhere .= " AND l.outcome NOT IN ('success','cooldown')";
        } elseif ($outcome !== '') {
            $listWhere .= " AND l.outcome = ?"; $listArgs[] = $outcome;
        }
        if ($userFilter > 0) {
            $listWhere .= " AND (l.matched_user_id = ? OR l.best_user_id = ?)";
            $listArgs[] = $userFilter; $listArgs[] = $userFilter;
        }
        $cnt = $db->selectOne("SELECT COUNT(*) AS c FROM face_scan_logs l WHERE $listWhere", $listArgs);
        $data['totalRows'] = (int) ($cnt['c'] ?? 0);
        $offset = ($page - 1) * $perPage;
        $data['rows'] = $db->select(
            "SELECT l.*, mu.name AS matched_name, bu.name AS best_name
             FROM face_scan_logs l
             LEFT JOIN users mu ON mu.id = l.matched_user_id
             LEFT JOIN users bu ON bu.id = l.best_user_id
             WHERE $listWhere ORDER BY l.created_at DESC, l.id DESC LIMIT $perPage OFFSET $offset",
            $listArgs
        );

        return View::render('attendance/face_scan_logs', $data);
    }

    public function historyView(): string
    {
        return $this->redirect(url('attendance/face-kiosk'));
    }

    public function verifyView(): string
    {
        return $this->redirect(url('attendance/face-kiosk'));
    }
}
