<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use Core\Session;
use App\Models\User;
use App\Models\ActivityLog;

class ParentPortalLoginController extends Controller
{
    public function showLogin(): string
    {
        return $this->view('auth/parent-login', ['title' => 'Parent Portal Login']);
    }

    public function login(): string
    {
        $data = $this->request->getBody();
        $rules = [
            'phone'    => 'required',
            'password' => 'required'
        ];

        $validator = new \Core\Validator($data, $rules);
        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::flash('old', $data);
            return $this->redirect('/parent-login');
        }

        $db = \Core\Application::$app->db;
        $user = $db->selectOne(
            "SELECT u.*, r.slug as role_slug 
             FROM users u 
             JOIN user_roles ur ON u.id = ur.user_id
             JOIN roles r ON ur.role_id = r.id
             WHERE u.phone = ? AND u.deleted_at IS NULL AND u.is_active = 1 AND r.slug = 'parent' LIMIT 1",
            [$data['phone']]
        );

        if (!$user || !password_verify($data['password'], $user['password'])) {
            Session::flash('error', 'Invalid phone number or password. Please try again.');
            Session::flash('old', ['phone' => $data['phone']]);
            return $this->redirect('/parent-login');
        }

        // Parent specific logic
        Session::set('user', [
            'id' => $user['id'],
            'uuid' => $user['uuid'],
            'name' => $user['name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'role' => $user['role_slug'],
            'tenant_id' => $user['tenant_id'],
            'school_id' => $user['school_id'] ?? null,
            'branch_id' => $user['branch_id'] ?? null,
            'roles' => [$user['role_slug']]
        ]);

        ActivityLog::log('user_login', (int)$user['id'], ['ip' => $_SERVER['REMOTE_ADDR'] ?? null]);

        // Check if this is the first login by verifying if password is the phone number
        if ($data['phone'] === $data['password']) {
            Session::flash('info', 'For security reasons, please set a new password for your account.');
            return $this->redirect('/parent/change-password');
        }

        // Parent portal home
        return $this->redirect('/parent/dashboard');
    }

    public function showChangePassword(): string
    {
        return $this->view('auth/parent-change-password', ['title' => 'Setup Parent Portal Account']);
    }

    public function changePassword(): string
    {
        $data = $this->request->getBody();
        $rules = [
            'name'                  => 'required|min:2|max:100',
            'password'              => 'required|min:6'
        ];
        
        $validator = new \Core\Validator($data, $rules);
        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            return $this->redirect('/parent/change-password');
        }
        
        if ($data['password'] !== ($data['password_confirmation'] ?? '')) {
            Session::flash('error', 'Passwords do not match.');
            return $this->redirect('/parent/change-password');
        }

        $userId = auth_id();
        $db = \Core\Application::$app->db;
        
        // Update user
        $db->update('users', [
            'name' => $data['name'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
            'updated_at' => now()
        ], "id = ?", [$userId]);
        
        // Update guardian table name to sync it
        $db->update('guardians', [
            'name' => $data['name']
        ], "user_id = ?", [$userId]);
        
        // Update session
        $userSession = Session::get('user');
        $userSession['name'] = $data['name'];
        Session::set('user', $userSession);
        
        Session::flash('success', 'Account updated successfully! Welcome to the Parent Portal.');
        return $this->redirect('/parent/dashboard');
    }

    public function logout(): string
    {
        $authService = new \App\Services\AuthService();
        $authService->logout();
        Session::flash('success', 'Logged out successfully from Parent Portal.');
        return $this->redirect('/parent-login');
    }
}
