<?php
$layout    = 'app';
$pageTitle = 'Scholarships & Aid';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Fees', 'url' => '/fees'], ['label' => 'Scholarships']];
ob_start();
?>

<div x-data="{ showAddModal: false }" class="space-y-6">

    <!-- Header section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-6 rounded-2xl border border-slate-800/60 bg-slate-900/40 backdrop-blur">
        <div>
            <h2 class="text-xl font-bold text-white">Scholarships & Financial Aid</h2>
            <p class="text-sm text-slate-500 mt-0.5">Manage and assign scholarship discounts and grants to students</p>
        </div>
        <button @click="showAddModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition-all shadow-lg hover:opacity-90 bg-gradient-to-r from-indigo-500 to-purple-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Award Scholarship
        </button>
    </div>

    <!-- Scholarships Directory -->
    <div class="rounded-2xl border border-slate-800/60 bg-slate-900/20 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-800 bg-slate-900/50">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Scholarship Name</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-right">Discount / Grant</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-center">Status</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Remarks</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Awarded Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-850">
                    <?php if (empty($scholarships)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                            No student scholarships or discounts recorded yet.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($scholarships as $s): ?>
                        <tr class="hover:bg-slate-900/30 transition-colors">
                            <!-- Student Details -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold shrink-0">
                                        <?= strtoupper(substr($s['first_name'], 0, 1) . substr($s['last_name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-white"><?= e($s['first_name'] . ' ' . $s['last_name']) ?></p>
                                        <p class="text-xs text-slate-500 font-mono"><?= e($s['admission_number']) ?> • <?= e($s['class']) ?></p>
                                    </div>
                                </div>
                            </td>

                            <!-- Scholarship Name -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-200">
                                <?= e($s['name']) ?>
                            </td>

                            <!-- Discount / Grant Amount -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-mono font-bold text-white">
                                <?php if ($s['type'] === 'percentage'): ?>
                                    <?= number_format((float)$s['amount'], 0) ?>% Off
                                <?php else: ?>
                                    <?= number_format((float)$s['amount'], 2) ?> INR
                                <?php endif; ?>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <?php if ($s['status'] === 'active'): ?>
                                <span class="text-3xs font-bold px-2 py-0.5 rounded bg-emerald-950/30 text-emerald-400 border border-emerald-800/30 uppercase tracking-wide">
                                    Active
                                </span>
                                <?php else: ?>
                                <span class="text-3xs font-bold px-2 py-0.5 rounded bg-slate-950/30 text-slate-400 border border-slate-800/30 uppercase tracking-wide">
                                    Inactive
                                </span>
                                <?php endif; ?>
                            </td>

                            <!-- Remarks -->
                            <td class="px-6 py-4 text-xs text-slate-400 max-w-xs truncate" title="<?= e($s['remarks']) ?>">
                                <?= e($s['remarks'] ?: '—') ?>
                            </td>

                            <!-- Created Date -->
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500 font-mono">
                                <?= format_date($s['created_at']) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Award Scholarship Modal Dialog -->
    <div x-show="showAddModal" class="fixed inset-0 overflow-y-auto z-[9999]" x-cloak>
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Backdrop -->
            <div x-show="showAddModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity" @click="showAddModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Content -->
            <div x-show="showAddModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 class="inline-block align-middle bg-slate-900 border border-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                
                <div class="p-6 border-b border-slate-800 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-white">Award Scholarship</h3>
                    <button @click="showAddModal = false" class="text-slate-500 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="<?= url('scholarships/store') ?>" class="p-6 space-y-4">
                    <?= \Core\View::csrf() ?>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Select Student <span class="text-red-400">*</span></label>
                        <select name="student_id" required class="w-full bg-slate-950 border border-slate-800 text-slate-350 rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500">
                            <option value="">Select Student...</option>
                            <?php foreach ($students as $stu): ?>
                                <option value="<?= $stu['id'] ?>"><?= e($stu['first_name'] . ' ' . $stu['last_name']) ?> (<?= e($stu['class']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Scholarship / Grant Name <span class="text-red-400">*</span></label>
                        <input type="text" name="name" required placeholder="e.g. NGO Financial Aid, Special Needs Merit" class="w-full bg-slate-950 border border-slate-800 text-slate-350 rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-medium text-slate-400">Award Type <span class="text-red-400">*</span></label>
                            <select name="type" required class="w-full bg-slate-950 border border-slate-800 text-slate-350 rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500">
                                <option value="fixed">Fixed Amount (INR)</option>
                                <option value="percentage">Percentage Discount (%)</option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-medium text-slate-400">Value / Amount <span class="text-red-400">*</span></label>
                            <input type="number" step="0.01" name="amount" required placeholder="e.g. 5000 or 50" class="w-full bg-slate-950 border border-slate-800 text-slate-350 rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Award Status <span class="text-red-400">*</span></label>
                        <select name="status" required class="w-full bg-slate-950 border border-slate-800 text-slate-350 rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Remarks / Description</label>
                        <textarea name="remarks" rows="2" placeholder="Describe the reason for financial aid allocation..." class="w-full bg-slate-950 border border-slate-800 text-slate-350 rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500 resize-none"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                        <button type="button" @click="showAddModal = false" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-400 hover:text-white border border-slate-800 hover:bg-slate-850">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-brand-650 hover:bg-brand-500 transition-all shadow-md">Grant Award</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
