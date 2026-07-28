<?php
$layout    = 'app';
$isTeachersOnly = $isTeachersOnly ?? false;
$pageTitle = $isTeachersOnly ? 'Teachers Registry' : 'Users';
$breadcrumbs = [['label'=>'Dashboard','url'=>'/dashboard'],['label'=> $isTeachersOnly ? 'Teachers' : 'Users']];
ob_start();
$roleColors = [
    'super_admin'  => 'bg-red-900/30 text-red-400 border-red-700/30',
    'school_admin' => 'bg-orange-900/30 text-orange-400 border-orange-700/30',
    'manager'      => 'bg-yellow-900/30 text-yellow-400 border-yellow-700/30',
    'teacher'      => 'bg-blue-900/30 text-blue-400 border-blue-700/30',
    'therapist'    => 'bg-purple-900/30 text-purple-400 border-purple-700/30',
    'staff'        => 'bg-slate-700/50 text-slate-300 border-slate-600/30',
    'parent'       => 'bg-emerald-900/30 text-emerald-400 border-emerald-700/30',
    'driver'       => 'bg-teal-900/30 text-teal-400 border-teal-700/30',
];
$redirectUrl = $isTeachersOnly ? '/academics/teachers' : '/users';
?>

<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-white"><?= e($isTeachersOnly ? 'Teachers Registry' : 'User Management') ?></h2>
            <p class="text-sm text-slate-500 mt-0.5"><?= number_format($total ?? 0) ?> accounts listed</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= url('users/attendance') ?>" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-300 hover:text-white border border-slate-700/50 hover:bg-slate-800/40 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Attendance Log
            </a>
            <?php if (has_permission('create_users')): ?>
            <a href="<?= url('users/create?redirect_to=' . urlencode($redirectUrl)) ?>" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition-all shadow-lg hover:opacity-90" style="background: linear-gradient(135deg, #6366f1, #a855f7);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                + Add Account
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Search & Filter (Hide role selector in Teachers only view) -->
    <form method="GET" action="<?= url($isTeachersOnly ? 'academics/teachers' : 'users') ?>" class="flex flex-col sm:flex-row items-center gap-3">
        <div class="relative w-full sm:max-w-xs">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <input type="text" name="search" value="<?= e($search ?? '') ?>"
                   placeholder="Search..."
                   class="w-full bg-slate-900/70 border border-slate-700/60 text-white placeholder-slate-500 rounded-xl py-2.5 pl-10 pr-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
        </div>
        
        <?php if (!$isTeachersOnly): ?>
        <div class="relative w-full sm:w-48">
            <select name="role_id" onchange="this.form.submit()"
                    class="w-full bg-slate-900/70 border border-slate-700/60 text-slate-300 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 transition-all">
                <option value="">All Roles</option>
                <?php foreach ($roles as $role): ?>
                <option value="<?= $role['id'] ?>" <?= ($role_id ?? '') == $role['id'] ? 'selected' : '' ?>><?= e($role['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <?php if (!empty($search) || !empty($role_id)): ?>
        <a href="<?= url($isTeachersOnly ? 'academics/teachers' : 'users') ?>" class="text-xs text-slate-400 hover:text-slate-350 transition-colors">Clear Filters</a>
        <?php endif; ?>
    </form>

    <!-- Table -->
    <?php if (empty($data)): ?>
    <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-16 text-center">
        <div class="w-16 h-16 rounded-2xl bg-slate-800 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <p class="text-slate-500 text-sm">No accounts found.</p>
    </div>
    <?php else: ?>
    <div class="rounded-2xl border border-slate-800/60 bg-slate-900/30 overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="border-b border-slate-800/60">
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">User</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider hidden md:table-cell">Roles</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider hidden lg:table-cell">Last Login</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/40">
                <?php foreach ($data as $u): ?>
                <tr class="hover:bg-slate-800/20 transition-colors group border-b border-slate-800/40">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0" style="background: linear-gradient(135deg, #6366f1, #a855f7);">
                                <?= strtoupper(substr($u['name'],0,1)) ?>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-white"><?= e($u['name']) ?></p>
                                <p class="text-xs text-slate-500"><?= e($u['email']) ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 hidden md:table-cell">
                        <?php
                        $userRoles = \Core\Application::$app->db->select(
                            "SELECT r.name, r.slug FROM roles r JOIN user_roles ur ON ur.role_id = r.id WHERE ur.user_id = ? LIMIT 3",
                            [$u['id']]
                        );
                        foreach (array_slice($userRoles, 0, 2) as $role):
                            $rc = $roleColors[$role['slug']] ?? 'bg-slate-700/50 text-slate-300 border-slate-600/30';
                        ?>
                        <span class="inline-flex text-xs px-2 py-0.5 rounded-full font-medium border mr-1 <?= $rc ?>"><?= e($role['name']) ?></span>
                        <?php endforeach; ?>
                    </td>
                    <td class="px-5 py-4 hidden lg:table-cell">
                        <span class="text-xs text-slate-500"><?= $u['last_login_at'] ? format_date($u['last_login_at'], 'd M Y, H:i') : 'Never' ?></span>
                    </td>
                    <td class="px-5 py-4">
                        <span class="inline-flex text-xs px-2.5 py-1 rounded-full font-medium <?= $u['is_active'] ? 'bg-emerald-900/30 text-emerald-400 border border-emerald-700/30' : 'bg-red-900/30 text-red-400 border border-red-700/30' ?>">
                            <?= $u['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <!-- Perfectly visible option buttons (removed group-hover opacity restriction) -->
                        <div class="flex items-center justify-end gap-3">
                            <?php if (has_permission('edit_users')): ?>
                            <a href="<?= url('users/'.$u['id'].'/edit?redirect_to=' . urlencode($redirectUrl)) ?>" 
                               class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-800 text-indigo-400 border border-indigo-500/20 hover:bg-slate-700 transition-all" title="Edit">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span>Edit</span>
                            </a>
                            <?php endif; ?>
                            <?php if (has_permission('delete_users') && $u['id'] !== auth_id()): ?>
                            <form method="POST" action="<?= url('users/'.$u['id'].'?redirect_to=' . urlencode($redirectUrl)) ?>" onsubmit="return confirm('Delete this user?')" class="inline-block">
                                <?= \Core\View::csrf() ?>
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" 
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-800 text-red-400 border border-red-500/20 hover:bg-slate-700 transition-all" title="Delete">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    <span>Delete</span>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if (($last_page ?? 1) > 1): ?>
    <div class="flex items-center justify-between mt-4">
        <p class="text-sm text-slate-500">Showing <?= $from ?? 1 ?>–<?= $to ?? count($data) ?> of <?= number_format($total ?? 0) ?></p>
        <div class="flex items-center gap-2">
            <?php if (($current_page??1) > 1): ?>
            <a href="?<?= http_build_query(['page' => ($current_page??1)-1, 'search' => $search??'', 'role_id' => $role_id??'']) ?>" class="px-3 py-1.5 rounded-lg text-sm text-slate-400 hover:text-white hover:bg-slate-800 transition-all border border-slate-700/50">← Prev</a>
            <?php endif; ?>
            <?php if (($current_page??1) < ($last_page??1)): ?>
            <a href="?<?= http_build_query(['page' => ($current_page??1)+1, 'search' => $search??'', 'role_id' => $role_id??'']) ?>" class="px-3 py-1.5 rounded-lg text-sm text-slate-400 hover:text-white hover:bg-slate-800 transition-all border border-slate-700/50">Next →</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
?>
