<?php
use App\Services\PearlReportCard as RC;

$layout    = 'app';
$pageTitle = 'Report Card';
$name = trim($student['first_name'] . ' ' . $student['last_name']);
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/academics'],
    ['label' => 'Report Cards', 'url' => '/report-cards'],
    ['label' => $name],
];
ob_start();

$isLocked = is_year_locked($academicYear);
$dis = $isLocked ? 'disabled' : '';
$tables = RC::tables();
$att = $reportCard['attendance_profile'] ?? [];
$academic = $reportCard['academic_profile'] ?? [];
$meta = $reportCard['authorized_by'] ?? [];
$q = 'semester=' . urlencode($semester) . '&academic_year=' . urlencode($academicYear);

$feedbackDate = '';
if (!empty($meta['feedback_date']) && preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', $meta['feedback_date'], $dm)) {
    $feedbackDate = "$dm[3]-$dm[2]-$dm[1]";
}

$inp = 'px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500';

/** Grade select + prompt box for one rating row. */
$ratingRow = function (string $store, string $key, string $label) use ($reportCard, $inp, $dis): string {
    $cell = $reportCard[$store][$key] ?? [];
    $g = (string) ($cell['g'] ?? '');
    $opts = '<option value="">—</option>';
    foreach (RC::RATINGS as $r) {
        $opts .= '<option value="' . $r . '"' . ($g === $r ? ' selected' : '') . '>' . $r . '</option>';
    }
    $n = $store . '[' . $key . ']';
    return '<div class="grid grid-cols-[1fr_5.5rem_6.5rem] items-center gap-2 py-1.5 border-b border-slate-100 dark:border-slate-800/60 last:border-0">'
        . '<span class="text-xs font-semibold text-slate-700 dark:text-slate-300">' . e($label) . '</span>'
        . '<select name="' . e($n) . '[g]" ' . $dis . ' class="' . $inp . ' font-bold text-center">' . $opts . '</select>'
        . '<input type="text" name="' . e($n) . '[p]" value="' . e(implode(',', (array) ($cell['p'] ?? []))) . '" ' . $dis . ' placeholder="prompt" list="rc-prompts" class="' . $inp . ' uppercase font-mono">'
        . '</div>';
};

/** A section card with all rows of one or more template tables. */
$section = function (string $title, string $page, array $tableKeys) use ($tables, $ratingRow): string {
    $html = '<div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 shadow-sm">'
        . '<div class="flex items-baseline justify-between mb-2"><h3 class="text-sm font-bold text-slate-900 dark:text-white">' . e($title) . '</h3>'
        . '<span class="text-[10px] text-slate-400">' . e($page) . '</span></div>'
        . '<div class="grid grid-cols-[1fr_5.5rem_6.5rem] gap-2 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400"><span></span><span class="text-center">Grade</span><span>Prompt</span></div>';
    foreach ($tableKeys as $tk) {
        foreach ($tables[$tk]['rows'] as [$key, $label]) {
            $html .= $key === 'group'
                ? '<div class="pt-3 pb-1 text-[11px] font-bold underline text-slate-600 dark:text-slate-400">' . e($label) . '</div>'
                : $ratingRow($tables[$tk]['store'], $key, $label);
        }
    }
    return $html . '</div>';
};
?>

<datalist id="rc-prompts">
    <?php foreach (RC::PROMPTS as $code => $desc): ?><option value="<?= $code ?>"><?= e($desc) ?></option><?php endforeach; ?>
    <option value="VP,GP">Verbal + Gestural</option>
</datalist>

<div class="max-w-6xl mx-auto space-y-5 py-2">

    <!-- Header -->
    <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 shadow-sm space-y-4">
        <?php if ($isLocked): ?>
            <div class="p-3 rounded-xl border border-amber-500/20 bg-amber-500/10 text-amber-600 text-xs font-bold">🔒 This academic year is locked. Report card updates are frozen.</div>
        <?php endif; ?>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white"><?= e(mb_strtoupper($name)) ?></h2>
                <p class="text-xs text-slate-500 mt-0.5">Std: <span class="font-bold text-slate-700 dark:text-slate-300"><?= e($standard ?: '—') ?></span>
                    · <?= e($semester) ?> · <?= e($academicYear) ?></p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <?php if ($reportCard['id']): ?>
                    <a href="<?= url('students/' . (int) $student['id'] . '/report-card/view?' . $q) ?>" target="_blank" class="px-3.5 py-2 rounded-xl text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200">Preview</a>
                    <a href="<?= url('students/' . (int) $student['id'] . '/report-card/view?' . $q . '&pdf=1') ?>" class="px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500">Download PDF</a>
                <?php endif; ?>
                <a href="<?= url('report-cards?' . $q) ?>" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300">All Students</a>
            </div>
        </div>
        <form method="GET" class="flex flex-wrap items-end gap-3 text-xs">
            <label class="space-y-1">
                <span class="block font-bold text-slate-400 uppercase tracking-wider">Academic Year</span>
                <select name="academic_year" class="<?= $inp ?> w-36">
                    <?php $years = array_column($yearsList, 'year_name'); if (!in_array($academicYear, $years, true)) array_unshift($years, $academicYear); ?>
                    <?php foreach ($years as $y): ?><option value="<?= e($y) ?>" <?= $y === $academicYear ? 'selected' : '' ?>><?= e($y) ?></option><?php endforeach; ?>
                </select>
            </label>
            <label class="space-y-1">
                <span class="block font-bold text-slate-400 uppercase tracking-wider">Semester</span>
                <select name="semester" class="<?= $inp ?> w-36">
                    <?php foreach (['Semester 1', 'Semester 2'] as $s): ?><option value="<?= $s ?>" <?= $s === $semester ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?>
                </select>
            </label>
            <button class="px-4 py-1.5 rounded-lg bg-slate-800 text-white font-bold hover:bg-slate-700">Load</button>
            <span class="text-slate-400 ml-auto">Grades: A Excellent · B Good · C Need To Improve · NA / AB / NS. Prompt: OP VP GP MP PP PR PRW PTL SWL (e.g. <span class="font-mono">VP,GP</span>)</span>
        </form>
    </div>

    <form method="POST" action="<?= url('students/' . (int) $student['id'] . '/report-card') ?>" class="space-y-5">
        <?= \Core\View::csrf() ?>
        <input type="hidden" name="academic_year" value="<?= e($academicYear) ?>">
        <input type="hidden" name="semester" value="<?= e($semester) ?>">

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 items-start">
            <?= $section('Routine Profile', 'Pages 2–3', ['routine_1', 'routine_2']) ?>
            <div class="space-y-5">
                <?= $section('Learning Skills Profile', 'Pages 4–5', ['learning_1', 'learning_2']) ?>
            </div>
        </div>

        <!-- Attendance (page 5) -->
        <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 shadow-sm">
            <div class="flex items-baseline justify-between mb-3"><h3 class="text-sm font-bold text-slate-900 dark:text-white">Attendance Profile</h3><span class="text-[10px] text-slate-400">Page 5</span></div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                <?php foreach ([
                    ['present_days', 'Present Days', 'number', $attendanceAuto['present_days'] ?? ''],
                    ['working_days', 'Working Days', 'number', $attendanceAuto['working_days'] ?? ''],
                    ['punctuality', 'Punctuality', 'text', ''],
                    ['combine_day_presence', 'Combine Day Presence', 'text', ''],
                ] as [$k, $lbl, $type, $auto]):
                    $val = (string) ($att[$k] ?? '');
                    $useAuto = !$reportCard['id'] && $val === '' && $auto !== '';
                ?>
                    <label class="space-y-1">
                        <span class="block font-bold text-slate-500"><?= $lbl ?></span>
                        <input type="<?= $type ?>" min="0" name="attendance_profile[<?= $k ?>]" value="<?= e($useAuto ? $auto : $val) ?>" <?= $dis ?> class="<?= $inp ?> w-full">
                        <?php if ($auto !== ''): ?><span class="block text-[10px] text-indigo-500">From records: <?= e($auto) ?></span><?php endif; ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Academic subjects (page 6) -->
        <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 shadow-sm overflow-x-auto">
            <div class="flex items-baseline justify-between mb-1"><h3 class="text-sm font-bold text-slate-900 dark:text-white">Academic Subjects Profile</h3><span class="text-[10px] text-slate-400">Page 6</span></div>
            <p class="text-[11px] text-slate-500 mb-3">Enter marks, or AB (Absent) / NS (Not Submitted) / NA. % and Grade are filled automatically once all three columns have marks.</p>
            <table class="w-full text-xs min-w-[640px]">
                <thead>
                    <tr class="text-[10px] uppercase tracking-wider text-slate-400 text-left">
                        <th class="py-2 pr-3">Subject</th>
                        <?php foreach (RC::ACADEMIC_COLUMNS as [$lbl]): ?><th class="py-2 pr-3"><?= e($lbl) ?></th><?php endforeach; ?>
                        <th class="py-2 pr-3 text-center">%</th><th class="py-2 text-center">Grade</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach (RC::ACADEMIC_SUBJECTS as $sub => $subLabel): ?>
                    <?php $init = []; foreach (RC::ACADEMIC_COLUMNS as $col => $_) { $init[$col] = (string) ($academic[$sub][$col]['v'] ?? ''); } ?>
                    <tr class="border-t border-slate-100 dark:border-slate-800/60" x-data="rcSubject(<?= e(json_encode($init)) ?>)">
                        <td class="py-2 pr-3 font-semibold text-slate-700 dark:text-slate-300"><?= e($subLabel) ?></td>
                        <?php foreach (RC::ACADEMIC_COLUMNS as $col => [, $max]): $c = $academic[$sub][$col] ?? []; ?>
                            <td class="py-2 pr-3">
                                <div class="flex gap-1.5">
                                    <input type="text" name="academic_profile[<?= $sub ?>][<?= $col ?>][v]" value="<?= e($c['v'] ?? '') ?>" <?= $dis ?>
                                           x-model="v.<?= $col ?>" placeholder="/<?= $max ?>" list="rc-mark-codes"
                                           class="<?= $inp ?> w-16 text-center font-bold uppercase">
                                    <input type="text" name="academic_profile[<?= $sub ?>][<?= $col ?>][p]" value="<?= e(implode(',', (array) ($c['p'] ?? []))) ?>" <?= $dis ?>
                                           placeholder="prompt" list="rc-prompts" class="<?= $inp ?> w-20 uppercase font-mono">
                                </div>
                            </td>
                        <?php endforeach; ?>
                        <td class="py-2 pr-3 text-center font-mono font-bold" x-text="result().pct || '—'"></td>
                        <td class="py-2 text-center font-bold text-indigo-600" x-text="result().grade || '—'"></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <datalist id="rc-mark-codes"><option value="AB">Absent</option><option value="NS">Not Submitted</option><option value="NA">Not Applicable</option></datalist>
            <div class="mt-4 max-w-md">
                <?php $hw = $academic['homework'] ?? []; ?>
                <div class="grid grid-cols-[1fr_5.5rem_6.5rem] items-center gap-2">
                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Homework</span>
                    <select name="academic_profile[homework][g]" <?= $dis ?> class="<?= $inp ?> font-bold text-center">
                        <option value="">—</option>
                        <?php foreach (RC::RATINGS as $r): ?><option value="<?= $r ?>" <?= ($hw['g'] ?? '') === $r ? 'selected' : '' ?>><?= $r ?></option><?php endforeach; ?>
                    </select>
                    <input type="text" name="academic_profile[homework][p]" value="<?= e(implode(',', (array) ($hw['p'] ?? []))) ?>" <?= $dis ?> placeholder="prompt" list="rc-prompts" class="<?= $inp ?> uppercase font-mono">
                </div>
            </div>
        </div>

        <?= $section('Co-Curriculum Profile and Towards Independence of Life Skills', 'Pages 7–8', ['cocurricular_1', 'cocurricular_2']) ?>

        <!-- Overall feedback (page 11) -->
        <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 shadow-sm space-y-3">
            <div class="flex items-baseline justify-between"><h3 class="text-sm font-bold text-slate-900 dark:text-white">Overall Feedback</h3><span class="text-[10px] text-slate-400">Page 11</span></div>
            <label class="block space-y-1 text-xs max-w-xs">
                <span class="block font-bold text-slate-500">Date</span>
                <input type="date" name="feedback_date" value="<?= e($feedbackDate) ?>" <?= $dis ?> class="<?= $inp ?> w-full">
            </label>
            <textarea name="feedback_text" rows="4" maxlength="2000" <?= $dis ?> placeholder="Overall, good performance. Improvement is required in…"
                      class="<?= $inp ?> w-full text-sm leading-relaxed"><?= e($reportCard['feedback_text'] ?? '') ?></textarea>
            <p class="text-[11px] text-slate-500">Trustee signatures, stamp and names come from <a href="<?= url('report-cards/settings') ?>" class="text-indigo-500 underline">Report Card Settings</a>.</p>
        </div>

        <?php if (!$isLocked): ?>
        <div class="sticky bottom-3 z-10 flex justify-end gap-2 p-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white/90 dark:bg-slate-900/90 backdrop-blur shadow-lg">
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-sm font-bold">Save</button>
            <button type="submit" name="after_save" value="preview" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-bold">Save &amp; Preview</button>
        </div>
        <?php endif; ?>
    </form>
</div>

<script>
function rcSubject(initial) {
    const max = <?= json_encode(array_map(fn($c) => $c[1], RC::ACADEMIC_COLUMNS)) ?>;
    return {
        v: Object.assign({}, initial),
        result() {
            let got = 0, total = 0;
            for (const k in max) {
                const n = (this.v[k] ?? '').toString().trim();
                if (n === '' || isNaN(n)) return { pct: '', grade: '' };
                got += Math.min(parseFloat(n), max[k]); total += max[k];
            }
            const pct = Math.round(got / total * 10000) / 100;
            const scale = [[91,'A+'],[81,'A'],[71,'B+'],[61,'B'],[51,'C+'],[41,'C'],[33,'D'],[21,'E']];
            const g = (scale.find(([m]) => pct >= m) || [0, 'F'])[1];
            return { pct: pct.toFixed(2), grade: g };
        }
    };
}
</script>

<?php
$content = ob_get_clean();
?>
