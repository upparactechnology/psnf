<?php
$layout    = 'app';
$pageTitle = 'Create User';
$breadcrumbs = [['label'=>'Dashboard','url'=>'/dashboard'],['label'=>'Users','url'=>'/users'],['label'=>'Create User']];
ob_start();
$errors = \Core\Session::getFlash('errors') ?? [];
$old    = \Core\Session::getFlash('old') ?? [];
$fn     = fn($k) => $old[$k] ?? '';
function iClass(string $f, array $e=[]): string { $b='w-full bg-slate-900/70 border text-white placeholder-slate-500 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-1 transition-all'; return $b.(isset($e[$f])?' border-red-600/60 focus:border-red-500 focus:ring-red-500/20':' border-slate-700/60 focus:border-brand-500 focus:ring-brand-500/30'); }
function sClass(string $f, array $e=[]): string { $b='w-full bg-slate-900/70 border text-slate-300 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-1 transition-all'; return $b.(isset($e[$f])?' border-red-600/60 focus:border-red-500 focus:ring-red-500/20':' border-slate-700/60 focus:border-brand-500 focus:ring-brand-500/30'); }
function eMsg(string $f, array $e): string { if(!isset($e[$f]))return ''; return '<p class="text-xs text-red-400 mt-1">'.htmlspecialchars($e[$f]).'</p>'; }
?>

<div class="max-w-3xl mx-auto space-y-5">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-white">Create New User</h2>
            <p class="text-sm text-slate-500 mt-0.5">Add a staff member to the system</p>
        </div>
        <a href="<?= e($_GET['redirect_to'] ?? '/users') ?>" class="text-sm text-slate-400 hover:text-slate-300 transition-colors">← Back</a>
    </div>

    <?php
    $oldRoleIds = array_map('intval', $old['roles'] ?? []);
    $oldRoleSlugs = [];
    foreach ($roles as $role) {
        if (in_array((int)$role['id'], $oldRoleIds)) {
            $oldRoleSlugs[] = $role['slug'];
        }
    }
    $redirectTo = $_GET['redirect_to'] ?? $old['redirect_to'] ?? '/users';
    ?>
    <form method="POST" action="<?= url('users') ?>" x-data="{ loading: false, showPass: false, selectedRoleSlugs: <?= json_encode($oldRoleSlugs) ?> }" @submit="loading = true">
        <?= \Core\View::csrf() ?>
        <input type="hidden" name="redirect_to" value="<?= e($redirectTo) ?>">

        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 space-y-5 mb-5">
            <h3 class="text-sm font-semibold text-slate-300 border-b border-slate-800 pb-3">Account Details</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Full Name <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="<?= e($fn('name')) ?>" required class="<?= iClass('name',$errors) ?>" placeholder="Full name">
                    <?= eMsg('name',$errors) ?>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Email Address <span class="text-red-400">*</span></label>
                    <input type="email" name="email" value="<?= e($fn('email')) ?>" required class="<?= iClass('email',$errors) ?>" placeholder="user@psnf.edu">
                    <?= eMsg('email',$errors) ?>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Password <span class="text-red-400">*</span></label>
                    <div class="relative">
                        <input :type="showPass ? 'text' : 'password'" name="password" required minlength="8" class="<?= iClass('password',$errors) ?> pr-10" placeholder="Min. 8 characters">
                        <button type="button" @click="showPass=!showPass" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-500 hover:text-slate-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                    <?= eMsg('password',$errors) ?>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Confirm Password <span class="text-red-400">*</span></label>
                    <input :type="showPass ? 'text' : 'password'" name="password_confirmation" required minlength="8" class="<?= iClass('password_confirmation',$errors) ?>" placeholder="Repeat password">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Phone</label>
                    <input type="tel" name="phone" value="<?= e($fn('phone')) ?>" class="<?= iClass('phone',$errors) ?>" placeholder="+91 XXXXX XXXXX">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Designation</label>
                    <input type="text" name="designation" value="<?= e($fn('designation')) ?>" class="<?= iClass('designation',$errors) ?>" placeholder="e.g. Class Teacher">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">School <span class="text-red-400">*</span></label>
                    <select name="school_id" required class="<?= sClass('school_id',$errors) ?>">
                        <option value="">Select school</option>
                        <?php foreach ($schools as $sc): ?>
                        <option value="<?= $sc['id'] ?>" <?= $fn('school_id')==$sc['id']?'selected':'' ?>><?= e($sc['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?= eMsg('school_id',$errors) ?>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Branch <span class="text-red-400">*</span></label>
                    <select name="branch_id" required class="<?= sClass('branch_id',$errors) ?>">
                        <option value="">Select branch</option>
                        <?php foreach ($branches as $br): ?>
                        <option value="<?= $br['id'] ?>" <?= $fn('branch_id')==$br['id']?'selected':'' ?>><?= e($br['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?= eMsg('branch_id',$errors) ?>
                </div>
            </div>
            <!-- Teacher settings -->
            <div class="border-t border-slate-800/60 pt-4 mt-4 space-y-4">
                <h4 class="text-xs font-semibold text-slate-300 uppercase tracking-wider">Teacher Attendance Settings</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Lecture Start Time</label>
                        <input type="time" name="lecture_time" value="<?= e($fn('lecture_time')) ?>" class="<?= iClass('lecture_time',$errors) ?>">
                        <p class="text-3xs text-slate-500">Only required for teachers to check daily attendance.</p>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Grace Period (Minutes)</label>
                        <input type="number" name="grace_period" min="0" value="<?= e($fn('grace_period') ?: '5') ?>" class="<?= iClass('grace_period',$errors) ?>" placeholder="e.g. 5">
                        <p class="text-3xs text-slate-500">Allowed delay (in minutes) before marked as Late.</p>
                    </div>
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-medium text-slate-400">Active Status</label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-brand-500 focus:ring-brand-500/30">
                    <span class="text-sm text-slate-300">Account is active</span>
                </label>
            </div>
        </div>

        <!-- Roles -->
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 mb-5">
            <h3 class="text-sm font-semibold text-slate-300 border-b border-slate-800 pb-3 mb-4">Assign Roles</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                <?php foreach ($roles as $role): ?>
                <label class="flex items-center gap-2 cursor-pointer p-3 rounded-xl border border-slate-700/30 hover:border-slate-600/50 hover:bg-slate-800/30 transition-all">
                    <input type="checkbox" name="roles[]" value="<?= $role['id'] ?>"
                           :checked="selectedRoleSlugs.includes('<?= $role['slug'] ?>')"
                           @change="if ($el.checked) { if (!selectedRoleSlugs.includes('<?= $role['slug'] ?>')) selectedRoleSlugs.push('<?= $role['slug'] ?>') } else { selectedRoleSlugs = selectedRoleSlugs.filter(s => s !== '<?= $role['slug'] ?>') }"
                           class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-brand-500 focus:ring-brand-500/30">
                    <span class="text-sm text-slate-300"><?= e($role['name']) ?></span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Applications -->
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 mb-5">
            <h3 class="text-sm font-semibold text-slate-300 border-b border-slate-800 pb-3 mb-4"
                x-text="'Assign Applications' + (selectedRoleSlugs.includes('parent') ? ' (for Mobile Application)' : (selectedRoleSlugs.includes('staff') ? ' (for Admin Dashboard)' : ''))">Assign Applications</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                <?php foreach ($apps as $appKey => $appName): ?>
                <label class="flex items-center gap-2 cursor-pointer p-3 rounded-xl border border-slate-700/30 hover:border-slate-600/50 hover:bg-slate-800/30 transition-all">
                    <input type="checkbox" name="apps[]" value="<?= $appKey ?>" <?= in_array($appKey, $fn('apps') ?: []) ? 'checked' : '' ?> class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-brand-500 focus:ring-brand-500/30">
                    <span class="text-sm text-slate-300"><?= e($appName) ?></span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <a href="<?= url('users') ?>" class="px-5 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:text-white border border-slate-700/50 hover:border-slate-600 transition-all">Cancel</a>
            <button type="submit" :disabled="loading" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition-all shadow-lg hover:opacity-90" style="background: linear-gradient(135deg, #6366f1, #a855f7);" :class="loading?'opacity-70 cursor-not-allowed':''">
                <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                Create User
            </button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
?>
