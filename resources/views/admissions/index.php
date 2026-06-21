<?php
$layout    = 'app';
$pageTitle = 'Admissions Pipeline';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Admissions']];
ob_start();
?>

<div x-data="{ activeTab: 'all' }" class="space-y-6">

    <!-- Header section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-6 rounded-2xl border border-slate-800/60 bg-slate-900/40 backdrop-blur">
        <div>
            <h2 class="text-xl font-bold text-white">Admissions Pipeline</h2>
            <p class="text-sm text-slate-500 mt-0.5">Manage new student admission applications and progress them through stages</p>
        </div>
        <a href="<?= url('students/create') ?>" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition-all shadow-lg hover:opacity-90 bg-gradient-to-r from-indigo-500 to-purple-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Intake Application
        </a>
    </div>

    <!-- Status Tabs -->
    <div class="flex flex-wrap gap-2 p-1 bg-slate-950/40 border border-slate-800/60 rounded-xl max-w-2xl">
        <button @click="activeTab = 'all'" :class="activeTab === 'all' ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white'" class="px-4 py-2 rounded-lg text-xs font-semibold transition-all">All Candidates</button>
        <button @click="activeTab = 'applied'" :class="activeTab === 'applied' ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white'" class="px-4 py-2 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5">
            Applied <span class="bg-slate-900 text-slate-400 px-1.5 py-0.5 rounded text-3xs border border-slate-800/60"><?= $statusCounts['applied'] ?? 0 ?></span>
        </button>
        <button @click="activeTab = 'review'" :class="activeTab === 'review' ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white'" class="px-4 py-2 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5">
            Under Review <span class="bg-slate-900 text-slate-400 px-1.5 py-0.5 rounded text-3xs border border-slate-800/60"><?= $statusCounts['review'] ?? 0 ?></span>
        </button>
        <button @click="activeTab = 'assessment'" :class="activeTab === 'assessment' ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white'" class="px-4 py-2 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5">
            Assessment <span class="bg-slate-900 text-slate-400 px-1.5 py-0.5 rounded text-3xs border border-slate-800/60"><?= $statusCounts['assessment'] ?? 0 ?></span>
        </button>
        <button @click="activeTab = 'approved'" :class="activeTab === 'approved' ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white'" class="px-4 py-2 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5">
            Approved <span class="bg-slate-900 text-slate-400 px-1.5 py-0.5 rounded text-3xs border border-slate-800/60"><?= $statusCounts['approved'] ?? 0 ?></span>
        </button>
    </div>

    <!-- Candidate List -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (empty($students)): ?>
        <div class="col-span-full rounded-2xl border border-slate-800/60 bg-slate-900/20 p-12 text-center shadow-sm">
            <svg class="w-12 h-12 text-slate-650 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <h3 class="text-sm font-semibold text-white">No active applications in pipeline</h3>
            <p class="text-xs text-slate-500 mt-1">All candidates are either enrolled or there are no pending submissions.</p>
        </div>
        <?php else: ?>
            <?php foreach ($students as $s): ?>
            <?php
            $badgeColors = [
                'applied'    => 'bg-slate-950/40 text-slate-400 border-slate-850',
                'review'     => 'bg-yellow-950/30 text-yellow-400 border-yellow-800/30',
                'assessment' => 'bg-blue-950/30 text-blue-400 border-blue-800/30',
                'approved'   => 'bg-emerald-950/30 text-emerald-400 border-emerald-800/30',
            ];
            $bc = $badgeColors[$s['admission_status']] ?? 'bg-slate-950/40 text-slate-400';
            ?>
            <div x-show="activeTab === 'all' || activeTab === '<?= $s['admission_status'] ?>'" class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-5 hover:border-slate-700/60 transition-all flex flex-col justify-between group shadow-sm hover:shadow-md">
                
                <div>
                    <!-- Top section -->
                    <div class="flex items-center justify-between mb-3.5">
                        <span class="text-3xs px-2.5 py-0.5 rounded-full font-bold border uppercase tracking-wider <?= $bc ?>">
                            <?= e($s['admission_status']) ?>
                        </span>
                        <span class="text-3xs text-slate-500 font-mono"><?= format_date($s['created_at'], 'd M Y') ?></span>
                    </div>

                    <!-- Name and details -->
                    <a href="<?= url('students/' . $s['id']) ?>" class="block group-hover:text-brand-400 transition-colors">
                        <h4 class="text-base font-bold text-white truncate"><?= e($s['first_name'] . ' ' . $s['last_name']) ?></h4>
                        <p class="text-xs text-slate-500 font-mono mt-0.5"><?= e($s['admission_number']) ?></p>
                    </a>

                    <div class="mt-4 space-y-2 border-t border-slate-800/60 pt-3 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Gender & Age:</span>
                            <span class="text-slate-350 font-medium"><?= ucfirst($s['gender']) ?> (<?= age($s['dob']) ?>)</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Disability Type:</span>
                            <span class="text-slate-350 font-medium truncate max-w-[150px]"><?= e($s['disability_type']) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Branch:</span>
                            <span class="text-slate-350 font-medium"><?= e($s['branch_name'] ?? 'Main') ?></span>
                        </div>
                    </div>
                </div>

                <!-- Action Button Matrix -->
                <div class="mt-5 border-t border-slate-800/60 pt-4 flex flex-col gap-2">
                    <form method="POST" action="<?= url('admissions/' . $s['id'] . '/status') ?>" class="flex flex-wrap gap-1.5 justify-end">
                        <?= \Core\View::csrf() ?>
                        
                        <?php if ($s['admission_status'] === 'applied'): ?>
                            <button type="submit" name="admission_status" value="review" class="px-2.5 py-1.5 rounded-lg text-3xs font-bold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-750 transition-all border border-slate-200 dark:border-slate-700/50 shadow-md">
                                Under Review
                            </button>
                        <?php elseif ($s['admission_status'] === 'review'): ?>
                            <button type="submit" name="admission_status" value="assessment" class="px-2.5 py-1.5 rounded-lg text-3xs font-bold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-750 transition-all border border-slate-200 dark:border-slate-700/50 shadow-md">
                                Schedule Assessment
                            </button>
                        <?php elseif ($s['admission_status'] === 'assessment'): ?>
                            <button type="submit" name="admission_status" value="approved" class="px-2.5 py-1.5 rounded-lg text-3xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 transition-all shadow-md">
                                Approve Admission
                            </button>
                        <?php elseif ($s['admission_status'] === 'approved'): ?>
                            <button type="submit" name="admission_status" value="enrolled" class="px-2.5 py-1.5 rounded-lg text-3xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition-all shadow-md">
                                Final Enroll
                            </button>
                        <?php endif; ?>

                        <!-- Universal Reject/Withdraw -->
                        <button type="submit" name="admission_status" value="withdrawn" class="px-2.5 py-1.5 rounded-lg text-3xs font-bold text-red-400 hover:bg-red-950/20 border border-red-900/30 transition-all">
                            Withdraw
                        </button>
                    </form>
                </div>

            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
