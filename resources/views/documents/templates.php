<?php
$layout    = 'app';
$pageTitle = 'Document Templates & Print Queue';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Documents Workspace', 'url' => '/documents'], ['label' => 'Templates']];
ob_start();
?>

<div class="space-y-6 max-w-6xl mx-auto py-2">

    <!-- Header & Tabs switcher -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-2">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Templates &amp; Print Queue</h2>
            <p class="text-xs text-slate-500 mt-0.5">Manage layouts for certificates, ID cards, slips, and run bulk printing pipelines.</p>
        </div>
    </div>

    <div class="flex items-center gap-6 border-b border-slate-200 dark:border-slate-800">
        <a href="?tab=templates" 
           class="py-3 font-semibold text-xs transition-all <?= $tab === 'templates' ? 'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'text-slate-400 hover:text-slate-200' ?>">
            📝 Document Templates
        </a>
        <a href="?tab=print-queue" 
           class="py-3 font-semibold text-xs transition-all <?= $tab === 'print-queue' ? 'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'text-slate-400 hover:text-slate-200' ?>">
            🖨 Print Queue &amp; Bulk Operations
        </a>
    </div>

    <?php if ($tab === 'templates'): ?>
    <!-- Templates list view -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php foreach ($templates as $tmpl): ?>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/30 p-5 space-y-4 hover:shadow-lg transition-all relative group">
            <div class="w-12 h-12 rounded-xl bg-indigo-500/10 text-indigo-500 flex items-center justify-center text-xl">
                📝
            </div>
            <div>
                <h4 class="font-bold text-slate-800 dark:text-white text-sm"><?= e($tmpl['name']) ?></h4>
                <p class="text-xs text-slate-550 mt-1 uppercase font-semibold text-[10px]">Type: <?= e($tmpl['type']) ?></p>
            </div>
            <div class="border-t border-slate-100 dark:border-slate-850 pt-3 flex items-center justify-between">
                <span class="text-[10px] text-slate-400">QR: <?= $tmpl['qr_enabled'] ? 'Enabled' : 'Disabled' ?></span>
                <a href="<?= e($tmpl['editor_url']) ?>" target="_blank" 
                   class="text-xs text-indigo-500 hover:underline font-bold">Edit Layout →</a>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Create Template Card -->
        <a href="<?= url('certificate_generator/index.php?page=certificate-types') ?>" target="_blank"
           class="rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-800 hover:border-indigo-500/50 p-6 flex flex-col items-center justify-center text-center group transition-all">
            <span class="text-3xl text-slate-400 group-hover:scale-110 transition-transform">➕</span>
            <h4 class="font-bold text-slate-800 dark:text-white text-sm mt-3">Add Custom Template</h4>
            <p class="text-xs text-slate-500 mt-1 max-w-[200px]">Launch visual editor to define custom headers, footers and fields</p>
        </a>
    </div>

    <?php else: ?>
    <!-- Print Queue View -->
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/30 p-6 space-y-4">
            <h3 class="font-bold text-slate-900 dark:text-white text-sm">Active Bulk Operations</h3>
            <p class="text-xs text-slate-500">Run parallel bulk tasks directly to PDF bundles or physical printer manifests.</p>

            <div class="divide-y divide-slate-100 dark:divide-slate-850">
                <!-- Row 1 -->
                <div class="py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <h4 class="text-sm font-bold text-slate-800 dark:text-white">100 Student ID Cards generation</h4>
                        <p class="text-xs text-slate-500">Pending processing · Target template: Standard Student ID Card</p>
                    </div>
                    <button onclick="alert('Bulk PDF bundle compilation has started in the background.');" 
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold shrink-0">
                        Compile PDF Bundle
                    </button>
                </div>

                <!-- Row 2 -->
                <div class="py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <h4 class="text-sm font-bold text-slate-800 dark:text-white">30 Sports Certificates bulk print</h4>
                        <p class="text-xs text-slate-500">Ready to dispatch · Target template: Sports Participation Template</p>
                    </div>
                    <button onclick="alert('Sent to school print server queue.');" 
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold shrink-0">
                        Print Queue
                    </button>
                </div>

                <!-- Row 3 -->
                <div class="py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <h4 class="text-sm font-bold text-slate-800 dark:text-white">50 Fee Receipts PDF export</h4>
                        <p class="text-xs text-slate-500">Pending export · Target template: Official Academic Fee Receipt</p>
                    </div>
                    <button onclick="alert('Exporting zip package containing 50 individual receipts.');" 
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold shrink-0">
                        Export ZIP
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php
$content = ob_get_clean();
?>
