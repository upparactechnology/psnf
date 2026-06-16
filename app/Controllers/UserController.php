<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use App\Models\{User, Role};

class UserController extends Controller
{
    public function index(): string
    {
        $search  = $this->request->get('search', '');
        $page    = (int) $this->request->get('page', 1);
        $result  = User::paginate($page, 15, $search ? "(name LIKE ? OR email LIKE ?)" : '', $search ? ["%$search%", "%$search%"] : []);
        $roles   = Role::allWithPermissionCount();

        return $this->view('users/index', array_merge($result, ['search' => $search, 'roles' => $roles]));
    }

    public function create(): string
    {
        $roles    = Role::all('sort_order');
        $schools  = \Core\Application::$app->db->select("SELECT id, name FROM schools WHERE tenant_id = ? AND is_active = 1 AND deleted_at IS NULL", [\Core\Database::getTenantId()]);
        $branches = \Core\Application::$app->db->select("SELECT id, name FROM branches WHERE tenant_id = ? AND is_active = 1 AND deleted_at IS NULL", [\Core\Database::getTenantId()]);
        return $this->view('users/create', compact('roles', 'schools', 'branches'));
    }

    public function store(): string
    {
        $data  = $this->request->getBody();
        $roles = $data['roles'] ?? [];
        unset($data['roles'], $data['_csrf']);

        $rules = [
            'name'      => 'required|min:2',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|min:8|confirmed',
            'school_id' => 'required',
            'branch_id' => 'required',
        ];

        $validator = new \Core\Validator($data, $rules);
        if ($validator->fails()) {
            \Core\Session::flash('errors', $validator->errors());
            \Core\Session::flash('old', $data);
            return $this->redirect('/users/create');
        }

        $data['uuid']      = str_uuid();
        $data['tenant_id'] = \Core\Database::getTenantId();
        $data['password']  = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        $data['created_by']= auth_id();
        unset($data['password_confirmation']);

        $userId = (int) User::create($data);

        if ($roles) {
            User::syncRoles($userId, array_map('intval', $roles));
        }

        \App\Models\ActivityLog::log('user_created', auth_id(), ['user_id' => $userId]);
        $this->flash('success', 'User created successfully.');
        return $this->redirect('/users');
    }

    public function edit(string $id): string
    {
        $user     = User::withRoles((int) $id);
        $roles    = Role::all('sort_order');
        $schools  = \Core\Application::$app->db->select("SELECT id, name FROM schools WHERE tenant_id = ? AND deleted_at IS NULL", [\Core\Database::getTenantId()]);
        $branches = \Core\Application::$app->db->select("SELECT id, name FROM branches WHERE tenant_id = ? AND deleted_at IS NULL", [\Core\Database::getTenantId()]);
        return $this->view('users/edit', compact('user', 'roles', 'schools', 'branches'));
    }

    public function update(string $id): string
    {
        $data  = $this->request->getBody();
        $roles = $data['roles'] ?? [];
        unset($data['roles'], $data['_csrf'], $data['_method'], $data['password'], $data['password_confirmation']);

        $data['updated_by'] = auth_id();
        User::update((int) $id, $data);
        User::syncRoles((int) $id, array_map('intval', $roles));

        \App\Models\ActivityLog::log('user_updated', auth_id(), ['user_id' => $id]);
        $this->flash('success', 'User updated.');
        return $this->redirect('/users');
    }

    public function destroy(string $id): string
    {
        User::delete((int) $id);
        \App\Models\ActivityLog::log('user_deleted', auth_id(), ['user_id' => $id]);

        if ($this->request->wantsJson()) {
            return $this->successResponse('User deleted.');
        }
        $this->flash('success', 'User deleted.');
        return $this->redirect('/users');
    }
}
