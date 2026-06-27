<?php
declare(strict_types=1);

use App\Services\DownloadService;

$selectedConferenceId = 1;
$conferenceStmt = db()->prepare('SELECT * FROM conferences WHERE id = 1 LIMIT 1');
$conferenceStmt->execute();
$conference = $conferenceStmt->fetch();

$typeStmt = db()->prepare('SELECT * FROM certificate_types WHERE conference_id = :conference_id ORDER BY name ASC');
$typeStmt->execute(['conference_id' => $selectedConferenceId]);
$certificateTypes = $typeStmt->fetchAll();

$selectedTypeId = (int) ($_GET['certificate_type_id'] ?? ($_POST['certificate_type_id'] ?? 0));
$availableTypeIds = array_map(static function (array $row): int {
    return (int) ($row['id'] ?? 0);
}, $certificateTypes);
if ($selectedTypeId > 0 && !in_array($selectedTypeId, $availableTypeIds, true)) {
    $selectedTypeId = 0;
}

$selectedFormat = (string) ($_GET['format'] ?? ($_POST['format'] ?? (setting('download_format', DEFAULT_DOWNLOAD_FORMAT) ?? 'pdf')));
$selectedSearch = trim((string) ($_GET['search'] ?? ($_POST['search'] ?? '')));
$selectedRange = (string) ($_GET['range'] ?? ($_POST['range'] ?? '30d'));
$rangeMap = ['7d' => 7, '30d' => 30, '90d' => 90, 'all' => 0];
if (!array_key_exists($selectedRange, $rangeMap)) {
    $selectedRange = '30d';
}

$pageNumber = max(1, (int) ($_GET['page_no'] ?? 1));
$pageSize = 12;

$selectedFormat = in_array($selectedFormat, ['pdf', 'jpg'], true) ? $selectedFormat : 'pdf';
$rangeDays = (int) ($rangeMap[$selectedRange] ?? 30);

$buildParticipantFilters = static function (string $prefix = 'flt_') use ($selectedConferenceId, $selectedTypeId, $selectedSearch): array {
    $where = ['p.conference_id = :' . $prefix . 'conference_id'];
    $params = [$prefix . 'conference_id' => $selectedConferenceId];

    if ($selectedTypeId > 0) {
        $where[] = 'p.certificate_type_id = :' . $prefix . 'certificate_type_id';
        $params[$prefix . 'certificate_type_id'] = $selectedTypeId;
    }

    if ($selectedSearch !== '') {
        $where[] = '(CONVERT(COALESCE(p.name, \'\') USING utf8mb4) COLLATE utf8mb4_general_ci LIKE :' . $prefix . 'search_name
                    OR CONVERT(COALESCE(p.email, \'\') USING utf8mb4) COLLATE utf8mb4_general_ci LIKE :' . $prefix . 'search_email
                    OR CONVERT(COALESCE(ct.name, \'\') USING utf8mb4) COLLATE utf8mb4_general_ci LIKE :' . $prefix . 'search_category
                    OR CONVERT(COALESCE(p.verify_code, \'\') USING utf8mb4) COLLATE utf8mb4_general_ci LIKE :' . $prefix . 'search_code)';
        $like = '%' . $selectedSearch . '%';
        $params[$prefix . 'search_name'] = $like;
        $params[$prefix . 'search_email'] = $like;
        $params[$prefix . 'search_category'] = $like;
        $params[$prefix . 'search_code'] = $like;
    }

    return [$where, $params];
};

$calculateChange = static function (int $current, int $previous): float {
    if ($previous <= 0) {
        return $current > 0 ? 100.0 : 0.0;
    }

    return (($current - $previous) / $previous) * 100;
};

if (is_post_request()) {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'download_zip') {
        $pathColumn = $selectedFormat === 'pdf' ? 'gc.pdf_path' : 'gc.jpg_path';

        [$zipWhere, $zipParams] = $buildParticipantFilters('zip_');
        $zipWhere[] = "COALESCE($pathColumn, '') <> ''";
        if ($rangeDays > 0) {
            $rangeStart = (new \DateTimeImmutable('today'))->modify('-' . ($rangeDays - 1) . ' days');
            $zipWhere[] = 'gc.generated_at >= :zip_generated_from';
            $zipParams['zip_generated_from'] = $rangeStart->format('Y-m-d 00:00:00');
        }

        $stmt = db()->prepare(
            'SELECT DISTINCT ' . $pathColumn . ' AS file_path
             FROM generated_certificates gc
             INNER JOIN participants p ON p.id = gc.participant_id
             INNER JOIN certificate_types ct ON ct.id = p.certificate_type_id
             WHERE ' . implode(' AND ', $zipWhere)
        );
        $stmt->execute($zipParams);

        $paths = [];
        foreach ($stmt->fetchAll() as $row) {
            if (!empty($row['file_path'])) {
                $paths[] = (string) $row['file_path'];
            }
        }

        try {
            $tmpDir = UPLOADS_ROOT . '/tmp';
            if (!is_dir($tmpDir) && !mkdir($tmpDir, 0777, true) && !is_dir($tmpDir)) {
                throw new \RuntimeException('Unable to create temporary ZIP folder.');
            }

            $zipName = safe_filename(
                $conference['name'] . '_' . $conference['year'] . '_' . ($selectedTypeId > 0 ? (string) $selectedTypeId : 'all') . '_' . $selectedFormat
            ) . '_' . date('Ymd_His') . '.zip';
            $zipAbsolutePath = $tmpDir . '/' . $zipName;

            $service = new DownloadService();
            $service->createZip($paths, $zipAbsolutePath);

            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . basename($zipAbsolutePath) . '"');
            header('Content-Length: ' . filesize($zipAbsolutePath));
            readfile($zipAbsolutePath);
            unlink($zipAbsolutePath);
            exit;
        } catch (\Throwable $exception) {
            flash('error', 'ZIP creation failed: ' . $exception->getMessage());
            redirect(url('downloads', [
                'conference_id' => $selectedConferenceId,
                'certificate_type_id' => $selectedTypeId,
                'format' => $selectedFormat,
                'search' => $selectedSearch,
                'range' => $selectedRange,
                'page_no' => $pageNumber,
            ]));
        }
    }
}

[$summaryWhere, $summaryParams] = $buildParticipantFilters('summary_');
if ($rangeDays > 0) {
    $summaryFrom = (new \DateTimeImmutable('today'))->modify('-' . ($rangeDays - 1) . ' days');
    $summaryWhere[] = 'gc.generated_at >= :summary_generated_from';
    $summaryParams['summary_generated_from'] = $summaryFrom->format('Y-m-d 00:00:00');
}

$summaryStmt = db()->prepare(
    'SELECT COUNT(DISTINCT gc.id) AS generated_total,
            COALESCE(SUM(gc.download_count), 0) AS download_total
     FROM generated_certificates gc
     INNER JOIN participants p ON p.id = gc.participant_id
     INNER JOIN certificate_types ct ON ct.id = p.certificate_type_id
     WHERE ' . implode(' AND ', $summaryWhere)
);
$summaryStmt->execute($summaryParams);
$summaryData = $summaryStmt->fetch() ?: [];

[$participantWhere, $participantParams] = $buildParticipantFilters('participant_');
$participantSummaryStmt = db()->prepare(
    "SELECT COUNT(*) AS total_recipients,
            SUM(CASE WHEN p.status = 'pending' THEN 1 ELSE 0 END) AS pending_total,
            SUM(CASE WHEN p.status IN ('generated', 'emailed') THEN 1 ELSE 0 END) AS active_total
     FROM participants p
     INNER JOIN certificate_types ct ON ct.id = p.certificate_type_id
     WHERE " . implode(' AND ', $participantWhere)
);
$participantSummaryStmt->execute($participantParams);
$participantSummary = $participantSummaryStmt->fetch() ?: [];

[$emailWhere, $emailParams] = $buildParticipantFilters('email_');
if ($rangeDays > 0) {
    $emailFrom = (new \DateTimeImmutable('today'))->modify('-' . ($rangeDays - 1) . ' days');
    $emailWhere[] = 'el.sent_at >= :email_sent_from';
    $emailParams['email_sent_from'] = $emailFrom->format('Y-m-d 00:00:00');
}

$emailSummaryStmt = db()->prepare(
    "SELECT SUM(CASE WHEN el.status = 'sent' THEN 1 ELSE 0 END) AS sent_total,
            SUM(CASE WHEN el.status = 'failed' THEN 1 ELSE 0 END) AS failed_total
     FROM email_logs el
     INNER JOIN participants p ON p.id = el.participant_id
     INNER JOIN certificate_types ct ON ct.id = p.certificate_type_id
     WHERE " . implode(' AND ', $emailWhere)
);
$emailSummaryStmt->execute($emailParams);
$emailSummary = $emailSummaryStmt->fetch() ?: [];

$totalGenerated = (int) ($summaryData['generated_total'] ?? 0);
$totalDownloads = (int) ($summaryData['download_total'] ?? 0);
$totalRecipients = (int) ($participantSummary['total_recipients'] ?? 0);
$pendingRecipients = (int) ($participantSummary['pending_total'] ?? 0);
$activeRecipients = (int) ($participantSummary['active_total'] ?? 0);
$sentEmails = (int) ($emailSummary['sent_total'] ?? 0);
$failedEmails = (int) ($emailSummary['failed_total'] ?? 0);

$issuedGrowth = 0.0;
$downloadGrowth = 0.0;
$sentGrowth = 0.0;
if ($rangeDays > 0) {
    $currentStart = (new \DateTimeImmutable('today'))->modify('-' . ($rangeDays - 1) . ' days');
    $previousStart = $currentStart->modify('-' . $rangeDays . ' days');

    [$previousGeneratedWhere, $previousGeneratedParams] = $buildParticipantFilters('prev_gen_');
    $previousGeneratedWhere[] = 'gc.generated_at >= :prev_gen_from';
    $previousGeneratedWhere[] = 'gc.generated_at < :prev_gen_to';
    $previousGeneratedParams['prev_gen_from'] = $previousStart->format('Y-m-d 00:00:00');
    $previousGeneratedParams['prev_gen_to'] = $currentStart->format('Y-m-d 00:00:00');

    $previousGeneratedStmt = db()->prepare(
        'SELECT COUNT(DISTINCT gc.id) AS generated_total,
                COALESCE(SUM(gc.download_count), 0) AS download_total
         FROM generated_certificates gc
         INNER JOIN participants p ON p.id = gc.participant_id
         INNER JOIN certificate_types ct ON ct.id = p.certificate_type_id
         WHERE ' . implode(' AND ', $previousGeneratedWhere)
    );
    $previousGeneratedStmt->execute($previousGeneratedParams);
    $previousGenerated = $previousGeneratedStmt->fetch() ?: [];

    [$previousEmailWhere, $previousEmailParams] = $buildParticipantFilters('prev_email_');
    $previousEmailWhere[] = 'el.sent_at >= :prev_email_from';
    $previousEmailWhere[] = 'el.sent_at < :prev_email_to';
    $previousEmailParams['prev_email_from'] = $previousStart->format('Y-m-d 00:00:00');
    $previousEmailParams['prev_email_to'] = $currentStart->format('Y-m-d 00:00:00');

    $previousEmailStmt = db()->prepare(
        "SELECT SUM(CASE WHEN el.status = 'sent' THEN 1 ELSE 0 END) AS sent_total
         FROM email_logs el
         INNER JOIN participants p ON p.id = el.participant_id
         INNER JOIN certificate_types ct ON ct.id = p.certificate_type_id
         WHERE " . implode(' AND ', $previousEmailWhere)
    );
    $previousEmailStmt->execute($previousEmailParams);
    $previousEmail = $previousEmailStmt->fetch() ?: [];

    $issuedGrowth = $calculateChange($totalGenerated, (int) ($previousGenerated['generated_total'] ?? 0));
    $downloadGrowth = $calculateChange($totalDownloads, (int) ($previousGenerated['download_total'] ?? 0));
    $sentGrowth = $calculateChange($sentEmails, (int) ($previousEmail['sent_total'] ?? 0));
}

$chartDays = $rangeDays > 0 ? $rangeDays : 14;
$trendStartDate = (new \DateTimeImmutable('today'))->modify('-' . ($chartDays - 1) . ' days');

[$trendWhere, $trendParams] = $buildParticipantFilters('trend_');
$trendWhere[] = 'gc.generated_at >= :trend_from';
$trendParams['trend_from'] = $trendStartDate->format('Y-m-d 00:00:00');

$trendStmt = db()->prepare(
    'SELECT DATE(gc.generated_at) AS day_key,
            COUNT(DISTINCT gc.id) AS generated_total,
            COALESCE(SUM(gc.download_count), 0) AS download_total
     FROM generated_certificates gc
     INNER JOIN participants p ON p.id = gc.participant_id
     INNER JOIN certificate_types ct ON ct.id = p.certificate_type_id
     WHERE ' . implode(' AND ', $trendWhere) . '
     GROUP BY DATE(gc.generated_at)
     ORDER BY DATE(gc.generated_at) ASC'
);
$trendStmt->execute($trendParams);
$trendRows = $trendStmt->fetchAll();

$trendMap = [];
foreach ($trendRows as $row) {
    $trendMap[(string) ($row['day_key'] ?? '')] = [
        'generated' => (int) ($row['generated_total'] ?? 0),
        'downloads' => (int) ($row['download_total'] ?? 0),
    ];
}

$dailySeries = [];
$seriesCursor = $trendStartDate;
$seriesEnd = new \DateTimeImmutable('today');
while ($seriesCursor <= $seriesEnd) {
    $dayKey = $seriesCursor->format('Y-m-d');
    $dayData = $trendMap[$dayKey] ?? ['generated' => 0, 'downloads' => 0];
    $dailySeries[] = [
        'day_key' => $dayKey,
        'day_label' => $seriesCursor->format('M d'),
        'generated_total' => (int) ($dayData['generated'] ?? 0),
        'download_total' => (int) ($dayData['downloads'] ?? 0),
    ];
    $seriesCursor = $seriesCursor->modify('+1 day');
}

[$templateWhere, $templateParams] = $buildParticipantFilters('template_');
$generatedCase = 'gc.id';
$downloadCase = 'gc.download_count';
if ($rangeDays > 0) {
    $templateFrom = (new \DateTimeImmutable('today'))->modify('-' . ($rangeDays - 1) . ' days');
    $templateGeneratedFrom = $templateFrom->format('Y-m-d 00:00:00');
    $templateParams['template_generated_from_generated'] = $templateGeneratedFrom;
    $templateParams['template_generated_from_download'] = $templateGeneratedFrom;
    $generatedCase = 'CASE WHEN gc.generated_at >= :template_generated_from_generated THEN gc.id END';
    $downloadCase = 'CASE WHEN gc.generated_at >= :template_generated_from_download THEN gc.download_count ELSE 0 END';
}

$templateStmt = db()->prepare(
    'SELECT ct.id,
            ct.name,
            COUNT(DISTINCT p.id) AS recipient_total,
            COUNT(DISTINCT ' . $generatedCase . ') AS generated_total,
            COALESCE(SUM(' . $downloadCase . '), 0) AS download_total
     FROM participants p
     INNER JOIN certificate_types ct ON ct.id = p.certificate_type_id
     LEFT JOIN generated_certificates gc ON gc.participant_id = p.id
     WHERE ' . implode(' AND ', $templateWhere) . '
     GROUP BY ct.id, ct.name
     ORDER BY generated_total DESC, download_total DESC, ct.name ASC
     LIMIT 6'
);
$templateStmt->execute($templateParams);
$templateBreakdown = $templateStmt->fetchAll();

[$generatedEventWhere, $generatedEventParams] = $buildParticipantFilters('event_gen_');
if ($rangeDays > 0) {
    $eventGeneratedFrom = (new \DateTimeImmutable('today'))->modify('-' . ($rangeDays - 1) . ' days');
    $generatedEventWhere[] = 'gc.generated_at >= :event_gen_from';
    $generatedEventParams['event_gen_from'] = $eventGeneratedFrom->format('Y-m-d 00:00:00');
}

$generatedEventStmt = db()->prepare(
    'SELECT gc.generated_at AS event_at,
            p.name AS participant_name,
            ct.name AS certificate_type_name,
            gc.download_count
     FROM generated_certificates gc
     INNER JOIN participants p ON p.id = gc.participant_id
     INNER JOIN certificate_types ct ON ct.id = p.certificate_type_id
     WHERE ' . implode(' AND ', $generatedEventWhere) . '
     ORDER BY gc.generated_at DESC
     LIMIT 8'
);
$generatedEventStmt->execute($generatedEventParams);
$generatedEvents = $generatedEventStmt->fetchAll();

[$emailEventWhere, $emailEventParams] = $buildParticipantFilters('event_email_');
if ($rangeDays > 0) {
    $eventEmailFrom = (new \DateTimeImmutable('today'))->modify('-' . ($rangeDays - 1) . ' days');
    $emailEventWhere[] = 'el.sent_at >= :event_email_from';
    $emailEventParams['event_email_from'] = $eventEmailFrom->format('Y-m-d 00:00:00');
}

$emailEventStmt = db()->prepare(
    'SELECT el.sent_at AS event_at,
            el.status,
            p.name AS participant_name,
            ct.name AS certificate_type_name
     FROM email_logs el
     INNER JOIN participants p ON p.id = el.participant_id
     INNER JOIN certificate_types ct ON ct.id = p.certificate_type_id
     WHERE ' . implode(' AND ', $emailEventWhere) . '
     ORDER BY el.sent_at DESC
     LIMIT 8'
);
$emailEventStmt->execute($emailEventParams);
$emailEvents = $emailEventStmt->fetchAll();

$recentEvents = [];
foreach ($generatedEvents as $eventRow) {
    $recentEvents[] = [
        'event_at' => (string) ($eventRow['event_at'] ?? ''),
        'event_label' => 'Certificate issued',
        'event_type' => 'generated',
        'participant_name' => (string) ($eventRow['participant_name'] ?? 'Participant'),
        'certificate_type_name' => (string) ($eventRow['certificate_type_name'] ?? 'Template'),
        'meta' => (int) ($eventRow['download_count'] ?? 0) . ' downloads',
    ];
}

foreach ($emailEvents as $eventRow) {
    $isSent = (string) ($eventRow['status'] ?? '') === 'sent';
    $recentEvents[] = [
        'event_at' => (string) ($eventRow['event_at'] ?? ''),
        'event_label' => $isSent ? 'Delivery sent' : 'Delivery failed',
        'event_type' => $isSent ? 'email_sent' : 'email_failed',
        'participant_name' => (string) ($eventRow['participant_name'] ?? 'Participant'),
        'certificate_type_name' => (string) ($eventRow['certificate_type_name'] ?? 'Template'),
        'meta' => $isSent ? 'Email delivered' : 'Retry required',
    ];
}

usort($recentEvents, static function (array $left, array $right): int {
    return strcmp((string) ($right['event_at'] ?? ''), (string) ($left['event_at'] ?? ''));
});
$recentEvents = array_slice($recentEvents, 0, 10);

[$countWhere, $countParams] = $buildParticipantFilters('count_');
if ($selectedFormat === 'pdf') {
    $countWhere[] = "COALESCE(gc.pdf_path, '') <> ''";
} else {
    $countWhere[] = "COALESCE(gc.jpg_path, '') <> ''";
}

if ($rangeDays > 0) {
    $countFrom = (new \DateTimeImmutable('today'))->modify('-' . ($rangeDays - 1) . ' days');
    $countWhere[] = 'gc.generated_at >= :count_generated_from';
    $countParams['count_generated_from'] = $countFrom->format('Y-m-d 00:00:00');
}

$countStmt = db()->prepare(
    'SELECT COUNT(DISTINCT gc.id) AS total_rows
     FROM generated_certificates gc
     INNER JOIN participants p ON p.id = gc.participant_id
     INNER JOIN certificate_types ct ON ct.id = p.certificate_type_id
     WHERE ' . implode(' AND ', $countWhere)
);
$countStmt->execute($countParams);
$totalRows = (int) (($countStmt->fetch()['total_rows'] ?? 0));
$totalPages = max(1, (int) ceil($totalRows / $pageSize));
if ($pageNumber > $totalPages) {
    $pageNumber = $totalPages;
}

$offset = ($pageNumber - 1) * $pageSize;

[$listWhere, $listParams] = $buildParticipantFilters('list_');
if ($selectedFormat === 'pdf') {
    $listWhere[] = "COALESCE(gc.pdf_path, '') <> ''";
} else {
    $listWhere[] = "COALESCE(gc.jpg_path, '') <> ''";
}

if ($rangeDays > 0) {
    $listFrom = (new \DateTimeImmutable('today'))->modify('-' . ($rangeDays - 1) . ' days');
    $listWhere[] = 'gc.generated_at >= :list_generated_from';
    $listParams['list_generated_from'] = $listFrom->format('Y-m-d 00:00:00');
}

$listStmt = db()->prepare(
    'SELECT p.id AS participant_id,
            p.name,
            p.email,
            p.verify_code,
            ct.name AS certificate_type_name,
            gc.jpg_path,
            gc.pdf_path,
            gc.generated_at,
            gc.download_count
     FROM generated_certificates gc
     INNER JOIN participants p ON p.id = gc.participant_id
     INNER JOIN certificate_types ct ON ct.id = p.certificate_type_id
     WHERE ' . implode(' AND ', $listWhere) . '
     ORDER BY gc.generated_at DESC, p.id DESC
     LIMIT :limit OFFSET :offset'
);
foreach ($listParams as $key => $value) {
    $listStmt->bindValue(':' . $key, $value);
}
$listStmt->bindValue(':limit', $pageSize, \PDO::PARAM_INT);
$listStmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
$listStmt->execute();
$rows = $listStmt->fetchAll();

if ((string) ($_GET['export'] ?? '') === 'csv') {
    $fileName = safe_filename((string) ($conference['name'] ?? 'conference') . '_downloads_' . date('Ymd_His')) . '.csv';
    stream_csv_download($fileName, ['Recipient', 'Email', 'Template', 'Generated At', 'Downloads'], $rows, static function (array $row): array {
        return [
            (string) ($row['name'] ?? ''),
            (string) ($row['email'] ?? ''),
            (string) ($row['certificate_type_name'] ?? ''),
            (string) ($row['generated_at'] ?? ''),
            (string) ($row['download_count'] ?? '0'),
        ];
    });
}

$rangeStart = $totalRows > 0 ? ($offset + 1) : 0;
$rangeEnd = $totalRows > 0 ? min($offset + count($rows), $totalRows) : 0;

render_view('downloads.php', [
    'pageTitle' => 'Analytics',
    'conference' => $conference,
    'certificateTypes' => $certificateTypes,
    'selectedConferenceId' => $selectedConferenceId,
    'selectedTypeId' => $selectedTypeId,
    'selectedFormat' => $selectedFormat,
    'selectedSearch' => $selectedSearch,
    'selectedRange' => $selectedRange,
    'rangeDays' => $rangeDays,
    'pageNumber' => $pageNumber,
    'totalPages' => $totalPages,
    'totalRows' => $totalRows,
    'rangeStart' => $rangeStart,
    'rangeEnd' => $rangeEnd,
    'totalGenerated' => $totalGenerated,
    'totalDownloads' => $totalDownloads,
    'totalRecipients' => $totalRecipients,
    'pendingRecipients' => $pendingRecipients,
    'activeRecipients' => $activeRecipients,
    'sentEmails' => $sentEmails,
    'failedEmails' => $failedEmails,
    'issuedGrowth' => $issuedGrowth,
    'downloadGrowth' => $downloadGrowth,
    'sentGrowth' => $sentGrowth,
    'dailySeries' => $dailySeries,
    'templateBreakdown' => $templateBreakdown,
    'recentEvents' => $recentEvents,
    'rows' => $rows,
    'headerMeta' => [
        'actions' => [
            [
                'label' => 'Export CSV',
                'url' => url('downloads', ['conference_id' => $selectedConferenceId, 'format' => $selectedFormat, 'range' => $rangeDays, 'export' => 'csv']),
                'class' => 'button-muted',
            ],
        ],
    ],
]);
