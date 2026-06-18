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
            return $this->json(['success' => true, 'token' => $result['token']]);
        }

        return $this->redirect(dashboard_url());
    }

    public function logout(): string
    {
        $this->authService->logout();
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

        if (strlen($password) < 8) {
            $this->flash('error', 'Password must be at least 8 characters.');
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
}
