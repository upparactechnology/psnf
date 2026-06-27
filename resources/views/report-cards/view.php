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

// Fetch dynamic fields configuration
$fieldsConfig = json_decode($settings['fields_config'] ?? '[]', true) ?: [];
$fields = $fieldsConfig[$academicYear][$semester] ?? \App\Controllers\ReportCardController::getDefaultFields();

$routineParams = $fields['routine'] ?? [];
$skillParams = $fields['skills'] ?? [];
$subjects = $fields['academics'] ?? [];
$activities = $fields['cocurricular'] ?? [];
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
            .report-container {
                width: 210mm !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .page {
                width: 210mm !important;
                height: 297mm !important;
                padding: 10mm !important;
                margin: 0 !important;
                box-shadow: none !important;
                page-break-after: always !important;
                page-break-inside: avoid !important;
                box-sizing: border-box !important;
            }
            .double-border {
                height: 100% !important;
                width: 100% !important;
                box-sizing: border-box !important;
                display: flex !important;
                flex-direction: column !important;
                padding: 20px !important;
            }
            .cover-border {
                height: 100% !important;
                width: 100% !important;
                box-sizing: border-box !important;
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                justify-content: space-between !important;
                text-align: center !important;
                padding: 30px !important;
            }
            .no-print,
            .no-print-space {
                display: none !important;
            }
        }

        /* Print Controller Floating Panel */
        .no-print {
            background-color: #0f172a;
            border-bottom: 1px solid #334155;
            padding: 12px 24px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .no-print-space {
            height: 60px;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #ffffff;
            background-color: #4f46e5;
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background 150ms;
        }

        .btn-action:hover {
            background-color: #4338ca;
        }

        .btn-secondary {
            background-color: #334155;
        }

        .btn-secondary:hover {
            background-color: #475569;
        }

        /* --- Page 1: Cover Layout --- */
        .cover-page {
            background-color: <?= $primaryColor ?>; /* Dynamic primary theme color */
            color: #ffffff;
        }

        .cover-border {
            border: 4px double #d4af37;
            height: 100%;
            width: 100%;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            text-align: center;
        }

        .cover-logo {
            width: 120px;
            height: 120px;
            background-color: #ffffff;
            border-radius: 50%;
            padding: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cover-logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .school-name {
            font-family: 'Cinzel', serif;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 2px;
            color: #d4af37; /* Gold accent color */
            margin-top: 15px;
            text-transform: uppercase;
        }

        .school-subtitle {
            font-family: 'Inter', sans-serif;
            font-size: 11px;
            color: #cbd5e1;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-top: 6px;
        }

        .report-title-container {
            margin: 40px 0;
        }

        .report-title-badge {
            font-family: 'Cinzel', serif;
            border-top: 1.5px solid #d4af37;
            border-bottom: 1.5px solid #d4af37;
            padding: 12px 30px;
            font-size: 28px;
            letter-spacing: 5px;
            color: #ffffff;
            font-weight: 800;
            display: inline-block;
        }

        .academic-year-badge {
            font-family: 'Playfair Display', serif;
            font-size: 18px;
            font-style: italic;
            color: #e2e8f0;
            margin-top: 15px;
        }

        .student-badge-card {
            background-color: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 12px;
            padding: 24px;
            width: 100%;
            max-width: 480px;
            margin-top: 30px;
            text-align: left;
        }

        .student-badge-row {
            display: flex;
            margin-bottom: 12px;
            border-bottom: 1px dashed rgba(255, 255, 255, 0.1);
            padding-bottom: 8px;
        }

        .student-badge-row:last-child {
            margin-bottom: 0;
            border-bottom: none;
            padding-bottom: 0;
        }

        .student-badge-label {
            font-size: 11px;
            color: #94a3b8;
            width: 130px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 650;
        }

        .student-badge-value {
            font-size: 14px;
            color: #ffffff;
            font-weight: 600;
        }

        .school-address-footer {
            font-size: 10px;
            color: #94a3b8;
            letter-spacing: 0.5px;
            line-height: 1.5;
        }

        /* --- Inside Pages Styles --- */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 15px;
            margin-bottom: 24px;
        }

        .page-header-logo {
            width: 50px;
            height: 50px;
            background-color: #f1f5f9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4px;
        }

        .page-header-logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .page-header-text h3 {
            font-family: 'Cinzel', serif;
            font-size: 14px;
            font-weight: 700;
            color: <?= $primaryColor ?>;
            text-align: right;
        }

        .page-header-text p {
            font-size: 10px;
            color: #64748b;
            text-align: right;
            margin-top: 3px;
        }

        .section-title {
            font-family: 'Cinzel', serif;
            font-size: 16px;
            color: <?= $primaryColor ?>;
            font-weight: 700;
            border-bottom: 2.5px solid <?= $primaryColor ?>;
            padding-bottom: 6px;
            margin-bottom: 20px;
            letter-spacing: 1.5px;
        }

        /* Standard Table Styles */
        .table-profile {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .table-profile th,
        .table-profile td {
            border: 1px solid #cbd5e1;
            padding: 10px 12px;
            font-size: 12px;
        }

        .table-profile th {
            background-color: <?= $primaryColor ?>;
            color: #ffffff;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }

        .table-profile td.param-label {
            font-weight: 600;
            color: #334155;
            width: 55%;
        }

        .table-profile td.rating-val {
            text-align: center;
            font-weight: 700;
            font-size: 13px;
        }

        /* Colors for Ratings */
        .rating-a { color: #16a34a; background-color: #f0fdf4; }
        .rating-b { color: #2563eb; background-color: #eff6ff; }
        .rating-c { color: #ea580c; background-color: #fff7ed; }
        .rating-ref { color: #dc2626; background-color: #fef2f2; }
        .rating-na { color: #64748b; background-color: #f8fafc; }

        /* --- Academic Sheet Styles --- */
        .academic-table th {
            text-align: center;
        }

        .academic-table td {
            text-align: center;
        }

        .academic-table td.subject-name {
            text-align: left;
            font-weight: 600;
            color: #1e293b;
        }

        .academic-table td.remarks-col {
            text-align: left;
            font-style: italic;
            color: #475569;
        }

        /* Evaluation Chart */
        .evaluation-chart-box {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 15px;
            background-color: #f8fafc;
            margin-top: 30px;
        }

        .evaluation-chart-box h4 {
            font-size: 11px;
            text-transform: uppercase;
            color: <?= $primaryColor ?>;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }

        .evaluation-chart-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
        }

        .chart-item {
            text-align: center;
            border-right: 1px solid #e2e8f0;
            padding-right: 5px;
        }

        .chart-item:last-child {
            border-right: none;
        }

        .chart-key {
            font-weight: 700;
            font-size: 12px;
            color: <?= $primaryColor ?>;
            margin-bottom: 2px;
        }

        .chart-desc {
            font-size: 9px;
            color: #64748b;
        }

        /* --- Summary Sheet Signatures & Comments --- */
        .feedback-text-box {
            border: 1.5px solid <?= $primaryColor ?>;
            background-color: #fcfdfd;
            border-radius: 8px;
            padding: 20px;
            min-height: 180px;
            font-size: 13px;
            line-height: 1.6;
            color: #334155;
            font-style: italic;
            margin-bottom: 35px;
        }

        .attendance-stats-box {
            display: flex;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 35px;
        }

        .attendance-stat-item {
            flex: 1;
            padding: 12px;
            text-align: center;
            border-right: 1px solid #cbd5e1;
            background-color: #f8fafc;
        }

        .attendance-stat-item:last-child {
            border-right: none;
        }

        .stat-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .stat-value {
            font-size: 15px;
            font-weight: 700;
            color: <?= $primaryColor ?>;
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
        .evaluation-chart-grid {
            display: block !important;
            width: 100% !important;
        }
        .chart-item {
            float: left !important;
            width: 19% !important;
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
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-3a2 2 0 00-2-2H9a2 2 0 00-2 2v3a2 2 0 002 2zm0 0l-3-3m3 3l3-3"/></svg>
                Download PDF / Print
            </button>
            <button onclick="window.close()" class="btn-action btn-secondary">Close View</button>
        </div>
    </div>
    <div class="no-print-space"></div>
    <?php endif; ?>

    <?php 
    $logoPath = url('images/logo.png');
    ?>

    <!-- Main Report Container -->
    <div class="report-container">

        <!-- ================= PAGE 1: COVER PAGE ================= -->
        <div class="page cover-page">
            <div class="cover-border">
                
                <!-- Logo & Heading -->
                <div style="display: flex; flex-direction: column; align-items: center;">
                    <div class="cover-logo">
                        <img src="<?= $logoPath ?>" onerror="this.onerror=null; this.src='https://pearlspecialneeds.org/wp-content/uploads/2021/04/pearl-logo.png';" alt="Pearl Logo">
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
                        <span class="student-badge-value font-mono"><?= e($student['gr_number'] ?? '—') ?></span>
                    </div>
                    <div class="student-badge-row">
                        <span class="student-badge-label">Admission ID:</span>
                        <span class="student-badge-value font-mono"><?= e($student['admission_number'] ?? '—') ?></span>
                    </div>
                </div>

                <!-- Footer address details -->
                <div class="school-address-footer">
                    <p><?= e($schoolName) ?></p>
                    <p><?= e($schoolAddress) ?></p>
                </div>
            </div>
        </div>

        <!-- ================= PAGE 2: ROUTINE PROFILE ================= -->
        <div class="page">
            <div class="double-border">
                <div class="page-header">
                    <div class="page-header-logo">
                        <img src="<?= $logoPath ?>" onerror="this.onerror=null; this.src='https://pearlspecialneeds.org/wp-content/uploads/2021/04/pearl-logo.png';" alt="Pearl Logo">
                    </div>
                    <div class="page-header-text">
                        <h3><?= e($schoolName) ?></h3>
                        <p>Progress Report · <?= e($semester) ?> (<?= e($academicYear) ?>)</p>
                    </div>
                </div>

                <h2 class="section-title">Routine & Social Behavioral Profile</h2>

                <table class="table-profile">
                    <thead>
                        <tr>
                            <th style="text-align: left;">Evaluation Parameter</th>
                            <th style="width: 25%; text-align: center;">Assessed Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($routineParams as $key => $label):
                            $val = $reportCard['routine_profile'][$key] ?? 'B';
                            $class = 'rating-' . strtolower(substr($val, 0, 3));
                        ?>
                        <tr>
                            <td class="param-label"><?= $label ?></td>
                            <td class="rating-val <?= $class ?>"><?= e($val) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="evaluation-chart-box">
                    <h4>Assessed Grading Scale Indicators</h4>
                    <div class="evaluation-chart-grid">
                        <div class="chart-item">
                            <p class="chart-key">A</p>
                            <p class="chart-desc">Excellent / Independent</p>
                        </div>
                        <div class="chart-item">
                            <p class="chart-key">B</p>
                            <p class="chart-desc">Good / Prompt Assisted</p>
                        </div>
                        <div class="chart-item">
                            <p class="chart-key">C</p>
                            <p class="chart-desc">Needs Improvement / Guided</p>
                        </div>
                        <div class="chart-item">
                            <p class="chart-key">Refused</p>
                            <p class="chart-desc">Student Refused Task</p>
                        </div>
                        <div class="chart-item">
                            <p class="chart-key">N/A</p>
                            <p class="chart-desc">Not Applicable</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= PAGE 3: LEARNING SKILLS ================= -->
        <div class="page">
            <div class="double-border">
                <div class="page-header">
                    <div class="page-header-logo">
                        <img src="<?= $logoPath ?>" onerror="this.onerror=null; this.src='https://pearlspecialneeds.org/wp-content/uploads/2021/04/pearl-logo.png';" alt="Pearl Logo">
                    </div>
                    <div class="page-header-text">
                        <h3><?= e($schoolName) ?></h3>
                        <p>Progress Report · <?= e($semester) ?> (<?= e($academicYear) ?>)</p>
                    </div>
                </div>

                <h2 class="section-title">Cognitive & Learning Skills Profile</h2>

                <table class="table-profile">
                    <thead>
                        <tr>
                            <th style="text-align: left;">Skill Indicator</th>
                            <th style="width: 25%; text-align: center;">Assessed Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($skillParams as $key => $label):
                            $val = $reportCard['learning_skills'][$key] ?? 'B';
                            $class = 'rating-' . strtolower(substr($val, 0, 3));
                        ?>
                        <tr>
                            <td class="param-label"><?= $label ?></td>
                            <td class="rating-val <?= $class ?>"><?= e($val) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="evaluation-chart-box">
                    <h4>Assessed Grading Scale Indicators</h4>
                    <div class="evaluation-chart-grid">
                        <div class="chart-item">
                            <p class="chart-key">A</p>
                            <p class="chart-desc">Excellent / Independent</p>
                        </div>
                        <div class="chart-item">
                            <p class="chart-key">B</p>
                            <p class="chart-desc">Good / Prompt Assisted</p>
                        </div>
                        <div class="chart-item">
                            <p class="chart-key">C</p>
                            <p class="chart-desc">Needs Improvement / Guided</p>
                        </div>
                        <div class="chart-item">
                            <p class="chart-key">Refused</p>
                            <p class="chart-desc">Student Refused Task</p>
                        </div>
                        <div class="chart-item">
                            <p class="chart-key">N/A</p>
                            <p class="chart-desc">Not Applicable</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= PAGE 4: ACADEMIC PROFILE ================= -->
        <div class="page">
            <div class="double-border">
                <div class="page-header">
                    <div class="page-header-logo">
                        <img src="<?= $logoPath ?>" onerror="this.onerror=null; this.src='https://pearlspecialneeds.org/wp-content/uploads/2021/04/pearl-logo.png';" alt="Pearl Logo">
                    </div>
                    <div class="page-header-text">
                        <h3><?= e($schoolName) ?></h3>
                        <p>Progress Report · <?= e($semester) ?> (<?= e($academicYear) ?>)</p>
                    </div>
                </div>

                <h2 class="section-title">Academic Subject Evaluations</h2>

                <table class="table-profile academic-table">
                    <thead>
                        <tr>
                            <th style="text-align: left; width: 25%;">Subject</th>
                            <th style="width: 15%;">Unit Test (20)</th>
                            <th style="width: 15%;">Theory (40)</th>
                            <th style="width: 15%;">Practical / Oral (15)</th>
                            <th style="width: 15%;">Total (75)</th>
                            <th style="text-align: left;">Evaluator Comments</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($subjects as $key => $label):
                            $subjData = $reportCard['academic_profile'][$key] ?? [];
                            $ut = (float)($subjData['unit_test'] ?? 0);
                            $th = (float)($subjData['theory'] ?? 0);
                            $pr = (float)($subjData['practical'] ?? 0);
                            $tot = $ut + $th + $pr;
                        ?>
                        <tr>
                            <td class="subject-name"><?= $label ?></td>
                            <td><?= $ut ?></td>
                            <td><?= $th ?></td>
                            <td><?= $pr ?></td>
                            <td style="font-weight: 700; color: #0d3827;"><?= $tot ?></td>
                            <td class="remarks-col"><?= e($subjData['remarks'] ?? '') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <h2 class="section-title" style="margin-top: 30px;">Sensory & Co-Curricular Skill Ratings</h2>

                <table class="table-profile">
                    <thead>
                        <tr>
                            <th style="text-align: left;">Activity Profile</th>
                            <th style="width: 30%; text-align: center;">Assessed Descriptor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($activities as $key => $label):
                            $val = $reportCard['cocurriculum_profile'][$key] ?? 'Good';
                        ?>
                        <tr>
                            <td class="param-label"><?= $label ?></td>
                            <td class="rating-val" style="color: #0d3827; background-color: #fcfdfd;"><?= e($val) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ================= PAGE 5: FEEDBACK & SIGNATURES ================= -->
        <div class="page">
            <div class="double-border">
                <div class="page-header">
                    <div class="page-header-logo">
                        <img src="<?= $logoPath ?>" onerror="this.onerror=null; this.src='https://pearlspecialneeds.org/wp-content/uploads/2021/04/pearl-logo.png';" alt="Pearl Logo">
                    </div>
                    <div class="page-header-text">
                        <h3><?= e($schoolName) ?></h3>
                        <p>Progress Report · <?= e($semester) ?> (<?= e($academicYear) ?>)</p>
                    </div>
                </div>

                <h2 class="section-title">Attendance & Social Integration Log</h2>

                <table style="width: 100%; border-collapse: collapse; margin-bottom: 35px; border: 1px solid #cbd5e1; border-radius: 8px;">
                    <tr>
                        <td style="width: 33.33%; padding: 12px; text-align: center; background-color: #f8fafc; border-right: 1px solid #cbd5e1;">
                            <div style="font-size: 10px; text-transform: uppercase; color: #64748b; font-weight: 600; margin-bottom: 4px; letter-spacing: 0.5px;">Total Working Days</div>
                            <div style="font-size: 15px; font-weight: 700; color: <?= $primaryColor ?>;"><?= e($reportCard['attendance_profile']['total_days'] ?? '90') ?></div>
                        </td>
                        <td style="width: 33.33%; padding: 12px; text-align: center; background-color: #f8fafc; border-right: 1px solid #cbd5e1;">
                            <div style="font-size: 10px; text-transform: uppercase; color: #64748b; font-weight: 600; margin-bottom: 4px; letter-spacing: 0.5px;">Days Present</div>
                            <div style="font-size: 15px; font-weight: 700; color: <?= $primaryColor ?>;"><?= e($reportCard['attendance_profile']['present_days'] ?? '85') ?></div>
                        </td>
                        <td style="width: 33.33%; padding: 12px; text-align: center; background-color: #f8fafc;">
                            <div style="font-size: 10px; text-transform: uppercase; color: #64748b; font-weight: 600; margin-bottom: 4px; letter-spacing: 0.5px;">Punctuality Grade</div>
                            <div style="font-size: 15px; font-weight: 700; color: <?= $primaryColor ?>;"><?= e($reportCard['attendance_profile']['punctuality'] ?? 'A') ?></div>
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
