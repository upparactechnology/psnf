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
                    <span class="inline-flex text-xs px-3 py-1 rounded-full font-medium border bg-emerald-900/30 text-emerald-400 border-emerald-700/30">
                        Enrolled
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
                    <?php if ($s['roll_number']): ?>
                    <span class="flex items-center gap-1.5">
                        <span class="text-slate-600">Roll:</span>
                        <span class="font-mono text-slate-300"><?= e($s['roll_number']) ?></span>
                    </span>
                    <?php endif; ?>
                    <span><?= age($s['dob']) ?></span>
                    <span><?= ucfirst($s['gender']) ?></span>
                    </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2 flex-shrink-0">
                <a href="<?= url('students/'.$s['id'].'/report-card/edit') ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white transition-all bg-emerald-600 hover:bg-emerald-500 border border-emerald-700/50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Report Card
                </a>
                <a href="<?= url('academics/students/'.$s['id'].'/edit') ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white transition-all" style="background: linear-gradient(135deg, #6366f1, #a855f7);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </a>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="flex items-center gap-1 border-b border-slate-800/60 mb-6 overflow-x-auto">
        <?php $tabs = ['overview' => 'Overview', 'subjects' => 'Inherited Curriculum', 'fees' => 'Financial Ledger', 'guardians' => 'Guardians & Contacts', 'documents' => 'Documents', 'timeline' => 'Timeline']; ?>
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
            ];
            foreach ($admFields as $label => $value): ?>
            <div class="flex items-start gap-3">
                <span class="text-xs text-slate-500 w-32 flex-shrink-0 mt-0.5"><?= $label ?></span>
                <span class="text-sm text-slate-300"><?= e($value) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Subjects Tab -->
    <div x-show="activeTab === 'subjects'" x-cloak class="space-y-6">
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 space-y-4">
            <div>
                <h3 class="text-sm font-semibold text-slate-300">Inherited Curriculum Subjects</h3>
                <p class="text-xs text-slate-500 mt-0.5"><?= count($inheritedSubjects) ?> subjects automatically inherited from Class.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <?php if (empty($inheritedSubjects)): ?>
                    <p class="col-span-full text-center text-slate-500 text-xs py-6">No subjects resolved for this student's class.</p>
                <?php else: ?>
                    <?php foreach ($inheritedSubjects as $sub): ?>
                        <div class="p-4 rounded-xl bg-slate-800/30 border border-slate-700/30 flex items-center justify-between">
                            <div>
                                <span class="text-2xs font-mono text-indigo-400"><?= e($sub['code']) ?></span>
                                <h4 class="text-xs font-bold text-white mt-0.5"><?= e($sub['name']) ?></h4>
                                <span class="text-[10px] text-slate-400"><?= e($sub['category']) ?> (<?= e($sub['assessment_type']) ?>)</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
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

    <!-- Fees Tab -->
    <div x-show="activeTab === 'fees'" x-cloak>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="rounded-2xl bg-indigo-900/40 border border-indigo-500/30 p-5 flex flex-col justify-center">
                <p class="text-indigo-400 text-sm font-medium mb-1">Total Expected</p>
                <h3 class="text-2xl font-bold text-white">₹<?= number_format((float)($s['ledger_summary']['total_debit'] ?? 0), 2) ?></h3>
            </div>
            <div class="rounded-2xl bg-emerald-900/40 border border-emerald-500/30 p-5 flex flex-col justify-center">
                <p class="text-emerald-400 text-sm font-medium mb-1">Total Collected</p>
                <h3 class="text-2xl font-bold text-white">₹<?= number_format((float)($s['ledger_summary']['total_credit'] ?? 0), 2) ?></h3>
            </div>
            <div class="rounded-2xl bg-red-900/40 border border-red-500/30 p-5 flex flex-col justify-center">
                <p class="text-red-400 text-sm font-medium mb-1">Outstanding Balance</p>
                <h3 class="text-2xl font-bold text-white">₹<?= number_format((float)($s['ledger_summary']['current_balance'] ?? 0), 2) ?></h3>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-5">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-sm font-semibold text-slate-300">Ledger History</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-slate-500 font-medium border-b border-slate-800">
                        <tr>
                            <th class="pb-3 font-medium">Date</th>
                            <th class="pb-3 font-medium">Description</th>
                            <th class="pb-3 font-medium text-right">Debit (-)</th>
                            <th class="pb-3 font-medium text-right">Credit (+)</th>
                            <th class="pb-3 font-medium text-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/50">
                        <?php if (empty($s['ledgers'])): ?>
                            <tr><td colspan="5" class="py-6 text-center text-slate-500">No financial records found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($s['ledgers'] as $ledger): ?>
                            <tr class="hover:bg-slate-800/30">
                                <td class="py-3 text-slate-400 whitespace-nowrap"><?= date('M d, Y', strtotime($ledger['created_at'])) ?></td>
                                <td class="py-3 text-slate-300">
                                    <span class="block truncate max-w-xs" title="<?= e($ledger['description']) ?>"><?= e($ledger['description']) ?></span>
                                    <span class="text-xs text-slate-500 uppercase"><?= e($ledger['entry_type']) ?></span>
                                </td>
                                <td class="py-3 text-right text-red-400 font-medium">
                                    <?= $ledger['debit'] > 0 ? '₹' . number_format($ledger['debit'], 2) : '-' ?>
                                </td>
                                <td class="py-3 text-right text-emerald-400 font-medium">
                                    <?= $ledger['credit'] > 0 ? '₹' . number_format($ledger['credit'], 2) : '-' ?>
                                </td>
                                <td class="py-3 text-right text-white font-bold">
                                    ₹<?= number_format($ledger['balance'], 2) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
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
                        <?php foreach (['birth_certificate'=>'Birth Certificate','aadhar'=>'Aadhar','medical_report'=>'Medical Report','disability_certificate'=>'Disability Certificate','transfer_certificate'=>'Transfer Certificate','certificate'=>'Certificate','other'=>'Other'] as $v=>$l): ?>
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

            <?php
            $studentId = (int)$s['id'];
            $fullName = trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? ''));

            $allDocs = $s['documents'] ?? [];
            $existingTitles = array_column($allDocs, 'title');

            // Fetch any generated certificates for this student that might not be in student_documents yet
            $genCerts = \Core\Application::$app->db->select("
                SELECT 
                    gc.id AS gen_id,
                    ct.name AS title,
                    'certificate' AS type,
                    gc.pdf_path AS stored_name,
                    'application/pdf' AS mime_type,
                    'verified' AS status,
                    gc.generated_at AS created_at,
                    p.id AS participant_id
                FROM generated_certificates gc
                JOIN participants p ON p.id = gc.participant_id
                JOIN certificate_types ct ON ct.id = p.certificate_type_id
                WHERE p.student_id = ? OR (p.name IS NOT NULL AND TRIM(p.name) = ?)
            ", [$studentId, $fullName]);

            foreach ($genCerts as $gCert) {
                if (!in_array($gCert['title'], $existingTitles, true)) {
                    $allDocs[] = [
                        'id'             => 'gen_' . $gCert['gen_id'],
                        'student_id'     => $studentId,
                        'type'           => 'certificate',
                        'title'          => $gCert['title'],
                        'file_name'      => $gCert['title'] . '.pdf',
                        'stored_name'    => $gCert['stored_name'],
                        'mime_type'      => 'application/pdf',
                        'file_size'      => 0,
                        'status'         => 'verified',
                        'created_at'     => $gCert['created_at'],
                        'participant_id' => $gCert['participant_id'],
                    ];
                }
            }
            ?>

            <?php if (empty($allDocs)): ?>
            <div class="text-center py-8">
                <svg class="w-10 h-10 text-slate-700 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <p class="text-sm text-slate-500">No documents uploaded yet.</p>
            </div>
            <?php else: ?>
            <div class="space-y-2">
                <?php foreach ($allDocs as $doc): ?>
                <?php
                    $pdfUrl = null;
                    $jpgUrl = null;

                    $pId = $doc['participant_id'] ?? null;
                    if (!$pId && str_starts_with((string)$doc['stored_name'], 'generated/')) {
                        $genRow = \Core\Application::$app->db->selectOne(
                            "SELECT participant_id FROM generated_certificates WHERE pdf_path = ? OR jpg_path = ? LIMIT 1",
                            [$doc['stored_name'], $doc['stored_name']]
                        );
                        if ($genRow) {
                            $pId = $genRow['participant_id'];
                        }
                    }

                    if ($pId) {
                        $pdfUrl = url("parent/certificates/" . (int)$pId . "/download?format=pdf&disposition=inline");
                        $jpgUrl = url("parent/certificates/" . (int)$pId . "/download?format=jpg&disposition=attachment");
                    } elseif (str_starts_with((string)$doc['stored_name'], 'certificate:')) {
                        $cId = (int) substr($doc['stored_name'], 12);
                        $pdfUrl = url("parent/certificates/" . $cId . "/download?format=pdf&disposition=inline");
                        $jpgUrl = url("parent/certificates/" . $cId . "/download?format=jpg&disposition=attachment");
                    } elseif (str_starts_with((string)$doc['stored_name'], 'generated/')) {
                        $pdfUrl = url("public/certificate_generator/" . $doc['stored_name']);
                    } else {
                        $pdfUrl = url("storage/uploads/documents/" . $doc['stored_name']);
                    }
                ?>
                <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-800/30 border border-slate-700/30">
                    <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center flex-shrink-0">
                        <?php if ($doc['type'] === 'certificate'): ?>
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <?php else: ?>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate"><?= e($doc['title']) ?></p>
                        <p class="text-xs text-slate-500"><?= e(str_replace('_',' ', $doc['type'])) ?> · <?= $doc['type'] === 'certificate' ? 'Official Credential' : format_bytes((int)$doc['file_size']) ?></p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium <?= $doc['status'] === 'verified' ? 'bg-emerald-900/30 text-emerald-400 border border-emerald-700/30' : ($doc['status'] === 'rejected' ? 'bg-red-900/30 text-red-400 border border-red-700/30' : 'bg-yellow-900/30 text-yellow-400 border border-yellow-700/30') ?>">
                            <?= ucfirst($doc['status']) ?>
                        </span>

                        <?php if ($jpgUrl): ?>
                        <a href="<?= e($jpgUrl) ?>" download
                           class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold border border-slate-700/50 transition-all">
                            JPG
                        </a>
                        <?php endif; ?>

                        <?php if ($pdfUrl): ?>
                        <a href="<?= e($pdfUrl) ?>" target="_blank"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold transition-all shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            View & Download PDF
                        </a>
                        <?php endif; ?>
                    </div>
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
