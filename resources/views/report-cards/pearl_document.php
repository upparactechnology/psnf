<?php
/**
 * Pearl School report card — 11 A4 pages, mirroring the printed PSNF card.
 * Rendered standalone (no app layout) for the browser preview and for Dompdf.
 *
 * In scope: $student, $card, $semester, $academicYear, $settings, $forPdf, $toolbar
 *   $card = decoded student_report_cards row (routine_profile, learning_skills,
 *           academic_profile, cocurriculum_profile, attendance_profile,
 *           feedback_text, authorized_by) + 'semester_range' ("JUN. to NOV.")
 */
use App\Services\PearlReportCard as RC;

$h = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

// Printed forms: "2026-27", "Semester 1"
$academicYear = RC::displayYear($academicYear);
$semester     = RC::displaySemester($semester);

$studentName = mb_strtoupper(trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')));
$standard    = RC::standardFor($student);
$semNumber   = preg_match('/(\d+)/', $semester, $m) ? $m[1] : '';
$semRange    = (string) ($card['semester_range'] ?? '');
$att         = $card['attendance_profile'] ?? [];
$academic    = $card['academic_profile'] ?? [];
$meta        = $card['authorized_by'] ?? [];
$feedback    = trim((string) ($card['feedback_text'] ?? ''));
$tables      = RC::tables();
$trustees    = RC::trustees($settings);
$stampText   = trim((string) ($settings['stamp_text'] ?? '')) ?: 'Pearl Special Needs Foundation';
$stampImage  = RC::uploadedImage($settings['stamp_image'] ?? '');

/** Value + prompt markup for a rating / marks cell. */
$valueHtml = static function (string $value, array $prompts, bool $redCodes = true) use ($h): string {
    $cls = ($redCodes && in_array($value, ['AB', 'NS'], true)) ? ' class="code"' : '';
    $out = $value !== '' ? '<span' . $cls . '>' . $h($value) . '</span>' : '';
    if ($prompts) {
        $out .= ($out !== '' ? '<br>' : '') . $h(RC::promptText($prompts));
    }
    return $out;
};

$page = 0;
$pageStart = static function () use (&$page): string {
    $page++;
    return '<div class="page"><div class="frame-outer"></div><div class="frame-inner"></div><div class="content">';
};
$pageEnd = static function () use (&$page, $academicYear, $h): string {
    return '</div><div class="footer"><div class="footer-rule"></div><div class="footer-rule-thin"></div>'
        . '<table class="footer-row"><tr>'
        . '<td class="f-left">PSNF &ndash; PEARL SCHOOL REPORT CARD</td>'
        . '<td class="f-mid">' . $h($academicYear) . '</td>'
        . '<td class="f-right">Page <b>' . $page . '</b> of <b>' . RC::TOTAL_PAGES . '</b></td>'
        . '</tr></table></div></div>';
};

/** A centred two-column rating table (Routine / Learning / Co-curriculum pages). */
$ratingTable = static function (string $key, float $rowH, float $headH) use ($tables, $card, $semester, $valueHtml, $h): string {
    $t = $tables[$key];
    $data = $card[$t['store']] ?? [];
    $html = '<table class="grid rating">';
    if ($t['column'] !== null) {
        $html .= '<tr><th style="height:' . $headH . 'mm">' . $h($t['column']) . '</th><th>' . $h($semester) . '</th></tr>';
    }
    foreach ($t['rows'] as [$rowKey, $label]) {
        if ($rowKey === 'group') {
            $html .= '<tr class="group"><td class="lbl" style="height:' . ($rowH * 0.62) . 'mm"><u>' . $h($label) . '</u></td><td class="val no-bottom"></td></tr>';
            continue;
        }
        $cell = $data[$rowKey] ?? [];
        $html .= '<tr><td class="lbl" style="height:' . $rowH . 'mm">' . $h($label) . '</td><td class="val">'
            . $valueHtml((string) ($cell['g'] ?? ''), (array) ($cell['p'] ?? [])) . '</td></tr>';
    }
    return $html . '</table>';
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= $h('Report Card – ' . $studentName . ' – ' . $semester . ' ' . $academicYear) ?></title>
<style>
    @page { size: A4 portrait; margin: 0; }
    html, body { margin: 0; padding: 0; }
    body { font-family: Helvetica, Arial, sans-serif; color: #000; font-size: 10.5pt; }

    .page { position: relative; width: 210mm; height: 296mm; overflow: hidden; page-break-after: always; background: #fff; }
    .page.last { page-break-after: auto; }
    .frame-outer { position: absolute; left: 6.5mm; top: 6.5mm; width: 195.5mm; height: 281.5mm; border: 2.2px solid #8a8a8a; }
    .frame-inner { position: absolute; left: 7.6mm; top: 7.6mm; width: 193.2mm; height: 279.2mm; border: 1.6px solid #000; }
    .content { position: absolute; left: 17mm; top: 15mm; width: 176mm; height: 255mm; }

    .footer { position: absolute; left: 13mm; top: 278mm; width: 184mm; }
    .footer-rule { border-top: 2.6px solid #7b2d26; }
    .footer-rule-thin { border-top: 0.6px solid #7b2d26; margin-top: 0.6mm; }
    .footer-row { width: 100%; border-collapse: collapse; font-family: 'Times New Roman', Times, serif; font-size: 8pt; }
    .footer-row td { padding: 0.6mm 2mm 0 2mm; }
    .f-left { text-align: left; width: 40%; }
    .f-mid { text-align: center; width: 20%; }
    .f-right { text-align: right; width: 40%; }

    .sec-title { font-weight: bold; text-decoration: underline; font-size: 10.5pt; margin: 0 0 4mm 0; }
    .grid { border-collapse: collapse; margin-left: auto; margin-right: auto; }
    .grid th, .grid td { border: 0.9px solid #000; text-align: center; vertical-align: middle; font-weight: bold; padding: 1mm 2mm; font-size: 10pt; }
    .rating { width: 108mm; }
    .rating td.lbl, .rating th { width: 50%; }
    .rating tr.group td.lbl { vertical-align: bottom; padding-bottom: 0; border-bottom: 0; }
    .rating td.no-bottom { border-bottom: 0; }
    .code { color: #e00000; }

    /* Cover */
    .cover { position: relative; width: 210mm; height: 296.9mm; overflow: hidden; page-break-after: always; }
    .cover img.bg { position: absolute; left: 0; top: 0; width: 210mm; height: 296.9mm; }
    .cover .fill { position: absolute; font-style: italic; color: #111; text-align: center; font-size: 18pt; white-space: nowrap; }
    .cover .name { left: 108mm; top: 211.5mm; width: 96mm; }
    .cover .std { left: 95mm; top: 227.5mm; width: 109mm; font-size: 16pt; }
    .cover .year { position: absolute; left: 166.5mm; top: 247mm; color: #fff; font-weight: bold; font-size: 23pt; letter-spacing: 1.2pt; white-space: nowrap; }

    /* Page 2 heading */
    .report-title { text-align: center; font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 15pt; text-decoration: underline; margin: 2mm 0 7mm 0; }
    .year-row { width: 100%; border-collapse: collapse; font-size: 10.5pt; font-weight: bold; margin-bottom: 6mm; }
    .year-row td { padding: 0; }
    .year-row .r { text-align: right; }

    /* Academic page */
    .academic { width: 162mm; }
    .academic th { font-size: 9.5pt; }
    .academic td.subj { text-align: left; padding-left: 3mm; width: 22mm; }
    .homework { width: 90mm; margin-top: 15mm; }

    /* Attendance */
    .attendance { width: 80mm; }
    .attendance td, .attendance th { padding: 1mm 1.5mm; }

    /* Notes & keys (pages 9–10) */
    .motto { text-align: center; font-weight: bold; font-size: 13.5pt; line-height: 1.45; margin: 0 0 3mm 0; }
    .notes-title { font-weight: bold; text-decoration: underline; font-size: 10.5pt; margin: 0 0 2mm 0; }
    .notes { margin: 0 0 4mm 8mm; padding: 0 0 0 4mm; font-weight: bold; font-size: 10.5pt; line-height: 1.45; }
    .notes li { margin-bottom: 1.6mm; }
    .key-title { text-align: center; font-weight: bold; text-decoration: underline; font-size: 10.5pt; margin: 3mm 0 3mm 0; }
    .keys { width: 140mm; border-collapse: collapse; margin: 0 auto; font-size: 10.5pt; }
    .keys th, .keys td { border: 0.9px solid #000; padding: 1mm 1.6mm; vertical-align: top; text-align: left; font-weight: normal; }
    .keys th { text-align: center; font-weight: bold; padding: 2.5mm 1.6mm; }
    .keys td.k { font-weight: bold; width: 9mm; }
    .keys td.n { width: 38mm; }
    .scale { border-collapse: collapse; font-size: 10.5pt; }
    .scale th, .scale td { border: 0.9px solid #000; padding: 0 2mm; text-align: center; }
    .scale th { font-weight: bold; }
    .scale td.g { font-weight: bold; width: 14mm; }
    .scale td.s { width: 28mm; }

    /* Page 11 */
    .fb-head { width: 100%; border-collapse: collapse; font-weight: bold; font-size: 10.5pt; margin: 6mm 0 3mm 0; }
    .fb-head td { padding: 0; }
    .fb-box { position: relative; height: 38mm; }
    .fb-rule { position: absolute; left: 0; width: 176mm; border-top: 1.6px solid #000; }
    .fb-text { position: absolute; left: 2mm; top: 0; width: 172mm; font-size: 12pt; line-height: 8.4mm; }
    .sig-label { font-weight: bold; font-size: 10.5pt; margin: 14mm 0 1mm 2mm; }
    .sig-line { border-top: 1.6px solid #000; width: 178mm; }
    .sig-roles { width: 100%; border-collapse: collapse; font-weight: bold; font-size: 10.5pt; margin-top: 1.5mm; }
    .auth { width: 100%; border-collapse: collapse; margin-top: 4mm; text-align: center; font-weight: bold; font-size: 10.5pt; }
    .auth td { width: 33.3%; vertical-align: bottom; }
    .auth img { max-height: 16mm; max-width: 40mm; }
    .auth .sig-cell { height: 19mm; }
    .auth-line { border-top: 1.6px solid #000; margin: 1mm 4mm 2mm 4mm; }
    .quote { font-style: italic; font-weight: bold; color: #1a9aa0; font-size: 13pt; line-height: 1.45; margin: 34mm 0 0 6mm; width: 160mm; }
    .quote .brand { color: #6b1f1f; }
    .quote img { height: 4mm; }
<?php if (!$forPdf): ?>
    @media screen {
        body { background: #e5e7eb; padding: 64px 0 24px; }
        .page, .cover { margin: 0 auto 16px; box-shadow: 0 4px 18px rgba(0,0,0,.18); }
        .toolbar { position: fixed; top: 0; left: 0; right: 0; z-index: 10; display: flex; gap: 8px; align-items: center; justify-content: center; flex-wrap: wrap;
                   padding: 10px 16px; background: #0f172a; color: #fff; font-family: system-ui, sans-serif; font-size: 13px; }
        .toolbar a, .toolbar button { color: #fff; background: #334155; border: 0; border-radius: 8px; padding: 7px 14px; text-decoration: none; font-weight: 600; cursor: pointer; font-size: 13px; }
        .toolbar .primary { background: #059669; }
        .toolbar .title { font-weight: 700; margin-right: 8px; }
    }
    @media print {
        body { background: #fff; padding: 0; }
        .toolbar { display: none; }
        .page, .cover { margin: 0; box-shadow: none; }
    }
<?php endif; ?>
</style>
</head>
<body>
<?php if (!$forPdf && $toolbar): ?>
<div class="toolbar">
    <span class="title"><?= $h($studentName) ?> · <?= $h($semester) ?> · <?= $h($academicYear) ?></span>
    <?php foreach ($toolbar as $btn): ?>
        <?php if (($btn['type'] ?? '') === 'print'): ?>
            <button type="button" onclick="window.print()"><?= $h($btn['label']) ?></button>
        <?php else: ?>
            <a href="<?= $h($btn['url']) ?>" class="<?= !empty($btn['primary']) ? 'primary' : '' ?>"><?= $h($btn['label']) ?></a>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ═══ Page 1: Cover ═══ -->
<?php $page++; ?>
<div class="cover">
    <img class="bg" src="<?= RC::asset('cover.jpg') ?>" alt="">
    <div class="fill name"><?= $h($studentName) ?></div>
    <div class="fill std"><?= $h($standard) ?></div>
    <div class="year"><?= $h($academicYear) ?></div>
</div>

<!-- ═══ Page 2: Routine Profile ═══ -->
<?= $pageStart() ?>
    <div class="report-title">PEARL SCHOOL PROGRESS REPORT</div>
    <table class="year-row"><tr>
        <td><u>Assessment Year: <?= $h($academicYear) ?></u></td>
        <td class="r"><u>Semester-<?= $h($semNumber) ?></u>&nbsp;&nbsp;<?= $semRange !== '' ? '<u>' . $h(explode(' to ', $semRange)[0]) . '</u> to <u>' . $h(explode(' to ', $semRange)[1] ?? '') . '</u>' : '' ?></td>
    </tr></table>
    <div class="sec-title">&nbsp;Routine Profile</div>
    <div style="height:3mm"></div>
    <?= $ratingTable('routine_1', 18.5, 18.5) ?>
<?= $pageEnd() ?>

<!-- ═══ Page 3: Routine Profile Continued ═══ -->
<?= $pageStart() ?>
    <div class="sec-title" style="text-decoration:none; margin-top:6mm">Routine Profile Continued</div>
    <div style="height:8mm"></div>
    <?= $ratingTable('routine_2', 14.4, 18) ?>
<?= $pageEnd() ?>

<!-- ═══ Page 4: Learning Skills Profile ═══ -->
<?= $pageStart() ?>
    <div class="sec-title" style="margin-top:6mm">Learning Skills Profile</div>
    <div style="height:6mm"></div>
    <?= $ratingTable('learning_1', 14.2, 15) ?>
<?= $pageEnd() ?>

<!-- ═══ Page 5: Learning Skills Continued + Attendance ═══ -->
<?= $pageStart() ?>
    <div class="sec-title" style="margin-top:6mm">Learning Skills Profile Continued</div>
    <div style="height:3mm"></div>
    <?= $ratingTable('learning_2', 15.6, 18) ?>
    <div style="height:18mm"></div>
    <div class="sec-title">Attendance Profile</div>
    <div style="height:3mm"></div>
    <table class="grid attendance">
        <tr><th style="height:14mm; width:34mm">Presence</th><th colspan="2"><?= $h($semester) ?></th></tr>
        <tr>
            <td rowspan="2">Attendance</td>
            <td style="height:10mm">Present<br>Days</td><td>Working<br>Days</td>
        </tr>
        <tr><td style="height:10mm"><?= $h($att['present_days'] ?? '') ?></td><td><?= $h($att['working_days'] ?? '') ?></td></tr>
        <tr><td style="height:17mm">Punctuality</td><td colspan="2"><?= $h($att['punctuality'] ?? '') ?></td></tr>
        <tr><td style="height:17mm">Combine Day<br>Presence</td><td colspan="2"><?= $h($att['combine_day_presence'] ?? '') ?></td></tr>
    </table>
<?= $pageEnd() ?>

<!-- ═══ Page 6: Academic Subjects Profile ═══ -->
<?= $pageStart() ?>
    <div class="sec-title" style="margin-top:6mm">Academic Subjects Profile</div>
    <div style="height:8mm"></div>
    <table class="grid academic">
        <tr><th colspan="6" style="height:10mm"><?= $h($semester) ?></th></tr>
        <tr>
            <th style="height:27mm">Subject</th>
            <?php foreach (RC::ACADEMIC_COLUMNS as [$label]): ?>
                <th style="width:24mm"><?= str_replace('/ ', '/<br>', $h($label)) ?></th>
            <?php endforeach; ?>
            <th style="width:24mm">%</th><th style="width:24mm">Grade</th>
        </tr>
        <?php foreach (RC::ACADEMIC_SUBJECTS as $subKey => $subLabel):
            $sub = $academic[$subKey] ?? [];
            $res = RC::subjectResult($sub);
            $label = $subKey === 'english'
                ? 'English<br>[Includes<br>English-1<br>English-2<br>English-3]'
                : ($subKey === 'gk' ? 'General<br>Knowledge<br>/ EVS' : $h($subLabel));
        ?>
        <tr>
            <td class="subj" style="height:38mm"><?= $label ?></td>
            <?php foreach (RC::ACADEMIC_COLUMNS as $col => $_): ?>
                <td><?= $valueHtml((string) ($sub[$col]['v'] ?? ''), (array) ($sub[$col]['p'] ?? [])) ?></td>
            <?php endforeach; ?>
            <td><?= $h($res['pct']) ?></td>
            <td><?= $h($res['grade']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <table class="grid homework">
        <tr><td style="height:17mm; width:50%">Homework</td>
            <td><?= $valueHtml((string) ($academic['homework']['g'] ?? ''), (array) ($academic['homework']['p'] ?? [])) ?></td></tr>
    </table>
<?= $pageEnd() ?>

<!-- ═══ Page 7: Co-Curriculum ═══ -->
<?= $pageStart() ?>
    <div class="sec-title" style="margin-top:4mm"><?= $h($tables['cocurricular_1']['title']) ?></div>
    <div style="height:3mm"></div>
    <?= $ratingTable('cocurricular_1', 12.3, 13) ?>
<?= $pageEnd() ?>

<!-- ═══ Page 8: Co-Curriculum Continued ═══ -->
<?= $pageStart() ?>
    <div class="sec-title" style="margin-top:4mm">Co-Curriculum Profile and Towards Independence of Life Skills&nbsp; Continued</div>
    <div style="height:3mm"></div>
    <?= $ratingTable('cocurricular_2', 13, 13) ?>
<?= $pageEnd() ?>

<!-- ═══ Page 9: Notes & Support key ═══ -->
<?= $pageStart() ?>
    <div class="motto">Most of us want the results now but getting valuable results takes efforts<br>from both the parents and the professionals.</div>
    <div class="notes-title">Important Note:-</div>
    <ul class="notes">
        <li>The progress report is designed primarily to report the progress of the student both quantitatively and qualitatively.</li>
        <li>SEP [Semester Educational Plan] should be referred for the subject specific topics learnt by the student periodically.</li>
        <li>The progress report will be sent to the parents periodically for their perusal and signature. Please ensure that the same is returned to the school on time.</li>
        <li>Refer to the evaluation key to understand the grading system.</li>
    </ul>
    <div class="key-title">EVALUATION KEY</div>
    <div class="key-title" style="margin-top:0">Key- Support Provided To The Student</div>
    <table class="keys">
        <tr><th colspan="2">Support Provided (Key)</th><th>Prompts Description</th></tr>
        <?php foreach ([
            ['OP', 'Occasional Prompt', 'Gentle guidance is provided occasionally to help the student to complete a portion of the questions.'],
            ['VP', 'Verbal Prompt', 'A verbal instruction is provided directly or indirectly that gives a hint about the next step or tells the student exactly what to do.'],
            ['GP', 'Gestural Prompt', 'Instructor uses gestures, e.g. pointing, tapping, or head nod, to move towards the answer or next step.'],
            ['MP', 'Modeling Prompt', 'Instructor shows the student what to do by demonstrating part or all of the action.'],
            ['PP', 'Physical Prompt', 'Instructor provides hand over hand support to help the student to complete the entire step or question.'],
            ['PR', 'Paper Read by the Instructor', 'Instructor reads the complete question paper for the student.'],
            ['PRW', 'Paper Read and Written by the Instructor [English/Hindi/Gujarati]', 'Instructor reads and writes the complete answer paper for the student with the students given answers.'],
            ['PTL', 'Paper Translated by the Instructor in Hindi or Gujarati Language', 'Instructor translates the complete question paper for the student to get required answers.'],
            ['SWL', 'Student writes in Hindi or Gujarati Language', 'Students writes the complete answer paper in Gujarati or Hindi Language.'],
        ] as [$k, $n, $d]): ?>
            <tr><td class="k"><?= $k ?></td><td class="n"><?= $h($n) ?></td><td><?= $h($d) ?></td></tr>
        <?php endforeach; ?>
    </table>
<?= $pageEnd() ?>

<!-- ═══ Page 10: Evaluation key ═══ -->
<?= $pageStart() ?>
    <div class="key-title" style="margin-top:4mm">EVALUATION KEY</div>
    <div class="sec-title" style="margin-top:6mm">Key- Academic Skills</div>
    <div style="height:8mm"></div>
    <table style="width:100%; border-collapse:collapse"><tr>
        <td style="width:50%; vertical-align:top; padding-left:12mm">
            <table class="scale">
                <tr><th style="height:12mm; vertical-align:bottom">Grade</th><th style="vertical-align:bottom">Scale</th></tr>
                <?php foreach ([['A+', '91.00 - 100.00'], ['A', '81.00 &ndash;90.00'], ['B+', '71.00 - 80.00'], ['B', '61.00 - 70.00'], ['C+', '51.00 - 60.00'],
                               ['C', '41.00 - 50.00'], ['D', '33.00 - 40.00'], ['E', '21.00 - 32.00'], ['F', '20 and Below'],
                               ['AB', 'Absent'], ['NS', 'Not Submitted'], ['NA', 'Not Applicable']] as [$g, $s]): ?>
                    <tr><td class="g" style="height:10.5mm; vertical-align:bottom"><?= $g ?></td><td class="s" style="vertical-align:bottom"><?= $s ?></td></tr>
                <?php endforeach; ?>
            </table>
        </td>
        <td style="width:50%; vertical-align:top">
            <table class="keys" style="width:68mm; margin:4mm 0 0 4mm">
                <tr><td class="k" style="text-align:center">Key</td><td style="font-weight:bold">Routine Profile,<br>Attendance Profile,<br>Learning Skills Profile,<br>Life Skills Profile,<br>Co-curriculum Profile</td></tr>
                <tr><td class="k" style="text-align:center">A</td><td>Excellent</td></tr>
                <tr><td class="k" style="text-align:center">B</td><td>Good</td></tr>
                <tr><td class="k" style="text-align:center">C</td><td>Need To Improve</td></tr>
            </table>
            <table class="keys" style="width:50mm; margin:70mm 0 0 14mm">
                <tr><td colspan="2" style="text-align:center">General (Key)</td></tr>
                <tr><td class="k">AB</td><td style="text-align:center">Absent</td></tr>
                <tr><td class="k">NS</td><td style="text-align:center">Not Submitted</td></tr>
                <tr><td class="k">NA</td><td style="text-align:center">Not Applicable</td></tr>
            </table>
        </td>
    </tr></table>
<?= $pageEnd() ?>

<!-- ═══ Page 11: Overall feedback & signatures ═══ -->
<?php
    $estLines = 0;
    foreach (preg_split('/\R/', $feedback) as $para) {
        $estLines += max(1, (int) ceil(mb_strlen($para) / 78));
    }
    $ruleCount = max(4, $estLines);
?>
<?= str_replace('class="page"', 'class="page last"', $pageStart()) ?>
    <div class="sec-title" style="margin-top:6mm">Overall Feedback</div>
    <table class="fb-head"><tr>
        <td>&nbsp;&nbsp;<?= $h($semester) ?></td>
        <td style="text-align:right">Date: <u><?= $h($meta['feedback_date'] ?? '') ?: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' ?></u></td>
    </tr></table>
    <div class="fb-box" style="height:<?= $ruleCount * 8.4 + 2 ?>mm">
        <?php for ($i = 1; $i <= $ruleCount; $i++): ?>
            <div class="fb-rule" style="top:<?= $i * 8.4 ?>mm"></div>
        <?php endfor; ?>
        <div class="fb-text"><?= nl2br($h($feedback)) ?></div>
    </div>

    <div class="sig-label">Signature</div>
    <div class="sig-line"></div>
    <table class="sig-roles"><tr>
        <td style="text-align:center; width:50%">[Class Teacher]</td>
        <td style="text-align:right; padding-right:14mm">[Parent]</td>
    </tr></table>

    <div style="font-weight:bold; margin-top:12mm">Authorised By:-</div>
    <table class="auth">
        <tr>
            <?php foreach ($trustees as $t): $sig = RC::trusteeSignature($t); ?>
                <td class="sig-cell"><?= $sig !== '' ? '<img src="' . $sig . '" alt="">' : '' ?></td>
            <?php endforeach; ?>
        </tr>
        <tr><td colspan="<?= count($trustees) ?>"><div class="auth-line"></div></td></tr>
        <tr>
            <?php foreach ($trustees as $t): ?>
                <td><?= $h($t['name'] ?? '') ?><br><br><?= $h('[' . trim((string) ($t['title'] ?? ''), '[]') . ']') ?></td>
            <?php endforeach; ?>
        </tr>
    </table>

    <div style="font-weight:bold; margin:12mm 0 0 8mm">Stamp:<br><?= $h($stampText) ?></div>
    <?php if ($stampImage !== ''): ?>
        <img src="<?= $stampImage ?>" alt="" style="height:22mm; margin:2mm 0 0 8mm">
    <?php endif; ?>

    <div class="quote">&ldquo;Together, with <span class="brand">Ghar Se Pagbhar</span> and shared commitment, parents and teachers build a caring&nbsp; community where every difference is embraced and every ability&nbsp; can bloom.&rdquo;<img src="<?= RC::asset('flower.jpg') ?>" alt=""></div>
<?= $pageEnd() ?>
</body>
</html>
