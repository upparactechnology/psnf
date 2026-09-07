<?php
$name = $student['first_name'] . ' ' . $student['last_name'];
$primaryColor = $settings['primary_color'] ?? '#0d3827';
$pdfFont = $settings['pdf_font'] ?? 'Inter';

function decode_entities(?string $str): string {
    if (!$str) return '';
    $decoded = html_entity_decode($str, ENT_QUOTES, 'UTF-8');
    while ($decoded !== $str) {
        $str = $decoded;
        $decoded = html_entity_decode($str, ENT_QUOTES, 'UTF-8');
    }
    return $decoded;
}

$schoolName = decode_entities($settings['school_name'] ?? 'Pearl Special Needs Foundation');
$schoolSubtitle = decode_entities($settings['school_subtitle'] ?? 'Center for Special Education & Care');
$schoolAddress = decode_entities($settings['school_address'] ?? 'Ahmedabad, Gujarat, India · contact@pearlspecialneeds.org');
$stampText = decode_entities($settings['stamp_text'] ?? 'Pearl Special Needs Foundation');
$stampImage = $settings['stamp_image'] ?? '';

$fontFamilyMap = [
    'Inter' => "'Inter', sans-serif",
    'Cinzel' => "'Cinzel', serif",
    'Playfair Display' => "'Playfair Display', serif"
];
$fontFamily = $fontFamilyMap[$pdfFont] ?? "'Inter', sans-serif";

$academicProfile = $reportCard['academic_profile'] ?? '{}';
$scores = is_array($academicProfile) ? $academicProfile : (json_decode($academicProfile, true) ?: []);

$activeExams = \Core\Application::$app->db->select(
    "SELECT name, max_marks FROM exams WHERE tenant_id = ? AND semester = ? ORDER BY id ASC",
    [$student['tenant_id'], $semester]
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress Report Card - <?= e($student['first_name'] . ' ' . $student['last_name']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700;800&family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,600;1,400&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: <?= $fontFamily ?>;
            color: #1e293b;
            background-color: #f1f5f9;
            padding: 40px 0;
        }

        /* Printable Page Layout Container */
        .report-container {
            width: 800px;
            margin: 0 auto;
        }

        /* Individual Pages */
        .page {
            width: 800px;
            height: 1120px;
            background-color: #ffffff;
            position: relative;
            padding: 60px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 40px;
            page-break-after: always;
            overflow: hidden;
        }

        /* Gold/Double Borders */
        .double-border {
            border: 4px double #d4af37;
            height: 100%;
            width: 100%;
            padding: 40px;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        /* Print Media Styles */
        @media print {
            @page {
                size: A4 portrait;
                margin: 0;
            }
            body {
                background-color: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 210mm !important;
                height: 297mm !important;
            }
            .no-print {
                display: none !important;
            }
            .no-print-space {
                display: none !important;
            }
            .report-container {
                width: 210mm !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .page {
                width: 210mm !important;
                height: 297mm !important;
                box-shadow: none !important;
                margin: 0 !important;
                padding: 15mm 15mm !important;
                page-break-after: always !important;
                page-break-inside: avoid !important;
                background-color: #ffffff !important;
                overflow: hidden !important;
                box-sizing: border-box !important;
            }
            .double-border {
                height: 267mm !important;
                width: 180mm !important;
                padding: 15mm !important;
                box-sizing: border-box !important;
            }
        }

        /* Floating action buttons */
        .no-print {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            background-color: #0f172a;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 40px;
            z-index: 9999;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }

        .no-print-space {
            height: 60px;
            width: 100%;
        }

        .btn-action {
            background-color: #6366f1;
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.15s ease;
        }

        .btn-action:hover {
            background-color: #4f46e5;
        }

        .btn-action.btn-secondary {
            background-color: #334155;
            color: #cbd5e1;
        }

        .btn-action.btn-secondary:hover {
            background-color: #475569;
            color: #ffffff;
        }

        /* Cover Page Elements */
        .cover-page {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .cover-border {
            border: 4px double #d4af37;
            height: 100%;
            width: 100%;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
        }

        .cover-logo {
            width: 110px;
            height: 110px;
            margin-bottom: 25px;
            background-color: #ffffff;
            border-radius: 50%;
            padding: 5px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cover-logo img {
            max-width: 100%;
            max-height: 100%;
            object-contain: fit;
        }

        .school-name {
            font-family: 'Cinzel', serif;
            font-size: 26px;
            font-weight: 800;
            color: <?= $primaryColor ?>;
            text-align: center;
            line-height: 1.2;
            letter-spacing: 0.5px;
        }

        .school-subtitle {
            font-size: 13px;
            color: #475569;
            text-align: center;
            margin-top: 6px;
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        .report-title-container {
            margin: 40px 0;
            text-align: center;
        }

        .report-title-badge {
            font-family: 'Cinzel', serif;
            font-size: 32px;
            font-weight: 700;
            color: <?= $primaryColor ?>;
            display: block;
            letter-spacing: 2px;
            margin-bottom: 8px;
        }

        .academic-year-badge {
            font-size: 14px;
            color: #d4af37;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .student-badge-card {
            background-color: #fcfdfd;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 24px;
            width: 100%;
            max-width: 480px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        }

        .student-badge-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .student-badge-row:last-child {
            border-bottom: none;
        }

        .student-badge-label {
            font-size: 12px;
            font-weight: 650;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .student-badge-value {
            font-size: 13px;
            font-weight: 700;
            color: #1e293b;
        }

        .school-address-footer {
            font-size: 10px;
            color: #64748b;
            text-align: center;
            border-top: 1px solid #cbd5e1;
            padding-top: 15px;
            width: 100%;
        }

        /* Inner Page Elements */
        .page-header {
            display: flex;
            align-items: center;
            gap: 15px;
            border-bottom: 2px solid <?= $primaryColor ?>;
            padding-bottom: 12px;
            margin-bottom: 25px;
        }

        .page-header-logo {
            width: 45px;
            height: 45px;
        }

        .page-header-logo img {
            max-width: 100%;
            max-height: 100%;
        }

        .page-header-text h3 {
            font-family: 'Cinzel', serif;
            font-size: 16px;
            font-weight: 700;
            color: <?= $primaryColor ?>;
        }

        .page-header-text p {
            font-size: 11px;
            color: #64748b;
            font-weight: 500;
            margin-top: 2px;
        }

        .section-title {
            font-family: 'Cinzel', serif;
            font-size: 15px;
            font-weight: 700;
            color: <?= $primaryColor ?>;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-left: 3px solid #d4af37;
            padding-left: 10px;
        }

        /* Structured tables */
        .table-profile {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .table-profile th,
        .table-profile td {
            border: 1px solid #cbd5e1;
            padding: 8px 12px;
            font-size: 11px;
            vertical-align: middle;
        }

        .table-profile th {
            background-color: #f8fafc;
            font-weight: 750;
            color: #334155;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.5px;
        }

        .param-label {
            font-weight: 650;
            color: #1e293b;
        }

        .rating-val {
            font-weight: 700;
            text-align: center;
        }

        .remarks-col {
            font-style: italic;
            color: #475569;
        }

        .feedback-text-box {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background-color: #fcfdfd;
            padding: 15px 20px;
            font-size: 11.5px;
            line-height: 1.6;
            color: #334155;
            min-height: 120px;
            margin-bottom: 25px;
        }

        .signature-row-1 {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .signature-row-2 {
            margin-top: 35px;
            border-top: 1px solid #cbd5e1;
            padding-top: 20px;
        }

        .signature-row-2-title {
            font-size: 11px;
            font-weight: 700;
            color: <?= $primaryColor ?>;
            margin-bottom: 25px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .trustees-grid {
            display: flex;
            justify-content: space-between;
        }

        .stamp-box {
            margin-top: 35px;
            font-size: 11px;
            color: #334155;
        }

        .stamp-title {
            font-weight: 700;
            margin-bottom: 4px;
        }

        .stamp-value {
            font-style: italic;
        }

        .signature-line-box {
            width: 200px;
            text-align: center;
        }

        .signature-line-box img {
            max-height: 45px;
            margin-bottom: 6px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .signature-line {
            border-top: 1px solid #475569;
            margin-top: 10px;
            padding-top: 6px;
            font-size: 11px;
            font-weight: 650;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .signature-title {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
        }

        <?php if (isset($isPdf) && $isPdf): ?>
        /* Dompdf specific overrides to use A4 millimeters */
        @page {
            size: A4 portrait;
            margin: 0;
        }
        body {
            background-color: #ffffff !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 210mm !important;
            height: 297mm !important;
        }
        .report-container {
            width: 210mm !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .page {
            width: 210mm !important;
            height: 280mm !important;
            padding: 8mm !important;
            margin: 0 !important;
            box-shadow: none !important;
            page-break-after: always !important;
            page-break-inside: avoid !important;
            box-sizing: border-box !important;
        }
        .double-border {
            height: 260mm !important;
            width: 190mm !important;
            box-sizing: border-box !important;
            display: block !important;
            padding: 20px !important;
        }
        .cover-border {
            height: 260mm !important;
            width: 190mm !important;
            box-sizing: border-box !important;
            display: block !important;
            padding: 30px !important;
        }
        .section-title {
            margin-top: 15px !important;
            margin-bottom: 8px !important;
        }
        .feedback-text-box {
            min-height: 80px !important;
            margin-bottom: 15px !important;
            padding: 10px 15px !important;
        }
        .signature-row-1 {
            display: block !important;
            width: 100% !important;
            margin-top: 15px !important;
        }
        .signature-row-1 .signature-line-box {
            float: left !important;
            width: 45% !important;
        }
        .signature-row-2 {
            display: block !important;
            clear: both !important;
            width: 100% !important;
            margin-top: 15px !important;
            padding-top: 10px !important;
        }
        .signature-row-2-title {
            margin-bottom: 10px !important;
        }
        .trustees-grid {
            display: block !important;
            width: 100% !important;
        }
        .stamp-box {
            clear: both !important;
            margin-top: 15px !important;
        }
        .attendance-stats-box {
            display: block !important;
            width: 100% !important;
        }
        .attendance-stat-item {
            float: left !important;
            width: 32% !important;
            text-align: center !important;
        }
        table {
            margin-bottom: 15px !important;
        }
        .page-header {
            margin-bottom: 15px !important;
        }
        /* Cover Page Overrides for DOMPDF */
        .cover-page .cover-logo {
            margin: 0 auto !important;
            float: none !important;
        }
        .cover-page img {
            display: block !important;
            margin: 0 auto !important;
        }
        .cover-page .school-name {
            display: block !important;
            text-align: center !important;
            margin-top: 15px !important;
        }
        .cover-page .school-subtitle {
            display: block !important;
            text-align: center !important;
            margin-top: 5px !important;
        }
        .report-title-container {
            text-align: center !important;
            margin: 25px 0 !important;
        }
        .student-badge-card {
            margin: 20px auto !important;
            display: block !important;
            float: none !important;
        }
        .student-badge-row {
            display: block !important;
            clear: both !important;
            width: 100% !important;
            height: 25px !important;
        }
        .student-badge-label {
            float: left !important;
            width: 150px !important;
        }
        .student-badge-value {
            float: left !important;
        }
        .school-address-footer {
            margin-top: 25px !important;
            text-align: center !important;
            display: block !important;
        }
        <?php endif; ?>
    </style>
</head>
<body>

    <!-- Floating Actions Panel -->
    <?php if (!isset($isPdf) || !$isPdf): ?>
    <div class="no-print">
        <span style="color: #cbd5e1; font-weight: 600; font-size: 14px;">Pearl Special Needs Progress Report</span>
        <div style="display: flex; gap: 10px;">
            <button onclick="window.print()" class="btn-action">
                Download PDF / Print
            </button>
            <button onclick="window.close()" class="btn-action btn-secondary">Close View</button>
        </div>
    </div>
    <div class="no-print-space"></div>
    <?php endif; ?>

    <?php 
    $logoPath = url('game/images/logo.png');
    ?>

    <!-- Main Report Container -->
    <div class="report-container">

        <!-- ================= PAGE 1: COVER PAGE ================= -->
        <div class="page cover-page">
            <div class="cover-border">
                
                <!-- Logo & Heading -->
                <div style="display: flex; flex-direction: column; align-items: center;">
                    <div class="cover-logo">
                        <img src="<?= $logoPath ?>" alt="Pearl Logo">
                    </div>
                    <h1 class="school-name"><?= e($schoolName) ?></h1>
                    <p class="school-subtitle"><?= e($schoolSubtitle) ?></p>
                </div>

                <!-- Report Title Badge -->
                <div class="report-title-container">
                    <span class="report-title-badge">Progress Report</span>
                    <p class="academic-year-badge"><?= e($semester) ?> · Academic Year <?= e($academicYear) ?></p>
                </div>

                <!-- Student details parameters card -->
                <div class="student-badge-card">
                    <div class="student-badge-row">
                        <span class="student-badge-label">Student Name:</span>
                        <span class="student-badge-value"><?= e($name) ?></span>
                    </div>
                    <div class="student-badge-row">
                        <span class="student-badge-label">Class Name:</span>
                        <span class="student-badge-value"><?= e($student['class'] ?? 'Special Care Program') ?></span>
                    </div>
                    <div class="student-badge-row">
                        <span class="student-badge-label">GR Number:</span>
                        <span class="student-badge-value"><?= e($student['gr_number'] ?? '—') ?></span>
                    </div>
                    <div class="student-badge-row">
                        <span class="student-badge-label">Roll Number:</span>
                        <span class="student-badge-value"><?= e($student['roll_number'] ?? '—') ?></span>
                    </div>
                </div>

                <!-- School Address Footer -->
                <div class="school-address-footer">
                    <?= e($schoolAddress) ?>
                </div>
            </div>
        </div>

        <!-- ================= DYNAMIC CURRICULUM SECTIONS PAGES ================= -->
        <?php foreach ($curriculumTree as $sec): ?>
            <?php 
                $isMarksBased = (!empty($sec['subjects']) && $sec['subjects'][0]['assessment_type'] === 'Marks');
            ?>
            <div class="page">
                <div class="double-border">
                    <div class="page-header">
                        <div class="page-header-logo">
                            <img src="<?= $logoPath ?>" alt="Pearl Logo">
                        </div>
                        <div class="page-header-text">
                            <h3><?= e($schoolName) ?></h3>
                            <p>Progress Report · <?= e($semester) ?> (<?= e($academicYear) ?>)</p>
                        </div>
                    </div>

                    <h2 class="section-title"><?= e($sec['section_name']) ?></h2>

                    <?php if ($isMarksBased): ?>
                        <table class="table-profile">
                            <thead>
                                <tr>
                                    <th style="text-align: left; width: 35%;">Subject</th>
                                    <?php foreach ($activeExams as $exam): ?>
                                        <th style="text-align: center; font-size: 9px;"><?= e($exam['name']) ?><br><span style="color: #64748b; font-size: 8px;">Max: <?= number_format((float)$exam['max_marks'], 0) ?></span></th>
                                    <?php endforeach; ?>
                                    <th style="text-align: center; font-size: 9px;">Total<br><span style="color: #64748b; font-size: 8px;">Max: <?= number_format(array_sum(array_column($activeExams, 'max_marks')), 0) ?></span></th>
                                    <th style="text-align: center; font-size: 9px;">Percentage</th>
                                    <th style="text-align: center; font-size: 9px;">Grade</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sec['subjects'] as $sub): ?>
                                    <?php 
                                        $val = $scores[$sub['subject_id']] ?? [];
                                        if (!is_array($val)) {
                                            $val = ['marks' => [], 'total' => 0, 'pct' => 0, 'grade' => '—'];
                                        }
                                    ?>
                                    <tr>
                                        <td class="param-label"><?= e($sub['subject_name']) ?></td>
                                        <?php foreach ($activeExams as $exam): ?>
                                            <td class="rating-val" style="font-family: monospace; font-size: 10px;">
                                                <?= isset($val['marks'][$exam['name']]) ? number_format((float)$val['marks'][$exam['name']], 1) : '—' ?>
                                            </td>
                                        <?php endforeach; ?>
                                        <td class="rating-val" style="color: <?= $primaryColor ?>; font-family: monospace; font-size: 10px; font-weight: bold; background-color: #fcfdfd;">
                                            <?= isset($val['total']) ? number_format((float)$val['total'], 1) : '—' ?>
                                        </td>
                                        <td class="rating-val" style="font-family: monospace; font-size: 10px;">
                                            <?= isset($val['pct']) ? number_format((float)$val['pct'], 2) . '%' : '—' ?>
                                        </td>
                                        <td class="rating-val" style="font-weight: bold; background-color: #fcfdfd;">
                                            <?= e($val['grade'] ?? '—') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <table class="table-profile">
                            <thead>
                                <tr>
                                    <th style="text-align: left; width: 65%;">Subject / Parameter</th>
                                    <th style="width: 35%; text-align: center;">Evaluation Outcome</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sec['subjects'] as $sub): ?>
                                    <?php 
                                        $val = $scores[$sub['subject_id']] ?? '—';
                                        if ($sub['assessment_type'] === 'Rating') {
                                            $ratingMap = ['A' => 'Excellent (A)', 'B' => 'Good (B)', 'C' => 'Needs Improvement (C)', 'R' => 'Refused (R)', 'N/A' => 'N/A'];
                                            $val = $ratingMap[$val] ?? $val;
                                        }
                                    ?>
                                    <tr>
                                        <td class="param-label"><?= e($sub['subject_name']) ?></td>
                                        <td class="rating-val" style="color: <?= $primaryColor ?>; background-color: #fcfdfd;">
                                            <?= e($val) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- ================= FINAL PAGE: FEEDBACK & SIGNATURES ================= -->
        <div class="page">
            <div class="double-border">
                <div class="page-header">
                    <div class="page-header-logo">
                        <img src="<?= $logoPath ?>" alt="Pearl Logo">
                    </div>
                    <div class="page-header-text">
                        <h3><?= e($schoolName) ?></h3>
                        <p>Progress Report · <?= e($semester) ?> (<?= e($academicYear) ?>)</p>
                    </div>
                </div>

                <h2 class="section-title">Attendance & Integration Log</h2>

                <table style="width: 100%; border-collapse: collapse; margin-bottom: 35px; border: 1px solid #cbd5e1; border-radius: 8px;">
                    <tr>
                        <td style="width: 50%; padding: 12px; text-align: center; background-color: #f8fafc; border-right: 1px solid #cbd5e1;">
                            <div style="font-size: 10px; text-transform: uppercase; color: #64748b; font-weight: 600; margin-bottom: 4px; letter-spacing: 0.5px;">Total Working Days</div>
                            <div style="font-size: 15px; font-weight: 700; color: <?= $primaryColor ?>;"><?= e($reportCard['attendance_profile']['total_days'] ?? '—') ?></div>
                        </td>
                        <td style="width: 50%; padding: 12px; text-align: center; background-color: #f8fafc;">
                            <div style="font-size: 10px; text-transform: uppercase; color: #64748b; font-weight: 600; margin-bottom: 4px; letter-spacing: 0.5px;">Days Present</div>
                            <div style="font-size: 15px; font-weight: 700; color: <?= $primaryColor ?>;"><?= e($reportCard['attendance_profile']['days_present'] ?? '—') ?></div>
                        </td>
                    </tr>
                </table>

                <h2 class="section-title">Overall Assessment & Teacher Narrative</h2>
                
                <div class="feedback-text-box">
                    <?= nl2br(e($reportCard['feedback_text'] ?? 'Student shows positive interest in learning sensory motor skills and interacts well with classmates.')) ?>
                </div>

                <!-- Signature grids (Double rows) -->
                <div class="signature-row-1" style="width: 100%;">
                    <div class="signature-line-box" style="<?= (isset($isPdf) && $isPdf) ? 'float: left; width: 45%;' : '' ?>">
                        <?php 
                        $teacherSig = !empty($settings['class_teacher_sig']) ? $settings['class_teacher_sig'] : ($reportCard['authorized_by']['class_teacher_sig'] ?? '');
                        $teacherName = !empty($settings['class_teacher_name']) ? $settings['class_teacher_name'] : ($reportCard['authorized_by']['class_teacher'] ?? 'Class Teacher');
                        ?>
                        <?php if (!empty($teacherSig)): ?>
                            <img src="<?= $teacherSig ?>" alt="Class Teacher Signature">
                        <?php else: ?>
                            <div style="height: 45px;"></div>
                        <?php endif; ?>
                        <div class="signature-line"><?= e($teacherName) ?></div>
                        <div class="signature-title">Class Teacher Signature</div>
                    </div>
                    <div class="signature-line-box" style="<?= (isset($isPdf) && $isPdf) ? 'float: right; width: 45%;' : '' ?>">
                        <div style="height: 45px;"></div>
                        <div class="signature-line"></div>
                        <div class="signature-title">Parent's Signature</div>
                    </div>
                    <div style="clear: both;"></div>
                </div>

                <?php 
                $trustees = $settings['trustees_config'] ?? []; 
                $trusteesCount = count($trustees);
                ?>
                <?php if ($trusteesCount > 0): ?>
                <div class="signature-row-2">
                    <div class="signature-row-2-title">Authorised By</div>
                    <div class="trustees-grid" style="width: 100%;">
                        <?php 
                        $tWidth = floor(100 / $trusteesCount) - 2; 
                        foreach ($trustees as $index => $trustee): 
                            $isLast = ($index === $trusteesCount - 1);
                            $itemStyle = '';
                            if (isset($isPdf) && $isPdf) {
                                $itemStyle = 'float: left; width: ' . $tWidth . '%; margin-right: 2%;';
                                if ($isLast) {
                                    $itemStyle = 'float: left; width: ' . $tWidth . '%;';
                                }
                            }
                        ?>
                        <div class="signature-line-box" style="<?= $itemStyle ?>">
                            <?php if (!empty($trustee['sig'])): ?>
                                <img src="<?= $trustee['sig'] ?>" alt="<?= e($trustee['name']) ?> Signature">
                            <?php else: ?>
                                <div style="height: 45px;"></div>
                            <?php endif; ?>
                            <div class="signature-line"><?= e($trustee['name']) ?></div>
                            <div class="signature-title"><?= e($trustee['title']) ?></div>
                        </div>
                        <?php endforeach; ?>
                        <div style="clear: both;"></div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($stampText) || !empty($stampImage)): ?>
                <div class="stamp-box">
                    <div class="stamp-title">Official Stamp</div>
                    <?php if (!empty($stampImage)): ?>
                        <img src="<?= $stampImage ?>" class="stamp-image-img" alt="Official Stamp" style="max-height: 70px; display: block; margin-top: 5px;">
                    <?php endif; ?>
                    <?php if (!empty($stampText)): ?>
                        <div class="stamp-value" style="margin-top: 4px;"><?= e($stampText) ?></div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

</body>
</html>
