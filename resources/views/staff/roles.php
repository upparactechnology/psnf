<?php
$layout    = 'app';
$pageTitle = 'Roles & Permissions Matrix';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Staff Workspace', 'url' => '/staff/overview'], ['label' => 'Roles']];
ob_start();
?>

<div x-data="{ createModal: false, editModal: false, editId: '', editName: '', editDesc: '' }" class="space-y-6 max-w-5xl mx-auto">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Roles & Excel Permission Matrix</h1>
            <p class="text-xs text-slate-500 mt-0.5">Granular access control policies for School Admins, Teachers, Therapists, Parents & Staff</p>
        </div>
        <?php if (has_permission('create_staff_roles')): ?>
        <button @click="createModal = true" class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition-all shadow-sm">+ Create New Role</button>
        <?php endif; ?>
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
                <span class="text-2xs text-slate-400"><?= $role['is_system'] ? 'System Role' : 'Custom Role' ?></span>
                <div class="flex items-center gap-2">
                    <?php if (has_permission('edit_staff_roles')): ?>
                    <a href="<?= url('roles/' . $role['id'] . '/edit') ?>" class="font-bold text-indigo-500 hover:underline">Permissions →</a>
                    <button @click="editId = '<?= $role['id'] ?>'; editName = '<?= e($role['name']) ?>'; editDesc = '<?= e($role['description'] ?? '') ?>'; editModal = true" class="px-2.5 py-1 rounded-lg text-2xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">Edit</button>
                    <?php endif; ?>
                    <?php if (has_permission('delete_staff_roles') && !$role['is_system']): ?>
                    <form action="<?= url('staff/roles/' . $role['id'] . '/delete') ?>" method="POST" onsubmit="return confirm('Delete this role? This cannot be undone.')" class="inline">
                        <?= \Core\View::csrf() ?>
                        <button type="submit" class="px-2.5 py-1 rounded-lg text-2xs font-semibold bg-red-50 dark:bg-red-500/10 text-red-500 hover:bg-red-100 dark:hover:bg-red-500/20 transition-all">Delete</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Learning Games Section -->
    <div class="mt-8">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Learning Games & Activities</h2>
                <p class="text-xs text-slate-500 mt-0.5">Gamified educational modules for students with special education needs</p>
            </div>
            <a href="<?= url('game/index.html') ?>" target="_blank" class="px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-pink-600 hover:bg-pink-500 transition-all shadow-sm">🎮 Open Game Hub →</a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php 
            $colorMap = [
                'amber'   => 'bg-amber-500/10 text-amber-500 dark:bg-amber-500/20 hover:border-amber-400/60',
                'red'     => 'bg-red-500/10 text-red-500 dark:bg-red-500/20 hover:border-red-400/60',
                'orange'  => 'bg-orange-500/10 text-orange-500 dark:bg-orange-500/20 hover:border-orange-400/60',
                'blue'    => 'bg-blue-500/10 text-blue-500 dark:bg-blue-500/20 hover:border-blue-400/60',
                'emerald' => 'bg-emerald-500/10 text-emerald-500 dark:bg-emerald-500/20 hover:border-emerald-400/60',
                'pink'    => 'bg-pink-500/10 text-pink-500 dark:bg-pink-500/20 hover:border-pink-400/60',
            ];
            foreach ($games as $game): 
                $colors = $colorMap[$game['color']] ?? $colorMap['pink'];
                $parts  = explode(' ', $colors);
                $iconBg = $parts[0] ?? 'bg-pink-500/10';
                $iconText = $parts[1] ?? 'text-pink-500';
                $hoverBorder = $parts[3] ?? 'hover:border-pink-400/60';
            ?>
            <div class="group relative p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 <?= $hoverBorder ?> hover:shadow-xl transition-all space-y-3">
                <div class="flex items-start justify-between">
                    <div class="w-12 h-12 rounded-2xl <?= $iconBg ?> <?= $iconText ?> flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                        <?= $game['emoji'] ?>
                    </div>
                    <a href="<?= $game['url'] ?>" target="_blank" class="text-xs text-slate-400 font-bold group-hover:text-slate-700 dark:group-hover:text-white transition-colors">Play →</a>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white text-sm"><?= e($game['title']) ?></h3>
                    <p class="text-xs text-slate-500 mt-1"><?= e($game['description']) ?></p>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-2xs font-bold <?= $iconText ?> <?= $iconBg ?> px-2 py-0.5 rounded-full"><?= e($game['tag']) ?></span>
                </div>
                <?php if (!empty($game['target_roles'])): ?>
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                    <p class="text-2xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Target Roles</p>
                    <div class="flex flex-wrap gap-1">
                        <?php foreach ($game['target_roles'] as $gRole): ?>
                        <span class="px-2 py-0.5 rounded-full text-2xs font-semibold bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400"><?= e($gRole) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Create Role Modal -->
    <div x-show="createModal" class="fixed inset-0 z-50 flex items-center justify-center px-4" x-cloak>
        <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm" @click="createModal = false"></div>
        <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4 z-10">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Create New Role</h3>
            <form action="<?= url('staff/roles') ?>" method="POST" class="space-y-4 text-xs">
                <?= \Core\View::csrf() ?>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Role Name</label>
                    <input type="text" name="name" placeholder="e.g. Speech Therapist" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Description</label>
                    <textarea name="description" rows="2" placeholder="Brief description of this role" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white"></textarea>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="createModal = false" class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-500">Create Role</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Role Modal -->
    <div x-show="editModal" class="fixed inset-0 z-50 flex items-center justify-center px-4" x-cloak>
        <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm" @click="editModal = false"></div>
        <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4 z-10">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Edit Role</h3>
            <form :action="'<?= url('staff/roles/') ?>' + editId + '/update'" method="POST" class="space-y-4 text-xs">
                <?= \Core\View::csrf() ?>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Role Name</label>
                    <input type="text" name="name" x-model="editName" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Description</label>
                    <textarea name="description" x-model="editDesc" rows="2" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white"></textarea>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="editModal = false" class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-500">Update Role</button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
