<?php
$layout    = 'app';
$pageTitle = 'Edit User';
$breadcrumbs = [['label'=>'Dashboard','url'=>'/dashboard'],['label'=>'Users','url'=>'/users'],['label'=>'Edit: '.e($user['name']??'')]];
ob_start();
?>

<div class="max-w-3xl mx-auto space-y-5">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-white">Edit User</h2>
            <p class="text-sm text-slate-500 mt-0.5"><?= e($user['email'] ?? '') ?></p>
        </div>
        <a href="<?= url('users') ?>" class="text-sm text-slate-400 hover:text-slate-300 transition-colors">← Back</a>
    </div>

    <form method="POST" action="<?= url('users/'.$user['id']) ?>" x-data="{ loading: false }" @submit="loading = true">
        <?= \Core\View::csrf() ?>

        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 space-y-5 mb-5">
            <h3 class="text-sm font-semibold text-slate-300 border-b border-slate-800 pb-3">Account Details</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Full Name</label>
                    <input type="text" name="name" value="<?= e($user['name']??'') ?>" class="w-full bg-slate-900/70 border border-slate-700/60 text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Email Address</label>
                    <input type="email" name="email" value="<?= e($user['email']??'') ?>" class="w-full bg-slate-900/70 border border-slate-700/60 text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Phone</label>
                    <input type="tel" name="phone" value="<?= e($user['phone']??'') ?>" class="w-full bg-slate-900/70 border border-slate-700/60 text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Designation</label>
                    <input type="text" name="designation" value="<?= e($user['designation']??'') ?>" class="w-full bg-slate-900/70 border border-slate-700/60 text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">School</label>
                    <select name="school_id" class="w-full bg-slate-900/70 border border-slate-700/60 text-slate-300 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 transition-all">
                        <?php foreach ($schools as $sc): ?>
                        <option value="<?= $sc['id'] ?>" <?= ($user['school_id']??'')==$sc['id']?'selected':'' ?>><?= e($sc['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Branch</label>
                    <select name="branch_id" class="w-full bg-slate-900/70 border border-slate-700/60 text-slate-300 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 transition-all">
                        <?php foreach ($branches as $br): ?>
                        <option value="<?= $br['id'] ?>" <?= ($user['branch_id']??'')==$br['id']?'selected':'' ?>><?= e($br['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" <?= ($user['is_active']??1)?'checked':'' ?> class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-brand-500 focus:ring-brand-500/30">
                    <span class="text-sm text-slate-300">Account is active</span>
                </label>
            </div>
        </div>

        <!-- Roles -->
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 mb-5">
            <h3 class="text-sm font-semibold text-slate-300 border-b border-slate-800 pb-3 mb-4">Assign Roles</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                <?php
                $currentRoleIds = $user['role_ids'] ?? array_column(
                    \Core\Application::$app->db->select("SELECT role_id FROM user_roles WHERE user_id = ?", [$user['id']]),
                    'role_id'
                );
                foreach ($roles as $role):
                ?>
                <label class="flex items-center gap-2 cursor-pointer p-3 rounded-xl border border-slate-700/30 hover:border-slate-600/50 hover:bg-slate-800/30 transition-all">
                    <input type="checkbox" name="roles[]" value="<?= $role['id'] ?>"
                           <?= in_array($role['id'], array_map('intval', $currentRoleIds)) ? 'checked' : '' ?>
                           class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-brand-500 focus:ring-brand-500/30">
                    <span class="text-sm text-slate-300"><?= e($role['name']) ?></span>
                    <span class="ml-auto text-xs text-slate-600"><?= $role['permission_count'] ?? 0 ?></span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <a href="<?= url('users') ?>" class="px-5 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:text-white border border-slate-700/50 hover:border-slate-600 transition-all">Cancel</a>
            <button type="submit" :disabled="loading" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition-all shadow-lg hover:opacity-90" style="background: linear-gradient(135deg, #6366f1, #a855f7);" :class="loading?'opacity-70 cursor-not-allowed':''">
                <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                Save Changes
            </button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/app.php';
?>
