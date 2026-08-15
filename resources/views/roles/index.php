<?php
$layout    = 'app';
$pageTitle = 'Roles & Permissions';
$breadcrumbs = [['label'=>'Dashboard','url'=>'/dashboard'],['label'=>'Roles & Permissions']];
ob_start();

// Flatten permissions list in PHP to pass to JavaScript
$flatPermissions = [];
foreach ($permissions as $module => $perms) {
    foreach ($perms as $perm) {
        $flatPermissions[] = $perm;
    }
}
?>

<div x-data="rolesPermissionsDashboard()" class="space-y-6" x-cloak>
    
    <!-- Title Area -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Roles & Permissions Workspace</h2>
            <p class="text-xs text-slate-500 mt-0.5">End-to-end dynamic access control panel</p>
        </div>
    </div>

    <!-- Workspace Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left Side: Roles Management (4 cols) -->
        <div class="lg:col-span-4 space-y-4">
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 p-5 space-y-4 shadow-sm">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200">Roles List</h3>
                    <button @click="openCreateModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 transition-all shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        New Role
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-800 text-2xs font-bold text-slate-400 uppercase tracking-wider">
                                <th class="py-2.5">Display</th>
                                <th class="py-2.5 text-center">Level</th>
                                <th class="py-2.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <template x-for="role in roles" :key="role.id">
                                <tr @click="selectRole(role.id)" 
                                    :class="activeRoleId === role.id 
                                        ? 'bg-indigo-50/50 dark:bg-indigo-950/20 border-l-4 border-indigo-600' 
                                        : 'hover:bg-slate-50 dark:hover:bg-slate-800/20 border-l-4 border-transparent'"
                                    class="group cursor-pointer transition-all">
                                    <td class="py-3 px-2">
                                        <div class="font-medium text-slate-900 dark:text-white text-xs" x-text="role.name"></div>
                                        <div class="text-3xs text-slate-400" x-text="role.description || 'No description'"></div>
                                    </td>
                                    <td class="py-3 text-center">
                                        <span class="text-3xs font-semibold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700/50" x-text="'lv' + role.level"></span>
                                    </td>
                                    <td class="py-3 text-right pr-2">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button @click.stop="openEditModal(role)" class="p-1 rounded text-slate-400 hover:text-brand-500 hover:bg-brand-50 dark:hover:bg-brand-950/30 transition-all" title="Edit Details">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                            <template x-if="!role.is_system">
                                                <button @click.stop="deleteRole(role.id)" class="p-1 rounded text-slate-400 hover:text-red-500 hover:bg-red-550/10 transition-all" title="Delete">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Side: Permissions Matrix Editor (8 cols) -->
        <div class="lg:col-span-8">
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 p-5 space-y-5 shadow-sm">
                
                <!-- Role Tabs selector -->
                <div class="flex flex-wrap gap-2 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <template x-for="role in roles" :key="role.id">
                        <button @click="selectRole(role.id)" 
                                :class="activeRoleId === role.id 
                                    ? 'bg-indigo-600 text-white shadow-sm' 
                                    : 'bg-slate-50 dark:bg-slate-850 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700/60'"
                                class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                            <span x-text="role.name"></span>
                            <span class="text-3xs font-semibold px-1.5 py-0.5 rounded-full" 
                                  :class="activeRoleId === role.id ? 'bg-indigo-500 text-indigo-100' : 'bg-slate-200 dark:bg-slate-700 text-slate-500 dark:text-slate-450'"
                                  x-text="'lv' + role.level"></span>
                        </button>
                    </template>
                </div>

                <!-- Super Admin Notice Banner -->
                <template x-if="getActiveRole()?.slug === 'super_admin'">
                    <div class="p-3.5 rounded-xl border border-indigo-200/30 bg-indigo-50/40 dark:border-indigo-900/30 dark:bg-indigo-950/20 text-indigo-700 dark:text-indigo-400 text-xs font-medium flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0-6V9m0 12a9 9 0 110-18 9 9 0 010 18z"/></svg>
                        <span>Super Admin has unrestricted full system access. All permissions are permanently granted and locked.</span>
                    </div>
                </template>

                <!-- Matrix Table -->
                <div class="overflow-x-auto min-h-[400px]">
                    <table class="w-full text-left border-collapse table-fixed">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-800 text-2xs font-bold text-slate-400 uppercase tracking-wider">
                                <th class="py-2.5 px-4 w-5/12 text-left">Module / Permission Title</th>
                                <th class="py-2.5 px-4 w-7/60 text-center">View</th>
                                <th class="py-2.5 px-4 w-7/60 text-center">Analytics</th>
                                <th class="py-2.5 px-4 w-7/60 text-center">Create</th>
                                <th class="py-2.5 px-4 w-7/60 text-center">Edit</th>
                                <th class="py-2.5 px-4 w-7/60 text-center">Delete</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                            <template x-for="row in tableRows" :key="row.type + '-' + (row.id || row.label)">
                                <tr :class="row.type === 'header' ? 'bg-slate-50/50 dark:bg-slate-900/50' : 'hover:bg-slate-50 dark:hover:bg-slate-800/10 transition-all'">
                                    
                                    <!-- Category Heading Row -->
                                    <template x-if="row.type === 'header'">
                                        <td colspan="6" class="py-2.5 px-4 font-bold text-slate-500 uppercase tracking-wider text-2xs" x-text="row.label"></td>
                                    </template>

                                    <!-- Individual Permission Name -->
                                    <template x-if="row.type === 'permission'">
                                        <td class="py-3 px-4 text-slate-850 dark:text-slate-200"
                                            :class="row.depth > 0 ? 'text-xs pl-8 font-normal' : 'text-xs font-semibold'"
                                            :style="'padding-left: ' + (row.depth * 1.5 + 1) + 'rem'">
                                            <span x-text="row.depth > 0 ? '└── ' + row.name : row.name"></span>
                                        </td>
                                    </template>
                                    
                                    <!-- VIEW COLUMN -->
                                    <template x-if="row.type === 'permission'">
                                        <td class="py-3 px-4 text-center">
                                            <template x-if="row.col === 'view'">
                                                <input type="checkbox" :value="row.id" 
                                                       :disabled="getActiveRole()?.slug === 'super_admin' || isPermissionDisabled(row.slug)"
                                                       :checked="getActiveRole()?.slug === 'super_admin' || isPermissionChecked(row.id)"
                                                       @change="togglePermission(row.id, $event.target.checked)"
                                                       class="w-4 h-4 rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-indigo-600 focus:ring-indigo-500/30 disabled:opacity-50 cursor-pointer disabled:cursor-not-allowed">
                                            </template>
                                            <template x-if="row.col !== 'view'">
                                                <span class="text-slate-300 dark:text-slate-700/60">-</span>
                                            </template>
                                        </td>
                                    </template>

                                    <!-- ANALYTICS COLUMN -->
                                    <template x-if="row.type === 'permission'">
                                        <td class="py-3 px-4 text-center">
                                            <template x-if="row.col === 'analytics'">
                                                <input type="checkbox" :value="row.id" 
                                                       :disabled="getActiveRole()?.slug === 'super_admin' || isPermissionDisabled(row.slug)"
                                                       :checked="getActiveRole()?.slug === 'super_admin' || isPermissionChecked(row.id)"
                                                       @change="togglePermission(row.id, $event.target.checked)"
                                                       class="w-4 h-4 rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-indigo-600 focus:ring-indigo-500/30 disabled:opacity-50 cursor-pointer disabled:cursor-not-allowed">
                                            </template>
                                            <template x-if="row.col !== 'analytics'">
                                                <span class="text-slate-300 dark:text-slate-700/60">-</span>
                                            </template>
                                        </td>
                                    </template>

                                    <!-- CREATE COLUMN -->
                                    <template x-if="row.type === 'permission'">
                                        <td class="py-3 px-4 text-center">
                                            <template x-if="row.col === 'create'">
                                                <input type="checkbox" :value="row.id" 
                                                       :disabled="getActiveRole()?.slug === 'super_admin' || isPermissionDisabled(row.slug)"
                                                       :checked="getActiveRole()?.slug === 'super_admin' || isPermissionChecked(row.id)"
                                                       @change="togglePermission(row.id, $event.target.checked)"
                                                       class="w-4 h-4 rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-indigo-600 focus:ring-indigo-500/30 disabled:opacity-50 cursor-pointer disabled:cursor-not-allowed">
                                            </template>
                                            <template x-if="row.col !== 'create'">
                                                <span class="text-slate-300 dark:text-slate-700/60">-</span>
                                            </template>
                                        </td>
                                    </template>

                                    <!-- EDIT COLUMN -->
                                    <template x-if="row.type === 'permission'">
                                        <td class="py-3 px-4 text-center">
                                            <template x-if="row.col === 'edit'">
                                                <input type="checkbox" :value="row.id" 
                                                       :disabled="getActiveRole()?.slug === 'super_admin' || isPermissionDisabled(row.slug)"
                                                       :checked="getActiveRole()?.slug === 'super_admin' || isPermissionChecked(row.id)"
                                                       @change="togglePermission(row.id, $event.target.checked)"
                                                       class="w-4 h-4 rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-indigo-600 focus:ring-indigo-500/30 disabled:opacity-50 cursor-pointer disabled:cursor-not-allowed">
                                            </template>
                                            <template x-if="row.col !== 'edit'">
                                                <span class="text-slate-300 dark:text-slate-700/60">-</span>
                                            </template>
                                        </td>
                                    </template>

                                    <!-- DELETE COLUMN -->
                                    <template x-if="row.type === 'permission'">
                                        <td class="py-3 px-4 text-center">
                                            <template x-if="row.col === 'delete'">
                                                <input type="checkbox" :value="row.id" 
                                                       :disabled="getActiveRole()?.slug === 'super_admin' || isPermissionDisabled(row.slug)"
                                                       :checked="getActiveRole()?.slug === 'super_admin' || isPermissionChecked(row.id)"
                                                       @change="togglePermission(row.id, $event.target.checked)"
                                                       class="w-4 h-4 rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-indigo-600 focus:ring-indigo-500/30 disabled:opacity-50 cursor-pointer disabled:cursor-not-allowed">
                                            </template>
                                            <template x-if="row.col !== 'delete'">
                                                <span class="text-slate-300 dark:text-slate-700/60">-</span>
                                            </template>
                                        </td>
                                    </template>

                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Footer with Controls -->
                <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-800">
                    <div>
                        <button @click="savePermissions()" 
                                :disabled="saving || getActiveRole()?.slug === 'super_admin'" 
                                style="color: #ffffff !important;"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-500 transition-all shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg x-show="saving" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            <svg x-show="!saving" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                            Save Permissions
                        </button>
                    </div>
                    <div class="flex items-center gap-2" x-show="getActiveRole()?.slug !== 'super_admin'">
                        <button @click="setPreset('viewer')" class="px-3 py-1.5 rounded-lg text-2xs font-semibold bg-slate-50 dark:bg-slate-850 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 transition-all">Viewer Preset</button>
                        <button @click="setPreset('editor')" class="px-3 py-1.5 rounded-lg text-2xs font-semibold bg-slate-50 dark:bg-slate-850 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 transition-all">Editor Preset</button>
                        <button @click="setPreset('clear')" class="px-3 py-1.5 rounded-lg text-2xs font-semibold bg-red-50 hover:bg-red-100/80 dark:bg-red-950/20 dark:hover:bg-red-950/30 border border-red-200 dark:border-red-900/50 text-red-650 dark:text-red-400 transition-all">Clear All</button>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- CREATE ROLE MODAL -->
    <div x-show="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-sm" x-transition>
        <div @click.away="showCreateModal = false" class="w-full max-w-md rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 space-y-4 shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="font-bold text-slate-950 dark:text-white text-sm">Add New Role</h3>
                <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="submitCreate()" class="space-y-4 text-xs">
                <div class="space-y-1.5">
                    <label class="block font-medium text-slate-450">Role Name *</label>
                    <input type="text" x-model="modalRoleData.name" required placeholder="e.g. Coordinator" 
                           class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-lg p-2.5 focus:outline-none focus:border-brand-500">
                </div>
                <div class="space-y-1.5">
                    <label class="block font-medium text-slate-450">Hierarchy Level (10 - 50) *</label>
                    <input type="number" x-model="modalRoleData.level" required min="1" max="100" 
                           class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-lg p-2.5 focus:outline-none focus:border-brand-500">
                </div>
                <div class="space-y-1.5">
                    <label class="block font-medium text-slate-450">Description</label>
                    <textarea x-model="modalRoleData.description" placeholder="Brief role description..." rows="2"
                              class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-lg p-2.5 focus:outline-none focus:border-brand-500"></textarea>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-750 text-slate-700 dark:text-slate-300 font-semibold transition-all">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-semibold transition-all shadow-sm">Create Role</button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT ROLE MODAL -->
    <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-sm" x-transition>
        <div @click.away="showEditModal = false" class="w-full max-w-md rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 space-y-4 shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="font-bold text-slate-950 dark:text-white text-sm">Edit Role Details</h3>
                <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="submitEdit()" class="space-y-4 text-xs">
                <div class="space-y-1.5">
                    <label class="block font-medium text-slate-450">Role Name *</label>
                    <input type="text" x-model="modalRoleData.name" required 
                           class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-lg p-2.5 focus:outline-none focus:border-brand-500">
                </div>
                <div class="space-y-1.5">
                    <label class="block font-medium text-slate-450">Hierarchy Level *</label>
                    <input type="number" x-model="modalRoleData.level" required min="1" max="100" 
                           class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-lg p-2.5 focus:outline-none focus:border-brand-500">
                </div>
                <div class="space-y-1.5">
                    <label class="block font-medium text-slate-450">Description</label>
                    <textarea x-model="modalRoleData.description" rows="2"
                              class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-lg p-2.5 focus:outline-none focus:border-brand-500"></textarea>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-750 text-slate-700 dark:text-slate-300 font-semibold transition-all">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-semibold transition-all shadow-sm">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function rolesPermissionsDashboard() {
    return {
        roles: <?= json_encode($roles) ?>,
        allPermissions: <?= json_encode($flatPermissions) ?>,
        activeRoleId: null,
        showCreateModal: false,
        showEditModal: false,
        saving: false,
        tableRows: [],
        slugToIdMap: {},
        idToSlugMap: {},
        
        // Define hierarchy dependencies (childSlug: parentSlug)
        permissionDependencies: {
            'create_users': 'view_users',
            'edit_users': 'view_users',
            'delete_users': 'view_users',
            'assign_roles': 'view_users',
            
            'create_roles': 'view_roles',
            'edit_roles': 'view_roles',
            'delete_roles': 'view_roles',
            
            'create_branches': 'view_branches',
            'edit_branches': 'view_branches',
            'delete_branches': 'view_branches',
            
            'create_schools': 'view_schools',
            'edit_schools': 'view_schools',
            'delete_schools': 'view_schools',
            
            'create_tenants': 'view_tenants',
            'edit_tenants': 'view_tenants',
            'delete_tenants': 'view_tenants',
            
            'create_students': 'view_students',
            'edit_students': 'view_students',
            'delete_students': 'view_students',
            'approve_admissions': 'view_students',
            
            'upload_documents': 'view_documents',
            'edit_documents': 'view_documents',
            'verify_documents': 'view_documents',
            'delete_documents': 'view_documents',
            
            'export_reports': 'view_reports',
            'assign_permissions': 'view_permissions',
            
            'logout': 'login',
            'manage_sessions': 'login',
            
            'driver_app_drop_route': 'driver_app',
            'driver_app_early_leave': 'driver_app',
            'driver_app_pickup_route': 'driver_app',
            'driver_app_safety_center': 'driver_app',
            'driver_app_sos': 'driver_app',
            'driver_app_speed_monitor': 'driver_app',
            'driver_app_students_onboard': 'driver_app',
            'driver_app_trip_logs': 'driver_app',
            
            'teacher_app_student_details': 'teacher_portal',
            'teacher_app_half_leave_notification': 'teacher_portal',
            'teacher_app_timetables': 'teacher_portal',
            'access_exams_app': 'teacher_portal',
            
            'staff_app_student_details': 'teacher_staff_app',
            'staff_app_half_leave_notification': 'teacher_staff_app',
            'staff_app_half_leave_details': 'teacher_staff_app',
            
            'see_student_guardian': 'parents_portal',
            'see_student_medical': 'parents_portal',
            'see_student_name': 'parents_portal',
        },

        modalRoleData: {
            id: null,
            name: '',
            level: 10,
            description: ''
        },

        init() {
            // Build slug map
            this.allPermissions.forEach(p => {
                this.slugToIdMap[p.slug] = p.id;
                this.idToSlugMap[p.id] = p.slug;
            });

            // Define structural metadata template
            const permissionMetadata = [
                {
                    module: "Audit Logs",
                    items: [
                        { name: "View Audit Logs", slug: "view_audit_logs", col: "view" }
                    ]
                },
                {
                    module: "Authentication",
                    items: [
                        { name: "Login", slug: "login", col: "view", children: ["logout", "manage_sessions"] },
                        { name: "Logout", slug: "logout", col: "edit" },
                        { name: "Manage Sessions", slug: "manage_sessions", col: "edit" }
                    ]
                },
                {
                    module: "Branches",
                    items: [
                        { name: "View Branches", slug: "view_branches", col: "view", children: ["create_branches", "edit_branches", "delete_branches"] },
                        { name: "Create Branches", slug: "create_branches", col: "create" },
                        { name: "Edit Branches", slug: "edit_branches", col: "edit" },
                        { name: "Delete Branches", slug: "delete_branches", col: "delete" }
                    ]
                },
                {
                    module: "Schools",
                    items: [
                        { name: "View Schools", slug: "view_schools", col: "view", children: ["create_schools", "edit_schools", "delete_schools"] },
                        { name: "Create Schools", slug: "create_schools", col: "create" },
                        { name: "Edit Schools", slug: "edit_schools", col: "edit" },
                        { name: "Delete Schools", slug: "delete_schools", col: "delete" }
                    ]
                },
                {
                    module: "Tenants",
                    items: [
                        { name: "View Tenants", slug: "view_tenants", col: "view", children: ["create_tenants", "edit_tenants", "delete_tenants"] },
                        { name: "Create Tenants", slug: "create_tenants", col: "create" },
                        { name: "Edit Tenants", slug: "edit_tenants", col: "edit" },
                        { name: "Delete Tenants", slug: "delete_tenants", col: "delete" }
                    ]
                },
                {
                    module: "Users",
                    items: [
                        { name: "View Users", slug: "view_users", col: "view", children: ["create_users", "edit_users", "delete_users", "assign_roles"] },
                        { name: "Create Users", slug: "create_users", col: "create" },
                        { name: "Edit Users", slug: "edit_users", col: "edit" },
                        { name: "Delete Users", slug: "delete_users", col: "delete" },
                        { name: "Assign Roles", slug: "assign_roles", col: "edit" }
                    ]
                },
                {
                    module: "Roles",
                    items: [
                        { name: "View Roles", slug: "view_roles", col: "view", children: ["create_roles", "edit_roles", "delete_roles"] },
                        { name: "Create Roles", slug: "create_roles", col: "create" },
                        { name: "Edit Roles", slug: "edit_roles", col: "edit" },
                        { name: "Delete Roles", slug: "delete_roles", col: "delete" }
                    ]
                },
                {
                    module: "Permissions",
                    items: [
                        { name: "View Permissions", slug: "view_permissions", col: "view", children: ["assign_permissions"] },
                        { name: "Assign Permissions", slug: "assign_permissions", col: "edit" }
                    ]
                },
                {
                    module: "Students & Admissions",
                    items: [
                        { name: "View Students & Admissions", slug: "view_students", col: "view", children: ["create_students", "edit_students", "delete_students", "approve_admissions"] },
                        { name: "Create Students", slug: "create_students", col: "create" },
                        { name: "Edit Students", slug: "edit_students", col: "edit" },
                        { name: "Delete Students", slug: "delete_students", col: "delete" },
                        { name: "Approve Admissions", slug: "approve_admissions", col: "edit" }
                    ]
                },
                {
                    module: "Documents",
                    items: [
                        { name: "View Documents", slug: "view_documents", col: "view", children: ["upload_documents", "verify_documents", "delete_documents"] },
                        { name: "Upload Documents", slug: "upload_documents", col: "create" },
                        { name: "Verify Documents", slug: "verify_documents", col: "edit" },
                        { name: "Delete Documents", slug: "delete_documents", col: "delete" }
                    ]
                },
                {
                    module: "Reports",
                    items: [
                        { name: "View Reports", slug: "view_reports", col: "view", children: ["export_reports"] },
                        { name: "Export Reports", slug: "export_reports", col: "analytics" }
                    ]
                },
                {
                    module: "Settings",
                    items: [
                        { name: "View Settings", slug: "view_settings", col: "view", children: ["edit_settings"] },
                        { name: "Edit Settings", slug: "edit_settings", col: "edit" }
                    ]
                },
                {
                    module: "Portal Access: Teacher App",
                    items: [
                        { name: "Teacher Portal", slug: "teacher_portal", col: "view", children: ["teacher_app_student_details", "teacher_app_half_leave_notification", "teacher_app_timetables", "access_exams_app"] },
                        { name: "Student Details", slug: "teacher_app_student_details", col: "view" },
                        { name: "Half Leave Notification", slug: "teacher_app_half_leave_notification", col: "view" },
                        { name: "Timetables", slug: "teacher_app_timetables", col: "view" },
                        { name: "Access Exams App", slug: "access_exams_app", col: "view" }
                    ]
                },
                {
                    module: "Portal Access: Staff App",
                    items: [
                        { name: "Teacher Staff App", slug: "teacher_staff_app", col: "view", children: ["staff_app_student_details", "staff_app_half_leave_notification", "staff_app_half_leave_details"] },
                        { name: "Student Details", slug: "staff_app_student_details", col: "view" },
                        { name: "Half Leave Notification", slug: "staff_app_half_leave_notification", col: "view" },
                        { name: "Half Leave Details", slug: "staff_app_half_leave_details", col: "view" }
                    ]
                },
                {
                    module: "Portal Access: Parents Portal",
                    items: [
                        { name: "Parents Portal", slug: "parents_portal", col: "view", children: ["see_student_name", "see_student_medical", "see_student_guardian"] },
                        { name: "See Student Name", slug: "see_student_name", col: "view" },
                        { name: "See Student Medical", slug: "see_student_medical", col: "view" },
                        { name: "See Student Guardian", slug: "see_student_guardian", col: "view" }
                    ]
                },
                {
                    module: "Portal Access: Driver App",
                    items: [
                        { name: "Driver App", slug: "driver_app", col: "view", children: [
                            "driver_app_pickup_route", "driver_app_drop_route", "driver_app_students_onboard", 
                            "driver_app_safety_center", "driver_app_speed_monitor", "driver_app_sos", 
                            "driver_app_trip_logs", "driver_app_early_leave"
                        ]},
                        { name: "Pickup Route", slug: "driver_app_pickup_route", col: "view" },
                        { name: "Drop Route", slug: "driver_app_drop_route", col: "view" },
                        { name: "Students Onboard", slug: "driver_app_students_onboard", col: "view" },
                        { name: "Safety Center", slug: "driver_app_safety_center", col: "view" },
                        { name: "Speed Monitor", slug: "driver_app_speed_monitor", col: "view" },
                        { name: "SOS", slug: "driver_app_sos", col: "view" },
                        { name: "Trip Logs", slug: "driver_app_trip_logs", col: "analytics" },
                        { name: "Early Leave", slug: "driver_app_early_leave", col: "view" }
                    ]
                }
            ];

            // Build hierarchical rows
            const mappedSlugs = new Set();
            permissionMetadata.forEach(mod => {
                // Add header row
                this.tableRows.push({ type: 'header', label: mod.module });

                mod.items.forEach(item => {
                    const id = this.slugToIdMap[item.slug];
                    if (id) {
                        // Check if it's a child (has a dependency)
                        const parentSlug = this.permissionDependencies[item.slug];
                        const depth = parentSlug ? 1 : 0;
                        
                        this.tableRows.push({
                            type: 'permission',
                            id: Number(id),
                            name: item.name,
                            slug: item.slug,
                            col: item.col,
                            depth: depth,
                            parentSlug: parentSlug || null
                        });
                        mappedSlugs.add(item.slug);
                    }
                });
            });

            // Add fallbacks for unmapped items
            const fallbacks = [];
            this.allPermissions.forEach(p => {
                if (!mappedSlugs.has(p.slug)) {
                    let col = 'view';
                    if (p.slug.includes('create') || p.slug.includes('upload')) col = 'create';
                    else if (p.slug.includes('edit') || p.slug.includes('update') || p.slug.includes('assign') || p.slug.includes('approve') || p.slug.includes('verify')) col = 'edit';
                    else if (p.slug.includes('delete') || p.slug.includes('remove') || p.slug.includes('destroy')) col = 'delete';
                    else if (p.slug.includes('analytics') || p.slug.includes('report') || p.slug.includes('log')) col = 'analytics';

                    fallbacks.push({
                        type: 'permission',
                        id: Number(p.id),
                        name: p.name,
                        slug: p.slug,
                        col: col,
                        depth: 0,
                        parentSlug: null
                    });
                }
            });
            if (fallbacks.length > 0) {
                this.tableRows.push({ type: 'header', label: "Other Systems" });
                this.tableRows = this.tableRows.concat(fallbacks);
            }

            // Select active role
            if (this.roles.length > 0) {
                this.activeRoleId = this.roles[0].id;
            }
        },

        selectRole(roleId) {
            this.activeRoleId = roleId;
        },

        getActiveRole() {
            return this.roles.find(r => r.id === this.activeRoleId);
        },

        isPermissionDisabled(slug) {
            const role = this.getActiveRole();
            if (role?.slug === 'super_admin') return false;

            const parentSlug = this.permissionDependencies[slug];
            if (!parentSlug) return false;

            // If there's a parent, the child is disabled if the parent is NOT checked
            const parentId = this.slugToIdMap[parentSlug];
            if (!parentId) return false;

            return !this.isPermissionChecked(parentId);
        },

        isPermissionChecked(permId) {
            const role = this.getActiveRole();
            if (!role) return false;
            
            // Super Admin has all permissions automatically
            if (role.slug === 'super_admin') return true;

            const isChecked = role.permission_ids.map(Number).includes(Number(permId));
            
            // Check inheritance: if it is a child, it can only be checked if its parent is checked
            const slug = this.idToSlugMap[permId];
            if (slug && isChecked) {
                const parentSlug = this.permissionDependencies[slug];
                if (parentSlug) {
                    const parentId = this.slugToIdMap[parentSlug];
                    if (parentId && !role.permission_ids.map(Number).includes(Number(parentId))) {
                        return false; // Parent not checked, so child must behave as unchecked
                    }
                }
            }

            return isChecked;
        },

        togglePermission(permId, checked) {
            const role = this.getActiveRole();
            if (!role || role.slug === 'super_admin') return;

            permId = Number(permId);
            const slug = this.idToSlugMap[permId];
            let pIds = role.permission_ids.map(Number);

            if (checked) {
                // Check this permission
                if (!pIds.includes(permId)) {
                    pIds.push(permId);
                }

                // If child is checked -> automatically check the parent!
                if (slug) {
                    const parentSlug = this.permissionDependencies[slug];
                    if (parentSlug) {
                        const parentId = this.slugToIdMap[parentSlug];
                        if (parentId && !pIds.includes(Number(parentId))) {
                            pIds.push(Number(parentId));
                        }
                    }
                }
            } else {
                // Uncheck this permission
                pIds = pIds.filter(id => id !== permId);

                // If parent is unchecked -> automatically uncheck all children!
                if (slug) {
                    // Find all kids
                    Object.keys(this.permissionDependencies).forEach(childSlug => {
                        if (this.permissionDependencies[childSlug] === slug) {
                            const childId = this.slugToIdMap[childSlug];
                            if (childId) {
                                pIds = pIds.filter(id => id !== Number(childId));
                            }
                        }
                    });
                }
            }
            role.permission_ids = pIds;
        },

        async savePermissions() {
            const role = this.getActiveRole();
            if (!role || role.slug === 'super_admin') return;

            this.saving = true;
            try {
                const formData = new FormData();
                formData.append('_csrf', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                
                // Ensure only checked permissions (that are also active/not disabled by parent) are saved
                const finalPermsToSave = role.permission_ids.filter(id => {
                    const slug = this.idToSlugMap[id];
                    if (!slug) return true;
                    const parentSlug = this.permissionDependencies[slug];
                    if (!parentSlug) return true;
                    const parentId = this.slugToIdMap[parentSlug];
                    return role.permission_ids.map(Number).includes(Number(parentId));
                });

                finalPermsToSave.forEach(id => formData.append('permissions[]', id));

                const response = await fetch('<?= url('roles') ?>/' + role.id, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const res = await response.json();
                if (res.success) {
                    alert('Permissions updated successfully!');
                } else {
                    alert('Failed to save permissions: ' + (res.message || 'Unknown error'));
                }
            } catch (err) {
                console.error(err);
                alert('An error occurred while saving.');
            } finally {
                this.saving = false;
            }
        },

        setPreset(type) {
            const role = this.getActiveRole();
            if (!role || role.slug === 'super_admin') return;

            let targetIds = [];

            if (type === 'viewer') {
                // Collect parent permissions (usually 'view' columns)
                this.tableRows.forEach(row => {
                    if (row.type === 'permission' && !row.parentSlug && row.col === 'view') {
                        targetIds.push(Number(row.id));
                    }
                });
            } else if (type === 'editor') {
                // Collect everything
                this.tableRows.forEach(row => {
                    if (row.type === 'permission') {
                        targetIds.push(Number(row.id));
                    }
                });
            } else if (type === 'clear') {
                targetIds = [];
            }

            role.permission_ids = targetIds;
        },

        openCreateModal() {
            this.modalRoleData = { id: null, name: '', level: 10, description: '' };
            this.showCreateModal = true;
        },

        openEditModal(role) {
            this.modalRoleData = {
                id: role.id,
                name: role.name,
                level: Number(role.level),
                description: role.description
            };
            this.showEditModal = true;
        },

        async submitCreate() {
            try {
                const formData = new FormData();
                formData.append('_csrf', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                formData.append('name', this.modalRoleData.name);
                formData.append('level', this.modalRoleData.level);
                formData.append('description', this.modalRoleData.description);

                const response = await fetch('<?= url('roles') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const res = await response.json();
                if (res.success) {
                    window.location.reload();
                } else {
                    alert('Error creating role: ' + (res.message || ''));
                }
            } catch (err) {
                console.error(err);
                alert('An error occurred.');
            }
        },

        async submitEdit() {
            try {
                const formData = new FormData();
                formData.append('_csrf', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                formData.append('name', this.modalRoleData.name);
                formData.append('level', this.modalRoleData.level);
                formData.append('description', this.modalRoleData.description);

                const response = await fetch('<?= url('roles') ?>/' + this.modalRoleData.id, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const res = await response.json();
                if (res.success) {
                    window.location.reload();
                } else {
                    alert('Error updating role: ' + (res.message || ''));
                }
            } catch (err) {
                console.error(err);
                alert('An error occurred.');
            }
        },

        async deleteRole(roleId) {
            if (!confirm('Are you sure you want to delete this role?')) return;

            try {
                const formData = new FormData();
                formData.append('_csrf', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                formData.append('_method', 'DELETE');

                const response = await fetch('<?= url('roles') ?>/' + roleId, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const res = await response.json();
                if (res.success) {
                    window.location.reload();
                } else {
                    alert('Error deleting role: ' + (res.message || ''));
                }
            } catch (err) {
                console.error(err);
                alert('An error occurred.');
            }
        }
    };
}
</script>

<?php
$content = ob_get_clean();
?>
