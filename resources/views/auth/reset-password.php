<?php $layout = 'auth'; ?>
<div class="space-y-5">
    <div class="text-center mb-6">
        <h2 class="text-xl font-bold text-white">Reset Password</h2>
        <p class="text-slate-500 text-sm mt-1">Enter your new password below</p>
    </div>
    <?php $error = \Core\Session::getFlash('error'); ?>
    <?php if ($error): ?>
    <div class="flex items-start gap-3 bg-red-950/60 border border-red-800/40 text-red-300 px-4 py-3 rounded-xl text-sm">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span><?= e($error) ?></span>
    </div>
    <?php endif; ?>
    <form method="POST" action="<?= url('reset-password') ?>" x-data="{ loading: false }" @submit="loading = true" class="space-y-5">
        <?= \Core\View::csrf() ?>
        <input type="hidden" name="token" value="<?= e($token ?? '') ?>">
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-slate-300">New Password</label>
            <input type="password" name="password" minlength="8" class="w-full bg-slate-900/70 border border-slate-700/60 text-white placeholder-slate-500 rounded-xl py-3 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all" placeholder="Minimum 8 characters" required>
        </div>
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-slate-300">Confirm Password</label>
            <input type="password" name="password_confirmation" minlength="8" class="w-full bg-slate-900/70 border border-slate-700/60 text-white placeholder-slate-500 rounded-xl py-3 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all" placeholder="Repeat password" required>
        </div>
        <button type="submit" :disabled="loading" class="w-full py-3 px-6 rounded-xl text-sm font-semibold text-white transition-all" style="background: linear-gradient(135deg, #6366f1, #a855f7);" :class="loading ? 'opacity-70 cursor-not-allowed' : 'hover:opacity-90'">
            <span x-text="loading ? 'Resetting...' : 'Reset Password'">Reset Password</span>
        </button>
    </form>
</div>
