<?php
$layout    = 'app';
$pageTitle = 'Staff Directory';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Staff Workspace', 'url' => '/staff/overview'], ['label' => 'Staff Directory']];
ob_start();
?>

<div x-data="{ createModal: false }" class="space-y-6 max-w-6xl mx-auto">

    <!-- Header & Search Filters -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Staff Directory</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage employee records, departments, designations & profile details</p>
        </div>
        <button @click="createModal = true" class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition-all shadow-sm">+ Add New Employee</button>
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
                                    <a href="<?= url('staff/employees/' . $emp['id']) ?>" class="font-bold text-slate-900 dark:text-white hover:text-indigo-500"><?= e($emp['first_name'] . ' ' . $emp['last_name']) ?></a>
                                    <p class="text-2xs text-slate-400"><?= e($emp['email']) ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="p-4 font-mono text-indigo-500 font-bold"><?= e($emp['emp_code']) ?></td>
                        <td class="p-4 text-slate-700 dark:text-slate-300"><?= e($emp['department_name'] ?? 'Unassigned') ?></td>
                        <td class="p-4 text-slate-700 dark:text-slate-300"><?= e($emp['designation_title'] ?? 'Staff') ?></td>
                        <td class="p-4 text-slate-400 font-mono"><?= e($emp['joining_date']) ?></td>
                        <td class="p-4 font-mono text-slate-700 dark:text-slate-300">
                            <span class="text-emerald-500 font-bold"><?= e(substr($emp['min_clock_in'] ?? '09:00:00', 0, 5)) ?></span> – <span class="text-indigo-500 font-bold"><?= e(substr($emp['max_clock_out'] ?? '17:00:00', 0, 5)) ?></span>
                        </td>
                        <td class="p-4 font-mono font-bold text-slate-900 dark:text-white">₹<?= number_format((float) $emp['salary_basic'], 2) ?></td>
                        <td class="p-4">
                            <span class="px-2.5 py-0.5 rounded-full text-2xs font-bold bg-emerald-500/10 text-emerald-500 uppercase"><?= e($emp['status']) ?></span>
                        </td>
                        <td class="p-4 text-right">
                            <a href="<?= url('staff/employees/' . $emp['id']) ?>" class="text-indigo-500 font-bold hover:underline">Profile →</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Employee Modal -->
    <div x-show="createModal" class="fixed inset-0 z-50 flex items-center justify-center px-4" x-cloak>
        <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm" @click="createModal = false"></div>
        <div class="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4 z-10">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Add New Employee</h3>
            
            <form action="<?= url('staff/employees') ?>" method="POST" class="space-y-4 text-xs">
                <?= \Core\View::csrf() ?>
                
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">First Name</label>
                        <input type="text" name="first_name" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Last Name</label>
                        <input type="text" name="last_name" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Email Address</label>
                    <input type="email" name="email" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Department</label>
                        <select name="department_id" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                            <?php foreach ($departments as $d): ?>
                                <option value="<?= $d['id'] ?>"><?= e($d['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Designation</label>
                        <select name="designation_id" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                            <?php foreach ($designations as $des): ?>
                                <option value="<?= $des['id'] ?>"><?= e($des['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Min Clock In Time</label>
                        <input type="time" name="min_clock_in" value="09:00" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Max Clock Out Time</label>
                        <input type="time" name="max_clock_out" value="17:00" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white font-mono">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Basic Monthly Salary (₹)</label>
                    <input type="number" step="0.01" name="salary_basic" value="35000.00" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white font-mono">
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="createModal = false" class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-500">Save Employee</button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
