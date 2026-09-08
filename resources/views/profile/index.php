<?php
$layout    = 'app';
$pageTitle = 'My Profile';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'My Profile']];
ob_start();
?>

<div class="max-w-4xl mx-auto space-y-6">

    <!-- Profile Header -->
    <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 backdrop-blur shadow-sm">
        <div class="flex flex-col sm:flex-row items-center gap-6">
            <!-- Avatar -->
            <div class="relative group" x-data="{ uploading: false }">
                <form method="POST" action="<?= url('profile/avatar') ?>" enctype="multipart/form-data" id="avatarForm">
                    <?= \Core\View::csrf() ?>
                    <input type="file" name="avatar" accept="image/*" class="hidden" id="avatarInput" onchange="this.form.submit()">
                    <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-brand-500 to-purple-600 flex items-center justify-center text-white text-3xl font-bold overflow-hidden cursor-pointer ring-4 ring-slate-800/60 group-hover:ring-brand-500/30 transition-all" @click="$refs.avatarClick.click()">
                        <?php if (!empty($user['avatar'])): ?>
                            <img src="<?= url('uploads/avatars/' . $user['avatar']) ?>" class="w-full h-full object-cover" alt="Avatar">
                        <?php else: ?>
                            <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <button type="button" x-ref="avatarClick" @click="$refs.fileInput.click()" class="hidden"></button>
                    <input type="file" name="avatar" accept="image/*" class="hidden" x-ref="fileInput" onchange="this.form.submit()">
                </form>
                <div class="absolute inset-0 rounded-2xl bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer" @click="$refs.fileInput.click()">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>

            <!-- User Info -->
            <div class="text-center sm:text-left flex-1">
                <h2 class="text-xl font-bold text-white"><?= e($user['name']) ?></h2>
                <p class="text-sm text-slate-400 mt-0.5"><?= e($user['email']) ?></p>
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mt-2">
                    <?php foreach ($roles as $role): ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-brand-500/10 text-brand-400 border border-brand-500/20"><?= e($role['name']) ?></span>
                    <?php endforeach; ?>
                    <?php if ($user['is_active']): ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Active</span>
                    <?php else: ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-500/10 text-red-400 border border-red-500/20">Inactive</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Personal Information -->
        <div class="lg:col-span-2">
            <form method="POST" action="<?= url('profile') ?>" class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 backdrop-blur shadow-sm space-y-5">
                <?= \Core\View::csrf() ?>
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Personal Information
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Full Name <span class="text-red-400">*</span></label>
                        <input type="text" name="name" value="<?= e($user['name']) ?>" required
                               class="w-full bg-slate-900 border border-slate-800 text-slate-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 transition-all">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Email Address <span class="text-red-400">*</span></label>
                        <input type="email" name="email" value="<?= e($user['email']) ?>" required
                               class="w-full bg-slate-900 border border-slate-800 text-slate-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 transition-all">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Phone Number</label>
                        <input type="text" name="phone" value="<?= e($user['phone']) ?>"
                               class="w-full bg-slate-900 border border-slate-800 text-slate-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 transition-all">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Designation</label>
                        <input type="text" name="designation" value="<?= e($user['designation']) ?>"
                               class="w-full bg-slate-900 border border-slate-800 text-slate-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 transition-all">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Gender</label>
                        <select name="gender" class="w-full bg-slate-900 border border-slate-800 text-slate-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 transition-all">
                            <option value="">Select Gender</option>
                            <option value="male" <?= $user['gender'] === 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= $user['gender'] === 'female' ? 'selected' : '' ?>>Female</option>
                            <option value="other" <?= $user['gender'] === 'other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Date of Birth</label>
                        <input type="date" name="dob" value="<?= e($user['dob']) ?>"
                               class="w-full bg-slate-900 border border-slate-800 text-slate-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 transition-all">
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition-all shadow-lg hover:opacity-90 bg-gradient-to-r from-indigo-500 to-purple-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- Sidebar Info -->
        <div class="space-y-6">

            <!-- Account Details -->
            <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 backdrop-blur shadow-sm">
                <h3 class="text-sm font-bold text-white flex items-center gap-2 mb-4">
                    <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Account Details
                </h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-500">User ID</span>
                        <span class="text-xs text-slate-300 font-mono">#<?= $user['id'] ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-500">Employee ID</span>
                        <span class="text-xs text-slate-300 font-mono"><?= e($user['employee_id'] ?: 'N/A') ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-500">Last Login</span>
                        <span class="text-xs text-slate-300"><?= $lastLogin ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-500">Login IP</span>
                        <span class="text-xs text-slate-300 font-mono"><?= e($lastLoginIp) ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-500">Member Since</span>
                        <span class="text-xs text-slate-300"><?= date('M d, Y', strtotime($user['created_at'])) ?></span>
                    </div>
                </div>
            </div>

            <!-- Change Password -->
            <form method="POST" action="<?= url('profile/password') ?>" class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 backdrop-blur shadow-sm space-y-4">
                <?= \Core\View::csrf() ?>
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Change Password
                </h3>

                <div class="space-y-3">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Current Password <span class="text-red-400">*</span></label>
                        <input type="password" name="current_password" required
                               class="w-full bg-slate-900 border border-slate-800 text-slate-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 transition-all"
                               placeholder="Enter current password">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">New Password <span class="text-red-400">*</span></label>
                        <input type="password" name="new_password" required
                               class="w-full bg-slate-900 border border-slate-800 text-slate-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 transition-all"
                               placeholder="Enter new password">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Confirm New Password <span class="text-red-400">*</span></label>
                        <input type="password" name="confirm_password" required
                               class="w-full bg-slate-900 border border-slate-800 text-slate-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 transition-all"
                               placeholder="Re-enter new password">
                    </div>
                </div>

                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition-all shadow-lg hover:opacity-90 bg-gradient-to-r from-amber-500 to-orange-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Update Password
                </button>
            </form>

        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
