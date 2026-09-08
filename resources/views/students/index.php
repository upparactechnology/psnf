<?php
$layout    = 'app';
$pageTitle = 'Students';
$breadcrumbs = [];
ob_start();
?>

<div x-data="studentDirectory()" class="space-y-6 max-w-6xl mx-auto">


    <!-- Top Header & Quick Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Students Directory</h1>
            <p class="text-xs text-slate-500 mt-0.5"><?= number_format($total ?? 0) ?> registered students</p>
        </div>
        <div class="flex items-center gap-3">
            <!-- Pending Online Applications Badge -->
            <?php if (!empty($pendingEnrollments) && has_permission('approve_enrollments')): ?>
            <button @click="showPendingModal = true" class="relative inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 hover:bg-amber-100 dark:hover:bg-amber-900/50 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Pending Applications
                <span class="absolute -top-1.5 -right-1.5 flex items-center justify-center w-5 h-5 text-2xs font-bold text-white bg-amber-500 rounded-full ring-2 ring-white dark:ring-slate-900">
                    <?= count($pendingEnrollments) ?>
                </span>
            </button>
            <?php endif; ?>

            <!-- View Mode Switcher -->
            <div class="flex items-center p-1 rounded-xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/50">
                <button @click="viewMode = 'grid'" :class="viewMode === 'grid' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'" class="p-1.5 rounded-lg text-xs font-semibold transition-all" title="Grid Cards View">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                </button>
                <button @click="viewMode = 'table'" :class="viewMode === 'table' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'" class="p-1.5 rounded-lg text-xs font-semibold transition-all" title="Compact Table View">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>

            <?php if (has_permission('create_students_list')): ?>
            <a href="<?= url('students/create') ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white transition-all shadow-sm hover:opacity-90 bg-brand-600 hover:bg-brand-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                + Student
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- PENDING ENROLLMENTS MODAL -->
    <?php if (has_permission('approve_enrollments')): ?>
    <div x-show="showPendingModal" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Backdrop -->
            <div x-show="showPendingModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80" @click="showPendingModal = false"></div>

            <!-- Modal Panel -->
            <div x-show="showPendingModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative inline-block align-bottom bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Pending Online Applications</h3>
                        <p class="text-xs text-slate-500 mt-0.5"><?= count($pendingEnrollments) ?> application(s) awaiting review</p>
                    </div>
                    <button @click="showPendingModal = false" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="px-6 py-4 max-h-[60vh] overflow-y-auto">
                    <?php if (empty($pendingEnrollments)): ?>
                    <div class="text-center py-12">
                        <svg class="w-12 h-12 text-slate-300 dark:text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-sm text-slate-500">No pending applications</p>
                    </div>
                    <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($pendingEnrollments as $enrollment): ?>
                        <div @click="openDetail(<?= htmlspecialchars(json_encode($enrollment), ENT_QUOTES) ?>)" class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 hover:border-indigo-300 dark:hover:border-indigo-600 hover:bg-indigo-50/50 dark:hover:bg-indigo-900/10 transition-all cursor-pointer group">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                    <div class="w-10 h-10 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center flex-shrink-0 text-sm font-bold text-slate-600 dark:text-slate-300 overflow-hidden">
                                        <?php if (!empty($enrollment['student_photo'])): ?>
                                            <img src="<?= e('/erpv2/public/online_enrollment/' . $enrollment['student_photo']) ?>" alt="Photo" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            👤
                                        <?php endif; ?>
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="text-sm font-bold text-slate-900 dark:text-white truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors"><?= e($enrollment['student_full_name']) ?></h4>
                                        <p class="text-2xs text-slate-500 mt-0.5">
                                            <span class="font-mono"><?= e($enrollment['application_code']) ?></span>
                                            &middot; Applied <?= date('M d, Y', strtotime($enrollment['created_at'])) ?>
                                        </p>
                                        <div class="flex items-center gap-2 mt-1 text-2xs text-slate-500">
                                            <span>DOB: <?= e($enrollment['dob']) ?></span>
                                            &middot;
                                            <span class="capitalize"><?= e($enrollment['gender']) ?></span>
                                            &middot;
                                            <span>Father: <?= e($enrollment['father_name'] ?? 'N/A') ?></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Enroll Button + School/Branch Selection -->
                                <div class="flex-shrink-0" x-data="{ open: false }" @click.stop>
                                    <button @click="open = !open" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-2xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        Enroll
                                    </button>

                                    <!-- School/Branch Dropdown -->
                                    <div x-show="open" @click.away="open = false" x-cloak x-transition class="absolute right-0 mt-2 w-72 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 p-4 z-10">
                                        <h5 class="text-xs font-bold text-slate-900 dark:text-white mb-3">Select School & Branch</h5>
                                        <form method="POST" action="<?= url('students/enrollments/' . $enrollment['id'] . '/quick-enroll') ?>" class="space-y-3">
                                            <div>
                                                <label class="block text-2xs font-semibold text-slate-600 dark:text-slate-400 mb-1">School *</label>
                                                <select name="school_id" required class="w-full text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                                    <option value="">Select School</option>
                                                    <?php foreach ($schools as $school): ?>
                                                    <option value="<?= $school['id'] ?>"><?= e($school['name']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-2xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Branch *</label>
                                                <select name="branch_id" required class="w-full text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                                    <option value="">Select Branch</option>
                                                    <?php foreach ($branches as $branch): ?>
                                                    <option value="<?= $branch['id'] ?>" data-school="<?= $branch['school_id'] ?>"><?= e($branch['name']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="flex items-center gap-2 pt-1">
                                                <button type="submit" onclick="return confirm('Enroll this student into the system?')" class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-2xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-colors">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    Confirm Enroll
                                                </button>
                                                <button type="button" @click="open = false" class="px-3 py-2 rounded-lg text-2xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">Cancel</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                    <button @click="showPendingModal = false" class="w-full px-4 py-2 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">Close</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ENROLLMENT DETAIL MODAL -->
    <div x-show="showDetailModal" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showDetailModal" x-transition class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80" @click="showDetailModal = false"></div>
            <div x-show="showDetailModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative inline-block align-bottom bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full max-h-[90vh] overflow-y-auto">

                <!-- Header -->
                <div class="sticky top-0 z-10 flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center overflow-hidden flex-shrink-0">
                            <template x-if="selectedEnrollment?.student_photo">
                                <img :src="'/erpv2/public/online_enrollment/' + selectedEnrollment?.student_photo" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!selectedEnrollment?.student_photo">
                                <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400" x-text="(selectedEnrollment?.student_full_name || '?').split(' ').map(w=>w[0]).join('').substring(0,2).toUpperCase()"></span>
                            </template>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white" x-text="selectedEnrollment?.student_full_name"></h3>
                            <p class="text-xs text-slate-500 font-mono" x-text="selectedEnrollment?.application_code"></p>
                        </div>
                    </div>
                    <button @click="showDetailModal = false" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="px-6 py-5 space-y-5">
                    <!-- Student Photo & Basic Info -->
                    <div class="flex items-start gap-5">
                        <div class="w-28 h-32 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center overflow-hidden border border-slate-200 dark:border-slate-700 flex-shrink-0">
                            <template x-if="selectedEnrollment?.student_photo">
                                <img :src="'/erpv2/public/online_enrollment/' + selectedEnrollment?.student_photo" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!selectedEnrollment?.student_photo">
                                <span class="text-xs text-slate-400">No Photo</span>
                            </template>
                        </div>
                        <div class="space-y-1 text-xs">
                            <div class="text-slate-500"><strong class="text-slate-700 dark:text-slate-300">DOB:</strong> <span x-text="selectedEnrollment?.dob"></span></div>
                            <div class="text-slate-500 capitalize"><strong class="text-slate-700 dark:text-slate-300">Gender:</strong> <span x-text="selectedEnrollment?.gender"></span></div>
                            <div class="text-slate-500"><strong class="text-slate-700 dark:text-slate-300">Aadhar:</strong> <span x-text="selectedEnrollment?.student_aadhar || 'Not Provided'"></span></div>
                            <div class="text-slate-500"><strong class="text-slate-700 dark:text-slate-300">Address:</strong> <span x-text="selectedEnrollment?.address || 'Not Provided'" class="text-slate-600 dark:text-slate-400"></span></div>
                            <div class="text-slate-500"><strong class="text-slate-700 dark:text-slate-300">Applied:</strong> <span x-text="selectedEnrollment?.created_at ? new Date(selectedEnrollment.created_at).toLocaleDateString('en-IN', {day:'2-digit',month:'short',year:'numeric'}) : ''"></span></div>
                        </div>
                    </div>

                    <!-- Student Aadhar Doc -->
                    <template x-if="selectedEnrollment?.student_aadhar_doc">
                        <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-3">
                            <p class="text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">📄 Student Aadhar Document</p>
                            <a :href="'/erpv2/public/online_enrollment/' + selectedEnrollment?.student_aadhar_doc" target="_blank" class="block">
                                <img :src="'/erpv2/public/online_enrollment/' + selectedEnrollment?.student_aadhar_doc" class="max-h-40 rounded-lg object-contain border border-slate-200 dark:border-slate-700">
                            </a>
                        </div>
                    </template>

                    <!-- Parents -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Father -->
                        <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 space-y-2">
                            <div class="text-xs font-bold text-blue-600 dark:text-blue-400">Father</div>
                            <div class="flex gap-3">
                                <template x-if="selectedEnrollment?.father_photo">
                                    <img :src="'/erpv2/public/online_enrollment/' + selectedEnrollment?.father_photo" class="w-14 h-16 rounded-xl object-cover border border-slate-200 dark:border-slate-700 flex-shrink-0">
                                </template>
                                <div class="text-xs space-y-0.5">
                                    <div class="font-bold text-slate-900 dark:text-white" x-text="selectedEnrollment?.father_name || '—'"></div>
                                    <div class="text-slate-500">📞 <span x-text="selectedEnrollment?.father_phone || '—'"></span></div>
                                    <div class="text-slate-500">🆔 <span x-text="selectedEnrollment?.father_aadhar || '—'"></span></div>
                                </div>
                            </div>
                            <template x-if="selectedEnrollment?.father_aadhar_doc">
                                <a :href="'/erpv2/public/online_enrollment/' + selectedEnrollment?.father_aadhar_doc" target="_blank" class="inline-block text-2xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline mt-1">📄 View Aadhar Card</a>
                            </template>
                        </div>
                        <!-- Mother -->
                        <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 space-y-2">
                            <div class="text-xs font-bold text-pink-600 dark:text-pink-400">Mother</div>
                            <div class="flex gap-3">
                                <template x-if="selectedEnrollment?.mother_photo">
                                    <img :src="'/erpv2/public/online_enrollment/' + selectedEnrollment?.mother_photo" class="w-14 h-16 rounded-xl object-cover border border-slate-200 dark:border-slate-700 flex-shrink-0">
                                </template>
                                <div class="text-xs space-y-0.5">
                                    <div class="font-bold text-slate-900 dark:text-white" x-text="selectedEnrollment?.mother_name || '—'"></div>
                                    <div class="text-slate-500">📞 <span x-text="selectedEnrollment?.mother_phone || '—'"></span></div>
                                    <div class="text-slate-500">🆔 <span x-text="selectedEnrollment?.mother_aadhar || '—'"></span></div>
                                </div>
                            </div>
                            <template x-if="selectedEnrollment?.mother_aadhar_doc">
                                <a :href="'/erpv2/public/online_enrollment/' + selectedEnrollment?.mother_aadhar_doc" target="_blank" class="inline-block text-2xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline mt-1">📄 View Aadhar Card</a>
                            </template>
                        </div>
                    </div>

                    <!-- Pickup Persons -->
                    <template x-if="selectedEnrollment?.pickup_persons_json">
                        <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 space-y-3">
                            <div class="text-xs font-bold text-slate-700 dark:text-slate-300">Authorized Pickup Persons</div>
                            <template x-for="(person, idx) in JSON.parse(selectedEnrollment?.pickup_persons_json || '[]')" :key="idx">
                                <div class="flex items-center gap-3 p-2 rounded-lg bg-slate-50 dark:bg-slate-800/50">
                                    <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center flex-shrink-0 overflow-hidden">
                                        <template x-if="person.photo">
                                            <img :src="'/erpv2/public/online_enrollment/' + person.photo" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!person.photo">
                                            <span class="text-2xs font-bold text-slate-500" x-text="(person.name||'?').split(' ').map(w=>w[0]).join('').substring(0,2)"></span>
                                        </template>
                                    </div>
                                    <div class="text-xs">
                                        <div class="font-semibold text-slate-900 dark:text-white" x-text="person.name"></div>
                                        <div class="text-slate-500" x-text="(person.relationship || '—') + ' · ' + (person.phone || '—')"></div>
                                    </div>
                                    <template x-if="person.is_emergency">
                                        <span class="ml-auto px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 text-2xs font-bold">Emergency</span>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <!-- Footer -->
                <div class="sticky bottom-0 px-6 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                    <button @click="showDetailModal = false" class="w-full px-4 py-2 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Input -->
    <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
        <input type="text" placeholder="Search student name or ADM number..."
               value="<?= e($search ?? '') ?>"
               class="w-full bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 rounded-xl py-2.5 pl-10 pr-4 text-xs focus:outline-none focus:border-brand-500 transition-all"
               hx-get="<?= url('students') ?>"
               hx-target="#student-container"
               hx-trigger="keyup changed delay:400ms"
               name="search">
    </div>

    <!-- Student Cards Container -->
    <div id="student-container">
        <?php if (empty($data)): ?>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 p-12 text-center shadow-sm">
            <p class="text-xs text-slate-500">No students found matching your criteria.</p>
        </div>
        <?php else: ?>

        <!-- ENHANCED STUDENT CARD GRID -->
        <div x-show="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php foreach ($data as $student): ?>
            <a href="<?= url('academics/students/' . $student['id']) ?>" class="group p-5 rounded-2xl border border-slate-200 dark:border-slate-800/80 bg-white dark:bg-slate-900/40 hover:border-slate-300 dark:hover:border-slate-700 transition-all shadow-sm hover:shadow-md flex flex-col justify-between space-y-4">
                
                <!-- Avatar & Identifiers -->
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs flex items-center justify-center flex-shrink-0 border border-slate-200 dark:border-slate-700/50">
                        👤 <?= strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)) ?>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                            <?= e($student['first_name'] . ' ' . $student['last_name']) ?>
                        </h3>
                        <p class="text-2xs font-mono text-slate-400 mt-0.5"><?= e($student['admission_number'] ?? 'ADM-2026-111') ?></p>
                    </div>
                </div>

                <!-- Demographics & Attributes -->
                <div class="space-y-1.5 border-t border-b border-slate-100 dark:border-slate-800/60 py-3 text-2xs">
                    <div class="flex items-center justify-between text-slate-600 dark:text-slate-300 font-medium">
                        <span>Class <?= e($student['class'] ?? '2-A') ?></span>
                        <span><?= ucfirst($student['gender'] ?? 'Male') ?> • <?= age($student['dob']) ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="px-2 py-0.5 rounded-full font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                            <?= e($student['disability_type'] ?? 'ADHD') ?>
                        </span>
                        <span class="flex items-center gap-1 font-semibold text-emerald-500">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <?= ucfirst($student['admission_status'] ?? 'Enrolled') ?>
                        </span>
                    </div>
                </div>

                <!-- Card Footer Action -->
                <div class="flex items-center justify-between text-2xs text-slate-400 font-medium">
                    <span>View Profile</span>
                    <span class="group-hover:translate-x-1 transition-transform">→</span>
                </div>

            </a>
            <?php endforeach; ?>
        </div>

        <!-- COMPACT TABLE -->
        <div x-show="viewMode === 'table'" class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 overflow-hidden shadow-sm">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 text-slate-400 text-2xs font-bold uppercase tracking-wider">
                        <th class="px-4 py-3">Student</th>
                        <th class="px-4 py-3">Class</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/40 text-xs">
                    <?php foreach ($data as $student): ?>
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                        <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">
                            <?= e($student['first_name'] . ' ' . $student['last_name']) ?>
                            <span class="block text-2xs text-slate-400 font-normal font-mono"><?= e($student['admission_number'] ?? '') ?></span>
                        </td>
                        <td class="px-4 py-3 text-slate-500"><?= e($student['class'] ?? '—') ?></td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-2xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                <?= ucfirst($student['admission_status']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="<?= url('academics/students/' . $student['id']) ?>" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">View →</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php endif; ?>
    </div>

</div>

<script>
function studentDirectory() {
    return {
        viewMode: 'grid',
        showPendingModal: false,
        selectedEnrollment: null,
        showDetailModal: false,
        openDetail(enrollment) {
            this.selectedEnrollment = enrollment;
            this.showDetailModal = true;
        },
    }
}
</script>

<?php
$content = ob_get_clean();
?>
