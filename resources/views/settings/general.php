<?php
$layout    = 'app';
$pageTitle = 'General Settings';
$breadcrumbs = [['label'=>'Dashboard','url'=>'/dashboard'],['label'=>'Settings','url'=>'/settings'],['label'=>'General']];
ob_start();
?>

<div class="max-w-4xl mx-auto space-y-5">
    <div>
        <h2 class="text-xl font-bold text-white">General & Branding Settings</h2>
        <p class="text-sm text-slate-500 mt-0.5">Manage organization details and receipt customizations</p>
    </div>

    <form method="POST" action="<?= url('settings/update') ?>" enctype="multipart/form-data" x-data="{ loading: false }" @submit="loading = true">
        <?= \Core\View::csrf() ?>
        <input type="hidden" name="redirect_tab" value="/settings/general">

        <!-- Organization / Tenant Details -->
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 space-y-4 mb-5">
            <h3 class="text-sm font-semibold text-slate-300 border-b border-slate-800 pb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Organization Details
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Organization Name <span class="text-red-400">*</span></label>
                    <input type="text" name="tenant_name" value="<?= e($tenant['name'] ?? '') ?>" required
                           class="w-full bg-slate-900/70 border border-slate-700/60 text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Email Address</label>
                    <input type="email" name="tenant_email" value="<?= e($tenant['email'] ?? '') ?>"
                           class="w-full bg-slate-900/70 border border-slate-700/60 text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Phone</label>
                    <input type="text" name="tenant_phone" value="<?= e($tenant['phone'] ?? '') ?>"
                           class="w-full bg-slate-900/70 border border-slate-700/60 text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Country</label>
                    <input type="text" name="tenant_country" value="<?= e($tenant['country'] ?? '') ?>"
                           class="w-full bg-slate-900/70 border border-slate-700/60 text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Timezone</label>
                    <input type="text" name="tenant_timezone" value="<?= e($tenant['timezone'] ?? '') ?>"
                           class="w-full bg-slate-900/70 border border-slate-700/60 text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                </div>
            </div>
        </div>

        <!-- Receipt Design Settings -->
        <div id="receipt-settings" class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 space-y-4 mb-5">
            <h3 class="text-sm font-semibold text-slate-300 border-b border-slate-800 pb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Receipt Template Customization
            </h3>

            <?php
            $tSettings = json_decode($tenant['settings'] ?? '{}', true) ?: [];
            $receipt = $tSettings['receipt'] ?? [];
            ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5 col-span-2 sm:col-span-1">
                    <label class="block text-xs font-medium text-slate-400">Receipt Logo</label>
                    <?php if (!empty($tenant['logo'])): ?>
                    <div class="flex items-center gap-3 mb-2">
                        <img src="<?= url('storage/uploads/logo/' . $tenant['logo']) ?>" class="w-12 h-12 object-contain rounded bg-slate-850 border border-slate-700/60" alt="Logo">
                        <span class="text-xs text-slate-500">Current logo</span>
                    </div>
                    <?php endif; ?>
                    <input type="file" name="receipt_logo" accept=".png,.jpg,.jpeg"
                           class="w-full bg-slate-900/70 border border-slate-700/60 text-slate-455 placeholder-slate-500 rounded-xl py-2 px-4 text-sm focus:outline-none file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-600/20 file:text-brand-400 hover:file:bg-brand-600/30 cursor-pointer">
                </div>
                <div class="space-y-1.5 col-span-2 sm:col-span-1">
                    <label class="block text-xs font-medium text-slate-400">Accent Color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="receipt_accent_color" value="<?= e($receipt['accent_color'] ?? '#6366f1') ?>"
                               class="w-12 h-10 bg-slate-900/70 border border-slate-700/60 rounded-xl p-1 cursor-pointer">
                        <span class="text-xs text-slate-450">Choose primary brand color for highlights</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Receipt Header Title</label>
                    <input type="text" name="receipt_header_title" value="<?= e($receipt['header_title'] ?? 'Official Fee Payment Receipt') ?>"
                           class="w-full bg-slate-900/70 border border-slate-700/60 text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all" placeholder="e.g. Official Fee Payment Receipt">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Show Watermark ("Paid" Stamp)</label>
                    <select name="receipt_show_watermark"
                            class="w-full bg-slate-900/70 border border-slate-700/60 text-slate-350 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 transition-all">
                        <option value="1" <?= ($receipt['show_watermark'] ?? '1') == '1' ? 'selected' : '' ?>>Yes, show stamp</option>
                        <option value="0" <?= ($receipt['show_watermark'] ?? '1') == '0' ? 'selected' : '' ?>>No, hide stamp</option>
                    </select>
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-medium text-slate-400">Receipt Footer Notes</label>
                <textarea name="receipt_footer_notes" rows="2" placeholder="e.g. This receipt is automatically generated..."
                          class="w-full bg-slate-900/70 border border-slate-700/60 text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all"><?= e($receipt['footer_notes'] ?? 'This receipt is automatically generated and serves as official proof of payment.') ?></textarea>
            </div>
        </div>

        <div class="flex items-center justify-end mb-10">
            <button type="submit" :disabled="loading" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition-all shadow-lg hover:opacity-90" style="background: linear-gradient(135deg, #6366f1, #a855f7);" :class="loading?'opacity-70 cursor-not-allowed':''">
                <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                Save General Settings
            </button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
?>
