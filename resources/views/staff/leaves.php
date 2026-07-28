<?php
$layout    = 'app';
$pageTitle = 'Leave Management Workflow';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Staff Workspace', 'url' => '/staff/overview'], ['label' => 'Leave Management']];
ob_start();
?>

<div x-data="{ leaveModal: false }" class="space-y-6 max-w-6xl mx-auto">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Leave Management & Approvals</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage leave entitlements, approvals workflow, casual/sick leave requests</p>
        </div>
        <button @click="leaveModal = true" class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition-all shadow-sm">+ Submit Leave Request</button>
    </div>

    <!-- Leave Requests Table -->
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 dark:bg-slate-800 text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-2xs">
                <tr>
                    <th class="p-4">Employee</th>
                    <th class="p-4">Leave Type</th>
                    <th class="p-4">Start Date</th>
                    <th class="p-4">End Date</th>
                    <th class="p-4">Days</th>
                    <th class="p-4">Reason</th>
                    <th class="p-4 text-right">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                <?php if (empty($requests)): ?>
                <tr>
                    <td colspan="7" class="p-8 text-center text-slate-400 text-xs">No leave requests logged yet. Click "+ Submit Leave Request" to test.</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($requests as $req): ?>
                    <tr>
                        <td class="p-4 font-bold text-slate-900 dark:text-white"><?= e($req['first_name'] . ' ' . $req['last_name']) ?></td>
                        <td class="p-4 text-indigo-500 font-bold"><?= e($req['leave_type_name'] ?? 'Casual Leave') ?></td>
                        <td class="p-4 font-mono"><?= e($req['start_date']) ?></td>
                        <td class="p-4 font-mono"><?= e($req['end_date']) ?></td>
                        <td class="p-4 font-mono font-bold"><?= e($req['days']) ?> day(s)</td>
                        <td class="p-4 text-slate-500"><?= e($req['reason'] ?? 'Personal reasons') ?></td>
                        <td class="p-4 text-right">
                            <span class="px-2.5 py-0.5 rounded-full text-2xs font-bold bg-emerald-500/10 text-emerald-500 uppercase"><?= e($req['status']) ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Request Leave Modal -->
    <div x-show="leaveModal" class="fixed inset-0 z-50 flex items-center justify-center px-4" x-cloak>
        <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm" @click="leaveModal = false"></div>
        <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4 z-10">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Submit Leave Request</h3>
            
            <form action="<?= url('staff/leaves') ?>" method="POST" class="space-y-4 text-xs">
                <?= \Core\View::csrf() ?>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Select Employee</label>
                    <select name="employee_id" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                        <?php foreach ($employees as $e): ?>
                            <option value="<?= $e['id'] ?>"><?= e($e['first_name'] . ' ' . $e['last_name'] . ' (' . $e['emp_code'] . ')') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Leave Type</label>
                    <select name="leave_type_id" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                        <?php foreach ($leaveTypes as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= e($t['name']) ?> (<?= (int)$t['annual_allowance'] ?>/yr)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Start Date</label>
                        <input type="date" name="start_date" value="<?= date('Y-m-d') ?>" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">End Date</label>
                        <input type="date" name="end_date" value="<?= date('Y-m-d') ?>" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white font-mono">
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Reason</label>
                    <textarea name="reason" rows="2" placeholder="e.g. Medical appointment" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white"></textarea>
                </div>
                
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="leaveModal = false" class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-500">Submit Request</button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
