<?php
$layout    = 'app';
$pageTitle = 'User Accounts Management';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Staff Workspace', 'url' => '/staff/overview'], ['label' => 'User Accounts']];
ob_start();
?>

<div class="space-y-6 max-w-6xl mx-auto">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">User Login Accounts</h1>
            <p class="text-xs text-slate-500 mt-0.5">Authentication accounts separated from employee profiles (Drivers & Cleaners may have no login access)</p>
        </div>
        <a href="<?= url('users/create') ?>" class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition-all shadow-sm">+ Create User Account</a>
    </div>

    <!-- Users Table -->
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 dark:bg-slate-800 text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-2xs">
                <tr>
                    <th class="p-4">User</th>
                    <th class="p-4">Assigned Roles</th>
                    <th class="p-4">Last Login</th>
                    <th class="p-4 text-right">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                <?php foreach ($users as $u): ?>
                <tr>
                    <td class="p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white font-bold text-xs flex items-center justify-center">
                                <?= strtoupper(substr($u['name'], 0, 1)) ?>
                            </div>
                            <div>
                                <span class="font-bold text-slate-900 dark:text-white block"><?= e($u['name']) ?></span>
                                <span class="text-2xs text-slate-400 font-mono"><?= e($u['email']) ?></span>
                            </div>
                        </div>
                    </td>
                    <td class="p-4">
                        <span class="px-2.5 py-0.5 rounded-full text-2xs font-bold bg-indigo-500/10 text-indigo-500"><?= e($u['role_names'] ?: 'User') ?></span>
                    </td>
                    <td class="p-4 font-mono text-slate-400"><?= e($u['last_login_at'] ?? 'Never') ?></td>
                    <td class="p-4 text-right">
                        <span class="px-2.5 py-0.5 rounded-full text-2xs font-bold bg-emerald-500/10 text-emerald-500 uppercase">ACTIVE</span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
