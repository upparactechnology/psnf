<?php
$layout    = 'app';
$pageTitle = 'Integrations & APIs';
$breadcrumbs = [['label'=>'Dashboard','url'=>'/dashboard'],['label'=>'Settings','url'=>'/settings'],['label'=>'Integrations']];
ob_start();
?>

<div class="max-w-4xl mx-auto space-y-5">
    <div>
        <h2 class="text-xl font-bold text-white">Integrations & API Settings</h2>
        <p class="text-sm text-slate-500 mt-0.5">Configure third-party services, notifications, and payment gateways</p>
    </div>

    <form method="POST" action="<?= url('settings/update') ?>" x-data="{ loading: false }" @submit="loading = true">
        <?= \Core\View::csrf() ?>
        <input type="hidden" name="redirect_tab" value="<?= url('settings/integrations') ?>">

        <!-- WhatsApp Integration -->
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 space-y-4 mb-5">
            <h3 class="text-sm font-semibold text-slate-300 border-b border-slate-800 pb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                WhatsApp API Connection Settings
            </h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">WhatsApp Notification Service Status</label>
                    <select name="whatsapp_enabled" class="w-full bg-slate-900/70 border border-slate-700/60 text-slate-350 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 transition-all">
                        <option value="1" <?= ($systemSettings['whatsapp_enabled'] ?? '1') == '1' ? 'selected' : '' ?>>Enabled</option>
                        <option value="0" <?= ($systemSettings['whatsapp_enabled'] ?? '1') == '0' ? 'selected' : '' ?>>Disabled</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">web.upparac.com Authorization API Key</label>
                    <input type="password" name="whatsapp_api_key" value="<?= e($systemSettings['whatsapp_api_key'] ?? '') ?>"
                           class="w-full bg-slate-900/70 border border-slate-700/60 text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                </div>
            </div>
        </div>
        
        <!-- Google Maps API Configuration -->
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 space-y-4 mb-5">
            <h3 class="text-sm font-semibold text-slate-300 border-b border-slate-800 pb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Google Maps API Configuration
            </h3>
            
            <div class="grid grid-cols-1 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Google Maps API Key (with Routes API Enabled)</label>
                    <input type="password" name="google_maps_api_key" value="<?= e($systemSettings['google_maps_api_key'] ?? '') ?>"
                           class="w-full bg-slate-900/70 border border-slate-700/60 text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                    <p class="text-[10px] text-slate-500 mt-1">Required for accurate ETA and Distance calculations in live tracking.</p>
                </div>
            </div>
        </div>

        <!-- SMTP Email Server Settings -->
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 space-y-4 mb-5">
            <h3 class="text-sm font-semibold text-slate-300 border-b border-slate-800 pb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                SMTP Mail Server Configurations
            </h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">SMTP Host Name</label>
                    <input type="text" name="email_host" value="<?= e($systemSettings['email_host'] ?? 'smtp.mailtrap.io') ?>"
                           class="w-full bg-slate-900/70 border border-slate-700/60 text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">SMTP Server Port</label>
                    <input type="number" name="email_port" value="<?= e($systemSettings['email_port'] ?? '2525') ?>"
                           class="w-full bg-slate-900/70 border border-slate-700/60 text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">SMTP SSL / TLS Encryption</label>
                    <select name="email_encryption" class="w-full bg-slate-900/70 border border-slate-700/60 text-slate-350 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 transition-all">
                        <option value="tls" selected>TLS</option>
                        <option value="ssl">SSL</option>
                        <option value="none">None</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">SMTP Username</label>
                    <input type="text" name="email_user" value="<?= e($systemSettings['email_user'] ?? '') ?>"
                           class="w-full bg-slate-900/70 border border-slate-700/60 text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">SMTP Password</label>
                    <input type="password" name="email_pass" value="<?= e($systemSettings['email_pass'] ?? '') ?>"
                           class="w-full bg-slate-900/70 border border-slate-700/60 text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                </div>
            </div>
        </div>

        <!-- Payment Gateways Configuration -->
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 space-y-4 mb-5">
            <h3 class="text-sm font-semibold text-slate-300 border-b border-slate-800 pb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                Online Fee Payment Gateways
            </h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Razorpay Key ID</label>
                    <input type="text" name="payment_razorpay_key" value="<?= e($systemSettings['payment_razorpay_key'] ?? '') ?>"
                           class="w-full bg-slate-900/70 border border-slate-700/60 text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Stripe Publishable Key</label>
                    <input type="text" name="payment_stripe_key" value="<?= e($systemSettings['payment_stripe_key'] ?? '') ?>"
                           class="w-full bg-slate-900/70 border border-slate-700/60 text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end mb-5">
            <button type="submit" :disabled="loading" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition-all shadow-lg hover:opacity-90" style="background: linear-gradient(135deg, #6366f1, #a855f7);" :class="loading?'opacity-70 cursor-not-allowed':''">
                <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                Save Integration Keys
            </button>
        </div>
    </form>

    <!-- Test WhatsApp Tool Section -->
    <div class="rounded-2xl border border-brand-800/60 bg-brand-900/20 p-6 space-y-4 mb-10">
        <h3 class="text-sm font-semibold text-brand-300 border-b border-brand-800/50 pb-3 flex items-center gap-2">
            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            Test WhatsApp Messenger sandbox
        </h3>
        <form method="POST" action="<?= url('settings/test-whatsapp') ?>" class="flex gap-4 items-end">
            <?= \Core\View::csrf() ?>
            <input type="hidden" name="redirect_tab" value="<?= url('settings/integrations') ?>">
            <div class="space-y-1.5 flex-1">
                <label class="block text-xs font-medium text-slate-400">Phone Number (with Country Code e.g. 919427961426)</label>
                <input type="text" name="phone" required
                       class="w-full bg-slate-900/70 border border-brand-700/60 text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-green-500 transition-all">
            </div>
            <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white bg-green-600 hover:bg-green-500 transition-all shadow-lg">
                Send Test Message
            </button>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
?>
