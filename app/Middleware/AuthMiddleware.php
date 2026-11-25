<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\{Session, Database};

class AuthMiddleware
{
    public function handle(\Core\Request $request): ?string
    {
        // Try JWT from header for API
        $token = $request->bearerToken();
        if ($token) {
            $payload = \Core\JWT::decode($token);
            $userId  = is_array($payload) ? (int) ($payload['sub'] ?? 0) : 0;

            // Never trust identity / roles / tenant claims from the token itself:
            // reload the user from the DB and reject disabled or deleted accounts.
            $dbUser = null;
            if ($userId > 0) {
                $dbUser = \Core\Application::$app->db->selectOne(
                    "SELECT id, tenant_id, school_id, branch_id, is_active, locked_until FROM users WHERE id = ? AND deleted_at IS NULL LIMIT 1",
                    [$userId]
                );
            }

            if ($dbUser && (int) $dbUser['is_active'] === 1) {
                Database::setTenantScope($dbUser['tenant_id'], $dbUser['school_id'], $dbUser['branch_id']);

                $freshUser = \App\Models\User::withRoles((int) $dbUser['id']);
                if ($freshUser) {
                    unset($freshUser['password'], $freshUser['remember_token'], $freshUser['two_factor_secret']);
                    $freshUser['_api'] = true;
                    // Request-scoped user so auth_id()/auth()/permission checks work
                    Session::set('user', $freshUser);
                    return null; // Authorized via JWT
                }
            }

            // Bearer token provided but invalid — return JSON 401
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid or expired token.']);
            exit();
        }

        // Session auth
        if (!Session::has('user')) {
            // Try remember token
            $authService = new \App\Services\AuthService();
            $user = $authService->checkRememberToken();
            if ($user) {
                Database::setTenantScope($user['tenant_id'], $user['school_id'], $user['branch_id']);
                return null;
            }

            Session::flash('error', 'Please log in to continue.');

            if ($request->isAjax() || $request->isHtmx()) {
                http_response_code(401);
                return json_encode(['success' => false, 'redirect' => url('login')]);
            }

            \Core\Application::$app->response->redirect('/login');
            exit();
        }

        // Check session timeout
        $timeout = config('auth.session_timeout', 1200);
        $last    = Session::lastActivity();

        if ($last && (time() - $last) > $timeout) {
            Session::destroy();
            Session::flash('error', 'Session expired. Please log in again.');
            \Core\Application::$app->response->redirect('/login');
            exit();
        }

        // Auto-logout teacher if today's timetable sessions are over
        $user = Session::get('user');
        if ($this->teacherScheduleEnded($user)) {
            Session::destroy();
            Session::flash('error', 'Session ended automatically after your scheduled lectures concluded.');
            \Core\Application::$app->response->redirect('/login');
            exit();
        }

        Session::updateActivity();

        // Re-set tenant scope from session
        $user = Session::get('user');
        Database::setTenantScope($user['tenant_id'], $user['school_id'], $user['branch_id']);

        // Refresh user roles & permissions from DB so changes take effect without re-login
        $status = \Core\Application::$app->db->selectOne(
            "SELECT is_active, deleted_at FROM users WHERE id = ? LIMIT 1",
            [(int) $user['id']]
        );
        if (!$status || (int) $status['is_active'] !== 1 || !empty($status['deleted_at'])) {
            // Account was deactivated / deleted since login
            Session::destroy();
            Session::flash('error', 'Your account is no longer active.');
            \Core\Application::$app->response->redirect('/login');
            exit();
        }

        $freshUser = \App\Models\User::withRoles((int) $user['id']);
        if ($freshUser) {
            unset($freshUser['password'], $freshUser['remember_token'], $freshUser['two_factor_secret']);
            $freshUser['id'] = $user['id']; // preserve session key
            Session::set('user', $freshUser);
        }

        return null;
    }

    /**
     * True when a pure teacher account logged in before today's last timetable
     * slot ended and that slot is now over. Admins are never auto-logged-out,
     * and a teacher who logs in after the last slot is allowed to stay.
     */
    private function teacherScheduleEnded(?array $user): bool
    {
        if (!$user) return false;
        $roles = array_map('strtolower', (array) ($user['roles'] ?? []));
        if (!in_array('teacher', $roles, true)) return false;
        if (array_intersect($roles, ['super_admin', 'school_admin', 'admin'])) return false;

        try {
            $db    = \Core\Application::$app->db;
            $today = date('l');

            // Prefer the user id link; fall back to the legacy name match only
            // when no timetable row for today is linked by teacher_id.
            $lastSlot = $db->selectOne(
                "SELECT MAX(end_time) AS max_end FROM timetables WHERE teacher_id = ? AND day_of_week = ?",
                [(int) $user['id'], $today]
            );
            if (empty($lastSlot['max_end'])) {
                $lastSlot = $db->selectOne(
                    "SELECT MAX(end_time) AS max_end FROM timetables
                     WHERE teacher_id IS NULL AND teacher_name = ? AND day_of_week = ?",
                    [(string) ($user['name'] ?? ''), $today]
                );
            }
            if (empty($lastSlot['max_end'])) return false;

            $endTs = strtotime(date('Y-m-d') . ' ' . $lastSlot['max_end']);
            if ($endTs === false || time() <= $endTs) return false;

            // Only log out sessions that started before the last slot ended.
            $loginAt = (int) Session::get('login_at', 0);
            if ($loginAt === 0) {
                // Legacy session without a login timestamp: record now, don't kick.
                Session::set('login_at', time());
                return false;
            }
            return $loginAt < $endTs;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
