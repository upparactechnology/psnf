<?php
$layout    = 'app';
$pageTitle = 'Face Scan Logs';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Attendance'], ['label' => 'Face Scan Logs']];

$outcomeLabels = [
    'success'        => 'Marked',
    'cooldown'       => 'Already marked',
    'not_recognized' => 'Not recognized',
    'blurry'         => 'Blurry',
    'no_face'        => 'No face',
    'multiple_faces' => 'Multiple faces',
    'liveness'       => 'Liveness',
    'dark'           => 'Too dark',
    'overexposed'    => 'Overexposed',
    'inactive_user'  => 'Inactive user',
    'error'          => 'Error',
];
$outcomeHelp = [
    'not_recognized' => 'Score below the threshold. Score under 0.35 = the system thinks this is a different / unregistered person (check the photo: is this person registered?). Score 0.40–0.54 = near miss: re-register this person at the kiosk itself.',
    'blurry'         => 'The face itself was not sharp (moving, low light). Retries usually fix it. Many in the late afternoon = not enough light for the camera.',
    'no_face'        => 'The server could not find a face in the photo (turned away, partly outside the picture).',
    'multiple_faces' => 'More than one face in the photo — people standing behind, or a reflection on the cover sheet.',
    'liveness'       => 'Face shape / angle looked wrong (strongly turned head, or a photo of a photo).',
    'dark'           => 'Whole picture too dark.',
    'overexposed'    => 'Whole picture too bright (direct light on the camera).',
    'inactive_user'  => 'Matched a user account that is inactive or deleted.',
    'error'          => 'Technical error while processing the photo.',
];
$isOk = fn($o) => in_array($o, ['success', 'cooldown'], true);
$badge = function (string $o) use ($outcomeLabels, $isOk): string {
    $cls = $isOk($o)
        ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border-emerald-500/30'
        : ($o === 'not_recognized' ? 'bg-red-500/10 text-red-700 dark:text-red-300 border-red-500/30'
                                    : 'bg-amber-500/10 text-amber-700 dark:text-amber-300 border-amber-500/30');
    $icon = $isOk($o) ? '✓' : '✕';
    return '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full border text-[11px] font-semibold ' . $cls . '">'
         . $icon . ' ' . e($outcomeLabels[$o] ?? $o) . '</span>';
};
$num = fn($v, $d = 2) => ($v === null || $v === '') ? '—' : number_format((float) $v, $d);
$imgUrl = fn(?string $p) => $p ? url('attendance/api.php?endpoint=/' . ltrim($p, '/')) : null;
$qs = function (array $over = []) use ($from, $to, $outcome, $userFilter): string {
    $q = array_merge(['from' => $from, 'to' => $to, 'outcome' => $outcome, 'user' => $userFilter ?: ''], $over);
    return url('attendance/face-scan-logs') . '?' . http_build_query(array_filter($q, fn($v) => $v !== '' && $v !== null));
};

$total    = (int) ($totals['total'] ?? 0);
$okCount  = (int) ($totals['success'] ?? 0) + (int) ($totals['cooldown'] ?? 0);
$failed   = (int) ($totals['failed'] ?? 0);
$sessTot  = (int) ($sessions['total'] ?? 0);
$sessOk   = (int) ($sessions['ok'] ?? 0);
$threshold = isset($rows[0]['threshold']) ? (float) $rows[0]['threshold'] : 0.55;
$pages = max(1, (int) ceil($totalRows / $perPage));

ob_start();
?>
<div class="space-y-6 max-w-7xl mx-auto" x-data="{ img: null, open: {} }">

    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Face Scan Logs</h1>
            <p class="text-xs text-slate-500 mt-0.5">Every face-kiosk attempt with its score, closest match, image quality and camera details. Scores are visible to admins only.</p>
        </div>
        <form method="get" action="<?= url('attendance/face-scan-logs') ?>" class="flex flex-wrap items-end gap-2">
            <label class="text-[11px] text-slate-500">From
                <input type="date" name="from" value="<?= e($from) ?>" class="block mt-0.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-2 py-1.5 text-xs text-slate-800 dark:text-slate-200">
            </label>
            <label class="text-[11px] text-slate-500">To
                <input type="date" name="to" value="<?= e($to) ?>" class="block mt-0.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-2 py-1.5 text-xs text-slate-800 dark:text-slate-200">
            </label>
            <label class="text-[11px] text-slate-500">Result
                <select name="outcome" class="block mt-0.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-2 py-1.5 text-xs text-slate-800 dark:text-slate-200">
                    <option value="">All</option>
                    <option value="failures" <?= $outcome === 'failures' ? 'selected' : '' ?>>All failures</option>
                    <?php foreach ($outcomes as $o): ?>
                        <option value="<?= e($o) ?>" <?= $outcome === $o ? 'selected' : '' ?>><?= e($outcomeLabels[$o] ?? $o) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="text-[11px] text-slate-500">Person
                <select name="user" class="block mt-0.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-2 py-1.5 text-xs text-slate-800 dark:text-slate-200 max-w-[180px]">
                    <option value="">Everyone</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= (int) $u['id'] ?>" <?= $userFilter === (int) $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button class="rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold px-4 py-2">Apply</button>
        </form>
    </div>

    <?php if (!$tableExists): ?>
        <div class="rounded-2xl border border-amber-500/30 bg-amber-500/10 p-5 text-sm text-amber-800 dark:text-amber-200">
            The scan-log table does not exist yet. It is created automatically when the face recognition service restarts
            (<code>systemctl restart face-recog</code>) or on the next page load after the database migration runs.
        </div>
    <?php endif; ?>

    <!-- Live engine settings -->
    <div x-data="{ cfg: null, err: '' }"
         x-init="fetch('<?= url('attendance/api.php?endpoint=/api/diagnostics/config') ?>', {credentials: 'same-origin'})
                 .then(r => r.json()).then(d => { if (d && d.success) cfg = d; else err = (d && (d.message || d.detail)) || 'Unavailable'; })
                 .catch(() => err = 'Face service offline')"
         class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 p-4 shadow-sm">
        <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-xs text-slate-600 dark:text-slate-300">
            <span class="font-semibold text-slate-800 dark:text-white">Live settings</span>
            <template x-if="cfg">
                <div class="flex flex-wrap gap-x-6 gap-y-1">
                    <span>Match threshold: <b x-text="cfg.similarity_threshold"></b></span>
                    <span>Face blur limit: <b x-text="cfg.face_blur_threshold"></b></span>
                    <span>Registered people: <b x-text="cfg.cached_users"></b> (<span x-text="cfg.cached_vectors"></span> photos)</span>
                    <span>Model: <b x-text="cfg.model_name"></b> · <span x-text="cfg.model_loaded ? 'loaded' : 'sleeping (loads on next face)'"></span></span>
                    <span>Cooldown: <b x-text="cfg.cooldown_minutes"></b> min</span>
                    <span>Failed photos kept: <b x-text="cfg.failed_image_days"></b> days</span>
                </div>
            </template>
            <span x-show="!cfg && !err" class="text-slate-400">Loading…</span>
            <span x-show="err" x-text="err" class="text-red-500"></span>
        </div>
    </div>

    <!-- KPI tiles -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <?php
        $tiles = [
            ['People served', $sessTot ? round($sessOk * 100 / $sessTot) . '%' : '—', $sessTot ? "$sessOk of $sessTot people got through (incl. automatic retries)" : 'No kiosk sessions yet'],
            ['Attempts', (string) $total, "$okCount ok · $failed failed"],
            ['Saved by retry', (string) $retrySaves, 'people who succeeded on attempt 2 or 3'],
            ['Avg match score', $num($totals['avg_ok_score'] ?? null), 'successful scans · avg ' . $num($totals['avg_ms'] ?? null, 0) . ' ms'],
        ];
        foreach ($tiles as [$label, $value, $sub]): ?>
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 p-5 shadow-sm">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400"><?= e($label) ?></p>
                <p class="text-3xl font-black text-slate-800 dark:text-white mt-1"><?= e($value) ?></p>
                <p class="text-[11px] text-slate-500 mt-0.5"><?= e($sub) ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <!-- Failure reasons -->
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 p-5 shadow-sm">
            <h2 class="text-sm font-bold text-slate-800 dark:text-white">Why scans failed</h2>
            <p class="text-[11px] text-slate-500 mb-4">Count per failure reason in the selected period. Click a reason to list those scans.</p>
            <?php $maxR = max([1, ...array_map(fn($r) => (int) $r['n'], $reasons)]); ?>
            <?php if (!$reasons): ?><p class="text-xs text-slate-400">No failures in this period.</p><?php endif; ?>
            <div class="space-y-3">
                <?php foreach ($reasons as $r): $o = $r['outcome']; $n = (int) $r['n']; ?>
                    <a href="<?= e($qs(['outcome' => $o, 'page' => null])) ?>" class="block group" title="<?= e(($outcomeLabels[$o] ?? $o) . ': ' . $n . ' scans') ?>">
                        <div class="flex justify-between text-xs text-slate-700 dark:text-slate-300 mb-1">
                            <span class="font-semibold group-hover:underline"><?= e($outcomeLabels[$o] ?? $o) ?></span>
                            <span class="tabular-nums"><?= $n ?> · <?= $failed ? round($n * 100 / $failed) : 0 ?>%</span>
                        </div>
                        <div class="h-2.5 rounded bg-slate-100 dark:bg-slate-800"><div class="h-2.5 rounded bg-indigo-500" style="width: <?= round($n * 100 / $maxR, 1) ?>%"></div></div>
                        <p class="text-[11px] text-slate-500 mt-1 leading-snug"><?= e($outcomeHelp[$o] ?? '') ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- By hour -->
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 p-5 shadow-sm">
            <h2 class="text-sm font-bold text-slate-800 dark:text-white">Success rate by hour of day</h2>
            <p class="text-[11px] text-slate-500 mb-3">Low success at certain hours usually means the light at that time is the problem. "Face light" is the average face brightness (0–255; below ~80 is dim).</p>
            <?php if (!$hours): ?><p class="text-xs text-slate-400">No scans in this period.</p><?php else: ?>
            <table class="w-full text-xs">
                <thead><tr class="text-left text-[10px] uppercase tracking-wider text-slate-400">
                    <th class="py-1 pr-2">Hour</th><th class="py-1 pr-2 w-2/5">Success rate</th><th class="py-1 pr-2 text-right">OK</th>
                    <th class="py-1 pr-2 text-right">Blurry</th><th class="py-1 pr-2 text-right">Not rec.</th><th class="py-1 pr-2 text-right">Other</th><th class="py-1 text-right">Face light</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                <?php foreach ($hours as $h):
                    $hTot = (int) $h['ok'] + (int) $h['blurry'] + (int) $h['notrec'] + (int) $h['other'];
                    $rate = $hTot ? round((int) $h['ok'] * 100 / $hTot) : 0; ?>
                    <tr>
                        <td class="py-1.5 pr-2 tabular-nums"><?= sprintf('%02d:00', (int) $h['h']) ?></td>
                        <td class="py-1.5 pr-2" title="<?= $rate ?>% of <?= $hTot ?> attempts succeeded">
                            <div class="flex items-center gap-2"><div class="flex-1 h-2 rounded bg-slate-100 dark:bg-slate-800"><div class="h-2 rounded bg-indigo-500" style="width: <?= $rate ?>%"></div></div><span class="tabular-nums w-9 text-right"><?= $rate ?>%</span></div>
                        </td>
                        <td class="py-1.5 pr-2 text-right tabular-nums"><?= (int) $h['ok'] ?></td>
                        <td class="py-1.5 pr-2 text-right tabular-nums"><?= (int) $h['blurry'] ?></td>
                        <td class="py-1.5 pr-2 text-right tabular-nums"><?= (int) $h['notrec'] ?></td>
                        <td class="py-1.5 pr-2 text-right tabular-nums"><?= (int) $h['other'] ?></td>
                        <td class="py-1.5 text-right tabular-nums"><?= $num($h['face_light'], 0) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Score distribution -->
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 p-5 shadow-sm">
        <h2 class="text-sm font-bold text-slate-800 dark:text-white">Match score distribution</h2>
        <p class="text-[11px] text-slate-500 mb-4">Score = how similar the face is to the closest registered person (1.00 = identical). Threshold <b><?= $num($threshold) ?></b>. Successful scans should sit well above it; not-recognized scans far below it mean an unregistered person, close below it (0.40+) mean a near miss.</p>
        <div class="grid md:grid-cols-2 gap-6">
            <?php foreach (['success' => 'Successful scans', 'not_recognized' => 'Not-recognized scans'] as $grp => $title):
                $hist = $scoreHist[$grp] ?? [];
                $maxH = max([1, ...array_values($hist)]); ?>
                <div>
                    <p class="text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2"><?= e($title) ?> <span class="font-normal text-slate-400">(<?= array_sum($hist) ?>)</span></p>
                    <?php if (!$hist): ?><p class="text-xs text-slate-400">None in this period.</p><?php endif; ?>
                    <div class="space-y-1">
                        <?php for ($b = 0.0; $b < 1.0; $b += 0.05):
                            $key = number_format($b, 2); if (!isset($hist[$key])) continue; $n = $hist[$key];
                            $below = ($b + 0.05) <= $threshold + 1e-9; ?>
                            <div class="flex items-center gap-2 text-[11px]" title="Score <?= $key ?>–<?= number_format($b + 0.05, 2) ?>: <?= $n ?> scans">
                                <span class="w-20 tabular-nums text-slate-500"><?= $key ?>–<?= number_format($b + 0.05, 2) ?></span>
                                <div class="flex-1 h-2.5 rounded bg-slate-100 dark:bg-slate-800"><div class="h-2.5 rounded <?= $below ? 'bg-slate-400 dark:bg-slate-500' : 'bg-indigo-500' ?>" style="width: <?= round($n * 100 / $maxH, 1) ?>%"></div></div>
                                <span class="w-8 text-right tabular-nums text-slate-700 dark:text-slate-300"><?= $n ?></span>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="text-[11px] text-slate-400 mt-3">Grey bars = below the threshold, indigo = at or above it.</p>
    </div>

    <!-- Registered staff -->
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 p-5 shadow-sm">
        <h2 class="text-sm font-bold text-slate-800 dark:text-white">Registered people</h2>
        <p class="text-[11px] text-slate-500 mb-3">Per person in the selected period. "Near misses" = not-recognized scans where this person was the closest match with score 0.40 or higher.</p>
        <div class="overflow-x-auto">
        <table class="w-full text-xs min-w-[720px]">
            <thead><tr class="text-left text-[10px] uppercase tracking-wider text-slate-400 border-b border-slate-200 dark:border-slate-800">
                <th class="py-2 pr-3">Person</th><th class="py-2 pr-3">Registered</th><th class="py-2 pr-3 text-right">Photos</th>
                <th class="py-2 pr-3 text-right">OK scans</th><th class="py-2 pr-3 text-right">Avg score</th><th class="py-2 pr-3 text-right">Lowest</th>
                <th class="py-2 pr-3 text-right">Near misses</th><th class="py-2 pr-3">Last check-in</th><th class="py-2">Status / what to do</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
            <?php if (!$staff): ?><tr><td colspan="9" class="py-4 text-slate-400">No one is registered yet.</td></tr><?php endif; ?>
            <?php foreach ($staff as $st):
                $s = $st['stats'] ?? null;
                $okN = (int) ($s['ok'] ?? 0); $avg = $s['avg_score'] ?? null; $near = (int) ($s['near_miss'] ?? 0);
                if (!$st['last_check_in']) { $status = ['Never checked in', 'Ask them to scan. If it fails, re-register them at the kiosk tablet.', 'text-red-600 dark:text-red-400']; }
                elseif ($near > 0 && $okN === 0) { $status = ['Failing (near misses)', 'Re-register at the kiosk tablet, in its normal light.', 'text-red-600 dark:text-red-400']; }
                elseif ($avg !== null && (float) $avg < $threshold + 0.07) { $status = ['Low margin', 'Scores close to the threshold — re-register at the kiosk tablet.', 'text-amber-600 dark:text-amber-400']; }
                elseif ($near > 0) { $status = ['Some near misses', 'Usually fine; re-register at the kiosk if it keeps happening.', 'text-amber-600 dark:text-amber-400']; }
                else { $status = ['OK', '', 'text-emerald-600 dark:text-emerald-400']; } ?>
                <tr>
                    <td class="py-2 pr-3"><a class="font-semibold hover:underline" href="<?= e($qs(['user' => (int) $st['id'], 'outcome' => null, 'page' => null])) ?>"><?= e($st['name']) ?></a>
                        <div class="text-[10px] text-slate-400">ID <?= (int) $st['id'] ?><?= $st['employee_id'] ? ' · ' . e($st['employee_id']) : '' ?></div></td>
                    <td class="py-2 pr-3 tabular-nums"><?= $st['registered_at'] ? e(date('d M Y', strtotime($st['registered_at']))) : '—' ?></td>
                    <td class="py-2 pr-3 text-right tabular-nums"><?= (int) $st['vectors'] ?></td>
                    <td class="py-2 pr-3 text-right tabular-nums"><?= $okN ?></td>
                    <td class="py-2 pr-3 text-right tabular-nums"><?= $num($avg) ?></td>
                    <td class="py-2 pr-3 text-right tabular-nums"><?= $num($s['min_score'] ?? null) ?></td>
                    <td class="py-2 pr-3 text-right tabular-nums"><?= $near ?></td>
                    <td class="py-2 pr-3 tabular-nums"><?= $st['last_check_in'] ? e(date('d M, h:i A', strtotime($st['last_check_in']))) : '—' ?></td>
                    <td class="py-2"><span class="font-semibold <?= $status[2] ?>"><?= e($status[0]) ?></span><?php if ($status[1]): ?><div class="text-[10px] text-slate-500"><?= e($status[1]) ?></div><?php endif; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>

    <!-- Attempt list -->
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 p-5 shadow-sm">
        <div class="flex flex-wrap items-baseline justify-between gap-2 mb-3">
            <div>
                <h2 class="text-sm font-bold text-slate-800 dark:text-white">All attempts <span class="font-normal text-slate-400">(<?= $totalRows ?>)</span></h2>
                <p class="text-[11px] text-slate-500">Newest first. Attempts of one person in front of the kiosk share a session (retries are numbered 1–3). Click a row for full details.</p>
            </div>
            <?php if ($outcome !== '' || $userFilter): ?><a href="<?= e($qs(['outcome' => null, 'user' => null, 'page' => null])) ?>" class="text-xs text-indigo-600 hover:underline">Clear filters</a><?php endif; ?>
        </div>
        <div class="overflow-x-auto">
        <table class="w-full text-xs min-w-[900px]">
            <thead><tr class="text-left text-[10px] uppercase tracking-wider text-slate-400 border-b border-slate-200 dark:border-slate-800">
                <th class="py-2 pr-3">Photo</th><th class="py-2 pr-3">Time</th><th class="py-2 pr-3">Result</th><th class="py-2 pr-3">Try</th>
                <th class="py-2 pr-3">Person / closest match</th><th class="py-2 pr-3 text-right">Score</th><th class="py-2 pr-3 text-right">2nd best</th>
                <th class="py-2 pr-3 text-right">Sharpness</th><th class="py-2 pr-3 text-right">Face px</th><th class="py-2 pr-3 text-right">Face light</th><th class="py-2 text-right">Time ms</th>
            </tr></thead>
            <tbody class="text-slate-700 dark:text-slate-300">
            <?php if (!$rows): ?><tr><td colspan="11" class="py-4 text-slate-400">No attempts match these filters.</td></tr><?php endif; ?>
            <?php foreach ($rows as $r):
                $rid = (int) $r['id'];
                $meta = $r['client_meta'] ? json_decode($r['client_meta'], true) : null;
                $src = $imgUrl($r['image_path']);
                $who = $r['matched_name'] ?: $r['best_name'];
                $blurLow = $r['blur_score'] !== null && $r['blur_threshold'] !== null && (float) $r['blur_score'] < (float) $r['blur_threshold']; ?>
                <tr class="border-t border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/40 cursor-pointer" @click="open[<?= $rid ?>] = !open[<?= $rid ?>]">
                    <td class="py-1.5 pr-3">
                        <?php if ($src): ?>
                            <img src="<?= e($src) ?>" loading="lazy" alt="Scan photo" class="w-12 h-12 object-cover rounded-lg border border-slate-200 dark:border-slate-700" @click.stop="img = '<?= e($src) ?>'">
                        <?php else: ?><div class="w-12 h-12 rounded-lg bg-slate-100 dark:bg-slate-800 grid place-items-center text-[9px] text-slate-400 text-center leading-tight">no<br>photo</div><?php endif; ?>
                    </td>
                    <td class="py-1.5 pr-3 tabular-nums whitespace-nowrap"><?= e(date('d M, h:i:s A', strtotime($r['created_at']))) ?></td>
                    <td class="py-1.5 pr-3"><?= $badge((string) $r['outcome']) ?></td>
                    <td class="py-1.5 pr-3 tabular-nums"><?= $r['attempt'] ? (int) $r['attempt'] . '/3' : '—' ?></td>
                    <td class="py-1.5 pr-3"><?= $who ? e($who) : '<span class="text-slate-400">—</span>' ?>
                        <?php if (!$r['matched_name'] && $r['best_name']): ?><div class="text-[10px] text-slate-400">closest match only</div><?php endif; ?></td>
                    <td class="py-1.5 pr-3 text-right tabular-nums font-semibold"><?= $num($r['score']) ?></td>
                    <td class="py-1.5 pr-3 text-right tabular-nums"><?= $num($r['second_score']) ?></td>
                    <td class="py-1.5 pr-3 text-right tabular-nums <?= $blurLow ? 'text-amber-600 dark:text-amber-400 font-semibold' : '' ?>"><?= $num($r['blur_score'], 0) ?></td>
                    <td class="py-1.5 pr-3 text-right tabular-nums"><?= $r['face_width'] !== null ? (int) $r['face_width'] : '—' ?></td>
                    <td class="py-1.5 pr-3 text-right tabular-nums"><?= $num($r['face_brightness'], 0) ?></td>
                    <td class="py-1.5 text-right tabular-nums"><?= $r['processing_ms'] !== null ? (int) $r['processing_ms'] : '—' ?></td>
                </tr>
                <tr x-show="open[<?= $rid ?>]" x-cloak class="bg-slate-50 dark:bg-slate-800/30">
                    <td colspan="11" class="px-3 py-3 text-[11px] text-slate-600 dark:text-slate-300">
                        <div class="grid md:grid-cols-3 gap-4">
                            <div class="space-y-0.5">
                                <p class="font-semibold text-slate-800 dark:text-white mb-1">Result</p>
                                <p>Message: <?= e($r['message'] ?: '—') ?></p>
                                <p>Threshold: <?= $num($r['threshold']) ?> · Score gap to 2nd: <?= ($r['score'] !== null && $r['second_score'] !== null) ? $num((float) $r['score'] - (float) $r['second_score']) : '—' ?></p>
                                <?php if (!$isOk((string) $r['outcome']) && isset($outcomeHelp[$r['outcome']])): ?>
                                    <p class="mt-1 text-slate-500">What it means: <?= e($outcomeHelp[$r['outcome']]) ?></p>
                                <?php endif; ?>
                                <p>Session: <span class="font-mono"><?= e($r['session_id'] ?: '—') ?></span></p>
                            </div>
                            <div class="space-y-0.5">
                                <p class="font-semibold text-slate-800 dark:text-white mb-1">Image quality</p>
                                <p>Sharpness (face): <?= $num($r['blur_score'], 1) ?> (limit <?= $num($r['blur_threshold'], 0) ?>)</p>
                                <p>Face brightness / contrast: <?= $num($r['face_brightness'], 0) ?> / <?= $num($r['face_contrast'], 1) ?></p>
                                <p>Frame brightness: <?= $num($r['brightness'], 0) ?></p>
                                <p>Detector confidence: <?= $num($r['det_score']) ?> · Head turn (yaw): <?= $num($r['yaw']) ?> · Tilt (pitch): <?= $num($r['pitch']) ?></p>
                                <p>Image received: <?= $r['img_width'] ? (int) $r['img_width'] . '×' . (int) $r['img_height'] : '—' ?> · Face width: <?= $r['face_width'] !== null ? (int) $r['face_width'] . ' px' : '—' ?></p>
                            </div>
                            <div class="space-y-0.5">
                                <p class="font-semibold text-slate-800 dark:text-white mb-1">Kiosk camera</p>
                                <?php $cam = is_array($meta['cam'] ?? null) ? $meta['cam'] : []; $capi = is_array($meta['cap'] ?? null) ? $meta['cap'] : []; ?>
                                <p>Camera frame: <?= e($capi['frame'] ?? (($cam['w'] ?? '?') . '×' . ($cam['h'] ?? '?'))) ?> · max <?= e($cam['maxRes'] ?? '—') ?></p>
                                <p>Auto-zoom crop: <?= isset($capi['crop']) && is_array($capi['crop']) ? e(implode(', ', array_map('intval', $capi['crop']))) : '—' ?> → <?= e($capi['out'] ?? '—') ?></p>
                                <p>Face on tablet: <?= isset($capi['face']) && is_array($capi['face']) ? e(implode('×', array_map('intval', $capi['face']))) . ' px' : '—' ?> · tablet detector <?= e(isset($capi['conf']) ? (string) $capi['conf'] : '—') ?></p>
                                <p>Focus: <?= e(is_array($cam['focus'] ?? null) ? (implode(', ', $cam['focus']) ?: 'not adjustable') : '—') ?> · Zoom: <?= e($cam['zoom'] ?? 'none') ?></p>
                                <p>Exposure comp: <?= e($cam['expComp'] ?? 'not supported') ?> · Face metering: <?= !empty($cam['poi']) ? 'yes' : 'no' ?><?= isset($cam['faceLuma']) ? ' · face light on tablet ' . (int) $cam['faceLuma'] : '' ?></p>
                                <p class="text-slate-400 break-all">IP <?= e($r['ip_address'] ?: '—') ?> · <?= e(mb_strimwidth((string) $r['user_agent'], 0, 90, '…')) ?></p>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>

        <?php if ($pages > 1): ?>
            <div class="flex items-center justify-between mt-4 text-xs">
                <span class="text-slate-500">Page <?= $page ?> of <?= $pages ?></span>
                <div class="flex gap-2">
                    <?php if ($page > 1): ?><a href="<?= e($qs(['page' => $page - 1])) ?>" class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800">← Newer</a><?php endif; ?>
                    <?php if ($page < $pages): ?><a href="<?= e($qs(['page' => $page + 1])) ?>" class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800">Older →</a><?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        <p class="text-[11px] text-slate-400 mt-3">Photos of failed scans are deleted automatically after 7 days. Photos of successful scans are the attendance snapshots.</p>
    </div>

    <!-- Image viewer -->
    <div x-show="img" x-cloak @click="img = null" @keydown.escape.window="img = null"
         class="fixed inset-0 z-50 bg-black/80 grid place-items-center p-4">
        <img :src="img" alt="Scan photo (enlarged)" class="max-h-[85vh] max-w-full rounded-xl shadow-2xl">
    </div>
</div>
<?php
$content = ob_get_clean();
?>
