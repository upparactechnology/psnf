<?php
$layout    = 'app';
$pageTitle = 'Edit User';
$breadcrumbs = [['label'=>'Dashboard','url'=>'/dashboard'],['label'=>'Users','url'=>'/users'],['label'=>'Edit: '.e($user['name']??'')]];
ob_start();
$errors = \Core\Session::getFlash('errors') ?? [];
$old    = \Core\Session::getFlash('old') ?? [];
$fn     = fn($k, $default='') => $old[$k] ?? ($user[$k] ?? $default);
function iClass(string $f, array $e=[]): string { $b='w-full bg-slate-900/70 border text-white placeholder-slate-500 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-1 transition-all'; return $b.(isset($e[$f])?' border-red-600/60 focus:border-red-500 focus:ring-red-500/20':' border-slate-700/60 focus:border-brand-500 focus:ring-brand-500/30'); }
function sClass(string $f, array $e=[]): string { $b='w-full bg-slate-900/70 border text-slate-300 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-1 transition-all'; return $b.(isset($e[$f])?' border-red-600/60 focus:border-red-500 focus:ring-red-500/20':' border-slate-700/60 focus:border-brand-500 focus:ring-brand-500/30'); }
function eMsg(string $f, array $e): string { if(!isset($e[$f]))return ''; return '<p class="text-xs text-red-400 mt-1">'.htmlspecialchars($e[$f]).'</p>'; }

$nameParts = explode(' ', trim($user['name'] ?? ''), 2);
$firstName = $old['first_name'] ?? $nameParts[0] ?? '';
$lastName  = $old['last_name'] ?? ($nameParts[1] ?? '');
$emp = $user['employee'] ?? null;
?>

<div class="max-w-3xl mx-auto space-y-5">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-white">Edit User</h2>
            <p class="text-sm text-slate-500 mt-0.5"><?= e($user['email'] ?? '') ?></p>
        </div>
        <a href="<?= e($_GET['redirect_to'] ?? url('users')) ?>" class="text-sm text-slate-400 hover:text-slate-300 transition-colors">← Back</a>
    </div>

    <?php
    $currentRoleIds = $user['role_ids'] ?? array_column(
        \Core\Application::$app->db->select("SELECT role_id FROM user_roles WHERE user_id = ?", [$user['id']]),
        'role_id'
    );
    $currentRoleSlugs = [];
    foreach ($roles as $role) {
        if (in_array((int)$role['id'], array_map('intval', $currentRoleIds))) {
            $currentRoleSlugs[] = $role['slug'];
        }
    }
    $redirectTo = $_GET['redirect_to'] ?? url('users');
    ?>
    <form method="POST" action="<?= url('users/'.$user['id']) ?>" x-data="{ loading: false, showPass: false, selectedRoleSlugs: <?= json_encode($currentRoleSlugs) ?> }" @submit="loading = true">
        <?= \Core\View::csrf() ?>
        <input type="hidden" name="redirect_to" value="<?= e($redirectTo) ?>">

        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 space-y-5 mb-5">
            <h3 class="text-sm font-semibold text-slate-300 border-b border-slate-800 pb-3">Account Details</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">First Name <span class="text-red-400">*</span></label>
                    <input type="text" name="first_name" value="<?= e($firstName) ?>" required class="<?= iClass('first_name',$errors) ?>" placeholder="First name">
                    <?= eMsg('first_name',$errors) ?>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Last Name</label>
                    <input type="text" name="last_name" value="<?= e($lastName) ?>" class="<?= iClass('last_name',$errors) ?>" placeholder="Last name">
                    <?= eMsg('last_name',$errors) ?>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Email Address <span class="text-red-400">*</span></label>
                    <input type="email" name="email" value="<?= e($fn('email')) ?>" required class="<?= iClass('email',$errors) ?>" placeholder="user@psnf.edu">
                    <?= eMsg('email',$errors) ?>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Phone</label>
                    <input type="tel" name="phone" value="<?= e($fn('phone')) ?>" class="<?= iClass('phone',$errors) ?>" placeholder="+91 XXXXX XXXXX">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Base Salary (per month)</label>
                    <input type="number" step="0.01" name="salary_basic" value="<?= e($fn('salary_basic', $emp['salary_basic'] ?? '0.00')) ?>" class="<?= iClass('salary_basic',$errors) ?>" placeholder="e.g. 35000">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Department</label>
                    <select name="department_id" class="<?= sClass('department_id',$errors) ?>">
                        <option value="">Select Department</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= $d['id'] ?>" <?= ($fn('department_id') ?: ($emp['department_id'] ?? ''))==$d['id']?'selected':'' ?>><?= e($d['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Designation</label>
                    <select name="designation_id" class="<?= sClass('designation_id',$errors) ?>">
                        <option value="">Select Designation</option>
                        <?php foreach ($designations as $des): ?>
                            <option value="<?= $des['id'] ?>" <?= ($fn('designation_id') ?: ($emp['designation_id'] ?? ''))==$des['id']?'selected':'' ?>><?= e($des['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">School <span class="text-red-400">*</span></label>
                    <select name="school_id" required class="<?= sClass('school_id',$errors) ?>">
                        <option value="">Select school</option>
                        <?php foreach ($schools as $sc): ?>
                        <option value="<?= $sc['id'] ?>" <?= ($fn('school_id'))==$sc['id']?'selected':'' ?>><?= e($sc['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?= eMsg('school_id',$errors) ?>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Branch <span class="text-red-400">*</span></label>
                    <select name="branch_id" required class="<?= sClass('branch_id',$errors) ?>">
                        <option value="">Select branch</option>
                        <?php foreach ($branches as $br): ?>
                        <option value="<?= $br['id'] ?>" <?= ($fn('branch_id'))==$br['id']?'selected':'' ?>><?= e($br['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?= eMsg('branch_id',$errors) ?>
                </div>
            </div>

            <!-- Staff/Teacher settings -->
            <div class="border-t border-slate-800/60 pt-4 mt-4 space-y-4">
                <h4 class="text-xs font-semibold text-slate-300 uppercase tracking-wider">Staff & Teacher Shift Settings</h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Shift Start Time</label>
                        <input type="time" name="min_clock_in" value="<?= e($fn('min_clock_in', $emp['min_clock_in'] ?? '09:00')) ?>" class="<?= iClass('min_clock_in',$errors) ?>">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Shift End Time</label>
                        <input type="time" name="max_clock_out" value="<?= e($fn('max_clock_out', $emp['max_clock_out'] ?? '17:00')) ?>" class="<?= iClass('max_clock_out',$errors) ?>">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Grace (Mins)</label>
                        <input type="number" name="grace_period" min="0" value="<?= e($fn('grace_period') ?: '5') ?>" class="<?= iClass('grace_period',$errors) ?>">
                    </div>
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-medium text-slate-400">Active Status</label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" <?= ($fn('is_active', '1'))?'checked':'' ?> class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-brand-500 focus:ring-brand-500/30">
                    <span class="text-sm text-slate-300">Account is active</span>
                </label>
            </div>
        </div>

        <!-- Roles -->
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 mb-5">
            <h3 class="text-sm font-semibold text-slate-300 border-b border-slate-800 pb-3 mb-4">Assign Roles</h3>
            <p class="text-xs text-slate-500 mb-3">Modules and pages visible to this user are determined by their assigned role permissions.</p>
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
?>
