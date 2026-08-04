<?php
$layout    = 'app';
$pageTitle = 'Student Document Registry';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Documents Workspace', 'url' => '/documents'], ['label' => 'Student Documents']];
ob_start();
?>

<div class="max-w-6xl mx-auto py-2">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

        <!-- Student Selector Panel (Left Sidebar) -->
        <div class="lg:col-span-1 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/30 p-4 space-y-4">
            <h3 class="font-bold text-slate-800 dark:text-white text-xs uppercase tracking-wider">Students Registry</h3>
            
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" id="studentSearch" placeholder="Search student name..." 
                       class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-1.5 pl-8 pr-3 text-xs focus:outline-none focus:border-indigo-500">
            </div>

            <div class="space-y-1 overflow-y-auto max-h-[480px] pr-1" id="studentList">
                <?php foreach ($students as $st): ?>
                <a href="?student_id=<?= $st['id'] ?>" 
                   class="block p-2.5 rounded-xl text-xs transition-all <?= $st['id'] == $studentId ? 'bg-indigo-600 text-white font-bold' : 'hover:bg-slate-50 dark:hover:bg-slate-800/40 text-slate-700 dark:text-slate-300' ?>">
                    <div class="truncate"><?= e($st['first_name'] . ' ' . $st['last_name']) ?></div>
                    <div class="text-[10px] opacity-80 font-mono mt-0.5">Adm: <?= e($st['admission_number']) ?></div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Document Viewer & Actions Panel (Right Content Area) -->
        <div class="lg:col-span-3 space-y-6">
            <?php 
            $activeStudent = null;
            foreach ($students as $st) {
                if ($st['id'] == $studentId) {
                    $activeStudent = $st;
                    break;
                }
            }
            ?>
            <?php if ($activeStudent): ?>
            <!-- Student Header Banner -->
            <div class="p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/30 flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-500 flex items-center justify-center text-xl font-bold font-mono">
                        <?= strtoupper(substr($activeStudent['first_name'], 0, 1)) ?>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white"><?= e($activeStudent['first_name'] . ' ' . $activeStudent['last_name']) ?></h2>
                        <p class="text-xs text-slate-500 font-mono">Admission No: <?= e($activeStudent['admission_number']) ?> · Class: <?= e($activeStudent['class_name'] ?? 'Not assigned') ?></p>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full text-3xs font-extrabold uppercase bg-emerald-500/10 text-emerald-500">Active Profile</span>
            </div>

            <!-- Documents Table -->
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/30 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/40 border-b border-slate-200 dark:border-slate-800 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                            <th class="px-5 py-3">Document Type</th>
                            <th class="px-5 py-3">Verification</th>
                            <th class="px-5 py-3">Last Updated</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-850 text-xs">
                        <?php foreach ($docTypes as $key => $label): 
                            $doc = null;
                            foreach ($documents as $d) {
                                if ($d['type'] === $key) {
                                    $doc = $d;
                                    break;
                                }
                            }
                        ?>
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-850/20 transition-all">
                            <td class="px-5 py-4">
                                <p class="font-bold text-slate-800 dark:text-slate-200"><?= e($label) ?></p>
                                <?php if ($doc): ?>
                                <span class="text-[10px] text-slate-450 truncate block max-w-xs" title="<?= e($doc['file_name']) ?>"><?= e($doc['file_name']) ?></span>
                                <?php else: ?>
                                <span class="text-[10px] text-red-400 font-medium">Missing File</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <?php if ($doc): ?>
                                    <?php if ($doc['status'] === 'verified'): ?>
                                    <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-500 font-bold text-[10px]">Verified</span>
                                    <?php elseif ($doc['status'] === 'rejected'): ?>
                                    <span class="px-2 py-0.5 rounded bg-red-500/10 text-red-500 font-bold text-[10px]">Rejected</span>
                                    <?php else: ?>
                                    <span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-500 font-bold text-[10px]">Pending Review</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-slate-400">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-slate-500 font-mono text-[10px]">
                                <?= $doc ? date('d M Y, H:i', strtotime($doc['created_at'])) : 'Never' ?>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <?php if ($doc): ?>
                                    <?php if (str_starts_with($doc['stored_name'], 'certificate:')): ?>
                                    <a href="/certificates/<?= (int)substr($doc['stored_name'], 12) ?>/view" target="_blank" 
                                       class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 font-bold text-[10px] text-slate-700 dark:text-slate-300">
                                        View
                                    </a>
                                    <?php else: ?>
                                    <a href="/psnf/storage/uploads/documents/<?= e($doc['stored_name']) ?>" target="_blank" 
                                       class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 font-bold text-[10px] text-slate-700 dark:text-slate-300">
                                        View
                                    </a>
                                    <a href="/psnf/storage/uploads/documents/<?= e($doc['stored_name']) ?>" download 
                                       class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 font-bold text-[10px] text-slate-700 dark:text-slate-300">
                                        Download
                                    </a>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                    
                                    <!-- Upload button triggers modal/inline form -->
                                    <button onclick="openUploadModal('<?= $key ?>', '<?= e($label) ?>')" 
                                            class="px-2.5 py-1 rounded font-bold text-[10px] text-white bg-indigo-600 hover:bg-indigo-500">
                                        <?= $doc ? 'Replace' : 'Upload' ?>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/30 p-16 text-center">
                <div class="text-4xl mb-3">👨‍🎓</div>
                <h3 class="font-bold text-slate-800 dark:text-white">No Student Selected</h3>
                <p class="text-xs text-slate-500 mt-1">Please select a student from the sidebar registry to view, replace or verify documents.</p>
            </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<!-- Upload Dialog / Modal -->
<div id="uploadModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur d-none" style="display: none;">
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 w-full max-w-md space-y-4">
        <h3 class="font-bold text-slate-900 dark:text-white text-base">Upload <span id="modalDocLabel">Document</span></h3>
        
        <form action="<?= url('documents/student-documents/upload') ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="student_id" value="<?= $studentId ?>">
            <input type="hidden" name="type" id="modalDocType" value="">

            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-400 uppercase">Document Title</label>
                <input type="text" name="title" id="modalDocTitle" required
                       class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2 px-3 text-xs focus:outline-none">
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-400 uppercase">Select File (PDF, Image)</label>
                <input type="file" name="file" required
                       class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 file:cursor-pointer">
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" onclick="closeUploadModal()" 
                        class="px-4 py-2 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-355 rounded-xl text-xs font-semibold hover:bg-slate-50">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-semibold">
                    Upload File
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const search = document.getElementById('studentSearch');
    if (search) {
        search.addEventListener('input', (e) => {
            const val = e.target.value.toLowerCase();
            const items = document.querySelectorAll('#studentList a');
            items.forEach(item => {
                const text = item.innerText.toLowerCase();
                if (text.includes(val)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
});

function openUploadModal(type, label) {
    document.getElementById('modalDocType').value = type;
    document.getElementById('modalDocLabel').innerText = label;
    document.getElementById('modalDocTitle').value = label;
    document.getElementById('uploadModal').style.display = 'flex';
}

function closeUploadModal() {
    document.getElementById('uploadModal').style.display = 'none';
}
</script>

<?php
$content = ob_get_clean();
?>
