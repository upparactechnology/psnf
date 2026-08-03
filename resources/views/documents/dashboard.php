<?php
$layout    = 'app';
$pageTitle = 'Documents Workspace';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Documents Workspace']];
ob_start();
?>

<div class="space-y-8 max-w-6xl mx-auto py-2">

    <!-- Greeting / Header Banner -->
    <div class="p-6 md:p-8 rounded-3xl border border-slate-200 dark:border-slate-800/80 bg-white dark:bg-slate-900/40 shadow-sm relative overflow-hidden">
        <div class="absolute right-0 top-0 w-64 h-64 bg-gradient-to-br from-indigo-500/10 to-purple-600/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span>📁 Centralized Document Repository</span>
                </h1>
                <p class="text-slate-500 dark:text-slate-400 mt-2 text-sm max-w-2xl leading-relaxed">
                    Search, preview, print, verify, and audit every academic, employee, and system-generated document across the school ERP from one unified workspace.
                </p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <a href="<?= url('documents/student-documents') ?>" 
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl text-xs font-bold text-white transition-all shadow-md hover:shadow-lg bg-gradient-to-r from-indigo-600 to-purple-600 hover:opacity-95">
                    Search Registry
                </a>
            </div>
        </div>
    </div>

    <!-- KPI Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
        <!-- 1. Total Documents -->
        <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 shadow-sm">
            <span class="text-[10px] font-bold text-slate-450 uppercase tracking-wider block">Total Documents</span>
            <div class="flex items-baseline gap-1 mt-2">
                <span class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= number_format($totalDocuments ?? 8254) ?></span>
            </div>
            <div class="h-1.5 w-full bg-slate-100 dark:bg-slate-800 rounded-full mt-3 overflow-hidden">
                <div class="h-full bg-indigo-600 rounded-full" style="width: 75%"></div>
            </div>
        </div>

        <!-- 2. Student Documents -->
        <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 shadow-sm">
            <span class="text-[10px] font-bold text-slate-450 uppercase tracking-wider block">Student Docs</span>
            <div class="flex items-baseline gap-1 mt-2">
                <span class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= number_format($studentDocsCount ?? 4212) ?></span>
            </div>
            <div class="h-1.5 w-full bg-slate-100 dark:bg-slate-800 rounded-full mt-3 overflow-hidden">
                <div class="h-full bg-blue-500 rounded-full" style="width: 60%"></div>
            </div>
        </div>

        <!-- 3. Certificates -->
        <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 shadow-sm">
            <span class="text-[10px] font-bold text-slate-450 uppercase tracking-wider block">Certificates</span>
            <div class="flex items-baseline gap-1 mt-2">
                <span class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= number_format($generatedCertsCount ?? 1384) ?></span>
            </div>
            <div class="h-1.5 w-full bg-slate-100 dark:bg-slate-800 rounded-full mt-3 overflow-hidden">
                <div class="h-full bg-purple-500 rounded-full" style="width: 45%"></div>
            </div>
        </div>

        <!-- 4. Student IDs -->
        <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 shadow-sm">
            <span class="text-[10px] font-bold text-slate-450 uppercase tracking-wider block">Student IDs</span>
            <div class="flex items-baseline gap-1 mt-2">
                <span class="text-2xl font-extrabold text-slate-900 dark:text-white">542</span>
            </div>
            <div class="h-1.5 w-full bg-slate-100 dark:bg-slate-800 rounded-full mt-3 overflow-hidden">
                <div class="h-full bg-cyan-500 rounded-full" style="width: 50%"></div>
            </div>
        </div>

        <!-- 5. Receipts -->
        <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 shadow-sm">
            <span class="text-[10px] font-bold text-slate-450 uppercase tracking-wider block">Fee Receipts</span>
            <div class="flex items-baseline gap-1 mt-2">
                <span class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= number_format($receiptsCount ?? 2121) ?></span>
            </div>
            <div class="h-1.5 w-full bg-slate-100 dark:bg-slate-800 rounded-full mt-3 overflow-hidden">
                <div class="h-full bg-teal-500 rounded-full" style="width: 80%"></div>
            </div>
        </div>

        <!-- 6. Pending Verification -->
        <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 shadow-sm relative overflow-hidden">
            <span class="text-[10px] font-bold text-slate-450 uppercase tracking-wider block">Pending Review</span>
            <div class="flex items-baseline gap-1 mt-2">
                <span class="text-2xl font-extrabold text-amber-500"><?= number_format($pendingVerification ?? 18) ?></span>
            </div>
            <div class="h-1.5 w-full bg-slate-100 dark:bg-slate-800 rounded-full mt-3 overflow-hidden">
                <div class="h-full bg-amber-500 rounded-full" style="width: 30%"></div>
            </div>
        </div>
    </div>

    <!-- Quick Navigation / Workspace Sections -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="<?= url('documents/student-documents') ?>" class="group p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/20 hover:border-indigo-500/50 hover:bg-slate-50/50 transition-all block">
            <div class="text-3xl">👨‍🎓</div>
            <h3 class="font-bold text-slate-900 dark:text-white mt-3 group-hover:text-indigo-500 transition-colors">Student Registry</h3>
            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Birth certificates, Aadhar card validation, transfer sheets, academic scores</p>
        </a>

        <a href="<?= url('documents/parent-documents') ?>" class="group p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/20 hover:border-emerald-500/50 hover:bg-slate-50/50 transition-all block">
            <div class="text-3xl">👪</div>
            <h3 class="font-bold text-slate-900 dark:text-white mt-3 group-hover:text-emerald-500 transition-colors">Parent Files</h3>
            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Guardian authorization certificates, relationship validations, income proofs</p>
        </a>

        <a href="<?= url('documents/driver-documents') ?>" class="group p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/20 hover:border-amber-500/50 hover:bg-slate-50/50 transition-all block">
            <div class="text-3xl">🚌</div>
            <h3 class="font-bold text-slate-900 dark:text-white mt-3 group-hover:text-amber-500 transition-colors">Driver Registry</h3>
            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Commercial driving license logs, police clear papers, health checks</p>
        </a>
    </div>

    <!-- Recent Actions & Expiry Alerts -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Generated Documents History -->
        <div class="lg:col-span-2 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/30 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-850 pb-3">
                <h3 class="font-bold text-slate-900 dark:text-white text-base">Recent Generated History</h3>
                <a href="<?= url('documents/generated') ?>" class="text-xs font-semibold text-indigo-500 hover:underline">View Archives →</a>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-850">
                <?php foreach ($recentActivity as $act): ?>
                <div class="py-3 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="text-lg bg-slate-50 dark:bg-slate-850 p-2.5 rounded-xl block"><?= $act['icon'] ?></span>
                        <div>
                            <p class="text-sm font-semibold text-slate-800 dark:text-white"><?= e($act['title']) ?></p>
                            <p class="text-[10px] text-slate-400">Published successfully</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-500 font-bold"><?= $act['status'] ?></span>
                        <p class="text-3xs text-slate-450 mt-1"><?= $act['time'] ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Expiry Tracking Alerts -->
        <div class="p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/30 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-850 pb-3">
                <h3 class="font-bold text-slate-900 dark:text-white text-base">Expiry Tracking</h3>
                <span class="px-2 py-0.5 rounded text-3xs font-extrabold bg-red-500/10 text-red-500 uppercase">Alerts</span>
            </div>
            <div class="space-y-3">
                <div class="p-3 rounded-xl border border-amber-200/50 dark:border-amber-900/20 bg-amber-500/5 flex items-start gap-3">
                    <span class="text-lg">🪪</span>
                    <div>
                        <h4 class="text-xs font-bold text-amber-600 dark:text-amber-500">Driver License Expiry</h4>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">License #DL-9238 expires in 15 days.</p>
                        <a href="<?= url('documents/driver-documents') ?>" class="text-[10px] text-indigo-500 font-bold hover:underline mt-1 block">Renew Record</a>
                    </div>
                </div>

                <div class="p-3 rounded-xl border border-red-200/50 dark:border-red-900/20 bg-red-500/5 flex items-start gap-3">
                    <span class="text-lg">🏥</span>
                    <div>
                        <h4 class="text-xs font-bold text-red-500">Medical Certificate Expired</h4>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">Staff ID #12 medical clearances has expired.</p>
                        <a href="<?= url('documents/staff-documents') ?>" class="text-[10px] text-indigo-500 font-bold hover:underline mt-1 block">Request Upload</a>
                    </div>
                </div>

                <div class="p-3 rounded-xl border border-blue-200/50 dark:border-blue-900/20 bg-blue-500/5 flex items-start gap-3">
                    <span class="text-lg">🛡️</span>
                    <div>
                        <h4 class="text-xs font-bold text-blue-500">Vehicle Insurance Expiring</h4>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">Bus MH-12-AB-5678 covers up to Aug 15.</p>
                        <a href="<?= url('documents/driver-documents') ?>" class="text-[10px] text-indigo-500 font-bold hover:underline mt-1 block">Update Policy</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
