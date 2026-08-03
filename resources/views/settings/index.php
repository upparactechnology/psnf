<?php
$layout    = 'app';
$pageTitle = 'Settings';
$breadcrumbs = [['label'=>'Dashboard','url'=>'/dashboard'],['label'=>'Settings']];
ob_start();
?>

<div class="max-w-4xl mx-auto space-y-5">
    <div>
        <h2 class="text-xl font-bold text-white">System Settings</h2>
        <p class="text-sm text-slate-500 mt-0.5">Manage organization details and school parameters</p>
    </div>

    <form method="POST" action="<?= url('settings') ?>" enctype="multipart/form-data" x-data="{ loading: false }" @submit="loading = true">
        <?= \Core\View::csrf() ?>

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

        <!-- School Settings -->
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 space-y-4 mb-5">
            <h3 class="text-sm font-semibold text-slate-300 border-b border-slate-800 pb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                School Settings
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">School Name <span class="text-red-400">*</span></label>
                    <input type="text" name="school_name" value="<?= e($school['name'] ?? '') ?>" required
                           class="w-full bg-slate-900/70 border border-slate-700/60 text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">School Code</label>
                    <input type="text" value="<?= e($school['code'] ?? '') ?>" readonly disabled
                           class="w-full bg-slate-900/50 border border-slate-800 text-slate-500 rounded-xl py-2.5 px-4 text-sm cursor-not-allowed">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">School Email</label>
                    <input type="email" name="school_email" value="<?= e($school['email'] ?? '') ?>"
                           class="w-full bg-slate-900/70 border border-slate-700/60 text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">School Phone</label>
                    <input type="text" name="school_phone" value="<?= e($school['phone'] ?? '') ?>"
                           class="w-full bg-slate-900/70 border border-slate-700/60 text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-medium text-slate-400">School Address</label>
                <textarea name="school_address" rows="3"
                          class="w-full bg-slate-900/70 border border-slate-700/60 text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all"><?= e($school['address'] ?? '') ?></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">City</label>
                    <input type="text" name="school_city" value="<?= e($school['city'] ?? '') ?>"
                           class="w-full bg-slate-900/70 border border-slate-700/60 text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">State</label>
                    <input type="text" name="school_state" value="<?= e($school['state'] ?? '') ?>"
                           class="w-full bg-slate-900/70 border border-slate-700/60 text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Pincode</label>
                    <input type="text" name="school_pincode" value="<?= e($school['pincode'] ?? '') ?>"
                           class="w-full bg-slate-900/70 border border-slate-700/60 text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Established Year</label>
                    <input type="number" name="school_established_year" min="1900" max="2100" value="<?= e($school['established_year'] ?? '') ?>"
                           class="w-full bg-slate-900/70 border border-slate-700/60 text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">School Type</label>
                    <select name="school_type"
                            class="w-full bg-slate-900/70 border border-slate-700/60 text-slate-300 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 transition-all">
                        <option value="special_needs" <?= ($school['type'] ?? '') === 'special_needs' ? 'selected' : '' ?>>Special Needs School</option>
                        <option value="regular" <?= ($school['type'] ?? '') === 'regular' ? 'selected' : '' ?>>Regular School</option>
                        <option value="both" <?= ($school['type'] ?? '') === 'both' ? 'selected' : '' ?>>Both</option>
                    </select>
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
                           class="w-full bg-slate-900/70 border border-slate-700/60 text-slate-450 placeholder-slate-500 rounded-xl py-2 px-4 text-sm focus:outline-none file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-600/20 file:text-brand-400 hover:file:bg-brand-600/30 cursor-pointer">
                </div>
                <div class="space-y-1.5 col-span-2 sm:col-span-1">
                    <label class="block text-xs font-medium text-slate-400">Accent Color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="receipt_accent_color" value="<?= e($receipt['accent_color'] ?? '#6366f1') ?>"
                               class="w-12 h-10 bg-slate-900/70 border border-slate-700/60 rounded-xl p-1 cursor-pointer">
                        <span class="text-xs text-slate-450">Choose a primary brand color for the receipt highlights</span>
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

        <!-- Global System Settings -->
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 space-y-4 mb-5 mt-5">
            <h3 class="text-sm font-semibold text-slate-300 border-b border-slate-800 pb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Communication & Integration (WhatsApp)
            </h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">WhatsApp Enable/Disable</label>
                    <select name="whatsapp_enabled" class="w-full bg-slate-900/70 border border-slate-700/60 text-slate-350 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 transition-all">
                        <option value="1" <?= ($systemSettings['whatsapp_enabled'] ?? '1') == '1' ? 'selected' : '' ?>>Enabled</option>
                        <option value="0" <?= ($systemSettings['whatsapp_enabled'] ?? '1') == '0' ? 'selected' : '' ?>>Disabled</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">web.upparac.com API Key</label>
                    <input type="password" name="whatsapp_api_key" value="<?= e($systemSettings['whatsapp_api_key'] ?? '') ?>"
                           class="w-full bg-slate-900/70 border border-slate-700/60 text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                </div>
            </div>

            <h3 class="text-sm font-semibold text-slate-300 border-b border-slate-800 pb-3 pt-4">Academic & Portal Settings</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Hide Exam Results (Portal)</label>
                    <select name="portal_hide_exams" class="w-full bg-slate-900/70 border border-slate-700/60 text-slate-350 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 transition-all">
                        <option value="1" <?= ($systemSettings['portal_hide_exams'] ?? '1') == '1' ? 'selected' : '' ?>>Yes, hide until published</option>
                        <option value="0" <?= ($systemSettings['portal_hide_exams'] ?? '1') == '0' ? 'selected' : '' ?>>No, show immediately</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Allow Online Payments</label>
                    <select name="portal_allow_payments" class="w-full bg-slate-900/70 border border-slate-700/60 text-slate-350 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 transition-all">
                        <option value="1" <?= ($systemSettings['portal_allow_payments'] ?? '0') == '1' ? 'selected' : '' ?>>Enabled</option>
                        <option value="0" <?= ($systemSettings['portal_allow_payments'] ?? '0') == '0' ? 'selected' : '' ?>>Disabled</option>
                    </select>
                </div>
            </div>
            
            <h3 class="text-sm font-semibold text-slate-300 border-b border-slate-800 pb-3 pt-4">Payment Gateways</h3>
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

        <div class="flex items-center justify-end mb-10">
            <button type="submit" :disabled="loading" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition-all shadow-lg hover:opacity-90" style="background: linear-gradient(135deg, #6366f1, #a855f7);" :class="loading?'opacity-70 cursor-not-allowed':''">
                <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                Save All Settings
            </button>
        </div>
    </form>
    
    <!-- Test WhatsApp Tool -->
    <div class="rounded-2xl border border-brand-800/60 bg-brand-900/20 p-6 space-y-4 mb-5">
        <h3 class="text-sm font-semibold text-brand-300 border-b border-brand-800/50 pb-3 flex items-center gap-2">
            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            Test WhatsApp Integration
        </h3>
        <form method="POST" action="<?= url('settings/test-whatsapp') ?>" class="flex gap-4 items-end">
            <?= \Core\View::csrf() ?>
            <div class="space-y-1.5 flex-1">
                <label class="block text-xs font-medium text-slate-400">Phone Number (with Country Code e.g. 919876543210)</label>
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
