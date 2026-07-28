<?php
$layout    = 'app';
$pageTitle = 'Roles & Permissions Matrix';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Staff Workspace', 'url' => '/staff/overview'], ['label' => 'Roles']];
ob_start();
?>

<div class="space-y-6 max-w-5xl mx-auto">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Roles & Excel Permission Matrix</h1>
            <p class="text-xs text-slate-500 mt-0.5">Granular access control policies for School Admins, Teachers, Therapists, Parents & Staff</p>
        </div>
        <a href="<?= url('roles/create') ?>" class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition-all shadow-sm">+ Create New Role</a>
    </div>

    <!-- Roles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php foreach ($roles as $role): ?>
        <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 space-y-3 flex flex-col justify-between">
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-mono text-xs font-bold text-indigo-500"><?= e($role['slug']) ?></span>
                    <span class="px-2.5 py-0.5 rounded-full text-2xs font-bold bg-indigo-500/10 text-indigo-500"><?= (int)($role['permission_count'] ?? 0) ?> Permissions</span>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white"><?= e($role['name']) ?></h3>
                <p class="text-xs text-slate-500"><?= e($role['description'] ?? 'System access role') ?></p>
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                <span class="text-2xs text-slate-400">System Role</span>
                <a href="<?= url('roles/' . $role['id'] . '/edit') ?>" class="font-bold text-indigo-500 hover:underline">Edit Permissions Matrix →</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
