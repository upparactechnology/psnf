<?php
$layout = 'auth';
$errors = flash('errors') ?? [];
$user = \Core\Session::get('user');
?>

<div class="space-y-5">
    <div class="text-center mb-6">
        <h2 class="text-xl font-bold text-slate-900 dark:text-white">Welcome to the Parent Portal!</h2>
        <p class="text-slate-500 text-sm mt-1">Since this is your first time logging in, please confirm your name and choose a secure password.</p>
    </div>

    <?php if ($err = flash('error')): ?>
    <div class="flex items-start gap-3 bg-red-50 dark:bg-red-950/60 border border-red-200 dark:border-red-800/40 text-red-600 dark:text-red-300 px-4 py-3 rounded-xl text-sm">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span><?= e($err) ?></span>
    </div>
    <?php endif; ?>

    <?php if ($info = flash('info')): ?>
    <div class="flex items-start gap-3 bg-brand-50 dark:bg-brand-900/20 border border-brand-200 dark:border-brand-800/40 text-brand-700 dark:text-brand-300 px-4 py-3 rounded-xl text-sm">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span><?= e($info) ?></span>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?= url('parent/change-password') ?>" class="space-y-5 mt-6">
        <?= \Core\View::csrf() ?>
        
        <div>
            <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Your Full Name</label>
            <input id="name" name="name" type="text" required class="w-full px-4 py-2.5 rounded-xl border <?= isset($errors['name']) ? 'border-red-500 focus:ring-red-500' : 'border-slate-300 dark:border-slate-700 focus:ring-brand-500' ?> bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:outline-none transition-colors" value="<?= e($user['name'] ?? '') ?>">
            <?php if (isset($errors['name'])): ?>
                <p class="mt-1 text-xs text-red-500"><?= e($errors['name']) ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">New Password</label>
            <input id="password" name="password" type="password" required class="w-full px-4 py-2.5 rounded-xl border <?= isset($errors['password']) ? 'border-red-500 focus:ring-red-500' : 'border-slate-300 dark:border-slate-700 focus:ring-brand-500' ?> bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:outline-none transition-colors">
            <?php if (isset($errors['password'])): ?>
                <p class="mt-1 text-xs text-red-500"><?= e($errors['password']) ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Confirm New Password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 focus:outline-none transition-colors">
        </div>

        <button type="submit" class="w-full py-3 px-4 bg-brand-600 hover:bg-brand-700 text-white font-medium rounded-xl shadow-sm transition-colors mt-4">
            Save and Continue
        </button>
    </form>
</div>
