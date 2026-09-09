<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use App\Models\{Role, Permission};

class RoleController extends Controller
{
    public function index(): string
    {
        $roles = Role::allWithPermissionCount();
        $permissions = Permission::allGroupedByModule();
        unset($permissions['branches'], $permissions['schools'], $permissions['tenants']);

        $rolesWithPerms = [];
        foreach ($roles as $role) {
            $rolesWithPerms[] = Role::withPermissions((int) $role['id']);
        }

        return $this->view('roles/index', [
            'roles' => $rolesWithPerms,
            'permissions' => $permissions
        ]);
    }

    public function create(): string
    {
        return $this->redirect(url('roles'));
    }

    public function store(): string
    {
        $data        = $this->request->getBody();
        $permIds     = $data['permissions'] ?? [];
        unset($data['permissions'], $data['_csrf']);

        $data['slug'] = strtolower(str_replace(' ', '_', $data['name']));
        $data['created_by'] = auth_id();

        $roleId = (int) Role::create($data);
        if ($permIds) {
            Role::syncPermissions($roleId, array_map('intval', $permIds));
        }

        \App\Models\ActivityLog::log('role_created', auth_id(), ['role_id' => $roleId]);
        
        if ($this->request->wantsJson()) {
            return $this->successResponse('Role created successfully.');
        }
        $this->flash('success', 'Role created.');
        return $this->redirect(url('roles'));
    }

    public function edit(string $id): string
    {
        if ($this->request->wantsJson()) {
            $role = Role::withPermissions((int) $id);
            return $this->successResponse('Role loaded.', ['role' => $role]);
        }
        return $this->redirect(url('roles'));
    }

    public function update(string $id): string
    {
        $data    = $this->request->getBody();
        $permIds = $data['permissions'] ?? [];
        unset($data['permissions'], $data['_csrf'], $data['_method']);

        if (!empty($data)) {
            Role::update((int) $id, $data);
        }
        Role::syncPermissions((int) $id, array_map('intval', $permIds));

        \App\Models\ActivityLog::log('role_updated', auth_id(), ['role_id' => $id]);

        if ($this->request->wantsJson()) {
            return $this->successResponse('Role updated successfully.');
        }
        $this->flash('success', 'Role updated.');
        return $this->redirect(url('roles'));
    }

    public function destroy(string $id): string
    {
        Role::delete((int) $id);
        \App\Models\ActivityLog::log('role_deleted', auth_id(), ['role_id' => $id]);

        if ($this->request->wantsJson()) {
            return $this->successResponse('Role deleted successfully.');
        }
        $this->flash('success', 'Role deleted.');
        return $this->redirect(url('roles'));
    }
}
