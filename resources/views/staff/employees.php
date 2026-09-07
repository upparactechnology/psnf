<?php
$layout    = 'app';
$pageTitle = 'Staff Directory';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Staff Workspace', 'url' => '/staff/overview'], ['label' => 'Staff Directory']];
ob_start();
?>

<div class="space-y-6 max-w-6xl mx-auto">

    <!-- Header & Search Filters -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Staff Directory</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage employee records, departments, designations & profile details</p>
        </div>
        <?php if (has_permission('create_staff_directory')): ?>
        <a href="<?= url('users/create?redirect_to=/staff/employees') ?>" class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition-all shadow-sm">+ Add New Employee</a>
        <?php endif; ?>
    </div>

    <!-- Filter Bar -->
    <form action="<?= url('staff/employees') ?>" method="GET" class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 flex flex-wrap items-center gap-3">
        <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search name, code, email..." class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs text-slate-900 dark:text-white flex-1 min-w-[200px]">
        
        <select name="department_id" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white">
            <option value="">All Departments</option>
            <?php foreach ($departments as $d): ?>
                <option value="<?= $d['id'] ?>" <?= $deptId == $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500">Filter</button>
    </form>

    <!-- Employees List Table -->
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-2xs">
                    <tr>
                        <th class="p-4">Employee</th>
                        <th class="p-4">EMP Code</th>
                        <th class="p-4">Department</th>
                        <th class="p-4">Designation</th>
                        <th class="p-4">Joining Date</th>
                        <th class="p-4">Shift Schedule</th>
                        <th class="p-4">Basic Salary</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium">
                    <?php foreach ($employees as $emp): ?>
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-all">
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white font-bold text-xs flex items-center justify-center">
                                    <?= strtoupper(substr($emp['first_name'], 0, 1) . substr($emp['last_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <?php if ($emp['id']): ?>
                                        <a href="<?= url('staff/employees/' . $emp['id']) ?>" class="font-bold text-slate-900 dark:text-white hover:text-indigo-500"><?= e($emp['first_name'] . ' ' . $emp['last_name']) ?></a>
                                    <?php else: ?>
                                        <span class="font-bold text-slate-900 dark:text-white"><?= e($emp['first_name'] . ' ' . $emp['last_name']) ?></span>
                                    <?php endif; ?>
                                    <p class="text-2xs text-slate-400"><?= e($emp['email']) ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="p-4 font-mono text-indigo-500 font-bold"><?= e($emp['emp_code']) ?></td>
                        <td class="p-4 text-slate-700 dark:text-slate-300"><?= e($emp['department_name'] ?? 'Unassigned') ?></td>
                        <td class="p-4 text-slate-700 dark:text-slate-300"><?= e($emp['designation_title'] ?? 'Staff') ?></td>
                        <td class="p-4 text-slate-400 font-mono"><?= e($emp['joining_date']) ?></td>
                        <td class="p-4 font-mono text-slate-700 dark:text-slate-300">
                            <span class="text-emerald-500 font-bold"><?= date('h:i A', strtotime($emp['min_clock_in'] ?? '09:00:00')) ?></span> – <span class="text-indigo-500 font-bold"><?= date('h:i A', strtotime($emp['max_clock_out'] ?? '17:00:00')) ?></span>
                        </td>
                        <td class="p-4 font-mono font-bold text-slate-900 dark:text-white">₹<?= number_format((float) $emp['salary_basic'], 2) ?></td>
                        <td class="p-4">
                            <span class="px-2.5 py-0.5 rounded-full text-2xs font-bold bg-emerald-500/10 text-emerald-500 uppercase"><?= e($emp['status']) ?></span>
                        </td>
                        <td class="p-4 text-right">
                            <?php if ($emp['id']): ?>
                                <a href="<?= url('staff/employees/' . $emp['id']) ?>" class="text-indigo-500 font-bold hover:underline">Profile →</a>
                            <?php else: ?>
                                <span class="text-slate-400 text-2xs">No record</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
