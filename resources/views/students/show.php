<?php
$layout    = 'app';
$pageTitle = 'Student Profile';
$s = $student;
$name = $s['first_name'] . ' ' . $s['last_name'];
$breadcrumbs = [['label' => 'Dashboard','url'=>'/dashboard'],['label'=>'Students','url'=>'/students'],['label'=>$name]];
ob_start();
$statusClasses = [
    'applied'    => 'bg-slate-700/50 text-slate-300 border-slate-600/30',
    'review'     => 'bg-yellow-900/30 text-yellow-400 border-yellow-700/30',
    'assessment' => 'bg-blue-900/30 text-blue-400 border-blue-700/30',
    'approved'   => 'bg-emerald-900/30 text-emerald-400 border-emerald-700/30',
    'enrolled'   => 'bg-indigo-900/30 text-indigo-400 border-indigo-700/30',
    'withdrawn'  => 'bg-red-900/30 text-red-400 border-red-700/30',
];
$sc = $statusClasses[$s['admission_status']] ?? 'bg-slate-700/50 text-slate-300 border-slate-600/30';
?>

<div x-data="studentProfile()" class="max-w-6xl mx-auto">

    <!-- Profile Header -->
    <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 mb-6">
        <div class="flex flex-col sm:flex-row items-start gap-5">

            <!-- Avatar / Photo -->
            <div class="relative flex-shrink-0">
                <?php if ($s['photo']): ?>
                <img src="<?= url('storage/uploads/students/' . $s['id'] . '/' . $s['photo']) ?>"
                     class="w-20 h-20 rounded-2xl object-cover border-2 border-brand-500/30" alt="">
                <?php else: ?>
                <div class="w-20 h-20 rounded-2xl flex items-center justify-center text-2xl font-bold text-white"
                     style="background: linear-gradient(135deg, #6366f1, #a855f7);">
                    <?= strtoupper(substr($s['first_name'],0,1).substr($s['last_name'],0,1)) ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Info -->
            <div class="flex-1">
                <div class="flex flex-wrap items-start gap-3 mb-2">
                    <h2 class="text-2xl font-bold text-white"><?= e($name) ?></h2>
                    <span class="inline-flex text-xs px-3 py-1 rounded-full font-medium border <?= $sc ?>">
                        <?= ucfirst($s['admission_status']) ?>
                    </span>
                </div>
                <div class="flex flex-wrap gap-4 text-sm text-slate-400">
                    <?php if ($s['admission_number']): ?>
                    <span class="flex items-center gap-1.5">
                        <span class="text-slate-600">ADM:</span>
                        <span class="font-mono text-slate-300"><?= e($s['admission_number']) ?></span>
                    </span>
                    <?php endif; ?>
                    <?php if ($s['gr_number']): ?>
                    <span class="flex items-center gap-1.5">
                        <span class="text-slate-600">GR:</span>
                        <span class="font-mono text-slate-300"><?= e($s['gr_number']) ?></span>
                    </span>
                    <?php endif; ?>
                    <span><?= age($s['dob']) ?></span>
                    <span><?= ucfirst($s['gender']) ?></span>
                    <span class="text-brand-400"><?= e($s['disability_type']) ?></span>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2 flex-shrink-0">
                <?php if (has_permission('approve_admissions')): ?>
                <div x-data="{ statusOpen: false }" class="relative">
                    <button @click="statusOpen = !statusOpen"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium bg-slate-800/60 hover:bg-slate-800 text-slate-300 border border-slate-700/50 transition-all">
                        Update Status <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="statusOpen" @click.outside="statusOpen = false" x-transition
                         id="status-dropdown"
                         class="absolute right-0 mt-2 w-48 rounded-xl border border-slate-700/50 bg-slate-900 shadow-2xl z-20 overflow-hidden">
                        <style>
                            html:not(.dark) #status-dropdown button {
                                color: #1e293b !important;
                            }
                            html:not(.dark) #status-dropdown button:hover {
                                color: #ffffff !important;
                                background-color: #4f46e5 !important;
                            }
                        </style>
                        <?php foreach (['applied','review','assessment','approved','enrolled','withdrawn'] as $st): ?>
                        <?php if ($st === 'enrolled'): ?>
                            <button type="button" @click="showEnrollModal = true; statusOpen = false" class="w-full text-left px-4 py-2.5 text-sm text-slate-300 hover:bg-slate-800 hover:text-white transition-colors <?= $s['admission_status'] === $st ? 'text-brand-400' : '' ?>">
                                <?= ucfirst($st) ?><?= $s['admission_status'] === $st ? ' ✓' : '' ?>
                            </button>
                        <?php else: ?>
                            <form method="POST" action="<?= url('students/'.$s['id'].'/status') ?>">
                                <?= \Core\View::csrf() ?>
                                <input type="hidden" name="admission_status" value="<?= $st ?>">
                                <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-slate-300 hover:bg-slate-800 hover:text-white transition-colors <?= $s['admission_status'] === $st ? 'text-brand-400' : '' ?>">
                                    <?= ucfirst($st) ?><?= $s['admission_status'] === $st ? ' ✓' : '' ?>
                                </button>
                            </form>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (has_permission('edit_students')): ?>
                <a href="<?= url('students/'.$s['id'].'/report-card/edit') ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white transition-all bg-emerald-600 hover:bg-emerald-500 border border-emerald-700/50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Report Card
                </a>
                <a href="<?= url('students/'.$s['id'].'/edit') ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white transition-all" style="background: linear-gradient(135deg, #6366f1, #a855f7);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="flex items-center gap-1 border-b border-slate-800/60 mb-6 overflow-x-auto">
        <?php $tabs = ['overview' => 'Overview', 'subjects' => 'Enrolled Subjects', 'medical' => 'Medical', 'guardians' => 'Guardians & Contacts', 'documents' => 'Documents', 'timeline' => 'Timeline']; ?>
        <?php foreach ($tabs as $key => $label): ?>
        <button @click="activeTab = '<?= $key ?>'"
                :class="activeTab === '<?= $key ?>' ? 'text-brand-400 border-b-2 border-brand-500' : 'text-slate-500 hover:text-slate-300 border-b-2 border-transparent'"
                class="px-4 py-3 text-sm font-medium transition-all whitespace-nowrap">
            <?= $label ?>
        </button>
        <?php endforeach; ?>
    </div>

    <!-- Overview Tab -->
    <div x-show="activeTab === 'overview'" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Personal -->
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-5 space-y-4">
            <h3 class="text-sm font-semibold text-slate-300">Personal Details</h3>
            <?php
            $fields = [
                'Date of Birth'   => format_date($s['dob']) . ' (' . age($s['dob']) . ')',
                'Gender'          => ucfirst($s['gender'] ?? '—'),
                'Blood Group'     => $s['blood_group'] ?? '—',
                'Nationality'     => $s['nationality'] ?? '—',
                'Religion'        => $s['religion'] ?? '—',
                'Mother Tongue'   => $s['mother_tongue'] ?? '—',
                'Aadhar Number'   => $s['aadhar_number'] ?? '—',
                'Address'         => $s['address'] ?? '—',
            ];
            foreach ($fields as $label => $value): ?>
            <div class="flex items-start gap-3">
                <span class="text-xs text-slate-500 w-32 flex-shrink-0 mt-0.5"><?= $label ?></span>
                <span class="text-sm text-slate-300"><?= e($value) ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Admission -->
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-5 space-y-4">
            <h3 class="text-sm font-semibold text-slate-300">Admission Details</h3>
            <?php
            $admFields = [
                'Status'         => ucfirst($s['admission_status']),
                'Applied On'     => format_date($s['created_at']),
                'Enrolled Date'  => $s['enrolled_date'] ? format_date($s['enrolled_date']) : '—',
                'Class / Section'=> ($s['class'] ?? '—') . ' / ' . ($s['section'] ?? '—'),
                'Academic Year'  => $s['academic_year'] ?? '—',
                'Disability Type'=> $s['disability_type'],
            ];
            foreach ($admFields as $label => $value): ?>
            <div class="flex items-start gap-3">
                <span class="text-xs text-slate-500 w-32 flex-shrink-0 mt-0.5"><?= $label ?></span>
                <span class="text-sm text-slate-300"><?= e($value) ?></span>
            </div>
            <?php endforeach; ?>

            <?php if ($s['disability_detail']): ?>
            <div class="mt-4 p-3 rounded-xl bg-slate-950/30 border border-slate-800/60">
                <p class="text-xs font-semibold text-slate-400 mb-1">Disability Details</p>
                <p class="text-xs text-slate-350"><?= e($s['disability_detail']) ?></p>
            </div>
            <?php endif; ?>

            <?php if ($s['special_needs_summary']): ?>
            <div class="mt-4 p-3 rounded-xl bg-slate-950/30 border border-slate-800/60">
                <p class="text-xs font-semibold text-slate-400 mb-1">Special Need Summary</p>
                <p class="text-xs text-slate-350"><?= e($s['special_needs_summary']) ?></p>
            </div>
            <?php endif; ?>

            <?php if ($s['care_instructions']): ?>
            <div class="mt-4 p-3 rounded-xl bg-blue-950/30 border border-blue-900/30">
                <p class="text-xs font-semibold text-blue-400 mb-1">Care Instructions</p>
                <p class="text-xs text-slate-450"><?= e($s['care_instructions']) ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Subjects Tab -->
    <div x-show="activeTab === 'subjects'" x-cloak class="space-y-6">
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-slate-300">Enrolled Subjects & Therapy Modules</h3>
                    <p class="text-xs text-slate-500 mt-0.5"><?= count($assignedSubjects ?? []) ?> active subjects assigned</p>
                </div>
            </div>

            <!-- Bulk Checkbox Subject Selector -->
            <form action="<?= url('academics/students/'.$s['id'].'/subjects') ?>" method="POST" class="p-5 rounded-2xl border border-slate-800/80 bg-slate-950/40 space-y-4">
                <?= \Core\View::csrf() ?>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Select Subjects to Assign</span>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition-all shadow-sm">
                        + Assign Selected Subjects
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                    <?php 
                    $assignedIds = array_column($assignedSubjects ?? [], 'id');
                    foreach ($availableSubjects as $sub): 
                        $isAssigned = in_array($sub['id'], $assignedIds);
                    ?>
                    <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-800/60 bg-slate-900/60 hover:border-slate-700 cursor-pointer transition-all">
                        <input type="checkbox" name="subject_ids[]" value="<?= $sub['id'] ?>" <?= $isAssigned ? 'checked disabled' : '' ?> class="mt-1 rounded bg-slate-950 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                        <div class="min-w-0 flex-1">
                            <span class="text-2xs font-mono text-indigo-400"><?= e($sub['code']) ?></span>
                            <p class="text-xs font-bold text-white truncate"><?= e($sub['name']) ?></p>
                            <span class="text-[10px] text-slate-500"><?= e($sub['type']) ?></span>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </form>

            <!-- Currently Assigned Subjects Grid -->
            <div>
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Currently Active Subjects</h4>
                <?php if (empty($assignedSubjects)): ?>
                <div class="p-8 text-center border border-slate-800/60 rounded-xl bg-slate-950/30">
                    <p class="text-xs text-slate-500">No subjects currently assigned to this student.</p>
                </div>
                <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    <?php foreach ($assignedSubjects as $sub): ?>
                    <div class="p-4 rounded-xl bg-slate-800/30 border border-slate-700/30 flex items-center justify-between">
                        <div>
                            <span class="text-2xs font-mono text-indigo-400"><?= e($sub['code']) ?></span>
                            <h4 class="text-xs font-bold text-white mt-0.5"><?= e($sub['name']) ?></h4>
                            <span class="text-[10px] text-slate-400"><?= e($sub['type']) ?></span>
                        </div>
                        <form action="<?= url('academics/students/'.$s['id'].'/subjects/' . $sub['id'] . '/delete') ?>" method="POST" onsubmit="return confirm('Unassign subject?')">
                            <?= \Core\View::csrf() ?>
                            <button type="submit" class="text-2xs text-red-400 hover:underline">Unassign</button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <!-- Medical Tab -->
    <div x-show="activeTab === 'medical'" x-cloak>
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-sm font-semibold text-slate-300">Medical Information</h3>
                <?php if (has_permission('edit_students')): ?>
                <span class="text-xs text-slate-500">Auto-saved via HTMX</span>
                <?php endif; ?>
            </div>

            <?php if (has_permission('edit_students')): ?>
            <form hx-post="<?= url('students/'.$s['id'].'/medical') ?>"
                  hx-swap="outerHTML"
                  hx-target="#medical-save-msg"
                  class="space-y-5">
                <?= \Core\View::csrf() ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Known Allergies</label>
                        <textarea name="allergies" rows="3" class="w-full bg-slate-900/70 border border-slate-700/60 text-white placeholder-slate-500 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 transition-all" placeholder="Food, medication, environmental..."><?= e($s['medical']['allergies'] ?? '') ?></textarea>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Triggers / Sensitivities</label>
                        <textarea name="triggers" rows="3" class="w-full bg-slate-900/70 border border-slate-700/60 text-white placeholder-slate-500 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 transition-all" placeholder="Known behavioral triggers..."><?= e($s['medical']['triggers'] ?? '') ?></textarea>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Current Medications</label>
                        <textarea name="current_medications" rows="3" class="w-full bg-slate-900/70 border border-slate-700/60 text-white placeholder-slate-500 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 transition-all" placeholder="Medication, dosage, frequency..."><?= e($s['medical']['current_medications'] ?? '') ?></textarea>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Emergency Protocols</label>
                        <textarea name="emergency_protocols" rows="3" class="w-full bg-slate-900/70 border border-slate-700/60 text-white placeholder-slate-500 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 transition-all" placeholder="What to do in an emergency..."><?= e($s['medical']['emergency_protocols'] ?? '') ?></textarea>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Care Instructions</label>
                        <textarea name="care_instructions" rows="3" class="w-full bg-slate-900/70 border border-slate-700/60 text-white placeholder-slate-500 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 transition-all"><?= e($s['medical']['care_instructions'] ?? '') ?></textarea>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-400">Doctor Name</label>
                        <input type="text" name="doctor_name" value="<?= e($s['medical']['doctor_name'] ?? '') ?>" class="w-full bg-slate-900/70 border border-slate-700/60 text-white placeholder-slate-500 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 transition-all" placeholder="Dr. Name">
                        <input type="tel" name="doctor_phone" value="<?= e($s['medical']['doctor_phone'] ?? '') ?>" class="mt-2 w-full bg-slate-900/70 border border-slate-700/60 text-white placeholder-slate-500 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 transition-all" placeholder="Doctor phone number">
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition-all shadow-lg hover:opacity-90" style="background: linear-gradient(135deg, #6366f1, #a855f7);">
                        Save Medical Records
                    </button>
                    <div id="medical-save-msg"></div>
                </div>
            </form>
            <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?php
                $medFields = ['allergies' => 'Allergies', 'triggers' => 'Triggers', 'current_medications' => 'Medications', 'emergency_protocols' => 'Emergency Protocols', 'care_instructions' => 'Care Instructions'];
                foreach ($medFields as $key => $label): ?>
                <div>
                    <p class="text-xs font-medium text-slate-500 mb-1"><?= $label ?></p>
                    <p class="text-sm text-slate-300"><?= e($s['medical'][$key] ?? '—') ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Guardians Tab -->
    <div x-show="activeTab === 'guardians'" x-cloak class="space-y-5">

        <!-- Emergency Contacts -->
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-slate-300">Emergency Contacts</h3>
                <?php if (has_permission('edit_students')): ?>
                <button @click="showAddContact = !showAddContact" class="text-xs text-brand-400 hover:text-brand-300 transition-colors">+ Add Contact</button>
                <?php endif; ?>
            </div>

            <?php if (empty($s['emergency_contacts'])): ?>
            <p class="text-sm text-slate-500 py-4 text-center">No emergency contacts added.</p>
            <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($s['emergency_contacts'] as $contact): ?>
                <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-800/30 border border-slate-700/30">
                    <div class="w-8 h-8 rounded-full bg-red-900/30 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-white"><?= e($contact['name']) ?> <span class="text-slate-500 text-xs">(<?= e($contact['relationship']) ?>)</span></p>
                        <p class="text-xs text-slate-400"><?= e($contact['phone']) ?><?= $contact['is_primary'] ? ' · Primary' : '' ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (has_permission('edit_students')): ?>
            <form x-show="showAddContact" hx-post="<?= url('students/'.$s['id'].'/emergency-contacts') ?>"
                  hx-on::after-request="showAddContact = false"
                  class="mt-4 grid grid-cols-2 gap-3 p-4 rounded-xl bg-slate-800/30 border border-slate-700/30">
                <?= \Core\View::csrf() ?>
                <input type="text" name="name" placeholder="Full Name" required class="w-full bg-slate-900/70 border border-slate-700/60 text-white placeholder-slate-500 rounded-lg py-2 px-3 text-sm focus:outline-none focus:border-brand-500 transition-all">
                <input type="text" name="relationship" placeholder="Relationship" required class="w-full bg-slate-900/70 border border-slate-700/60 text-white placeholder-slate-500 rounded-lg py-2 px-3 text-sm focus:outline-none focus:border-brand-500 transition-all">
                <input type="tel" name="phone" placeholder="Phone Number" required class="w-full bg-slate-900/70 border border-slate-700/60 text-white placeholder-slate-500 rounded-lg py-2 px-3 text-sm focus:outline-none focus:border-brand-500 transition-all">
                <select name="is_primary" class="w-full bg-slate-900/70 border border-slate-700/60 text-slate-300 rounded-lg py-2 px-3 text-sm focus:outline-none focus:border-brand-500 transition-all">
                    <option value="0">Secondary</option>
                    <option value="1">Primary</option>
                </select>
                <div class="col-span-2 flex gap-2">
                    <button type="submit" class="px-4 py-2 rounded-lg text-sm font-medium text-white" style="background: linear-gradient(135deg, #6366f1, #a855f7);">Add Contact</button>
                    <button type="button" @click="showAddContact = false" class="px-4 py-2 rounded-lg text-sm text-slate-400 hover:text-white border border-slate-700/50">Cancel</button>
                </div>
            </form>
            <?php endif; ?>
        </div>

        <!-- Guardians -->
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-5">
            <h3 class="text-sm font-semibold text-slate-300 mb-4">Parents / Guardians</h3>
            <?php if (empty($s['guardians'])): ?>
            <p class="text-sm text-slate-500 py-4 text-center">No guardians linked.</p>
            <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($s['guardians'] as $g): ?>
                <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-800/30 border border-slate-700/30">
                    <div class="w-9 h-9 rounded-full bg-brand-900/30 flex items-center justify-center flex-shrink-0 font-bold text-sm text-brand-400">
                        <?= strtoupper(substr($g['name'],0,1)) ?>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-white"><?= e($g['name']) ?> <?= $g['is_primary'] ? '<span class="text-xs text-brand-400">(Primary)</span>' : '' ?></p>
                        <p class="text-xs text-slate-400">
                            <?= e($g['relationship']) ?> · <?= e($g['phone']) ?>
                            <?= !empty($g['email']) ? ' · ' . e($g['email']) : '' ?>
                            <?= !empty($g['aadhar']) ? ' · Aadhaar: ' . e($g['aadhar']) : '' ?>
                        </p>
                    </div>
                    <?php if ($g['can_pickup']): ?>
                    <span class="text-xs bg-emerald-900/30 text-emerald-400 border border-emerald-700/30 px-2 py-0.5 rounded-full">Can Pickup</span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Documents Tab -->
    <div x-show="activeTab === 'documents'" x-cloak>
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-5">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-sm font-semibold text-slate-300">Student Documents</h3>
                <?php if (has_permission('upload_documents')): ?>
                <button @click="showUpload = !showUpload" class="inline-flex items-center gap-1.5 text-xs text-brand-400 hover:text-brand-300 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Upload Document
                </button>
                <?php endif; ?>
            </div>

            <?php if (has_permission('upload_documents')): ?>
            <div x-show="showUpload" class="mb-5 p-4 rounded-xl bg-slate-800/30 border border-slate-700/30 space-y-3">
                <form hx-post="<?= url('students/'.$s['id'].'/documents') ?>"
                      hx-encoding="multipart/form-data"
                      hx-on::after-request="showUpload = false; window.location.reload()"
                      class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <?= \Core\View::csrf() ?>
                    <select name="document_type" class="bg-slate-900/70 border border-slate-700/60 text-slate-300 rounded-lg py-2 px-3 text-sm focus:outline-none focus:border-brand-500 transition-all">
                        <?php foreach (['birth_certificate'=>'Birth Certificate','aadhar'=>'Aadhar','medical_report'=>'Medical Report','disability_certificate'=>'Disability Certificate','transfer_certificate'=>'Transfer Certificate','other'=>'Other'] as $v=>$l): ?>
                        <option value="<?= $v ?>"><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="title" placeholder="Document title" class="bg-slate-900/70 border border-slate-700/60 text-white placeholder-slate-500 rounded-lg py-2 px-3 text-sm focus:outline-none focus:border-brand-500 transition-all">
                    <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required class="text-sm text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-brand-600/20 file:text-brand-400 hover:file:bg-brand-600/30 cursor-pointer">
                    <div class="sm:col-span-3 flex gap-2">
                        <button type="submit" class="px-4 py-2 rounded-lg text-sm font-medium text-white" style="background: linear-gradient(135deg, #6366f1, #a855f7);">Upload</button>
                        <button type="button" @click="showUpload = false" class="px-4 py-2 rounded-lg text-sm text-slate-400 hover:text-white border border-slate-700/50">Cancel</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <?php if (empty($s['documents'])): ?>
            <div class="text-center py-8">
                <svg class="w-10 h-10 text-slate-700 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <p class="text-sm text-slate-500">No documents uploaded yet.</p>
            </div>
            <?php else: ?>
            <div class="space-y-2">
                <?php foreach ($s['documents'] as $doc): ?>
                <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-800/30 border border-slate-700/30">
                    <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate"><?= e($doc['title']) ?></p>
                        <p class="text-xs text-slate-500"><?= e(str_replace('_',' ', $doc['type'])) ?> · <?= format_bytes((int)$doc['file_size']) ?></p>
                    </div>
                    <span class="text-xs <?= $doc['status'] === 'verified' ? 'text-emerald-400' : ($doc['status'] === 'rejected' ? 'text-red-400' : 'text-yellow-400') ?>">
                        <?= ucfirst($doc['status']) ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Timeline Tab -->
    <div x-show="activeTab === 'timeline'" x-cloak>
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-5">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-sm font-semibold text-slate-300">Student Timeline</h3>
                <a href="<?= url('students/'.$s['id'].'/timeline') ?>" class="text-xs text-brand-400 hover:text-brand-300 transition-colors">Full Timeline →</a>
            </div>

            <?php if (empty($s['timeline'])): ?>
            <p class="text-sm text-slate-500 text-center py-6">No timeline events yet.</p>
            <?php else: ?>
            <div class="relative space-y-4">
                <div class="absolute left-4 top-0 bottom-0 w-px bg-slate-800"></div>
                <?php
                $timelineColors = ['blue'=>'bg-blue-500','green'=>'bg-emerald-500','red'=>'bg-red-500','yellow'=>'bg-yellow-500','purple'=>'bg-purple-500','teal'=>'bg-teal-500'];
                foreach (array_slice($s['timeline'],0,8) as $event):
                    $dot = $timelineColors[$event['color']] ?? 'bg-slate-500';
                ?>
                <div class="flex items-start gap-4 relative">
                    <div class="w-8 h-8 rounded-full <?= $dot ?>/20 border-2 border-<?= $event['color'] ?? 'slate' ?>-500/40 flex items-center justify-center flex-shrink-0 relative z-10">
                        <div class="w-2.5 h-2.5 rounded-full <?= $dot ?>"></div>
                    </div>
                    <div class="flex-1 pb-4">
                        <p class="text-sm font-medium text-white"><?= e($event['title']) ?></p>
                        <?php if ($event['description']): ?>
                        <p class="text-xs text-slate-500 mt-0.5"><?= e($event['description']) ?></p>
                        <?php endif; ?>
                        <div class="flex items-center gap-3 mt-1.5">
                            <span class="text-xs text-slate-600"><?= e($event['actor_name'] ?? 'System') ?></span>
                            <span class="text-xs text-slate-700">·</span>
                            <span class="text-xs text-slate-600"><?= format_date($event['occurred_at'], 'd M Y, H:i') ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Enrollment Modal -->
    <div x-show="showEnrollModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
         x-cloak
         x-transition>
        <div class="w-full max-w-md rounded-2xl border border-slate-800/60 bg-slate-900 p-6 space-y-5 shadow-2xl relative"
             @click.outside="showEnrollModal = false">
            
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-lg font-bold text-white">Enroll Student</h3>
                <button @click="showEnrollModal = false" class="text-slate-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="<?= url('students/'.$s['id'].'/status') ?>" class="space-y-4">
                <?= \Core\View::csrf() ?>
                <input type="hidden" name="admission_status" value="enrolled">

                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-450">Class <span class="text-red-400">*</span></label>
                    <input type="text" name="class" required placeholder="e.g. Class A" list="existing-classes"
                           class="w-full bg-slate-900 border border-slate-800 text-white placeholder-slate-500 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                    <datalist id="existing-classes">
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= e($c['class']) ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-450">Section</label>
                    <input type="text" name="section" placeholder="e.g. A (optional)" list="existing-sections"
                           class="w-full bg-slate-900 border border-slate-800 text-white placeholder-slate-500 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                    <datalist id="existing-sections">
                        <?php foreach ($sections as $sec): ?>
                            <option value="<?= e($sec['section']) ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-450">Academic Year <span class="text-red-400">*</span></label>
                    <input type="text" name="academic_year" required placeholder="e.g. 2026-2027" list="existing-academic-years"
                           value="<?= date('Y') . '-' . (date('Y') + 1) ?>"
                           class="w-full bg-slate-900 border border-slate-800 text-white placeholder-slate-500 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition-all">
                    <datalist id="existing-academic-years">
                        <?php foreach ($academicYears as $ay): ?>
                            <option value="<?= e($ay['academic_year']) ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="showEnrollModal = false"
                            class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-800 text-slate-350 hover:bg-slate-750 transition-all">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl text-xs font-bold text-white shadow-md hover:opacity-95 transition-all"
                            style="background: linear-gradient(135deg, #6366f1, #a855f7); color: #ffffff !important;">
                        Enroll Student
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function studentProfile() {
    return {
        activeTab: 'overview',
        statusOpen: false,
        showAddContact: false,
        showUpload: false,
        showEnrollModal: false,
    }
}
</script>

<?php
$content = ob_get_clean();
?>
