<?php
$layout    = 'app';
$isEdit    = isset($role);
$pageTitle = $isEdit ? 'Edit Role' : 'Create Role';
$breadcrumbs = [['label'=>'Dashboard','url'=>'/dashboard'],['label'=>'Roles','url'=>'/roles'],['label'=>$pageTitle]];
ob_start();
$r = $role ?? [];
$currentPermIds = $r['permission_ids'] ?? [];
?>

<div class="max-w-4xl mx-auto space-y-5">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-white"><?= $pageTitle ?></h2>
            <p class="text-sm text-slate-500 mt-0.5">Define role name and assign permissions</p>
        </div>
        <a href="<?= url('roles') ?>" class="text-sm text-slate-400 hover:text-slate-300 transition-colors">← Back</a>
    </div>

    <form method="POST" action="<?= $isEdit ? url('roles/'.$r['id']) : url('roles') ?>" x-data="{ loading: false, allSelected: false }" @submit="loading = true">
        <?= \Core\View::csrf() ?>

        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 space-y-4 mb-5">
            <h3 class="text-sm font-semibold text-slate-300 border-b border-slate-800 pb-3">Role Details</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Role Name <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="<?= e($r['name']??'') ?>" required
                           class="w-full bg-slate-900/70 border border-slate-700/60 text-white placeholder-slate-500 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all"
                           placeholder="e.g. Academic Coordinator">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Description</label>
                    <input type="text" name="description" value="<?= e($r['description']??'') ?>"
                           class="w-full bg-slate-900/70 border border-slate-700/60 text-white placeholder-slate-500 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all"
                           placeholder="Role description">
                </div>
            </div>
        </div>

        <!-- Permissions -->
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 mb-5">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3 mb-5">
                <h3 class="text-sm font-semibold text-slate-300">Assign Permissions</h3>
                <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-400 hover:text-slate-300">
                    <input type="checkbox" @change="$el.closest('form').querySelectorAll('input[name=\"permissions[]\"]').forEach(cb => cb.checked = $event.target.checked)"
                           class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-brand-500 focus:ring-brand-500/30">
                    Select All
                </label>
            </div>

            <?php foreach ($permissions as $module => $perms): ?>
            <div class="mb-6 last:mb-0">
                <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3"><?= ucfirst(str_replace('_', ' ', $module)) ?></h4>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
                    <?php foreach ($perms as $perm): ?>
                    <label class="flex items-center gap-2 p-2.5 rounded-lg border border-slate-800/60 hover:border-slate-700/60 hover:bg-slate-800/30 cursor-pointer transition-all">
                        <input type="checkbox" name="permissions[]" value="<?= $perm['id'] ?>"
                               <?= in_array((int)$perm['id'], array_map('intval', $currentPermIds)) ? 'checked' : '' ?>
                               class="w-3.5 h-3.5 rounded border-slate-600 bg-slate-800 text-brand-500 focus:ring-brand-500/30 flex-shrink-0">
                        <span class="text-xs text-slate-400"><?= e(str_replace('_',' ', $perm['slug'])) ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="flex items-center justify-between">
            <a href="<?= url('roles') ?>" class="px-5 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:text-white border border-slate-700/50 hover:border-slate-600 transition-all">Cancel</a>
            <button type="submit" :disabled="loading" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition-all shadow-lg hover:opacity-90" style="background: linear-gradient(135deg, #6366f1, #a855f7);" :class="loading?'opacity-70 cursor-not-allowed':''">
                <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                <?= $isEdit ? 'Update Role' : 'Create Role' ?>
            </button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
?>
