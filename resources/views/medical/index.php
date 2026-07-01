<?php
$layout    = 'app';
$pageTitle = 'Medical & Care';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Medical & Care']];
ob_start();
?>

<div x-data="{ 
    showEditModal: false,
    studentId: null,
    studentName: '',
    allergies: '',
    allergySeverity: 'mild',
    triggers: '',
    currentMedications: '',
    medicalConditions: '',
    careInstructions: '',
    emergencyProtocols: '',
    doctorName: '',
    doctorPhone: '',
    hospitalName: '',
    insuranceProvider: '',
    insuranceNumber: '',
    bloodGroup: 'Unknown',
    bloodPressure: '',
    weightKg: '',
    heightCm: '',
    dietaryRestrictions: '',
    
    initEdit(data) {
        this.studentId = data.id;
        this.studentName = data.name;
        this.allergies = decodeURIComponent(data.allergies);
        this.allergySeverity = data.allergySeverity || 'mild';
        this.triggers = decodeURIComponent(data.triggers);
        this.currentMedications = decodeURIComponent(data.currentMedications);
        this.medicalConditions = decodeURIComponent(data.medicalConditions);
        this.careInstructions = decodeURIComponent(data.careInstructions);
        this.emergencyProtocols = decodeURIComponent(data.emergencyProtocols);
        this.doctorName = data.doctorName;
        this.doctorPhone = data.doctorPhone;
        this.hospitalName = data.hospitalName;
        this.insuranceProvider = data.insuranceProvider;
        this.insuranceNumber = data.insuranceNumber;
        this.bloodGroup = data.bloodGroup || 'Unknown';
        this.bloodPressure = data.bloodPressure;
        this.weightKg = data.weightKg;
        this.heightCm = data.heightCm;
        this.dietaryRestrictions = decodeURIComponent(data.dietaryRestrictions);
        this.showEditModal = true;
    }
}" class="space-y-6">

    <!-- ── Live Stats Cards ─────────────────────────────────────────── -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <?php
        $statCards = [
            ['label' => 'Total Students',   'value' => $stats['total_students']  ?? 0, 'color' => 'text-slate-200',   'bg' => 'bg-slate-800/40',   'border' => 'border-slate-700/50'],
            ['label' => 'Care Profiles',    'value' => $stats['with_profile']    ?? 0, 'color' => 'text-brand-400',   'bg' => 'bg-brand-950/20',   'border' => 'border-brand-900/30'],
            ['label' => 'With Allergies',   'value' => $stats['with_allergies']  ?? 0, 'color' => 'text-amber-400',   'bg' => 'bg-amber-950/20',   'border' => 'border-amber-900/30'],
            ['label' => 'Severe Allergy',   'value' => $stats['severe_allergy']  ?? 0, 'color' => 'text-red-400',     'bg' => 'bg-red-950/20',     'border' => 'border-red-900/30'],
            ['label' => 'On Medications',   'value' => $stats['on_medications']  ?? 0, 'color' => 'text-purple-400',  'bg' => 'bg-purple-950/20',  'border' => 'border-purple-900/30'],
            ['label' => 'Emergency Plans',  'value' => $stats['has_emergency_plan'] ?? 0, 'color' => 'text-emerald-400', 'bg' => 'bg-emerald-950/20', 'border' => 'border-emerald-900/30'],
        ];
        ?>
        <?php foreach ($statCards as $card): ?>
        <div class="p-4 rounded-2xl border <?= $card['border'] ?> <?= $card['bg'] ?> flex flex-col gap-1.5">
            <p class="text-xs font-semibold text-slate-500 leading-tight"><?= $card['label'] ?></p>
            <p class="text-2xl font-extrabold <?= $card['color'] ?>"><?= number_format((int)$card['value']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- View Switcher Tabs -->
    <div class="flex items-center border-b border-slate-800 gap-6">
        <a href="<?= url('medical') ?>"
           class="flex items-center gap-2 py-3 px-1 border-b-2 font-bold text-sm transition-all focus:outline-none border-red-500 text-red-400">
            <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            All Student Medical
            <span class="inline-flex items-center justify-center px-2 py-0.5 ml-1 text-2xs font-semibold rounded-full bg-red-950/40 text-red-400 border border-red-900/30">
                <?= $total ?>
            </span>
        </a>
    </div>

    <!-- Main content + sidebar -->
    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
        <!-- Left: Table (3/4 width) -->
        <div class="xl:col-span-3 space-y-4">

    <!-- Header section (Dynamic filters only, main heading rendered by layout app.php) -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
        <form method="GET" action="<?= url('medical') ?>" class="flex flex-col sm:flex-row gap-3 w-full flex-wrap">
            <div class="relative flex-1 min-w-[200px]">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" name="search" placeholder="Search by name or ADM no..." value="<?= e($search) ?>"
                       class="w-full bg-slate-900 border border-slate-800 text-white placeholder-slate-500 rounded-xl py-2.5 pl-10 pr-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
            </div>

            <select name="class" onchange="this.form.submit()"
                    class="bg-slate-900 border border-slate-800 text-slate-300 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 transition-all">
                <option value="">All Classes</option>
                <?php foreach ($classes as $c): ?>
                    <option value="<?= e($c) ?>" <?= $classFilter === $c ? 'selected' : '' ?>><?= e($c) ?></option>
                <?php endforeach; ?>
            </select>

            <select name="disability" onchange="this.form.submit()"
                    class="bg-slate-900 border border-slate-800 text-slate-350 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 transition-all">
                <option value="">All Special Needs</option>
                <?php foreach ($disabilities as $d): ?>
                    <option value="<?= $d ?>" <?= $disability === $d ? 'selected' : '' ?>><?= $d ?></option>
                <?php endforeach; ?>
            </select>

            <select name="blood_group" onchange="this.form.submit()"
                    class="bg-slate-900 border border-slate-800 text-slate-350 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 transition-all">
                <option value="">All Blood Groups</option>
                <?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?>
                    <option value="<?= $bg ?>" <?= ($bloodFilter ?? '') === $bg ? 'selected' : '' ?>><?= $bg ?></option>
                <?php endforeach; ?>
            </select>

            <?php if ($search || $classFilter || $disability || ($bloodFilter ?? '')): ?>
            <a href="<?= url('medical') ?>" class="px-3 py-2.5 rounded-xl border border-slate-700 text-slate-400 text-xs font-medium hover:text-white hover:border-slate-500 transition-all">
                ✕ Clear
            </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Student Medical Directory -->
    <div class="rounded-2xl border border-slate-800/60 bg-slate-900/30 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-800 bg-slate-900/20">
                        <th class="px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">Student</th>
                        <th class="px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center whitespace-nowrap">Blood Group</th>
                        <th class="px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">Allergies & Severity</th>
                        <th class="px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">Current Medications</th>
                        <th class="px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">Care & Protocols</th>
                        <th class="px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-850">
                    <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-slate-500">
                            No student medical logs found in registry.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($students as $s): ?>
                        <?php
                        $disabilityColors = [
                            'ASD'                     => 'bg-blue-950/30 text-blue-400 border-blue-900/30',
                            'ADHD'                    => 'bg-amber-950/30 text-amber-400 border-amber-900/30',
                            'Down Syndrome'           => 'bg-pink-950/30 text-pink-400 border-pink-900/30',
                            'Cerebral Palsy'          => 'bg-purple-950/30 text-purple-400 border-purple-900/30',
                            'Dyslexia'                => 'bg-indigo-950/30 text-indigo-400 border-indigo-900/30',
                            'Intellectual Disability' => 'bg-cyan-950/30 text-cyan-400 border-cyan-900/30',
                        ];
                        $dc = $disabilityColors[$s['disability_type']] ?? 'bg-slate-950/30 text-slate-400 border-slate-900/30';

                        $severityColors = [
                            'severe'   => 'bg-red-950/40 text-red-400 border-red-900/40',
                            'moderate' => 'bg-amber-950/40 text-amber-400 border-amber-900/40',
                            'mild'     => 'bg-emerald-950/40 text-emerald-400 border-emerald-900/40',
                        ];
                        $sc = $severityColors[$s['allergy_severity']] ?? 'bg-slate-950/30 text-slate-400 border-slate-900/30';
                        ?>
                        <tr class="hover:bg-slate-800/10 transition-colors align-top">
                            <!-- Student Details -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="flex items-start gap-3">
                                    <?php if ($s['photo']): ?>
                                        <img src="<?= url('storage/uploads/students/' . $s['id'] . '/' . $s['photo']) ?>" class="w-9 h-9 rounded-full object-cover flex-shrink-0 mt-0.5 shadow-md" alt="">
                                    <?php else: ?>
                                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-red-500 to-pink-600 flex items-center justify-center text-white text-xs font-bold shrink-0 mt-0.5 shadow-md">
                                            <?= strtoupper(substr($s['first_name'], 0, 1) . substr($s['last_name'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <a href="<?= url('students/' . $s['id']) ?>" class="text-sm font-semibold text-white hover:text-brand-400 transition-colors">
                                            <?= e($s['first_name'] . ' ' . $s['last_name']) ?>
                                        </a>
                                        <p class="text-xs text-slate-500 font-mono mt-0.5"><?= e($s['admission_number']) ?></p>
                                        <p class="text-3xs text-slate-400 mt-1"><?= age($s['dob']) ?> · <?= ucfirst($s['gender']) ?><?php if (!empty($s['class'])): ?> · Class: <?= e($s['class']) ?><?php endif; ?></p>
                                        <span class="inline-flex text-3xs font-semibold px-2 py-0.5 rounded border mt-2 <?= $dc ?>">
                                            <?= $s['disability_type'] ?>
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Blood Group -->
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-red-950/20 text-red-400 border border-red-900/30 font-bold font-mono text-xs shadow-inner">
                                    <?= e($s['blood_group'] ?: 'Unknown') ?>
                                </span>
                            </td>

                            <!-- Allergies -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                <?php if ($s['allergies']): ?>
                                    <p class="text-xs font-semibold text-white"><?= e($s['allergies']) ?></p>
                                    <span class="inline-flex text-4xs font-bold px-2 py-0.5 rounded border mt-1.5 uppercase tracking-wide <?= $sc ?>">
                                        <?= $s['allergy_severity'] ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-xs text-slate-500">No known allergies</span>
                                <?php endif; ?>
                            </td>

                            <!-- Current Medications -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                <?php if ($s['current_medications']): ?>
                                    <p class="text-xs text-white font-medium whitespace-pre-line leading-relaxed"><?= e($s['current_medications']) ?></p>
                                <?php else: ?>
                                    <span class="text-xs text-slate-500">No medications logged</span>
                                <?php endif; ?>
                            </td>

                            <!-- Care & Emergency Protocols -->
                            <td class="px-5 py-4 max-w-sm">
                                <div class="space-y-3">
                                    <?php if ($s['emergency_protocols']): ?>
                                    <div class="text-xs leading-relaxed">
                                        <strong class="text-red-400 font-bold block mb-0.5">Emergency Protocol:</strong>
                                        <span class="text-slate-200"><?= e($s['emergency_protocols']) ?></span>
                                    </div>
                                    <?php endif; ?>

                                    <?php if ($s['care_instructions']): ?>
                                    <div class="text-xs leading-relaxed">
                                        <strong class="text-brand-400 font-bold block mb-0.5">Daily Care Instructions:</strong>
                                        <span class="text-slate-200"><?= e($s['care_instructions']) ?></span>
                                    </div>
                                    <?php endif; ?>

                                    <?php if (!$s['emergency_protocols'] && !$s['care_instructions']): ?>
                                        <span class="text-slate-650 text-xs">—</span>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-4 whitespace-nowrap text-sm text-right">
                                <button type="button" 
                                        @click="initEdit({
                                            id: <?= $s['id'] ?>,
                                            name: '<?= e($s['first_name'] . ' ' . $s['last_name']) ?>',
                                            allergies: '<?= rawurlencode($s['allergies'] ?? '') ?>',
                                            allergySeverity: '<?= $s['allergy_severity'] ?? '' ?>',
                                            triggers: '<?= rawurlencode($s['triggers'] ?? '') ?>',
                                            currentMedications: '<?= rawurlencode($s['current_medications'] ?? '') ?>',
                                            medicalConditions: '<?= rawurlencode($s['medical_conditions'] ?? '') ?>',
                                            careInstructions: '<?= rawurlencode($s['care_instructions'] ?? '') ?>',
                                            emergencyProtocols: '<?= rawurlencode($s['emergency_protocols'] ?? '') ?>',
                                            doctorName: '<?= e($s['doctor_name'] ?? '') ?>',
                                            doctorPhone: '<?= e($s['doctor_phone'] ?? '') ?>',
                                            hospitalName: '<?= e($s['hospital_name'] ?? '') ?>',
                                            insuranceProvider: '<?= e($s['insurance_provider'] ?? '') ?>',
                                            insuranceNumber: '<?= e($s['insurance_number'] ?? '') ?>',
                                            bloodGroup: '<?= $s['blood_group'] ?? 'Unknown' ?>',
                                            bloodPressure: '<?= e($s['blood_pressure'] ?? '') ?>',
                                            weightKg: '<?= $s['weight_kg'] ?? '' ?>',
                                            heightCm: '<?= $s['height_cm'] ?? '' ?>',
                                            dietaryRestrictions: '<?= rawurlencode($s['dietary_restrictions'] ?? '') ?>'
                                        })"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-300 bg-slate-800 hover:bg-slate-750 border border-slate-700/50 shadow-sm transition-all">
                                    <svg class="w-3.5 h-3.5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                    Edit Logs
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if (($lastPage ?? 1) > 1): ?>
    <div class="flex items-center justify-between mt-4 px-2">
        <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">
            Showing <?= $offset + 1 ?>–<?= min($total, $offset + $perPage) ?> of <?= number_format($total) ?> students
        </p>
        <div class="flex items-center gap-2">
            <?php if (($page ?? 1) > 1): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => ($page ?? 1) - 1])) ?>"
               class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-400 bg-slate-900 border border-slate-800 hover:bg-slate-800 hover:text-white transition-all shadow-sm">
                ← Prev
            </a>
            <?php endif; ?>
            <?php for ($p = max(1, ($page??1) - 2); $p <= min($lastPage??1, ($page??1) + 2); $p++): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"
               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all border <?= $p === ($page??1) ? 'bg-brand-500/10 text-brand-400 border-brand-500/30' : 'text-slate-400 bg-slate-900 border border-slate-800 hover:bg-slate-800 hover:text-white' ?>">
                <?= $p ?>
            </a>
            <?php endfor; ?>
            <?php if (($page??1) < ($lastPage??1)): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => ($page??1) + 1])) ?>"
               class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-400 bg-slate-900 border border-slate-800 hover:bg-slate-800 hover:text-white transition-all shadow-sm">
                Next →
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

        </div><!-- /xl:col-span-3 -->

        <!-- Sidebar: Blood Groups + Recent Updates -->
        <div class="space-y-5">

            <!-- Blood Group Distribution -->
            <div class="rounded-2xl border border-slate-800/60 bg-slate-900/30 p-5">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Blood Group Distribution</h4>
                <?php if (empty($bloodGroups)): ?>
                <p class="text-xs text-slate-500 text-center py-4">No blood group data recorded.</p>
                <?php else: ?>
                <?php
                $maxBg = max(array_column($bloodGroups, 'cnt'));
                $bgColors = ['A+'=>'bg-red-500','A-'=>'bg-red-400','B+'=>'bg-orange-500','B-'=>'bg-orange-400','AB+'=>'bg-purple-500','AB-'=>'bg-purple-400','O+'=>'bg-blue-500','O-'=>'bg-blue-400'];
                ?>
                <div class="space-y-2.5">
                    <?php foreach ($bloodGroups as $bg): ?>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <a href="?blood_group=<?= urlencode($bg['blood_group']) ?>" class="text-xs font-bold text-slate-300 hover:text-white transition-colors"><?= e($bg['blood_group']) ?></a>
                            <span class="text-xs text-slate-500"><?= $bg['cnt'] ?> students</span>
                        </div>
                        <div class="h-1.5 bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full rounded-full <?= $bgColors[$bg['blood_group']] ?? 'bg-slate-500' ?>" style="width:<?= $maxBg > 0 ? round($bg['cnt']/$maxBg*100) : 0 ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Recent Medical Updates -->
            <div class="rounded-2xl border border-slate-800/60 bg-slate-900/30 p-5">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Recently Updated</h4>
                <?php if (empty($recentUpdates)): ?>
                <p class="text-xs text-slate-500 text-center py-4">No recent updates.</p>
                <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($recentUpdates as $ru): ?>
                    <div class="flex items-start gap-3 p-2.5 rounded-xl border border-slate-800/40 bg-slate-900/20 hover:border-slate-700/60 transition-colors">
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-red-500 to-pink-600 flex items-center justify-center text-white text-3xs font-bold shrink-0 mt-0.5">
                            <?= strtoupper(substr($ru['first_name'],0,1).substr($ru['last_name'],0,1)) ?>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-white truncate"><?= e($ru['first_name'].' '.$ru['last_name']) ?></p>
                            <p class="text-3xs text-slate-500 font-mono"><?= e($ru['admission_number']) ?><?= $ru['class'] ? ' · '.$ru['class'] : '' ?></p>
                            <p class="text-3xs text-slate-600 mt-0.5"><?= $ru['updated_at'] ? date('d M, h:i A', strtotime($ru['updated_at'])) : '—' ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

        </div><!-- /sidebar -->
    </div><!-- /grid -->

    <!-- Edit Medical Modal Dialog -->
    <div x-show="showEditModal" class="fixed inset-0 overflow-y-auto z-[9999]" x-cloak>

        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Backdrop -->
            <div x-show="showEditModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity" @click="showEditModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Content -->
            <div x-show="showEditModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 class="inline-block align-middle bg-slate-900 border border-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                
                <div class="p-6 border-b border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-white">Update Medical Care Record</h3>
                        <p class="text-xs text-slate-500 mt-0.5" x-text="studentName"></p>
                    </div>
                    <button @click="showEditModal = false" class="text-slate-500 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" :action="'<?= url('medical') ?>/' + studentId" class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                    <?= \Core\View::csrf() ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Blood Group -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Blood Group</label>
                            <select name="blood_group" x-model="bloodGroup" class="w-full bg-slate-950 border border-slate-800 text-slate-350 rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500">
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                                <option value="Unknown">Unknown / Select...</option>
                            </select>
                        </div>

                        <!-- Blood Pressure -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Blood Pressure</label>
                            <input type="text" name="blood_pressure" x-model="bloodPressure" placeholder="e.g. 120/80" class="w-full bg-slate-950 border border-slate-800 text-slate-350 rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Weight -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Weight (kg)</label>
                            <input type="number" step="0.1" name="weight_kg" x-model="weightKg" placeholder="e.g. 45.5" class="w-full bg-slate-950 border border-slate-800 text-slate-350 rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500">
                        </div>

                        <!-- Height -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Height (cm)</label>
                            <input type="number" step="0.1" name="height_cm" x-model="heightCm" placeholder="e.g. 152.0" class="w-full bg-slate-950 border border-slate-800 text-slate-350 rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500">
                        </div>
                    </div>

                    <!-- Allergies Section -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2 space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Allergies</label>
                            <input type="text" name="allergies" x-model="allergies" placeholder="Food, medicines, or environmental triggers..." class="w-full bg-slate-950 border border-slate-800 text-slate-350 rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500">
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Allergy Severity</label>
                            <select name="allergy_severity" x-model="allergySeverity" class="w-full bg-slate-950 border border-slate-800 text-slate-350 rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500">
                                <option value="mild">Mild</option>
                                <option value="moderate">Moderate</option>
                                <option value="severe">Severe</option>
                            </select>
                        </div>
                    </div>

                    <!-- Dietary Restrictions & Triggers -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Dietary Restrictions</label>
                            <textarea name="dietary_restrictions" x-model="dietaryRestrictions" rows="2" placeholder="e.g. Vegetarian, Gluten-Free, No dairy..." class="w-full bg-slate-950 border border-slate-800 text-slate-350 rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500 resize-none"></textarea>
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Behavioral Triggers</label>
                            <textarea name="triggers" x-model="triggers" rows="2" placeholder="Describe triggers (sensory overload, loud noises...)" class="w-full bg-slate-950 border border-slate-800 text-slate-350 rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500 resize-none"></textarea>
                        </div>
                    </div>

                    <!-- Medical Conditions & Medications -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Medical Conditions</label>
                            <textarea name="medical_conditions" x-model="medicalConditions" rows="2" placeholder="e.g. Epilepsy, Asthma, Type 1 Diabetes..." class="w-full bg-slate-950 border border-slate-800 text-slate-350 rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500 resize-none"></textarea>
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Current Medications & Dosage</label>
                            <textarea name="current_medications" x-model="currentMedications" rows="2" placeholder="Medication name, dosage, timing..." class="w-full bg-slate-950 border border-slate-800 text-slate-350 rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500 resize-none"></textarea>
                        </div>
                    </div>

                    <!-- Care & Emergency Protocols -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Daily Care Instructions</label>
                            <textarea name="care_instructions" x-model="careInstructions" rows="3" placeholder="Step by step assistance details..." class="w-full bg-slate-950 border border-slate-800 text-slate-350 rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500 resize-none"></textarea>
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Emergency Protocol <span class="text-red-400">*</span></label>
                            <textarea name="emergency_protocols" x-model="emergencyProtocols" rows="3" placeholder="Critical steps to follow in crisis..." class="w-full bg-slate-950 border border-slate-800 text-slate-350 rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500 resize-none"></textarea>
                        </div>
                    </div>

                    <!-- Clinical / Doctor Contact Details -->
                    <div class="p-4 rounded-xl border border-slate-800 bg-slate-950/20 space-y-3">
                        <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider">Emergency Doctor & Clinic Details</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="space-y-1.5">
                                <label class="block text-4xs font-medium text-slate-500 uppercase tracking-wider">Primary Doctor Name</label>
                                <input type="text" name="doctor_name" x-model="doctorName" placeholder="Dr. John Doe" class="w-full bg-slate-950 border border-slate-800 text-slate-350 rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-4xs font-medium text-slate-500 uppercase tracking-wider">Primary Doctor Phone</label>
                                <input type="tel" name="doctor_phone" x-model="doctorPhone" placeholder="+91 98765 43210" class="w-full bg-slate-950 border border-slate-800 text-slate-350 rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-4xs font-medium text-slate-500 uppercase tracking-wider">Preferred Hospital</label>
                                <input type="text" name="hospital_name" x-model="hospitalName" placeholder="City General Clinic" class="w-full bg-slate-950 border border-slate-800 text-slate-350 rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="block text-4xs font-medium text-slate-500 uppercase tracking-wider">Insurance Provider</label>
                                <input type="text" name="insurance_provider" x-model="insuranceProvider" placeholder="e.g. Star Health" class="w-full bg-slate-950 border border-slate-800 text-slate-350 rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-4xs font-medium text-slate-500 uppercase tracking-wider">Insurance Policy Number</label>
                                <input type="text" name="insurance_number" x-model="insuranceNumber" placeholder="e.g. POL-998822" class="w-full bg-slate-950 border border-slate-800 text-slate-350 rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-brand-500">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-400 hover:text-white border border-slate-800 hover:bg-slate-850">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-red-650 hover:bg-red-500 transition-all shadow-md">Save Care Changes</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
