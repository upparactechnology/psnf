<?php
$layout    = 'app';
$pageTitle = 'Bus & Transport';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Bus & Transport']];
ob_start();
?>

<div class="space-y-6">

    <!-- View Switcher Tabs -->
    <div class="flex items-center border-b border-slate-200 dark:border-slate-800 gap-6">
        <a href="<?= url('transport') ?>"
           class="flex items-center gap-2 py-3 px-1 border-b-2 font-bold text-sm transition-all focus:outline-none border-indigo-500 text-indigo-600 dark:text-indigo-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
            Routes & Vehicles
        </a>
        <a href="<?= url('transport/tracking') ?>"
           class="flex items-center gap-2 py-3 px-1 border-b-2 font-medium text-sm transition-all focus:outline-none border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-350">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Live Tracking
        </a>
    </div>

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Routes & Vehicles</h2>
            <p class="text-sm text-slate-500 mt-0.5">Manage vehicles, drivers, and student route configurations.</p>
        </div>
        <a href="<?= url('transport/create') ?>"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white transition-all shadow-md hover:opacity-90"
           style="background: linear-gradient(135deg, #6366f1, #a855f7);">
            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Route
        </a>
    </div>

    <!-- Main Grid: Left is Routes List, Right is Map Student -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Routes & Drivers List -->
        <div class="lg:col-span-2 space-y-4">
            <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Active Bus Routes</h3>
            
            <?php if (empty($routes)): ?>
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/30 p-12 text-center shadow-sm">
                <p class="text-sm text-slate-500">No transport routes configured. Create one to begin.</p>
            </div>
            <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <?php foreach ($routes as $r): ?>
                <?php
                $statusColors = [
                    'inactive'  => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700/50',
                    'en_route'  => 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/30 dark:text-indigo-400 dark:border-indigo-900/20',
                    'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/30 dark:text-emerald-400 dark:border-emerald-900/20',
                ];
                $sc = $statusColors[$r['status']] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                ?>
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/30 shadow-sm p-5 hover:shadow-md transition-shadow relative group">
                    <div class="flex items-center justify-between mb-3.5">
                        <span class="inline-flex text-3xs px-2 py-0.5 rounded-full font-semibold border <?= $sc ?>">
                            <?= strtoupper(str_replace('_', ' ', $r['status'])) ?>
                        </span>
                        
                        <!-- Actions -->
                        <div class="flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity absolute top-4 right-4 bg-white dark:bg-slate-900 p-1.5 rounded-xl border border-slate-200 dark:border-slate-800 shadow-md">
                            <a href="<?= url("transport/{$r['id']}/edit") ?>" class="p-1 rounded-md text-slate-400 hover:text-brand-500 transition-all">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <form action="<?= url("transport/{$r['id']}/delete") ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this route?')">
                                <?= \Core\View::csrf() ?>
                                <button type="submit" class="p-1 rounded-md text-slate-400 hover:text-red-500 transition-all">
                                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>

                    <h4 class="text-base font-bold text-slate-900 dark:text-white mb-1"><?= e($r['route_name']) ?></h4>
                    <p class="text-xs text-slate-500 font-medium font-mono mb-4"><?= e($r['bus_number']) ?></p>

                    <div class="border-t border-slate-100 dark:border-slate-800/80 pt-4 space-y-2.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500">Driver</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-300"><?= e($r['driver_name']) ?></span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500">Phone</span>
                            <a href="tel:<?= e($r['driver_phone']) ?>" class="font-semibold text-brand-600 dark:text-brand-400 hover:underline"><?= e($r['driver_phone']) ?></a>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500">Assigned Students</span>
                            <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 font-bold text-slate-700 dark:text-slate-300"><?= $r['student_count'] ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Student Transport Assignments Log -->
            <div class="mt-8">
                <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-4">Student Assignments</h3>
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/30 overflow-hidden shadow-sm">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800/60 bg-slate-50 dark:bg-slate-900/20">
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Student</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Route</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Pickup Point</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Pickup Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800/40">
                            <?php if (empty($assignments)): ?>
                            <tr>
                                <td colspan="4" class="px-5 py-6 text-center text-sm text-slate-500 dark:text-slate-400">
                                    No student route assignments yet.
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($assignments as $a): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/10 transition-colors">
                                <td class="px-5 py-3.5">
                                    <div class="text-sm font-medium text-slate-800 dark:text-white">
                                        <?= e($a['first_name'] . ' ' . $a['last_name']) ?>
                                    </div>
                                    <span class="text-3xs text-slate-500 font-mono"><?= e($a['admission_number']) ?></span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300"><?= e($a['route_name']) ?></span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="text-xs text-slate-600 dark:text-slate-450"><?= e($a['pickup_point'] ?: '—') ?></span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="text-xs font-mono text-slate-650 dark:text-slate-400"><?= e($a['pickup_time'] ? date('h:i A', strtotime($a['pickup_time'])) : '—') ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Map/Assign Student Section -->
        <div>
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/30 p-5 shadow-sm space-y-4 sticky top-6">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Assign Student to Route</h3>
                <p class="text-xs text-slate-500">Quickly map a student to their active transportation vehicle and pick-up settings.</p>

                <?php if (empty($routes)): ?>
                <p class="text-xs text-red-500 font-medium">Please create a transport route first.</p>
                <?php elseif (empty($unassignedStudents)): ?>
                <p class="text-xs text-emerald-600 dark:text-emerald-500 font-medium">✓ All students currently have transport routes assigned.</p>
                <?php else: ?>
                <form id="assign-form" method="POST" action="" class="space-y-4">
                    <?= \Core\View::csrf() ?>

                    <!-- Select Route -->
                    <div>
                        <label class="block text-3xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Target Route</label>
                        <select id="route-selector" onchange="document.getElementById('assign-form').action = '<?= url('transport') ?>/' + this.value + '/assign'" required
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500">
                            <option value="">Select Route...</option>
                            <?php foreach ($routes as $r): ?>
                            <option value="<?= $r['id'] ?>"><?= e($r['route_name']) ?> (<?= e($r['bus_number']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Select Student -->
                    <div>
                        <label class="block text-3xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Select Student</label>
                        <select name="student_id" required
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500">
                            <option value="">Select Student...</option>
                            <?php foreach ($unassignedStudents as $student): ?>
                            <option value="<?= $student['id'] ?>"><?= e($student['first_name'] . ' ' . $student['last_name']) ?> (<?= e($student['admission_number']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Pickup Point -->
                    <div>
                        <label class="block text-3xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Pickup Point</label>
                        <input type="text" name="pickup_point" required placeholder="e.g. 5th Cross, Gandhi Nagar"
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500">
                    </div>

                    <!-- Pickup Time -->
                    <div>
                        <label class="block text-3xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Pickup Time</label>
                        <input type="time" name="pickup_time" required value="08:00"
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500">
                    </div>

                    <button type="submit"
                            class="w-full py-2.5 text-white font-semibold rounded-xl text-xs transition-all text-center shadow-md hover:opacity-95"
                            style="background: linear-gradient(135deg, #6366f1, #a855f7);">
                        Assign Student
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

<?php
$content = ob_get_clean();
?>
