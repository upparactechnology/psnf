<?php
$layout = 'app';
$pageTitle = 'Global Reports & Analytics';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Reports']];
ob_start();
?>

<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-white">System Reports & Analytics</h2>
            <p class="text-sm text-slate-400 mt-1">Comprehensive overview of school operations.</p>
        </div>
        <button onclick="window.print()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-sm font-medium transition-colors border border-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Export/Print
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Financial Card -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 rounded-xl bg-green-500/10 flex items-center justify-center text-green-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-slate-400">Total Revenue</h3>
                    <p class="text-2xl font-bold text-white">₹<?= number_format((float)$totalRevenue, 2) ?></p>
                </div>
            </div>
            <div class="text-xs text-slate-500 flex justify-between">
                <span>Outstanding Fees</span>
                <span class="text-red-400 font-semibold">₹<?= number_format((float)$outstandingFees, 2) ?></span>
            </div>
        </div>

        <!-- Academic Card -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-slate-400">Total Students</h3>
                    <p class="text-2xl font-bold text-white"><?= number_format($totalStudents) ?></p>
                </div>
            </div>
            <div class="text-xs text-slate-500 flex justify-between">
                <span>Active Classes</span>
                <span class="text-blue-400 font-semibold"><?= number_format($totalClasses) ?></span>
            </div>
        </div>

        <!-- Staff Card -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 rounded-xl bg-purple-500/10 flex items-center justify-center text-purple-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-slate-400">Total Staff</h3>
                    <p class="text-2xl font-bold text-white"><?= number_format($totalStaff) ?></p>
                </div>
            </div>
            <div class="text-xs text-slate-500 flex justify-between">
                <span>Active Employees</span>
                <span class="text-purple-400 font-semibold"><?= number_format($totalStaff) ?></span>
            </div>
        </div>

        <!-- Communication Card -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 rounded-xl bg-brand-500/10 flex items-center justify-center text-brand-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-slate-400">WhatsApp Msgs</h3>
                    <p class="text-2xl font-bold text-white"><?= number_format($whatsappTotal) ?></p>
                </div>
            </div>
            <div class="text-xs text-slate-500 flex justify-between">
                <span>Sent this month</span>
                <span class="text-brand-400 font-semibold"><?= number_format($whatsappSentThisMonth) ?></span>
            </div>
            <?php if ($whatsappFailed > 0): ?>
            <div class="text-xs text-red-400 flex justify-between mt-1">
                <span>Failed Deliveries</span>
                <span class="font-semibold"><?= number_format($whatsappFailed) ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Additional Report Tables/Charts can go here in the future -->
    <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-8 text-center">
        <svg class="w-12 h-12 text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
        <h3 class="text-lg font-medium text-white mb-1">Detailed Reports</h3>
        <p class="text-sm text-slate-400">Select a specific module from the sidebar for detailed PDF/CSV exports.</p>
    </div>
</div>

<?php
$content = ob_get_clean();
?>
