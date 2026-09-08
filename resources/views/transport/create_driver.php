<?php
$layout    = 'app';
$pageTitle = 'Add Driver User';
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Transport', 'url' => '/transport'],
    ['label' => 'New Driver User']
];
ob_start();
$errors = \Core\Session::getFlash('errors') ?? [];
$old    = \Core\Session::getFlash('old') ?? [];
$fn     = fn($k) => $old[$k] ?? '';
function iClass(string $f, array $e=[]): string { 
    $b = 'w-full bg-slate-900/70 border text-white placeholder-slate-500 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-1 transition-all'; 
    return $b . (isset($e[$f]) ? ' border-red-600/60 focus:border-red-500 focus:ring-red-500/20' : ' border-slate-700/60 focus:border-brand-500 focus:ring-brand-500/30'); 
}
function eMsg(string $f, array $e): string { 
    if(!isset($e[$f])) return ''; 
    return '<p class="text-xs text-red-400 mt-1">' . htmlspecialchars($e[$f]) . '</p>'; 
}
?>

<div class="max-w-2xl mx-auto space-y-5">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-white">Create New Driver User</h2>
            <p class="text-sm text-slate-500 mt-0.5">Add a driver user account to the system</p>
        </div>
        <a href="<?= url('transport') ?>" class="text-sm text-slate-400 hover:text-slate-300 transition-colors">← Back</a>
    </div>

    <form method="POST" action="<?= url('transport/driver') ?>" x-data="{ loading: false, showPass: false }" @submit="loading = true">
        <?= \Core\View::csrf() ?>

        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 space-y-5 mb-5">
            <h3 class="text-sm font-semibold text-slate-300 border-b border-slate-800 pb-3">Driver Account Details</h3>

            <!-- Full Name -->
            <div class="space-y-1.5">
                <label class="block text-xs font-medium text-slate-400">Full Name <span class="text-red-400">*</span></label>
                <input type="text" name="name" value="<?= e($fn('name')) ?>" required class="<?= iClass('name',$errors) ?>" placeholder="Full name">
                <?= eMsg('name',$errors) ?>
            </div>

            <!-- Email / Username -->
            <div class="space-y-1.5">
                <label class="block text-xs font-medium text-slate-400">Email Address / Username <span class="text-red-400">*</span></label>
                <input type="email" name="email" value="<?= e($fn('email')) ?>" required class="<?= iClass('email',$errors) ?>" placeholder="driver@psnf.edu">
                <p class="text-3xs text-slate-500 mt-1">This email will be used as the login username for the driver app.</p>
                <?= eMsg('email',$errors) ?>
            </div>

            <!-- Phone Number -->
            <div class="space-y-1.5">
                <label class="block text-xs font-medium text-slate-400">Phone Number <span class="text-red-400">*</span></label>
                <input type="text" name="phone" value="<?= e($fn('phone')) ?>" required class="<?= iClass('phone',$errors) ?>" placeholder="+91 91234 56789">
                <?= eMsg('phone',$errors) ?>
            </div>

            <!-- License Number -->
            <div class="space-y-1.5">
                <label class="block text-xs font-medium text-slate-400">License Number</label>
                <input type="text" name="license_number" value="<?= e($fn('license_number')) ?>" class="<?= iClass('license_number',$errors) ?>" placeholder="DL-142011XXXXX">
                <?= eMsg('license_number',$errors) ?>
            </div>

            <!-- Password -->
            <div class="space-y-1.5">
                <label class="block text-xs font-medium text-slate-400">Password <span class="text-red-400">*</span></label>
                <div class="relative">
                    <input :type="showPass ? 'text' : 'password'" name="password" required minlength="8" class="<?= iClass('password',$errors) ?> pr-10" placeholder="Minimum 8 characters">
                    <button type="button" @click="showPass=!showPass" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-500 hover:text-slate-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
                <?= eMsg('password',$errors) ?>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-between">
            <a href="<?= url('transport') ?>" class="px-5 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:text-white border border-slate-700/50 hover:border-slate-600 transition-all">Cancel</a>
            <button type="submit" :disabled="loading" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition-all shadow-lg hover:opacity-90" style="background: linear-gradient(135deg, #6366f1, #a855f7);" :class="loading?'opacity-70 cursor-not-allowed':''">
                <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                Create Driver User
            </button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
?>
