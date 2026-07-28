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
        return $this->view('roles/index', ['roles' => $roles]);
    }

    public function create(): string
    {
        $permissions = Permission::allGroupedByModule();
        unset($permissions['branches'], $permissions['schools'], $permissions['tenants']);
        return $this->view('roles/create', ['permissions' => $permissions]);
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
        $this->flash('success', 'Role created.');
        return $this->redirect('/roles');
    }

    public function edit(string $id): string
    {
        $role        = Role::withPermissions((int) $id);
        $permissions = Permission::allGroupedByModule();
        unset($permissions['branches'], $permissions['schools'], $permissions['tenants']);
        return $this->view('roles/edit', compact('role', 'permissions'));
    }

    public function update(string $id): string
    {
        $data    = $this->request->getBody();
        $permIds = $data['permissions'] ?? [];
        unset($data['permissions'], $data['_csrf'], $data['_method']);

        Role::update((int) $id, $data);
        Role::syncPermissions((int) $id, array_map('intval', $permIds));

        \App\Models\ActivityLog::log('role_updated', auth_id(), ['role_id' => $id]);
        $this->flash('success', 'Role updated.');
        return $this->redirect('/roles');
    }

    public function destroy(string $id): string
    {
        Role::delete((int) $id);
        \App\Models\ActivityLog::log('role_deleted', auth_id(), ['role_id' => $id]);

        if ($this->request->wantsJson()) return $this->successResponse('Role deleted.');
        $this->flash('success', 'Role deleted.');
        return $this->redirect('/roles');
    }
}
