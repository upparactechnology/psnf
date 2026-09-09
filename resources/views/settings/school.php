<?php
$layout    = 'app';
$pageTitle = 'School Settings';
$breadcrumbs = [['label'=>'Dashboard','url'=>'/dashboard'],['label'=>'Settings','url'=>'/settings'],['label'=>'School']];
ob_start();
?>

<div class="max-w-4xl mx-auto space-y-5">
    <div>
        <h2 class="text-xl font-bold text-white">School Parameters</h2>
        <p class="text-sm text-slate-500 mt-0.5">Manage branch info, school categorization, and contact details</p>
    </div>

    <form method="POST" action="<?= url('settings/update') ?>" x-data="{ loading: false }" @submit="loading = true">
        <?= \Core\View::csrf() ?>
        <input type="hidden" name="redirect_tab" value="<?= url('settings/school') ?>">

        <!-- School Settings -->
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 space-y-4 mb-5">
            <h3 class="text-sm font-semibold text-slate-300 border-b border-slate-800 pb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                School Information
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

            <!-- Additional Custom System Parameters -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-3 border-t border-slate-800/40">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Established Year</label>
                    <input type="number" name="school_established_year" min="1900" max="2100" value="<?= e($school['established_year'] ?? '') ?>"
                           class="w-full bg-slate-900/70 border border-slate-700/60 text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">School Category Type</label>
                    <select name="school_type"
                            class="w-full bg-slate-900/70 border border-slate-700/60 text-slate-300 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 transition-all">
                        <option value="special_needs" <?= ($school['type'] ?? '') === 'special_needs' ? 'selected' : '' ?>>Special Needs Foundation</option>
                        <option value="regular" <?= ($school['type'] ?? '') === 'regular' ? 'selected' : '' ?>>Regular School</option>
                        <option value="both" <?= ($school['type'] ?? '') === 'both' ? 'selected' : '' ?>>Inclusive Academy (Both)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-3 border-t border-slate-800/40">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">School Website URL</label>
                    <input type="url" name="school_website" placeholder="https://example.com" value="https://pearlspecialneeds.org"
                           class="w-full bg-slate-900/70 border border-slate-700/60 text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Principal Name</label>
                    <input type="text" name="school_principal" placeholder="Dr. Principal Name" value="Mrs. Anita Shah"
                           class="w-full bg-slate-900/70 border border-slate-700/60 text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Affiliation Registration Code</label>
                    <input type="text" name="school_affiliation" placeholder="REG-87429" value="REG-87429"
                           class="w-full bg-slate-900/70 border border-slate-700/60 text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end mb-10">
            <button type="submit" :disabled="loading" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition-all shadow-lg hover:opacity-90" style="background: linear-gradient(135deg, #6366f1, #a855f7);" :class="loading?'opacity-70 cursor-not-allowed':''">
                <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                Save School Info
            </button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
?>
