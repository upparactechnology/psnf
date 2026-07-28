<?php
$layout    = 'app';
$pageTitle = 'Driver Document Registry';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Documents Workspace', 'url' => '/documents'], ['label' => 'Driver Documents']];
ob_start();
?>

<div class="max-w-6xl mx-auto py-2">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

        <!-- Driver Selector Panel -->
        <div class="lg:col-span-1 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/30 p-4 space-y-4">
            <h3 class="font-bold text-slate-800 dark:text-white text-xs uppercase tracking-wider">Drivers Registry</h3>
            
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" id="driverSearch" placeholder="Search driver name..." 
                       class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-1.5 pl-8 pr-3 text-xs focus:outline-none focus:border-indigo-500">
            </div>

            <div class="space-y-1 overflow-y-auto max-h-[480px] pr-1" id="driverList">
                <?php foreach ($drivers as $d): ?>
                <a href="?driver_id=<?= $d['id'] ?>" 
                   class="block p-2.5 rounded-xl text-xs transition-all <?= $d['id'] == $driverId ? 'bg-amber-600 text-white font-bold' : 'hover:bg-slate-50 dark:hover:bg-slate-800/40 text-slate-700 dark:text-slate-300' ?>">
                    <div class="truncate"><?= e($d['name']) ?></div>
                    <div class="text-[10px] opacity-80 font-mono mt-0.5">Lic: <?= e($d['license_number']) ?> · <?= e($d['phone']) ?></div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Content Area -->
        <div class="lg:col-span-3 space-y-6">
            <?php 
            $activeDriver = null;
            foreach ($drivers as $d) {
                if ($d['id'] == $driverId) {
                    $activeDriver = $d;
                    break;
                }
            }
            ?>
            <?php if ($activeDriver): ?>
            <!-- Driver Header Banner -->
            <div class="p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/30 flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center text-xl font-bold font-mono">
                        <?= strtoupper(substr($activeDriver['name'], 0, 1)) ?>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white"><?= e($activeDriver['name']) ?></h2>
                        <p class="text-xs text-slate-500 font-mono">License Number: <?= e($activeDriver['license_number']) ?> · Phone: <?= e($activeDriver['phone']) ?></p>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full text-3xs font-extrabold uppercase bg-amber-500/10 text-amber-500">Active Profile</span>
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
                            <td class="px-5 py-4 whitespace-nowrap text-right text-slate-450">
                                Simulated actions
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/30 p-16 text-center">
                <div class="text-4xl mb-3">🚌</div>
                <h3 class="font-bold text-slate-800 dark:text-white">No Driver Selected</h3>
                <p class="text-xs text-slate-500 mt-1">Please select a driver from the sidebar registry to view licenses, badges or insurance files.</p>
            </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const search = document.getElementById('driverSearch');
    if (search) {
        search.addEventListener('input', (e) => {
            const val = e.target.value.toLowerCase();
            const items = document.querySelectorAll('#driverList a');
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
</script>

<?php
$content = ob_get_clean();
?>
