<?php
$layout    = 'app';
$pageTitle = 'System Settings';
$breadcrumbs = [['label'=>'Dashboard','url'=>'/dashboard'],['label'=>'Settings','url'=>'/settings'],['label'=>'System']];
ob_start();
?>

<div class="max-w-4xl mx-auto space-y-5">
    <div>
        <h2 class="text-xl font-bold text-white">System Configuration Parameters</h2>
        <p class="text-sm text-slate-500 mt-0.5">Control academic layouts, portal permissions, HR rules, transport delay threshold, and security policies</p>
    </div>

    <form method="POST" action="<?= url('settings/update') ?>" x-data="{ loading: false }" @submit="loading = true">
        <?= \Core\View::csrf() ?>
        <input type="hidden" name="redirect_tab" value="/settings/system">

        <!-- Academic & Portal Permission Rules -->
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 space-y-4 mb-5">
            <h3 class="text-sm font-semibold text-slate-300 border-b border-slate-800 pb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                Academic & Portal Settings
            </h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Grading Scale Format</label>
                    <select name="academic_grading_scale" class="w-full bg-slate-900/70 border border-slate-700/60 text-slate-300 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 transition-all">
                        <option value="percentage" <?= ($systemSettings['academic_grading_scale'] ?? 'percentage') === 'percentage' ? 'selected' : '' ?>>Percentage & Grade (A-F)</option>
                        <option value="gpa" <?= ($systemSettings['academic_grading_scale'] ?? 'percentage') === 'gpa' ? 'selected' : '' ?>>GPA Scale (4.0 Max)</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Default Report Card Layout Template</label>
                    <select name="academic_report_template" class="w-full bg-slate-900/70 border border-slate-700/60 text-slate-300 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 transition-all">
                        <option value="special_needs_standard" <?= ($systemSettings['academic_report_template'] ?? 'special_needs_standard') === 'special_needs_standard' ? 'selected' : '' ?>>Special Needs Comprehensive Template</option>
                        <option value="regular_card" <?= ($systemSettings['academic_report_template'] ?? 'special_needs_standard') === 'regular_card' ? 'selected' : '' ?>>Regular Academic Report Card</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Hide Exam Results (Parent Portal Access)</label>
                    <select name="portal_hide_exams" class="w-full bg-slate-900/70 border border-slate-700/60 text-slate-350 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 transition-all">
                        <option value="1" <?= ($systemSettings['portal_hide_exams'] ?? '1') == '1' ? 'selected' : '' ?>>Yes, hide from parents until published</option>
                        <option value="0" <?= ($systemSettings['portal_hide_exams'] ?? '1') == '0' ? 'selected' : '' ?>>No, show to parents immediately</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Allow Online Payments (Parent Portal Access)</label>
                    <select name="portal_allow_payments" class="w-full bg-slate-900/70 border border-slate-700/60 text-slate-350 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 transition-all">
                        <option value="1" <?= ($systemSettings['portal_allow_payments'] ?? '0') == '1' ? 'selected' : '' ?>>Allow online payments</option>
                        <option value="0" <?= ($systemSettings['portal_allow_payments'] ?? '0') == '0' ? 'selected' : '' ?>>Disable online payments</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- HR & Leave Configurations -->
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 space-y-4 mb-5">
            <h3 class="text-sm font-semibold text-slate-300 border-b border-slate-800 pb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                HR & Leave Configurations
            </h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Default Annual Casual/Paid Leave Quota (Days)</label>
                    <input type="number" name="hr_leave_quota" value="<?= e($systemSettings['hr_leave_quota'] ?? '12') ?>"
                           class="w-full bg-slate-900/70 border border-slate-700/60 text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Default Monthly Payroll Generation Date</label>
                    <select name="hr_payroll_date" class="w-full bg-slate-900/70 border border-slate-700/60 text-slate-350 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 transition-all">
                        <option value="28" <?= ($systemSettings['hr_payroll_date'] ?? '30') == '28' ? 'selected' : '' ?>>28th of every month</option>
                        <option value="30" <?= ($systemSettings['hr_payroll_date'] ?? '30') == '30' ? 'selected' : '' ?>>30th of every month</option>
                        <option value="31" <?= ($systemSettings['hr_payroll_date'] ?? '30') == '31' ? 'selected' : '' ?>>End of every month</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Transport Configurations -->
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 space-y-4 mb-5">
            <h3 class="text-sm font-semibold text-slate-300 border-b border-slate-800 pb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                Transport Settings
            </h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Driver App GPS Tracking Refresh Interval (Seconds)</label>
                    <input type="number" name="transport_gps_interval" min="5" max="300" value="<?= e($systemSettings['transport_gps_interval'] ?? '15') ?>"
                           class="w-full bg-slate-900/70 border border-slate-700/60 text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Route Delay Alert Threshold (Minutes)</label>
                    <input type="number" name="transport_delay_threshold" min="2" max="60" value="<?= e($systemSettings['transport_delay_threshold'] ?? '10') ?>"
                           class="w-full bg-slate-900/70 border border-slate-700/60 text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                </div>
            </div>
        </div>

        <!-- Security & Sessions Policies -->
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 space-y-4 mb-5">
            <h3 class="text-sm font-semibold text-slate-300 border-b border-slate-800 pb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Security & Session Policies
            </h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">Password Complexity Policy Rules</label>
                    <select name="security_password_policy" class="w-full bg-slate-900/70 border border-slate-700/60 text-slate-305 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 transition-all">
                        <option value="low" <?= ($systemSettings['security_password_policy'] ?? 'low') === 'low' ? 'selected' : '' ?>>Low (Min 6 chars, any)</option>
                        <option value="medium" <?= ($systemSettings['security_password_policy'] ?? 'low') === 'medium' ? 'selected' : '' ?>>Medium (Min 8 chars, numbers & letters)</option>
                        <option value="high" <?= ($systemSettings['security_password_policy'] ?? 'low') === 'high' ? 'selected' : '' ?>>High (Min 8 chars, uppercase, lowercase, numbers & special chars)</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-400">User Session Inactivity Auto-Timeout (Minutes)</label>
                    <input type="number" name="security_timeout" min="5" max="480" value="<?= e($systemSettings['security_timeout'] ?? '60') ?>"
                           class="w-full bg-slate-900/70 border border-slate-700/60 text-white rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end mb-10">
            <button type="submit" :disabled="loading" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition-all shadow-lg hover:opacity-90" style="background: linear-gradient(135deg, #6366f1, #a855f7);" :class="loading?'opacity-70 cursor-not-allowed':''">
                <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                Save System Configurations
            </button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
?>
