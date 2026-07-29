<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\{User, ActivityLog};
use Core\{Session, JWT, RateLimiter};

class AuthService
{
    private RateLimiter $limiter;

    public function __construct()
    {
        $this->limiter = new RateLimiter(
            config('auth.max_login_attempts', 5),
            config('auth.lockout_minutes', 15)
        );
    }

    public function attempt(string $email, string $password, bool $remember = false): array
    {
        $ip  = \Core\Application::$app->request->ip();
        $key = RateLimiter::key('login', $ip . $email);

        // Rate limit check
        if ($this->limiter->tooManyAttempts($key)) {
            $available = $this->limiter->availableIn($key);
            ActivityLog::log('login_rate_limited', null, ['email' => $email]);
            return ['success' => false, 'error' => "Too many attempts. Try again in " . ceil($available / 60) . " minutes.", 'rate_limited' => true];
        }

        // Find user
        $user = User::findByEmailGlobal($email);

        if (!$user) {
            $this->limiter->hit($key);
            ActivityLog::log('login_failed', null, ['email' => $email, 'reason' => 'user_not_found']);
            return ['success' => false, 'error' => 'Invalid email or password.'];
        }

        // Account locked
        if (User::isLocked($user)) {
            ActivityLog::log('login_blocked_locked', $user['id'], ['email' => $email]);
            return ['success' => false, 'error' => 'Account is locked. Contact administrator.'];
        }

        // Account inactive
        if (!$user['is_active']) {
            ActivityLog::log('login_blocked_inactive', $user['id'], ['email' => $email]);
            return ['success' => false, 'error' => 'Account is inactive. Contact administrator.'];
        }

        // Password check
        if (!password_verify($password, $user['password'])) {
            $this->limiter->hit($key);
            User::incrementLoginAttempts($user['id']);

            $attempts = (int) $user['login_attempts'] + 1;
            $maxAttempts = config('auth.max_login_attempts', 5);

            if ($attempts >= $maxAttempts) {
                User::lockAccount($user['id'], config('auth.lockout_minutes', 15));
                ActivityLog::log('login_account_locked', $user['id'], ['email' => $email]);
                return ['success' => false, 'error' => "Account locked after $maxAttempts failed attempts."];
            }

            ActivityLog::log('login_failed', $user['id'], ['email' => $email, 'attempts' => $attempts]);
            $remaining = $maxAttempts - $attempts;
            return ['success' => false, 'error' => "Invalid credentials. $remaining attempts remaining."];
        }

        // Success — load full user with roles/permissions
        $fullUser = User::withRoles($user['id']);

        // Set tenant scope globally
        \Core\Database::setTenantScope($user['tenant_id'], $user['school_id'], $user['branch_id']);

        // Store in session
        $this->createSession($fullUser, $remember);

        // Auto attendance check for teachers on login
        $roles = $fullUser['roles'] ?? [];
        if (in_array('teacher', $roles) || in_array('Teacher', $roles)) {
            $db = \Core\Application::$app->db;
            
            // Ensure teacher_lecture_attendance table exists
            $db->query("
                CREATE TABLE IF NOT EXISTS `teacher_lecture_attendance` (
                  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                  `tenant_id` int(10) UNSIGNED NOT NULL,
                  `school_id` int(10) UNSIGNED NOT NULL,
                  `branch_id` int(10) UNSIGNED NOT NULL,
                  `user_id` int(10) UNSIGNED NOT NULL,
                  `attendance_date` date NOT NULL,
                  `opened_at` datetime NOT NULL,
                  `status` enum('on_time','late') NOT NULL,
                  `lecture_time` time NOT NULL,
                  `grace_period` int(10) UNSIGNED NOT NULL,
                  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            $today = date('l');
            $now = date('H:i:s');
            
            // Check for timetabled slot starting within 5 minutes of now
            $slot = $db->selectOne("
                SELECT * FROM timetables
                WHERE teacher_name = ? AND day_of_week = ?
                  AND ABS(TIME_TO_SEC(TIMEDIFF(start_time, ?))) <= 300
                LIMIT 1
            ", [$fullUser['name'], $today, $now]);

            if ($slot) {
                // Ensure no duplicate checkins for this slot today
                $exists = $db->selectOne("
                    SELECT id FROM teacher_lecture_attendance
                    WHERE user_id = ? AND attendance_date = ? AND lecture_time = ?
                    LIMIT 1
                ", [$fullUser['id'], date('Y-m-d'), $slot['start_time']]);

                if (!$exists) {
                    $db->insert('teacher_lecture_attendance', [
                        'tenant_id'       => $fullUser['tenant_id'],
                        'school_id'       => $fullUser['school_id'],
                        'branch_id'       => $fullUser['branch_id'],
                        'user_id'         => $fullUser['id'],
                        'attendance_date' => date('Y-m-d'),
                        'opened_at'       => date('Y-m-d H:i:s'),
                        'status'          => 'on_time',
                        'lecture_time'    => $slot['start_time'],
                        'grace_period'    => 5
                    ]);
                    ActivityLog::log('teacher_lec_auto_attendance', $fullUser['id'], [
                        'timetable_id' => $slot['id'],
                        'subject'      => $slot['subject']
                    ]);
                }
            }
        }

        // Generate JWT
        $jwt = JWT::encode([
            'sub'       => $user['id'],
            'email'     => $user['email'],
            'tenant_id' => $user['tenant_id'],
            'school_id' => $user['school_id'],
            'branch_id' => $user['branch_id'],
            'roles'     => $fullUser['roles'],
        ]);

        // Record login
        User::recordLogin($user['id'], $ip);
        $this->limiter->clear($key);

        ActivityLog::log('login_success', $user['id'], []);

        return ['success' => true, 'user' => $fullUser, 'token' => $jwt];
    }

    public function logout(): void
    {
        $userId = auth_id();
        if ($userId) {
            ActivityLog::log('logout', $userId);
            try {
                \Core\Application::$app->db->update('users', ['remember_token' => null], 'id = ?', [$userId]);
            } catch (\Throwable $e) {}
        }
        if (isset($_COOKIE['remember_token'])) {
            unset($_COOKIE['remember_token']);
            setcookie('remember_token', '', time() - 3600, '/');
        }
        Session::destroy();
    }

    public function createSession(array $user, bool $remember = false): void
    {
        unset($user['password'], $user['remember_token'], $user['two_factor_secret']);

        Session::set('user', $user);
        Session::set('tenant_id', $user['tenant_id']);
        Session::set('school_id', $user['school_id']);
        Session::set('branch_id', $user['branch_id']);
        Session::updateActivity();
        Session::regenerate();

        if ($remember) {
            $token = bin2hex(random_bytes(40));
            \Core\Application::$app->db->update('users', ['remember_token' => hash('sha256', $token)], 'id = ?', [$user['id']]);
            setcookie('remember_token', $token, time() + (86400 * config('auth.remember_me_days', 30)), '/', '', false, true);
        }
    }

    public function checkRememberToken(): ?array
    {
        $cookie = $_COOKIE['remember_token'] ?? null;
        if (!$cookie) return null;

        $hash = hash('sha256', $cookie);
        $user = \Core\Application::$app->db->selectOne(
            "SELECT * FROM users WHERE remember_token = ? AND is_active = 1 AND deleted_at IS NULL LIMIT 1",
            [$hash]
        );

        if (!$user) return null;

        $fullUser = User::withRoles($user['id']);
        $this->createSession($fullUser);
        return $fullUser;
    }

    public function user(): ?array
    {
        return Session::get('user');
    }

    public function check(): bool
    {
        return Session::has('user');
    }
}
