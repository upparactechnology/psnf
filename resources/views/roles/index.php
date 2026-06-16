<?php
$layout    = 'app';
$pageTitle = 'Roles & Permissions';
$breadcrumbs = [['label'=>'Dashboard','url'=>'/dashboard'],['label'=>'Roles & Permissions']];
ob_start();
$roleIconColors = [
    'super_admin'  => 'bg-red-900/40 text-red-400',
    'school_admin' => 'bg-orange-900/40 text-orange-400',
    'manager'      => 'bg-yellow-900/40 text-yellow-400',
    'teacher'      => 'bg-blue-900/40 text-blue-400',
    'therapist'    => 'bg-purple-900/40 text-purple-400',
    'staff'        => 'bg-slate-700/60 text-slate-300',
    'driver'       => 'bg-teal-900/40 text-teal-400',
    'parent'       => 'bg-emerald-900/40 text-emerald-400',
    'student'      => 'bg-brand-900/40 text-brand-400',
];
?>

<div class="space-y-5">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-white">Roles & Permissions</h2>
            <p class="text-sm text-slate-500 mt-0.5"><?= count($roles) ?> roles defined</p>
        </div>
        <?php if (has_permission('create_roles')): ?>
        <a href="<?= url('roles/create') ?>" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition-all shadow-lg hover:opacity-90" style="background: linear-gradient(135deg, #6366f1, #a855f7);">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Role
        </a>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
        <?php foreach ($roles as $role): ?>
        <?php $ic = $roleIconColors[$role['slug']] ?? 'bg-slate-700/60 text-slate-300'; ?>
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-5 hover:border-slate-700/60 transition-all group">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center <?= $ic ?>">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-white"><?= e($role['name']) ?></h3>
                        <?php if ($role['is_system']): ?>
                        <span class="text-xs text-slate-600">System Role</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <?php if (has_permission('edit_roles')): ?>
                    <a href="<?= url('roles/'.$role['id'].'/edit') ?>" class="p-1.5 rounded-lg text-slate-500 hover:text-brand-400 hover:bg-brand-900/30 transition-all" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                    <?php endif; ?>
                    <?php if (has_permission('delete_roles') && !$role['is_system']): ?>
                    <form method="POST" action="<?= url('roles/'.$role['id']) ?>" onsubmit="return confirm('Delete this role?')">
                        <?= \Core\View::csrf() ?>
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-red-400 hover:bg-red-900/30 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($role['description']): ?>
            <p class="text-xs text-slate-500 mb-3"><?= e($role['description']) ?></p>
            <?php endif; ?>

            <div class="flex items-center justify-between">
                <span class="text-xs text-slate-600"><?= $role['permission_count'] ?? 0 ?> permissions</span>
                <a href="<?= url('roles/'.$role['id'].'/edit') ?>" class="text-xs text-brand-400 hover:text-brand-300 transition-colors">View →</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/app.php';
?>
