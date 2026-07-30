<?php $layout = 'auth'; ?>

<div class="space-y-5">
    <div class="text-center mb-6">
        <h2 class="text-xl font-bold text-slate-900 dark:text-white">Parent Portal</h2>
        <p class="text-slate-500 text-sm mt-1">Sign in to view your child's academic progress</p>
    </div>

    <?php if ($err = flash('error')): ?>
    <div class="flex items-start gap-3 bg-red-50 dark:bg-red-950/60 border border-red-200 dark:border-red-800/40 text-red-600 dark:text-red-300 px-4 py-3 rounded-xl text-sm">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span><?= e($err) ?></span>
    </div>
    <?php endif; ?>

    <?php if ($msg = flash('success')): ?>
    <div class="flex items-start gap-3 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800/40 text-emerald-600 dark:text-emerald-300 px-4 py-3 rounded-xl text-sm">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span><?= e($msg) ?></span>
    </div>
    <?php endif; ?>

    <div class="bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/30 text-indigo-700 dark:text-indigo-300 px-4 py-3 rounded-xl text-sm text-center">
        <strong>First time login?</strong><br>
        Use your <strong>Phone Number</strong> as both your Username and Password. You will be asked to set a new password afterwards.
    </div>

    <form method="POST" action="<?= url('parent-login') ?>" class="space-y-5 mt-6">
        <?= \Core\View::csrf() ?>
        
        <div>
            <label for="phone" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Phone Number</label>
            <input id="phone" name="phone" type="text" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors" placeholder="e.g., 9876543210" value="<?= e(flash('old')['phone'] ?? '') ?>">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Password</label>
            <input id="password" name="password" type="password" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors">
        </div>

        <button type="submit" class="w-full py-3 px-4 bg-brand-600 hover:bg-brand-700 text-white font-medium rounded-xl shadow-sm transition-colors mt-2">
            Sign In to Portal
        </button>
        
        <div class="text-center mt-6">
            <a href="<?= url('login') ?>" class="text-sm text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors">Staff Login</a>
        </div>
    </form>
</div>
