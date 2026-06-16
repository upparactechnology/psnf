<?php $layout = 'auth'; ?>

<div x-data="loginForm()" class="space-y-5">
    <div class="text-center mb-6">
        <h2 class="text-xl font-bold text-white">Welcome back</h2>
        <p class="text-slate-500 text-sm mt-1">Sign in to your account</p>
    </div>

    <?php $error = \Core\Session::getFlash('error'); ?>
    <?php if ($error): ?>
    <div class="flex items-start gap-3 bg-red-950/60 border border-red-800/40 text-red-300 px-4 py-3 rounded-xl text-sm">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span><?= e($error) ?></span>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?= url('login') ?>" @submit="loading = true" class="space-y-5">
        <?= \Core\View::csrf() ?>

        <!-- Email -->
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-slate-300" for="login_email">Email Address</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                </div>
                <input type="email" id="login_email" name="email"
                       value="<?= e(\Core\View::old('email')) ?>"
                       class="w-full bg-slate-900/70 border border-slate-700/60 text-white placeholder-slate-500 rounded-xl py-3 pl-10 pr-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all"
                       placeholder="admin@psnf.edu" required autofocus>
            </div>
        </div>

        <!-- Password -->
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-slate-300" for="login_password">Password</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <input type="password" :type="showPass ? 'text' : 'password'"
                       id="login_password" name="password"
                       class="w-full bg-slate-900/70 border border-slate-700/60 text-white placeholder-slate-500 rounded-xl py-3 pl-10 pr-12 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all"
                       placeholder="••••••••" required>
                <button type="button" @click="showPass = !showPass"
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300 transition-colors">
                    <svg x-show="!showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg x-show="showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                </button>
            </div>
        </div>

        <!-- Remember + Forgot -->
        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="remember_me" id="remember_me"
                       class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-brand-500 focus:ring-brand-500/30">
                <span class="text-sm text-slate-400">Remember me</span>
            </label>
            <a href="<?= url('forgot-password') ?>" class="text-sm text-brand-400 hover:text-brand-300 transition-colors">Forgot password?</a>
        </div>

        <!-- Submit -->
        <button type="submit" id="login_btn"
                class="w-full flex items-center justify-center gap-2 py-3 px-6 rounded-xl text-sm font-semibold text-white transition-all duration-200 shadow-lg"
                style="background: linear-gradient(135deg, #6366f1, #a855f7);"
                :disabled="loading"
                :class="loading ? 'opacity-70 cursor-not-allowed' : 'hover:opacity-90 hover:shadow-indigo-500/25'">
            <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            <span x-text="loading ? 'Signing in...' : 'Sign In'">Sign In</span>
        </button>
    </form>
</div>

<script>
function loginForm() {
    return { loading: false, showPass: false }
}
</script>
