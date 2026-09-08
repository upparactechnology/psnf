<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;
use App\Models\{User, ActivityLog};

class ProfileController extends Controller
{
    private function db()
    {
        return Application::$app->db;
    }

    public function index(): string
    {
        $userId = $this->authId();
        $user = User::find($userId);
        if (!$user) {
            $this->flash('error', 'User not found.');
            return $this->redirect('/dashboard');
        }

        // Get user roles
        $roles = User::getRoles($userId);

        // Get last login info
        $lastLogin = $user['last_login_at'] ? date('M d, Y h:i A', strtotime($user['last_login_at'])) : 'Never';
        $lastLoginIp = $user['last_login_ip'] ?? 'N/A';

        return $this->view('profile/index', compact('user', 'roles', 'lastLogin', 'lastLoginIp'));
    }

    public function update(): string
    {
        $userId = $this->authId();
        $user = User::find($userId);
        if (!$user) {
            $this->flash('error', 'User not found.');
            return $this->redirect('/dashboard');
        }

        $name  = trim((string)$this->request->input('name', ''));
        $email = trim((string)$this->request->input('email', ''));
        $phone = trim((string)$this->request->input('phone', ''));
        $gender = trim((string)$this->request->input('gender', ''));
        $dob = trim((string)$this->request->input('dob', ''));
        $designation = trim((string)$this->request->input('designation', ''));

        if (empty($name)) {
            $this->flash('error', 'Name is required.');
            return $this->redirect('/profile');
        }

        if (empty($email)) {
            $this->flash('error', 'Email is required.');
            return $this->redirect('/profile');
        }

        // Check if email is taken by another user
        $existing = $this->db()->selectOne(
            "SELECT id FROM users WHERE email = ? AND id != ? AND deleted_at IS NULL",
            [$email, $userId]
        );
        if ($existing) {
            $this->flash('error', 'Email address is already taken by another user.');
            return $this->redirect('/profile');
        }

        try {
            $updateData = [
                'name'        => $name,
                'email'       => $email,
                'phone'       => $phone ?: null,
                'gender'      => $gender ?: null,
                'dob'         => $dob ?: null,
                'designation' => $designation ?: null,
                'updated_at'  => now(),
            ];

            $this->db()->update('users', $updateData, 'id = ?', [$userId]);

            // Update session data
            $sessionUser = $this->auth();
            if ($sessionUser) {
                $sessionUser['name'] = $name;
                $sessionUser['email'] = $email;
                \Core\Session::set('user', $sessionUser);
            }

            ActivityLog::log('profile_updated', $userId, [
                'fields' => array_keys($updateData),
            ]);

            $this->flash('success', 'Profile updated successfully.');
        } catch (\Throwable $e) {
            $this->flash('error', 'Failed to update profile: ' . $e->getMessage());
        }

        return $this->redirect('/profile');
    }

    public function changePassword(): string
    {
        $userId = $this->authId();
        $user = User::find($userId);
        if (!$user) {
            $this->flash('error', 'User not found.');
            return $this->redirect('/dashboard');
        }

        $currentPassword = $this->request->input('current_password', '');
        $newPassword = $this->request->input('new_password', '');
        $confirmPassword = $this->request->input('confirm_password', '');

        if (empty($currentPassword)) {
            $this->flash('error', 'Current password is required.');
            return $this->redirect('/profile');
        }

        if (empty($newPassword)) {
            $this->flash('error', 'New password is required.');
            return $this->redirect('/profile');
        }

        if ($newPassword !== $confirmPassword) {
            $this->flash('error', 'New password and confirmation do not match.');
            return $this->redirect('/profile');
        }

        // Verify current password
        if (!password_verify($currentPassword, $user['password'])) {
            $this->flash('error', 'Current password is incorrect.');
            return $this->redirect('/profile');
        }

        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
            $this->db()->update('users', [
                'password'   => $hashedPassword,
                'updated_at' => now(),
            ], 'id = ?', [$userId]);

            ActivityLog::log('password_changed', $userId);

            $this->flash('success', 'Password changed successfully.');
        } catch (\Throwable $e) {
            $this->flash('error', 'Failed to change password: ' . $e->getMessage());
        }

        return $this->redirect('/profile');
    }

    public function uploadAvatar()
    {
        $userId = $this->authId();
        $user = User::find($userId);
        if (!$user) {
            $this->flash('error', 'User not found.');
            return $this->redirect('/dashboard');
        }

        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            $this->flash('error', 'Please select an image to upload.');
            return $this->redirect('/profile');
        }

        $file = $_FILES['avatar'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (!in_array($file['type'], $allowedTypes)) {
            $this->flash('error', 'Only JPG, PNG, GIF, and WebP images are allowed.');
            return $this->redirect('/profile');
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            $this->flash('error', 'Image must be less than 2MB.');
            return $this->redirect('/profile');
        }

        try {
            $uploadDir = STORAGE_PATH . '/uploads/avatars';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'avatar_' . $userId . '_' . time() . '.' . strtolower($ext);
            $filepath = $uploadDir . '/' . $filename;

            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                throw new \RuntimeException('Failed to move uploaded file.');
            }

            // Delete old avatar if exists
            if ($user['avatar']) {
                $oldPath = STORAGE_PATH . '/uploads/avatars/' . $user['avatar'];
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $this->db()->update('users', [
                'avatar'     => $filename,
                'updated_at' => now(),
            ], 'id = ?', [$userId]);

            // Update session
            $sessionUser = $this->auth();
            if ($sessionUser) {
                $sessionUser['avatar'] = $filename;
                \Core\Session::set('user', $sessionUser);
            }

            ActivityLog::log('avatar_uploaded', $userId);

            $this->flash('success', 'Profile picture updated successfully.');
        } catch (\Throwable $e) {
            $this->flash('error', 'Failed to upload image: ' . $e->getMessage());
        }

        return $this->redirect('/profile');
    }
}
