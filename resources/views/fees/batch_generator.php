<?php
$layout    = 'app';
$pageTitle = 'Batch Fee Generator';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Fees', 'url' => '/fees'], ['label' => 'Batch Generator']];
ob_start();
?>

<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Batch Fee Generator</h1>
            <p class="text-sm text-slate-500 mt-1">Automate invoice generation for entire groups of students based on fee structures.</p>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <form action="<?= url('fees/batch-generator') ?>" method="POST" class="p-6 md:p-8 space-y-6" onsubmit="return confirm('Are you sure you want to generate invoices for all students in this group? This action cannot be undone.');">
            <?= \Core\View::csrf() ?>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Fee Structure -->
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Fee Structure Template <span class="text-red-500">*</span></label>
                    <select name="fee_structure_id" required class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                        <option value="">Select Structure...</option>
                        <?php foreach ($structures as $st): ?>
                            <option value="<?= $st['id'] ?>"><?= e($st['name']) ?> (<?= e($st['year_name']) ?> - <?= e($st['main_group_name']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-xs text-slate-500 mt-1.5">Only active structures are shown.</p>
                </div>

                <!-- Target Group -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Target Main Group <span class="text-red-500">*</span></label>
                    <select name="main_group_id" required class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                        <option value="">Select Main Group...</option>
                        <?php foreach ($mainGroups as $mg): ?>
                            <option value="<?= $mg['id'] ?>"><?= e($mg['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Invoice Details -->
                <div class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Invoice Title Override</label>
                        <input type="text" name="invoice_title" placeholder="e.g. Term 1 Tuition Fee" class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                        <p class="text-xs text-slate-500 mt-1.5">Leave blank to use structure name.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Due Date Override</label>
                        <input type="date" name="due_date" class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                        <p class="text-xs text-slate-500 mt-1.5">Default is 15 days from today.</p>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                <button type="submit" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl text-sm font-medium transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Generate Invoices
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();

