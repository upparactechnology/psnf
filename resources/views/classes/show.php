<?php
$layout    = 'app';
$pageTitle = 'Class Directory — ' . e($className);
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Classes', 'url' => '/classes'], ['label' => $className]];
ob_start();
?>

<div class="space-y-6">

    <!-- Header section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-6 rounded-2xl border border-slate-800/60 bg-slate-900/40 backdrop-blur">
        <div>
            <h2 class="text-xl font-bold text-white"><?= e($className) ?> Student Directory</h2>
            <p class="text-sm text-slate-500 mt-0.5"><?= count($students) ?> students enrolled in this class</p>
        </div>
        <a href="<?= url('classes') ?>" class="text-sm text-slate-400 hover:text-white transition-colors">← Back to Classes</a>
    </div>

    <!-- Student Table / Grid -->
    <div class="rounded-2xl border border-slate-800/60 bg-slate-900/20 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-800 bg-slate-900/50">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Admission #</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Age & Gender</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Disability Classification</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Branch</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-850">
                    <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                            No students enrolled in this class yet.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($students as $s): ?>
                        <tr class="hover:bg-slate-900/30 transition-colors group">
                            <!-- Name / avatar -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-xs text-white"
                                         style="background: linear-gradient(135deg, #6366f1, #a855f7);">
                                        <?= strtoupper(substr($s['first_name'], 0, 1) . substr($s['last_name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-white group-hover:text-brand-400 transition-colors">
                                            <?= e($s['first_name'] . ' ' . $s['last_name']) ?>
                                        </p>
                                        <p class="text-xs text-slate-500"><?= e($s['blood_group'] ? $s['blood_group'] . ' Blood Group' : '—') ?></p>
                                    </div>
                                </div>
                            </td>

                            <!-- Adm No -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-450 font-mono">
                                <?= e($s['admission_number']) ?>
                            </td>

                            <!-- Age / Gender -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-350">
                                <?= ucfirst($s['gender']) ?> (<?= age($s['dob']) ?>)
                            </td>

                            <!-- Disability -->
                            <td class="px-6 py-4 text-sm text-slate-350 max-w-xs truncate" title="<?= e($s['disability_detail']) ?>">
                                <span class="font-medium text-white block truncate"><?= e($s['disability_type']) ?></span>
                                <span class="text-xs text-slate-500 block truncate"><?= e($s['special_needs_summary'] ?: 'No summary') ?></span>
                            </td>

                            <!-- Branch -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-350">
                                <?= e($s['branch_name'] ?? 'Main') ?>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                <a href="<?= url('students/' . $s['id']) ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-750 transition-all border border-slate-200 dark:border-slate-700/50 shadow-sm">
                                    Profile
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
