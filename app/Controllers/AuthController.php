<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use App\Services\AuthService;
use App\Models\{User, ActivityLog};

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct()
    {
        parent::__construct();
        $this->authService = new AuthService();
    }

    public function showLogin(): string
    {
        return $this->view('auth/login', ['title' => 'Login — PSNF ERP']);
    }

    public function login(): string
    {
        $body     = $this->request->getBody();
        $email    = $body['email']    ?? '';
        $password = $body['password'] ?? '';
        $remember = !empty($body['remember_me']);

        if (empty($email) || empty($password)) {
            $this->flash('error', 'Email and password are required.');
            return $this->redirect('/login');
        }

        $result = $this->authService->attempt($email, $password, $remember);

        if (!$result['success']) {
            $this->flash('error', $result['error']);
            \Core\Session::flash('old',   ['email' => $email]);

            if ($this->request->wantsJson()) {
                return $this->json(['success' => false, 'message' => $result['error']], 401);
            }
            return $this->redirect('/login');
        }

        if ($this->request->wantsJson()) {
            $userObj = $result['user'];
            $roles = $userObj['role_names'] ?? $userObj['roles'] ?? [];
            $roleStr = !empty($roles) ? (is_array($roles) ? implode(', ', $roles) : (string)$roles) : 'Educator';
            
            return $this->json([
                'success' => true,
                'token' => $result['token'],
                'user' => [
                    'id' => (string)$userObj['id'],
                    'name' => $userObj['name'] ?? 'Staff Member',
                    'email' => $userObj['email'] ?? '',
                    'phone' => $userObj['phone'] ?? '',
                    'role' => $roleStr,
                    'designation' => $userObj['designation'] ?? 'Educator',
                    'employee_id' => $userObj['employee_id'] ?? ('ST' . (1000 + (int)$userObj['id'])),
                    'school' => 'Pearl Special Needs School',
                    'branch' => 'Main Branch'
                ]
            ]);
        }

        return $this->redirect(dashboard_url());
    }

    public function logout(): string
    {
        $userSession = \Core\Session::get('user');
        $isParent = false;
        if ($userSession) {
            $role = $userSession['role'] ?? '';
            $roles = $userSession['roles'] ?? [];
            if ($role === 'parent' || in_array('parent', (array)$roles, true)) {
                $isParent = true;
            }
        }

        $this->authService->logout();

        if ($isParent) {
            return $this->redirect('/parent-login');
        }

        return $this->redirect('/login');
    }

    public function showForgotPassword(): string
    {
        return $this->view('auth/forgot-password', ['title' => 'Forgot Password — PSNF ERP']);
    }

    public function forgotPassword(): string
    {
        $email = $this->request->input('email');

        if (!$email) {
            $this->flash('error', 'Email is required.');
            return $this->redirect('/forgot-password');
        }

        $user = User::findByEmailGlobal($email);

        // Always show success to prevent email enumeration
        $this->flash('success', 'If an account with that email exists, a reset link has been sent.');

        if ($user) {
            $token     = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

            \Core\Application::$app->db->query(
                "DELETE FROM password_resets WHERE email = ?", [$email]
            );
            \Core\Application::$app->db->insert('password_resets', [
                'email'      => $email,
                'token'      => $token,
                'expires_at' => $expiresAt,
            ]);

            // TODO: Send email with reset link
            $resetLink = url("reset-password?token=$token");
            // Log for development
            ActivityLog::log('password_reset_requested', $user['id'], ['email' => $email]);
        }

        return $this->redirect('/forgot-password');
    }

    public function showResetPassword(): string
    {
        $token = $this->request->get('token');
        return $this->view('auth/reset-password', ['title' => 'Reset Password — PSNF ERP', 'token' => $token]);
    }

    public function resetPassword(): string
    {
        $body     = $this->request->getBody();
        $token    = $body['token']                ?? '';
        $password = $body['password']             ?? '';
        $confirm  = $body['password_confirmation']?? '';

        if (strlen($password) < 1) {
            $this->flash('error', 'Password is required.');
            return $this->redirect("/reset-password?token=$token");
        }

        if ($password !== $confirm) {
            $this->flash('error', 'Passwords do not match.');
            return $this->redirect("/reset-password?token=$token");
        }

        $reset = \Core\Application::$app->db->selectOne(
            "SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW() LIMIT 1",
            [$token]
        );

        if (!$reset) {
            $this->flash('error', 'Invalid or expired reset link.');
            return $this->redirect('/forgot-password');
        }

        $user = User::findByEmailGlobal($reset['email']);
        if ($user) {
            User::update($user['id'], ['password' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12])]);
            \Core\Application::$app->db->update('password_resets', ['used' => 1], 'token = ?', [$token]);
            ActivityLog::log('password_reset_success', $user['id'], []);
        }

        $this->flash('success', 'Password reset successfully. You can now log in.');
        return $this->redirect('/login');
    }

    public function showOtp(): string
    {
        return $this->view('auth/otp', ['title' => 'OTP Verification — PSNF ERP']);
    }

    public function verifyOtp(): string
    {
        $body = $this->request->getBody();
        $code = $body['code'] ?? '';
        $id   = $body['identifier'] ?? '';

        $otp = \Core\Application::$app->db->selectOne(
            "SELECT * FROM otp_codes WHERE identifier = ? AND used = 0 AND expires_at > NOW() ORDER BY id DESC LIMIT 1",
            [$id]
        );

        if (!$otp || $otp['code'] !== $code) {
            $this->flash('error', 'Invalid OTP code.');
            return $this->redirect('/otp');
        }

        \Core\Application::$app->db->update('otp_codes', ['used' => 1], 'id = ?', [$otp['id']]);

        if ($this->request->wantsJson()) {
            return $this->json(['success' => true, 'message' => 'OTP verified.']);
        }

        return $this->redirect(dashboard_url());
    }
    public function apiDriverLogin(): string
    {
        $body     = $this->request->getBody();
        $name     = $body['name']     ?? '';
        $password = $body['password'] ?? '';

        if (empty($name) || empty($password)) {
            return $this->json(['success' => false, 'message' => 'Name and password are required.'], 400);
        }

        $db = \Core\Application::$app->db;
        $user = $db->selectOne(
            "SELECT u.*, r.slug as role_slug 
             FROM users u 
             JOIN user_roles ur ON u.id = ur.user_id
             JOIN roles r ON ur.role_id = r.id
             WHERE u.name = ? AND u.deleted_at IS NULL AND u.is_active = 1 AND r.slug = 'driver' LIMIT 1",
            [$name]
        );

        if (!$user || !password_verify($password, $user['password'])) {
            return $this->json(['success' => false, 'message' => 'Invalid name or password.'], 401);
        }

        $requiresChange = ($password === $user['phone']);

        $payload = [
            'sub'       => $user['id'],
            'email'     => $user['email'],
            'tenant_id' => $user['tenant_id'],
            'school_id' => $user['school_id'],
            'branch_id' => $user['branch_id'],
            'roles'     => [$user['role_slug']],
        ];
        
        $token = \Core\JWT::encode($payload);
        
        ActivityLog::log('user_login', (int)$user['id'], ['ip' => \Core\Application::$app->request->ip()]);

        return $this->json([
            'success' => true,
            'token' => $token,
            'requires_password_change' => $requiresChange,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'phone' => $user['phone'],
            ]
        ]);
    }

    public function apiDriverChangePassword(): string
    {
        $userId = auth_id();
        if (!$userId) {
            return $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $body = $this->request->getBody();
        $password = $body['password'] ?? '';
        $passwordConfirm = $body['password_confirmation'] ?? '';

        if (strlen($password) < 6) {
            return $this->json(['success' => false, 'message' => 'Password must be at least 6 characters.'], 400);
        }

        if ($password !== $passwordConfirm) {
            return $this->json(['success' => false, 'message' => 'Passwords do not match.'], 400);
        }

        $db = \Core\Application::$app->db;
        $db->update('users', [
            'password' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
            'updated_at' => now()
        ], "id = ?", [$userId]);

        ActivityLog::log('password_reset_success', (int)$userId, []);

        return $this->json(['success' => true, 'message' => 'Password changed successfully.']);
    }
}
