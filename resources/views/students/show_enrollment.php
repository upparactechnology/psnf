<?php
$layout    = 'app';
$pageTitle = 'Review Online Application - ' . e($enrollment['application_code']);
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Students', 'url' => '/students'],
    ['label' => 'Applications', 'url' => '/students/enrollments'],
    ['label' => e($enrollment['application_code'])]
];
ob_start();
$pickups = json_decode($enrollment['pickup_persons_json'] ?? '[]', true) ?: [];
?>

<div class="max-w-5xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-xl font-bold text-white">Application Review</h2>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-indigo-600/20 text-indigo-400 border border-indigo-500/30">
                    <?= e($enrollment['application_code']) ?>
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-1">Submitted on <?= date('d M Y, h:i A', strtotime($enrollment['created_at'])) ?></p>
        </div>
        <a href="<?= url('students/enrollments') ?>" class="text-xs text-slate-400 hover:text-white">← Back to List</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Details -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Student Details -->
            <div class="rounded-2xl border border-slate-800 bg-slate-900/40 p-6 space-y-4">
                <h3 class="text-sm font-bold text-indigo-400 border-b border-slate-800 pb-2">Student Information</h3>
                <div class="flex items-start gap-4">
                    <?php if (!empty($enrollment['student_photo'])): ?>
                        <img src="<?= e($enrollment['student_photo']) ?>" class="w-24 h-28 rounded-2xl object-cover border border-slate-700">
                    <?php else: ?>
                        <div class="w-24 h-28 rounded-2xl bg-slate-800 flex items-center justify-center text-xs text-slate-500">No Photo</div>
                    <?php endif; ?>
                    <div class="space-y-1 text-xs">
                        <div class="text-base font-bold text-white"><?= e($enrollment['student_full_name']) ?></div>
                        <div class="text-slate-400"><strong class="text-slate-300">DOB:</strong> <?= e($enrollment['dob']) ?></div>
                        <div class="text-slate-400 capitalize"><strong class="text-slate-300">Gender:</strong> <?= e($enrollment['gender']) ?></div>
                        <div class="text-slate-400"><strong class="text-slate-300">Aadhar:</strong> <?= e($enrollment['student_aadhar'] ?: 'Not Provided') ?></div>
                        <div class="text-slate-400"><strong class="text-slate-300">Address:</strong> <?= e($enrollment['address'] ?: 'Not Provided') ?></div>
                    </div>
                </div>
            </div>

            <!-- Parents Details -->
            <div class="rounded-2xl border border-slate-800 bg-slate-900/40 p-6 space-y-4">
                <h3 class="text-sm font-bold text-indigo-400 border-b border-slate-800 pb-2">Parents Information</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    
                    <!-- Father -->
                    <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800 space-y-2">
                        <div class="text-xs font-bold text-indigo-300">Father</div>
                        <div class="flex gap-3">
                            <?php if (!empty($enrollment['father_photo'])): ?>
                                <img src="<?= e($enrollment['father_photo']) ?>" class="w-16 h-20 rounded-xl object-cover border border-slate-700">
                            <?php endif; ?>
                            <div class="text-xs space-y-0.5">
                                <div class="font-bold text-white"><?= e($enrollment['father_name']) ?></div>
                                <div class="text-slate-400">📞 <?= e($enrollment['father_phone']) ?></div>
                                <div class="text-slate-400">🆔 <?= e($enrollment['father_aadhar']) ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Mother -->
                    <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800 space-y-2">
                        <div class="text-xs font-bold text-pink-300">Mother</div>
                        <div class="flex gap-3">
                            <?php if (!empty($enrollment['mother_photo'])): ?>
                                <img src="<?= e($enrollment['mother_photo']) ?>" class="w-16 h-20 rounded-xl object-cover border border-slate-700">
                            <?php endif; ?>
                            <div class="text-xs space-y-0.5">
                                <div class="font-bold text-white"><?= e($enrollment['mother_name']) ?></div>
                                <div class="text-slate-400">📞 <?= e($enrollment['mother_phone']) ?></div>
                                <div class="text-slate-400">🆔 <?= e($enrollment['mother_aadhar']) ?></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Authorized Pickup Persons -->
            <div class="rounded-2xl border border-slate-800 bg-slate-900/40 p-6 space-y-4">
                <h3 class="text-sm font-bold text-indigo-400 border-b border-slate-800 pb-2">Authorized Pickup Persons (<?= count($pickups) ?>)</h3>
                <?php if (empty($pickups)): ?>
                    <p class="text-xs text-slate-500">No pickup persons specified.</p>
                <?php else: ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <?php foreach ($pickups as $p): ?>
                        <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800 flex gap-3">
                            <?php if (!empty($p['photo'])): ?>
                                <img src="<?= e($p['photo']) ?>" class="w-14 h-16 rounded-lg object-cover border border-slate-700">
                            <?php endif; ?>
                            <div class="text-xs space-y-0.5">
                                <div class="font-bold text-white"><?= e($p['name']) ?></div>
                                <div class="text-indigo-400 font-medium"><?= e($p['relationship'] ?: 'Pickup Person') ?></div>
                                <div class="text-slate-400">📞 <?= e($p['phone'] ?: '-') ?></div>
                                <?php if (!empty($p['is_emergency'])): ?>
                                    <span class="inline-block px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 text-[10px] font-bold">Emergency Contact</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Uploaded Documents -->
            <div class="rounded-2xl border border-slate-800 bg-slate-900/40 p-6 space-y-4">
                <h3 class="text-sm font-bold text-indigo-400 border-b border-slate-800 pb-2">Uploaded Document Attachments</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                    <?php if (!empty($enrollment['father_aadhar_doc'])): ?>
                        <a href="<?= e($enrollment['father_aadhar_doc']) ?>" target="_blank" class="p-3 rounded-xl bg-slate-900 border border-slate-700 text-slate-300 hover:text-white flex items-center gap-2">
                            📄 Father Aadhar Card
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($enrollment['mother_aadhar_doc'])): ?>
                        <a href="<?= e($enrollment['mother_aadhar_doc']) ?>" target="_blank" class="p-3 rounded-xl bg-slate-900 border border-slate-700 text-slate-300 hover:text-white flex items-center gap-2">
                            📄 Mother Aadhar Card
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($enrollment['student_aadhar_doc'])): ?>
                        <a href="<?= e($enrollment['student_aadhar_doc']) ?>" target="_blank" class="p-3 rounded-xl bg-slate-900 border border-slate-700 text-slate-300 hover:text-white flex items-center gap-2">
                            📄 Student Aadhar Card
                        </a>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- Right Col: Approval Action Box -->
        <div>
            <?php if ($enrollment['status'] === 'pending'): ?>
            <div class="rounded-2xl border border-indigo-500/30 bg-slate-900/80 p-6 space-y-5 sticky top-6">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Approve & Import Student
                </h3>

                <form method="POST" action="<?= url('students/enrollments/' . $enrollment['id'] . '/approve') ?>" class="space-y-4">
                    <?= \Core\View::csrf() ?>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-300">Assign School <span class="text-red-400">*</span></label>
                        <select name="school_id" required class="w-full bg-slate-900 border border-slate-700 text-white rounded-xl py-2.5 px-3 text-xs outline-none focus:border-indigo-500">
                            <option value="">Select School</option>
                            <?php foreach ($schools as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= e($s['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-300">Assign Branch <span class="text-red-400">*</span></label>
                        <select name="branch_id" required class="w-full bg-slate-900 border border-slate-700 text-white rounded-xl py-2.5 px-3 text-xs outline-none focus:border-indigo-500">
                            <option value="">Select Branch</option>
                            <?php foreach ($branches as $b): ?>
                                <option value="<?= $b['id'] ?>"><?= e($b['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl transition-all shadow-lg shadow-emerald-600/30">
                        ✓ Confirm Approval & Import to ERP
                    </button>
                </form>

                <div class="border-t border-slate-800 pt-4">
                    <form method="POST" action="<?= url('students/enrollments/' . $enrollment['id'] . '/reject') ?>" class="space-y-3">
                        <?= \Core\View::csrf() ?>
                        <textarea name="admin_notes" rows="2" placeholder="Rejection reason..." class="w-full bg-slate-900 border border-slate-800 text-white text-xs rounded-xl p-2.5 outline-none"></textarea>
                        <button type="submit" onclick="return confirm('Are you sure you want to reject this application?')" class="w-full py-2 bg-red-950/60 hover:bg-red-900 text-red-300 font-semibold text-xs rounded-xl border border-red-800/40">
                            Reject Application
                        </button>
                    </form>
                </div>
            </div>
            <?php else: ?>
            <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 text-center space-y-2">
                <div class="text-xs uppercase text-slate-500 font-bold">Status</div>
                <div class="text-lg font-bold capitalize <?= $enrollment['status'] === 'approved' ? 'text-emerald-400' : 'text-red-400' ?>">
                    <?= e($enrollment['status']) ?>
                </div>
                <p class="text-xs text-slate-500">Processed at <?= e($enrollment['processed_at']) ?></p>
            </div>
            <?php endif; ?>
        </div>

    </div>

</div>

<?php
$content = ob_get_clean();
require VIEWS_PATH . '/layouts/' . $layout . '.php';
