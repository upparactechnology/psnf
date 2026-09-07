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
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-800 text-2xs font-bold text-slate-400 uppercase tracking-wider">
                                <th class="py-2.5 px-4 text-left">Module / Page</th>
                                <th class="py-2.5 px-4 text-center w-16">View</th>
                                <th class="py-2.5 px-4 text-center w-16">Create</th>
                                <th class="py-2.5 px-4 text-center w-16">Edit</th>
                                <th class="py-2.5 px-4 text-center w-16">Delete</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                            <template x-for="(mod, modIdx) in permissionMetadata" :key="mod.module">
                                <template x-for="(row, rowIdx) in getModuleRows(mod, modIdx)" :key="row.key">
                                    <tr :class="row.isHeader ? 'bg-slate-50/50 dark:bg-slate-900/50' : 'hover:bg-slate-50 dark:hover:bg-slate-800/10 transition-all'">
                                        <!-- Module Header -->
                                        <template x-if="row.isHeader">
                                            <td colspan="5" class="py-2.5 px-4 font-bold text-slate-500 uppercase tracking-wider text-2xs cursor-pointer select-none" @click="toggleModule(modIdx)">
                                                <div class="flex items-center gap-2">
                                                    <svg class="w-3 h-3 transition-transform" :class="expandedModules[modIdx] ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                                    <span x-text="mod.module"></span>
                                                    <span class="text-3xs font-normal text-slate-400 normal-case tracking-normal" x-text="'(' + mod.items.length + ' pages)'"></span>
                                                </div>
                                            </td>
                                        </template>

                                        <!-- Page Row -->
                                        <template x-if="!row.isHeader">
                                            <td class="py-3 px-4 text-slate-850 dark:text-slate-200 text-xs font-normal" style="padding-left: 2.5rem">
                                                <span x-text="row.name"></span>
                                            </td>
                                        </template>

                                        <!-- View Column -->
                                        <template x-if="!row.isHeader && row.viewSlug">
                                            <td class="py-3 px-4 text-center">
                                                <input type="checkbox"
                                                       :disabled="getActiveRole()?.slug === 'super_admin'"
                                                       :checked="getActiveRole()?.slug === 'super_admin' || isPermChecked(row.viewSlug)"
                                                       @change="togglePermBySlug(row.viewSlug, $event.target.checked)"
                                                       class="w-4 h-4 rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-indigo-600 focus:ring-indigo-500/30 disabled:opacity-50 cursor-pointer disabled:cursor-not-allowed">
                                            </td>
                                        </template>
                                        <template x-if="!row.isHeader && !row.viewSlug">
                                            <td class="py-3 px-4 text-center"><span class="text-slate-300 dark:text-slate-700/60">-</span></td>
                                        </template>

                                        <!-- Create Column -->
                                        <template x-if="!row.isHeader && row.createSlug">
                                            <td class="py-3 px-4 text-center">
                                                <input type="checkbox"
                                                       :disabled="getActiveRole()?.slug === 'super_admin'"
                                                       :checked="getActiveRole()?.slug === 'super_admin' || isPermChecked(row.createSlug)"
                                                       @change="togglePermBySlug(row.createSlug, $event.target.checked)"
                                                       class="w-4 h-4 rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-indigo-600 focus:ring-indigo-500/30 disabled:opacity-50 cursor-pointer disabled:cursor-not-allowed">
                                            </td>
                                        </template>
                                        <template x-if="!row.isHeader && !row.createSlug">
                                            <td class="py-3 px-4 text-center"><span class="text-slate-300 dark:text-slate-700/60">-</span></td>
                                        </template>

                                        <!-- Edit Column -->
                                        <template x-if="!row.isHeader && row.editSlug">
                                            <td class="py-3 px-4 text-center">
                                                <input type="checkbox"
                                                       :disabled="getActiveRole()?.slug === 'super_admin'"
                                                       :checked="getActiveRole()?.slug === 'super_admin' || isPermChecked(row.editSlug)"
                                                       @change="togglePermBySlug(row.editSlug, $event.target.checked)"
                                                       class="w-4 h-4 rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-indigo-600 focus:ring-indigo-500/30 disabled:opacity-50 cursor-pointer disabled:cursor-not-allowed">
                                            </td>
                                        </template>
                                        <template x-if="!row.isHeader && !row.editSlug">
                                            <td class="py-3 px-4 text-center"><span class="text-slate-300 dark:text-slate-700/60">-</span></td>
                                        </template>

                                        <!-- Delete Column -->
                                        <template x-if="!row.isHeader && row.delSlug">
                                            <td class="py-3 px-4 text-center">
                                                <input type="checkbox"
                                                       :disabled="getActiveRole()?.slug === 'super_admin'"
                                                       :checked="getActiveRole()?.slug === 'super_admin' || isPermChecked(row.delSlug)"
                                                       @change="togglePermBySlug(row.delSlug, $event.target.checked)"
                                                       class="w-4 h-4 rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-indigo-600 focus:ring-indigo-500/30 disabled:opacity-50 cursor-pointer disabled:cursor-not-allowed">
                                            </td>
                                        </template>
                                        <template x-if="!row.isHeader && !row.delSlug">
                                            <td class="py-3 px-4 text-center"><span class="text-slate-300 dark:text-slate-700/60">-</span></td>
                                        </template>

                                    </tr>
                                </template>
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
        slugToIdMap: {},
        expandedModules: {},
        permissionMetadata: [
            { module: "Academic", items: [
                { name: "Academic Years", view: "view_academic_years", create: "create_academic_years", edit: "edit_academic_years", del: "delete_academic_years" },
                { name: "Main Groups", view: "view_main_groups", create: "create_main_groups", edit: "edit_main_groups", del: "delete_main_groups" },
                { name: "Curriculum", view: "view_curriculum", create: "create_curriculum", edit: "edit_curriculum", del: "delete_curriculum" },
                { name: "Subjects", view: "view_subjects", create: "create_subjects", edit: "edit_subjects", del: "delete_subjects" },
                { name: "Classes", view: "view_classes", create: "create_classes", edit: "edit_classes", del: "delete_classes" },
                { name: "Students", view: "view_students_list", create: "create_students_list", edit: "edit_students_list", del: "delete_students_list" },
                { name: "Teachers", view: "view_teachers", create: "create_teachers", edit: "edit_teachers", del: "delete_teachers" },
                { name: "Attendance", view: "view_acad_attendance", create: "create_acad_attendance", edit: "edit_acad_attendance", del: "delete_acad_attendance" },
                { name: "Timetable", view: "view_timetable", create: "create_timetable", edit: "edit_timetable", del: "delete_timetable" },
                { name: "Assessments", view: "view_assessments", create: "create_assessments", edit: "edit_assessments", del: "delete_assessments" },
                { name: "Exams Setup", view: "view_exams", create: "create_exams", edit: "edit_exams", del: "delete_exams" },
                { name: "Report Cards", view: "view_report_cards", create: "create_report_cards", edit: "edit_report_cards", del: "delete_report_cards" },
                { name: "Promotion", view: "view_promotion", create: "create_promotion", edit: "edit_promotion", del: "delete_promotion" },
                { name: "Announcements", view: "view_announcements", create: "create_announcements", edit: "edit_announcements", del: "delete_announcements" },
                { name: "Settings", view: "view_academic_settings", edit: "edit_academic_settings" }
            ]},
            { module: "Staff", items: [
                { name: "Staff Overview", view: "view_staff_overview" },
                { name: "Staff Directory", view: "view_staff_directory", create: "create_staff_directory", edit: "edit_staff_directory", del: "delete_staff_directory" },
                { name: "Departments", view: "view_departments", create: "create_departments", edit: "edit_departments", del: "delete_departments" },
                { name: "Designations", view: "view_designations", create: "create_designations", edit: "edit_designations", del: "delete_designations" },
                { name: "Staff Attendance", view: "view_staff_attendance", create: "create_staff_attendance", edit: "edit_staff_attendance", del: "delete_staff_attendance" },
                { name: "Face Kiosk", view: "view_face_kiosk", create: "create_face_kiosk" },
                { name: "Face Register", view: "view_face_register", create: "create_face_register", edit: "edit_face_register", del: "delete_face_register" },
                { name: "Leave Management", view: "view_leave_management", create: "create_leave_management", edit: "edit_leave_management", del: "delete_leave_management" },
                { name: "Roles & Permissions", view: "view_staff_roles", create: "create_staff_roles", edit: "edit_staff_roles", del: "delete_staff_roles" },
                { name: "User Accounts", view: "view_staff_user_accounts", create: "create_staff_user_accounts", edit: "edit_staff_user_accounts", del: "delete_staff_user_accounts" },
                { name: "Staff Settings", view: "view_staff_settings", edit: "edit_staff_settings" }
            ]},
            { module: "Fees", items: [
                { name: "Fees Dashboard", view: "view_fees_dashboard" },
                { name: "All Invoices", view: "view_all_invoices", create: "create_all_invoices", edit: "edit_all_invoices", del: "delete_all_invoices" },
                { name: "Fee Structures", view: "view_fee_structures", create: "create_fee_structures", edit: "edit_fee_structures", del: "delete_fee_structures" },
                { name: "Batch Generator", view: "view_batch_generator", create: "create_batch_generator" },
                { name: "Receipts Log", view: "view_receipts_log", create: "create_receipts_log" },
                { name: "Fee Categories", view: "view_fee_categories", create: "create_fee_categories", edit: "edit_fee_categories", del: "delete_fee_categories" },
                { name: "Late Fee Policies", view: "view_late_fee_policies", create: "create_late_fee_policies", edit: "edit_late_fee_policies", del: "delete_late_fee_policies" }
            ]},
            { module: "Transport", items: [
                { name: "Transport Overview", view: "view_transport_overview" },
                { name: "Drivers", view: "view_transport_drivers", create: "create_transport_drivers", edit: "edit_transport_drivers", del: "delete_transport_drivers" },
                { name: "Student Assignments", view: "view_student_transport", create: "create_student_transport", edit: "edit_student_transport", del: "delete_student_transport" },
                { name: "Live Tracking", view: "view_live_tracking" },
                { name: "Transport Settings", view: "view_transport_settings", edit: "edit_transport_settings" },
                { name: "Transport Logs", view: "view_transport_logs" }
            ]},
            { module: "Payroll", items: [
                { name: "Payroll Runs", view: "view_payroll_runs", create: "create_payroll_runs", edit: "edit_payroll_runs", del: "delete_payroll_runs" },
                { name: "Detailed Attendance", view: "view_payroll_attendance" },
                { name: "Holidays Calendar", view: "view_holidays_calendar", create: "create_holidays_calendar", edit: "edit_holidays_calendar", del: "delete_holidays_calendar" },
                { name: "Audit History Logs", view: "view_payroll_audit" }
            ]},
            { module: "Documents", items: [
                { name: "Documents Overview", view: "view_documents_overview" },
                { name: "Student Documents", view: "view_student_documents", create: "create_student_documents", edit: "edit_student_documents", del: "delete_student_documents" },
                { name: "Parent Documents", view: "view_parent_documents", create: "create_parent_documents", edit: "edit_parent_documents", del: "delete_parent_documents" },
                { name: "Driver Documents", view: "view_driver_documents", create: "create_driver_documents", edit: "edit_driver_documents", del: "delete_driver_documents" }
            ]},
            { module: "Certificate Generator", items: [
                { name: "Certificates – View", view: "view_certificates" },
                { name: "Certificates – Create", create: "create_certificates" },
                { name: "Certificates – Edit", edit: "edit_certificates" },
                { name: "Certificates – Delete", del: "delete_certificates" }
            ]},
            { module: "File Manager", items: [
                { name: "File Manager – View", view: "view_file_manager" },
                { name: "File Manager – Upload", create: "upload_file_manager" },
                { name: "File Manager – Delete", del: "delete_file_manager" },
                { name: "File Manager – Share", create: "share_file_manager" }
            ]},
            { module: "Reports", items: [
                { name: "Reports Overview", view: "view_reports_overview" },
                { name: "Financial Reports", view: "view_financial_reports", create: "export_financial_reports" },
                { name: "Student Reports", view: "view_student_reports", create: "export_student_reports" },
                { name: "Staff & HR Reports", view: "view_staff_reports", create: "export_staff_reports" },
                { name: "WhatsApp Logs", view: "view_whatsapp_logs" }
            ]},
            { module: "Settings", items: [
                { name: "General Settings", view: "view_general_settings", edit: "edit_general_settings" },
                { name: "Integrations & APIs", view: "view_integrations", edit: "edit_integrations" },
                { name: "System Configurations", view: "view_system_config", edit: "edit_system_config" }
            ]},
            { module: "Online Enrollment", items: [
                { name: "Enrollments", view: "view_enrollments", create: "approve_enrollments", del: "reject_enrollments" }
            ]},
            { module: "Games", items: [
                { name: "Games Overview", view: "view_games_overview" },
                { name: "Money Counting", view: "view_game_money_counting", create: "create_game_money_counting", edit: "edit_game_money_counting", del: "delete_game_money_counting" },
                { name: "Safe vs Unsafe", view: "view_game_safe_vs_unsafe", create: "create_game_safe_vs_unsafe", edit: "edit_game_safe_vs_unsafe", del: "delete_game_safe_vs_unsafe" },
                { name: "Safety Signs", view: "view_game_safety_signs", create: "create_game_safety_signs", edit: "edit_game_safety_signs", del: "delete_game_safety_signs" },
                { name: "Sentence Builder", view: "view_game_sentence_builder", create: "create_game_sentence_builder", edit: "edit_game_sentence_builder", del: "delete_game_sentence_builder" },
                { name: "Shopping Store", view: "view_game_shopping_store", create: "create_game_shopping_store", edit: "edit_game_shopping_store", del: "delete_game_shopping_store" }
            ]}
        ],
        modalRoleData: { id: null, name: '', level: 10, description: '' },

        init() {
            this.allPermissions.forEach(p => { this.slugToIdMap[p.slug] = p.id; });
            this.permissionMetadata.forEach((mod, idx) => { this.expandedModules[idx] = false; });
            if (this.roles.length > 0) this.activeRoleId = this.roles[0].id;
        },

        toggleModule(idx) { this.expandedModules[idx] = !this.expandedModules[idx]; },

        getModuleRows(mod, modIdx) {
            const rows = [{ key: 'h-' + modIdx, isHeader: true }];
            if (this.expandedModules[modIdx]) {
                mod.items.forEach((item, i) => {
                    rows.push({ key: 'r-' + modIdx + '-' + i, isHeader: false, name: item.name, viewSlug: item.view || null, createSlug: item.create || null, editSlug: item.edit || null, delSlug: item.del || null });
                });
            }
            return rows;
        },

        isPermChecked(slug) {
            const role = this.getActiveRole();
            if (!role) return false;
            if (role.slug === 'super_admin') return true;
            const id = this.slugToIdMap[slug];
            return id ? role.permission_ids.map(Number).includes(Number(id)) : false;
        },

        togglePermBySlug(slug, checked) {
            const role = this.getActiveRole();
            if (!role || role.slug === 'super_admin') return;
            const id = this.slugToIdMap[slug];
            if (!id) return;
            let pIds = role.permission_ids.map(Number);
            if (checked) { if (!pIds.includes(Number(id))) pIds.push(Number(id)); }
            else { pIds = pIds.filter(x => x !== Number(id)); }
            role.permission_ids = pIds;
        },

        selectRole(roleId) { this.activeRoleId = roleId; },

        getActiveRole() { return this.roles.find(r => r.id === this.activeRoleId); },

        async savePermissions() {
            const role = this.getActiveRole();
            if (!role || role.slug === 'super_admin') return;
            this.saving = true;
            try {
                const formData = new FormData();
                formData.append('_csrf', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                role.permission_ids.forEach(id => formData.append('permissions[]', id));
                const response = await fetch('<?= url('roles') ?>/' + role.id, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const res = await response.json();
                if (res.success) { alert('Permissions updated successfully!'); } else { alert('Failed: ' + (res.message || 'Unknown error')); }
            } catch (err) { console.error(err); alert('An error occurred while saving.'); }
            finally { this.saving = false; }
        },

        setPreset(type) {
            const role = this.getActiveRole();
            if (!role || role.slug === 'super_admin') return;
            let targetIds = [];
            if (type === 'viewer') {
                this.permissionMetadata.forEach(mod => { mod.items.forEach(item => { if (item.view) { const id = this.slugToIdMap[item.view]; if (id) targetIds.push(Number(id)); } }); });
            } else if (type === 'editor') {
                this.permissionMetadata.forEach(mod => { mod.items.forEach(item => { ['view','create','edit','del'].forEach(col => { if (item[col]) { const id = this.slugToIdMap[item[col]]; if (id) targetIds.push(Number(id)); } }); }); });
            }
            role.permission_ids = targetIds;
        },

        openCreateModal() { this.modalRoleData = { id: null, name: '', level: 10, description: '' }; this.showCreateModal = true; },
        openEditModal(role) { this.modalRoleData = { id: role.id, name: role.name, level: Number(role.level), description: role.description }; this.showEditModal = true; },

        async submitCreate() {
            try {
                const formData = new FormData();
                formData.append('_csrf', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                formData.append('name', this.modalRoleData.name);
                formData.append('level', this.modalRoleData.level);
                formData.append('description', this.modalRoleData.description);
                const response = await fetch('<?= url('roles') ?>', { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const res = await response.json();
                if (res.success) { window.location.reload(); } else { alert('Error: ' + (res.message || '')); }
            } catch (err) { console.error(err); alert('An error occurred.'); }
        },

        async submitEdit() {
            try {
                const formData = new FormData();
                formData.append('_csrf', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                formData.append('name', this.modalRoleData.name);
                formData.append('level', this.modalRoleData.level);
                formData.append('description', this.modalRoleData.description);
                const response = await fetch('<?= url('roles') ?>/' + this.modalRoleData.id, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const res = await response.json();
                if (res.success) { window.location.reload(); } else { alert('Error: ' + (res.message || '')); }
            } catch (err) { console.error(err); alert('An error occurred.'); }
        },

        async deleteRole(roleId) {
            if (!confirm('Are you sure you want to delete this role?')) return;
            try {
                const formData = new FormData();
                formData.append('_csrf', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                formData.append('_method', 'DELETE');
                const response = await fetch('<?= url('roles') ?>/' + roleId, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const res = await response.json();
                if (res.success) { window.location.reload(); } else { alert('Error: ' + (res.message || '')); }
            } catch (err) { console.error(err); alert('An error occurred.'); }
        }
    };
}
</script>

<?php
$content = ob_get_clean();
?>
