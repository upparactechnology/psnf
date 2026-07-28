<?php
$layout    = 'app';
$pageTitle = 'Student Transport Assignments';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Transport Workspace', 'url' => '/transport/overview'], ['label' => 'Student Assignments']];
ob_start();
?>

<div x-data="{ assignModal: false }" class="space-y-6 max-w-6xl mx-auto">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Student Transport Assignments</h1>
            <p class="text-xs text-slate-500 mt-0.5">Map students to routes, assigned vehicles, drivers, pickup stops & pickup times</p>
        </div>
        <button @click="assignModal = true" class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition-all shadow-sm">+ Assign Student to Route</button>
    </div>

    <!-- Student Assignments Table -->
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 dark:bg-slate-800 text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-2xs">
                <tr>
                    <th class="p-4">Student</th>
                    <th class="p-4">Route</th>
                    <th class="p-4">Assigned Driver & Bus</th>
                    <th class="p-4">Pickup Point</th>
                    <th class="p-4">Pickup Time</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                <?php foreach ($assignments as $a): ?>
                <tr>
                    <td class="p-4">
                        <span class="font-bold text-slate-900 dark:text-white block"><?= e($a['first_name'] . ' ' . $a['last_name']) ?></span>
                        <span class="font-mono text-2xs text-slate-400"><?= e($a['admission_number']) ?></span>
                    </td>
                    <td class="p-4 font-bold text-indigo-500"><?= e($a['route_name']) ?></td>
                    <td class="p-4">
                        <span class="font-bold text-slate-900 dark:text-white block"><?= e($a['driver_name'] ?? 'Rajesh Patel') ?></span>
                        <span class="font-mono text-2xs text-slate-400"><?= e($a['bus_number'] ?? 'MH-12-AB-5678') ?></span>
                    </td>
                    <td class="p-4 text-slate-700 dark:text-slate-300"><?= e($a['pickup_point'] ?? 'Society Main Gate') ?></td>
                    <td class="p-4 font-mono font-bold text-emerald-500"><?= e($a['pickup_time'] ?? '08:15 AM') ?></td>
                    <td class="p-4 text-right">
                        <button class="text-red-500 font-bold hover:underline">Remove</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Assign Modal -->
    <div x-show="assignModal" class="fixed inset-0 z-50 flex items-center justify-center px-4" x-cloak>
        <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm" @click="assignModal = false"></div>
        <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4 z-10">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Assign Student to Transport Route</h3>
            
            <form action="<?= url('transport') ?>" method="POST" class="space-y-4 text-xs">
                <?= \Core\View::csrf() ?>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Select Route</label>
                    <select name="route_id" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                        <?php foreach ($routes as $r): ?>
                            <option value="<?= $r['id'] ?>"><?= e($r['route_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Select Student</label>
                    <select name="student_id" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                        <?php foreach ($unassignedStudents as $stu): ?>
                            <option value="<?= $stu['id'] ?>"><?= e($stu['first_name'] . ' ' . $stu['last_name'] . ' (' . $stu['admission_number'] . ')') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Pickup Point</label>
                    <input type="text" name="pickup_point" placeholder="e.g. 5th Cross, Gandhi Nagar" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Pickup Time</label>
                    <input type="text" name="pickup_time" value="08:00 AM" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white font-mono">
                </div>
                
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="assignModal = false" class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-500">Save Assignment</button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
