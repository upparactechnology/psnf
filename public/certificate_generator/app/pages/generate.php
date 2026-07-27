<?php
declare(strict_types=1);

use App\Services\CertificateGenerator;
use App\Services\PdfService;

$selectedConferenceId = 1;
$conferenceStmt = db()->prepare('SELECT * FROM conferences WHERE id = 1 LIMIT 1');
$conferenceStmt->execute();
$conference = $conferenceStmt->fetch();

$typeStmt = db()->prepare('SELECT * FROM certificate_types WHERE conference_id = :conference_id ORDER BY name ASC');
$typeStmt->execute(['conference_id' => $selectedConferenceId]);
$certificateTypes = $typeStmt->fetchAll();

$requestedTypeId = (int) ($_GET['certificate_type_id'] ?? ($_POST['certificate_type_id'] ?? 0));
$validTypeIds = array_map(static fn (array $row): int => (int) ($row['id'] ?? 0), $certificateTypes);
$selectedTypeId = in_array($requestedTypeId, $validTypeIds, true) ? $requestedTypeId : 0;

$statusFilter = strtolower(trim((string) ($_GET['status'] ?? ($_POST['status'] ?? 'all'))));
$allowedStatusFilters = ['all', 'issued', 'sent', 'viewed', 'pending', 'failed'];
if (!in_array($statusFilter, $allowedStatusFilters, true)) {
    $statusFilter = 'all';
}

$dateRange = strtolower(trim((string) ($_GET['date_range'] ?? ($_POST['date_range'] ?? '30d'))));
$allowedDateRanges = ['7d', '30d', '90d', 'all'];
if (!in_array($dateRange, $allowedDateRanges, true)) {
    $dateRange = '30d';
}

$searchTerm = trim((string) ($_GET['search'] ?? ($_POST['search'] ?? '')));
$pageNumber = max(1, (int) ($_GET['page_no'] ?? ($_POST['page_no'] ?? 1)));
$pageSize = 10;

$buildFilterParams = static function (array $overrides = []) use (
    $selectedConferenceId,
    $selectedTypeId,
    $statusFilter,
    $dateRange,
    $searchTerm,
    $pageNumber
): array {
    $params = [
        'conference_id' => $selectedConferenceId,
        'certificate_type_id' => $selectedTypeId,
        'status' => $statusFilter,
        'date_range' => $dateRange,
        'search' => $searchTerm,
        'page_no' => $pageNumber,
    ];

    if ($params['certificate_type_id'] <= 0) {
        unset($params['certificate_type_id']);
    }

    if ($params['search'] === '') {
        unset($params['search']);
    }

    if ($params['status'] === 'all') {
        unset($params['status']);
    }

    if ($params['date_range'] === '30d') {
        unset($params['date_range']);
    }

    if ($params['page_no'] <= 1) {
        unset($params['page_no']);
    }

    foreach ($overrides as $key => $value) {
        $params[$key] = $value;
    }

    foreach (['search', 'status', 'date_range', 'certificate_type_id', 'page_no'] as $optionalKey) {
        if (!array_key_exists($optionalKey, $params)) {
            continue;
        }

        if ($optionalKey === 'search' && trim((string) $params[$optionalKey]) === '') {
            unset($params[$optionalKey]);
        }

        if ($optionalKey === 'status' && (string) $params[$optionalKey] === 'all') {
            unset($params[$optionalKey]);
        }

        if ($optionalKey === 'date_range' && (string) $params[$optionalKey] === '30d') {
            unset($params[$optionalKey]);
        }

        if ($optionalKey === 'certificate_type_id' && (int) $params[$optionalKey] <= 0) {
            unset($params[$optionalKey]);
        }

        if ($optionalKey === 'page_no' && (int) $params[$optionalKey] <= 1) {
            unset($params[$optionalKey]);
        }
    }

    return $params;
};

if (is_post_request()) {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');

    $generator = new CertificateGenerator(db(), new PdfService());

    if ($action === 'generate_bulk') {
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }
        @ini_set('max_execution_time', '0');
        @ignore_user_abort(true);

        try {
            $typeIdOrNull = $selectedTypeId > 0 ? $selectedTypeId : null;
            $generationResult = $generator->generateBulk($selectedConferenceId, $typeIdOrNull);

            $generated = (int) ($generationResult['generated'] ?? 0);
            $failed = (int) ($generationResult['failed'] ?? 0);
            $total = (int) ($generationResult['total'] ?? 0);
            $errors = is_array($generationResult['errors'] ?? null) ? $generationResult['errors'] : [];

            if ($total <= 0) {
                flash('warning', 'No participants found for bulk issue in current filters.');
            } elseif ($failed > 0) {
                $sample = array_slice($errors, 0, 3);
                $suffix = $sample === [] ? '' : ' Example: ' . implode(' | ', $sample);
                flash('warning', 'Bulk issue completed. Success: ' . $generated . ', Failed: ' . $failed . '.' . $suffix);
            } else {
                flash('success', 'Bulk issue completed. Success: ' . $generated . ', Failed: ' . $failed . '.');
            }
        } catch (\Throwable $exception) {
            flash('error', 'Bulk generation failed: ' . $exception->getMessage());
        }

        redirect(url('generate', $buildFilterParams(['page_no' => 1])));
    }

    if ($action === 'generate_single') {
        $participantId = (int) ($_POST['participant_id'] ?? 0);

        try {
            $generator->generateForParticipant($participantId);
            flash('success', 'Certificate generated for participant #' . $participantId . '.');
        } catch (\Throwable $exception) {
            flash('error', 'Single generation failed: ' . $exception->getMessage());
        }

        redirect(url('generate', $buildFilterParams()));
    }
}

$now = new DateTimeImmutable('now');
$dateFrom = null;
if ($dateRange === '7d') {
    $dateFrom = $now->modify('-7 days')->format('Y-m-d H:i:s');
} elseif ($dateRange === '30d') {
    $dateFrom = $now->modify('-30 days')->format('Y-m-d H:i:s');
} elseif ($dateRange === '90d') {
    $dateFrom = $now->modify('-90 days')->format('Y-m-d H:i:s');
}

$whereBase = ['p.conference_id = :conference_id'];
$paramsBase = ['conference_id' => $selectedConferenceId];

if ($selectedTypeId > 0) {
    $whereBase[] = 'p.certificate_type_id = :certificate_type_id';
    $paramsBase['certificate_type_id'] = $selectedTypeId;
}

if ($searchTerm !== '') {
    $whereBase[] = '(CONVERT(COALESCE(p.name, \'\') USING utf8mb4) COLLATE utf8mb4_general_ci LIKE :search_name
                    OR CONVERT(COALESCE(p.email, \'\') USING utf8mb4) COLLATE utf8mb4_general_ci LIKE :search_email
                    OR CONVERT(COALESCE(ct.name, \'\') USING utf8mb4) COLLATE utf8mb4_general_ci LIKE :search_template)';
    $searchLike = '%' . $searchTerm . '%';
    $paramsBase['search_name'] = $searchLike;
    $paramsBase['search_email'] = $searchLike;
    $paramsBase['search_template'] = $searchLike;
}

if ($dateFrom !== null) {
    $whereBase[] = 'COALESCE(gc.generated_at, p.created_at) >= :date_from';
    $paramsBase['date_from'] = $dateFrom;
}

$whereStatus = $whereBase;
$paramsStatus = $paramsBase;

if ($statusFilter === 'issued') {
    $whereStatus[] = '(p.status IN (\'generated\', \'emailed\') OR gc.generated_at IS NOT NULL)';
} elseif ($statusFilter === 'sent') {
    $whereStatus[] = 'p.status = :status_sent';
    $paramsStatus['status_sent'] = 'emailed';
} elseif ($statusFilter === 'viewed') {
    $whereStatus[] = 'COALESCE(gc.download_count, 0) > 0';
} elseif ($statusFilter === 'pending') {
    $whereStatus[] = 'p.status = :status_pending';
    $paramsStatus['status_pending'] = 'pending';
} elseif ($statusFilter === 'failed') {
    $whereStatus[] = 'p.status = :status_failed';
    $paramsStatus['status_failed'] = 'failed';
}

$whereBaseSql = implode(' AND ', $whereBase);
$whereStatusSql = implode(' AND ', $whereStatus);

$statusLabelForRow = static function (array $row): string {
    $rawStatus = strtolower((string) ($row['status'] ?? 'pending'));
    $downloads = (int) ($row['download_count'] ?? 0);

    if ($downloads > 0) {
        return 'viewed';
    }

    if ($rawStatus === 'emailed') {
        return 'sent';
    }

    if ($rawStatus === 'generated' || !empty($row['generated_at'])) {
        return 'issued';
    }

    if ($rawStatus === 'failed') {
        return 'failed';
    }

    return 'pending';
};

if ((string) ($_GET['export'] ?? '') === 'csv') {
    $exportStmt = db()->prepare(
        'SELECT p.name, p.email, p.status, p.created_at, p.issued_date,
                ct.name AS template_name,
                gc.generated_at, gc.download_count
         FROM participants p
         INNER JOIN certificate_types ct ON ct.id = p.certificate_type_id
         LEFT JOIN generated_certificates gc ON gc.participant_id = p.id
         WHERE ' . $whereStatusSql . '
         ORDER BY COALESCE(gc.generated_at, p.created_at) DESC, p.id DESC'
    );
    $exportStmt->execute($paramsStatus);
    $exportRows = $exportStmt->fetchAll();

    $fileName = safe_filename((string) ($conference['name'] ?? 'conference') . '_certificates_' . date('Ymd_His')) . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');

    $output = fopen('php://output', 'wb');
    if ($output === false) {
        exit;
    }

    fputcsv($output, ['Recipient', 'Email', 'Template', 'Date Issued', 'Status', 'Downloads']);

    foreach ($exportRows as $row) {
        $issuedAt = (string) ($row['issued_date'] ?? $row['generated_at'] ?? $row['created_at'] ?? '');
        $statusLabel = $statusLabelForRow($row);
        fputcsv($output, [
            (string) ($row['name'] ?? ''),
            (string) ($row['email'] ?? ''),
            (string) ($row['template_name'] ?? ''),
            $issuedAt,
            strtoupper($statusLabel),
            (string) ((int) ($row['download_count'] ?? 0)),
        ]);
    }

    fclose($output);
    exit;
}

$summaryStmt = db()->prepare(
    'SELECT COUNT(*) AS total_count,
            SUM(CASE WHEN (p.status IN (\'generated\', \'emailed\') OR gc.generated_at IS NOT NULL) THEN 1 ELSE 0 END) AS issued_count,
            SUM(CASE WHEN p.status = \'emailed\' THEN 1 ELSE 0 END) AS sent_count,
            SUM(CASE WHEN COALESCE(gc.download_count, 0) > 0 THEN 1 ELSE 0 END) AS viewed_count,
            COALESCE(SUM(gc.download_count), 0) AS download_total
     FROM participants p
     LEFT JOIN generated_certificates gc ON gc.participant_id = p.id
     INNER JOIN certificate_types ct ON ct.id = p.certificate_type_id
     WHERE ' . $whereBaseSql
);
$summaryStmt->execute($paramsBase);
$summary = $summaryStmt->fetch() ?: [
    'total_count' => 0,
    'issued_count' => 0,
    'sent_count' => 0,
    'viewed_count' => 0,
    'download_total' => 0,
];

$countStmt = db()->prepare(
    'SELECT COUNT(*)
     FROM participants p
     INNER JOIN certificate_types ct ON ct.id = p.certificate_type_id
     LEFT JOIN generated_certificates gc ON gc.participant_id = p.id
     WHERE ' . $whereStatusSql
);
$countStmt->execute($paramsStatus);
$totalRows = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalRows / $pageSize));

if ($pageNumber > $totalPages) {
    $pageNumber = $totalPages;
}

$offset = ($pageNumber - 1) * $pageSize;

$listStmt = db()->prepare(
    'SELECT p.id AS participant_id, p.name, p.email, p.status, p.created_at, p.issued_date,
            ct.name AS template_name,
            gc.jpg_path, gc.pdf_path, gc.generated_at, gc.download_count
     FROM participants p
     INNER JOIN certificate_types ct ON ct.id = p.certificate_type_id
     LEFT JOIN generated_certificates gc ON gc.participant_id = p.id
     WHERE ' . $whereStatusSql . '
     ORDER BY COALESCE(gc.generated_at, p.created_at) DESC, p.id DESC
     LIMIT :limit OFFSET :offset'
);

foreach ($paramsStatus as $key => $value) {
    $listStmt->bindValue(':' . $key, $value);
}
$listStmt->bindValue(':limit', $pageSize, PDO::PARAM_INT);
$listStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$listStmt->execute();
$rows = $listStmt->fetchAll();

$rangeStart = $totalRows === 0 ? 0 : (($pageNumber - 1) * $pageSize) + 1;
$rangeEnd = $totalRows === 0 ? 0 : min($totalRows, $pageNumber * $pageSize);

render_view('generate.php', [
    'pageTitle' => 'Certificates',
    'conference' => $conference,
    'selectedConferenceId' => $selectedConferenceId,
    'certificateTypes' => $certificateTypes,
    'selectedTypeId' => $selectedTypeId,
    'statusFilter' => $statusFilter,
    'dateRange' => $dateRange,
    'searchTerm' => $searchTerm,
    'pageNumber' => $pageNumber,
    'pageSize' => $pageSize,
    'totalRows' => $totalRows,
    'totalPages' => $totalPages,
    'rangeStart' => $rangeStart,
    'rangeEnd' => $rangeEnd,
    'rows' => $rows,
    'statusTotals' => [
        'all' => (int) ($summary['total_count'] ?? 0),
        'issued' => (int) ($summary['issued_count'] ?? 0),
        'sent' => (int) ($summary['sent_count'] ?? 0),
        'viewed' => (int) ($summary['viewed_count'] ?? 0),
    ],
    'totalDownloads' => (int) ($summary['download_total'] ?? 0),
]);
