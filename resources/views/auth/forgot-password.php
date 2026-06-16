<?php $layout = 'auth'; ?>

<div class="space-y-5">
    <div class="text-center mb-6">
        <h2 class="text-xl font-bold text-white">Forgot Password?</h2>
        <p class="text-slate-500 text-sm mt-1">Enter your email and we'll send a reset link</p>
    </div>

    <?php $success = \Core\Session::getFlash('success'); $error = \Core\Session::getFlash('error'); ?>
    <?php if ($success): ?>
    <div class="flex items-start gap-3 bg-emerald-950/60 border border-emerald-800/40 text-emerald-300 px-4 py-3 rounded-xl text-sm">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span><?= e($success) ?></span>
    </div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="flex items-start gap-3 bg-red-950/60 border border-red-800/40 text-red-300 px-4 py-3 rounded-xl text-sm">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span><?= e($error) ?></span>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?= url('forgot-password') ?>" x-data="{ loading: false }" @submit="loading = true" class="space-y-5">
        <?= \Core\View::csrf() ?>
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-slate-300">Email Address</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                </div>
                <input type="email" name="email" class="w-full bg-slate-900/70 border border-slate-700/60 text-white placeholder-slate-500 rounded-xl py-3 pl-10 pr-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all" placeholder="you@example.com" required>
            </div>
        </div>
        <button type="submit" :disabled="loading" class="w-full py-3 px-6 rounded-xl text-sm font-semibold text-white transition-all duration-200 shadow-lg" style="background: linear-gradient(135deg, #6366f1, #a855f7);" :class="loading ? 'opacity-70 cursor-not-allowed' : 'hover:opacity-90'">
            <span x-text="loading ? 'Sending...' : 'Send Reset Link'">Send Reset Link</span>
        </button>
    </form>

    <p class="text-center text-sm text-slate-500">
        Remember it? <a href="<?= url('login') ?>" class="text-brand-400 hover:text-brand-300 font-medium">Back to Login</a>
    </p>
</div>
