<?php
declare(strict_types=1);

$scope = conference_scope_sql();

$calculateTrend = static function (float $current, float $previous): float {
    if ($previous <= 0.0) {
        return $current > 0.0 ? 100.0 : 0.0;
    }

    return round((($current - $previous) / $previous) * 100, 1);
};

$calculateDirectorySize = static function (string $directory): int {
    if (!is_dir($directory)) {
        return 0;
    }

    $total = 0;

    try {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isFile()) {
                $size = $fileInfo->getSize();
                $total += is_int($size) ? $size : 0;
            }
        }
    } catch (Throwable $exception) {
        return 0;
    }

    return $total;
};

$monthStart = new DateTimeImmutable('first day of this month 00:00:00');
$nextMonthStart = $monthStart->modify('+1 month');
$previousMonthStart = $monthStart->modify('-1 month');

$currentPeriodLabel = $monthStart->format('M j, Y') . ' - ' . $monthStart->modify('last day of this month')->format('M j, Y');

$countConferencesStmt = db()->prepare('SELECT COUNT(*) FROM conferences c WHERE ' . $scope['sql']);
$exec = static function (PDOStatement $stmt, array $params, string $label = ''): void {
    try {
        $stmt->execute($params);
    } catch (PDOException $e) {
        $msg = sprintf("SQL execute failed (%s): %s; params=%s", $label, $e->getMessage(), json_encode($params));
        error_log($msg);
        throw new PDOException($msg, (int) $e->getCode(), $e);
    }
};

$exec($countConferencesStmt, $scope['params'], 'countConferences');
$totalConferences = (int) $countConferencesStmt->fetchColumn();

$countParticipantsStmt = db()->prepare(
    'SELECT COUNT(*)
     FROM participants p
     INNER JOIN conferences c ON c.id = p.conference_id
     WHERE ' . $scope['sql']
);
$exec($countParticipantsStmt, $scope['params'], 'countParticipants');
$totalParticipants = (int) $countParticipantsStmt->fetchColumn();

$countGeneratedStmt = db()->prepare(
    'SELECT COUNT(*)
     FROM generated_certificates g
     INNER JOIN participants p ON p.id = g.participant_id
     INNER JOIN conferences c ON c.id = p.conference_id
     WHERE ' . $scope['sql']
);
$exec($countGeneratedStmt, $scope['params'], 'countGenerated');
$totalGenerated = (int) $countGeneratedStmt->fetchColumn();

$countEmailsStmt = db()->prepare(
    'SELECT COUNT(*)
     FROM email_logs e
     INNER JOIN participants p ON p.id = e.participant_id
     INNER JOIN conferences c ON c.id = p.conference_id
     WHERE e.status = :status AND ' . $scope['sql']
);
$params = array_merge(['status' => 'sent'], $scope['params']);
$exec($countEmailsStmt, $params, 'countEmails');
$totalEmails = (int) $countEmailsStmt->fetchColumn();

$countOpenedStmt = db()->prepare(
    'SELECT COUNT(*)
     FROM generated_certificates g
     INNER JOIN participants p ON p.id = g.participant_id
     INNER JOIN conferences c ON c.id = p.conference_id
     WHERE p.status = :status
         AND g.download_count > 0
         AND ' . $scope['sql']
);
$openedParams = array_merge(['status' => 'emailed'], $scope['params']);
$exec($countOpenedStmt, $openedParams, 'countOpened');
$totalOpened = (int) $countOpenedStmt->fetchColumn();
$openRate = $totalEmails > 0 ? round(($totalOpened / $totalEmails) * 100, 1) : 0.0;

$templateCountStmt = db()->prepare(
    "SELECT
         COUNT(*) AS total_templates,
         SUM(CASE WHEN ct.template_path IS NOT NULL AND ct.template_path <> '' THEN 1 ELSE 0 END) AS active_templates
     FROM certificate_types ct
     INNER JOIN conferences c ON c.id = ct.conference_id
     WHERE " . $scope['sql']
);
$exec($templateCountStmt, $scope['params'], 'templateCounts');
$templateCounts = $templateCountStmt->fetch() ?: [];
$totalTemplates = (int) ($templateCounts['total_templates'] ?? 0);
$activeTemplates = (int) ($templateCounts['active_templates'] ?? 0);

$periodParams = array_merge([
    'current_start' => $monthStart->format('Y-m-d H:i:s'),
    'current_end' => $nextMonthStart->format('Y-m-d H:i:s'),
    'previous_start' => $previousMonthStart->format('Y-m-d H:i:s'),
    // Some drivers do not accept the same named placeholder repeated
    // in a prepared statement when emulation is disabled. Provide
    // duplicate names for repeated boundaries used in CASE expressions.
    'current_start_2' => $monthStart->format('Y-m-d H:i:s'),
], $scope['params']);

$generatedPeriodStmt = db()->prepare(
    'SELECT
         SUM(CASE WHEN gc.generated_at >= :current_start AND gc.generated_at < :current_end THEN 1 ELSE 0 END) AS current_count,
         SUM(CASE WHEN gc.generated_at >= :previous_start AND gc.generated_at < :current_start_2 THEN 1 ELSE 0 END) AS previous_count
     FROM generated_certificates gc
     INNER JOIN participants p ON p.id = gc.participant_id
     INNER JOIN conferences c ON c.id = p.conference_id
     WHERE ' . $scope['sql']
);
$exec($generatedPeriodStmt, $periodParams, 'generatedPeriod');
$generatedPeriod = $generatedPeriodStmt->fetch() ?: [];
$currentGenerated = (int) ($generatedPeriod['current_count'] ?? 0);
$previousGenerated = (int) ($generatedPeriod['previous_count'] ?? 0);

$emailsPeriodStmt = db()->prepare(
    'SELECT
         SUM(CASE WHEN e.sent_at >= :current_start AND e.sent_at < :current_end THEN 1 ELSE 0 END) AS current_count,
         SUM(CASE WHEN e.sent_at >= :previous_start AND e.sent_at < :current_start_2 THEN 1 ELSE 0 END) AS previous_count
     FROM email_logs e
     INNER JOIN participants p ON p.id = e.participant_id
     INNER JOIN conferences c ON c.id = p.conference_id
     WHERE e.status = :status
         AND ' . $scope['sql']
);
$exec($emailsPeriodStmt, array_merge(['status' => 'sent'], $periodParams), 'emailsPeriod');
$emailPeriod = $emailsPeriodStmt->fetch() ?: [];
$currentEmails = (int) ($emailPeriod['current_count'] ?? 0);
$previousEmails = (int) ($emailPeriod['previous_count'] ?? 0);

$templatesPeriodStmt = db()->prepare(
    'SELECT
         SUM(CASE WHEN ct.created_at >= :current_start AND ct.created_at < :current_end THEN 1 ELSE 0 END) AS current_count,
         SUM(CASE WHEN ct.created_at >= :previous_start AND ct.created_at < :current_start_2 THEN 1 ELSE 0 END) AS previous_count
     FROM certificate_types ct
     INNER JOIN conferences c ON c.id = ct.conference_id
     WHERE ' . $scope['sql']
);
$exec($templatesPeriodStmt, $periodParams, 'templatesPeriod');
$templatePeriod = $templatesPeriodStmt->fetch() ?: [];
$currentTemplates = (int) ($templatePeriod['current_count'] ?? 0);
$previousTemplates = (int) ($templatePeriod['previous_count'] ?? 0);

$fetchOpenRateForPeriod = static function (string $startDate, string $endDate, array $scopeData): float {
    $sentCountStmt = db()->prepare(
        'SELECT COUNT(DISTINCT e.participant_id)
         FROM email_logs e
         INNER JOIN participants p ON p.id = e.participant_id
         INNER JOIN conferences c ON c.id = p.conference_id
         WHERE e.status = :status
             AND e.sent_at >= :period_start
             AND e.sent_at < :period_end
             AND ' . $scopeData['sql']
    );
    $sentCountStmt->execute(array_merge([
        'status' => 'sent',
        'period_start' => $startDate,
        'period_end' => $endDate,
    ], $scopeData['params']));
    $sentCount = (int) $sentCountStmt->fetchColumn();

    if ($sentCount === 0) {
        return 0.0;
    }

    $openedCountStmt = db()->prepare(
        'SELECT COUNT(DISTINCT e.participant_id)
         FROM email_logs e
         INNER JOIN participants p ON p.id = e.participant_id
         INNER JOIN conferences c ON c.id = p.conference_id
         INNER JOIN generated_certificates g ON g.participant_id = p.id
         WHERE e.status = :status
             AND e.sent_at >= :period_start
             AND e.sent_at < :period_end
             AND g.download_count > 0
             AND ' . $scopeData['sql']
    );
    $openedCountStmt->execute(array_merge([
        'status' => 'sent',
        'period_start' => $startDate,
        'period_end' => $endDate,
    ], $scopeData['params']));
    $openedCount = (int) $openedCountStmt->fetchColumn();

    return round(($openedCount / $sentCount) * 100, 1);
};

$previousOpenRate = $fetchOpenRateForPeriod(
    $previousMonthStart->format('Y-m-d H:i:s'),
    $monthStart->format('Y-m-d H:i:s'),
    $scope
);

$currentOpenRate = $fetchOpenRateForPeriod(
    $monthStart->format('Y-m-d H:i:s'),
    $nextMonthStart->format('Y-m-d H:i:s'),
    $scope
);

$dashboardOpenRate = $currentOpenRate > 0.0 ? $currentOpenRate : $openRate;

$certificateQuota = max(1, (int) (setting('dashboard_certificate_quota', '25000') ?? '25000'));
$storageQuotaGb = max(1.0, (float) (setting('dashboard_storage_quota_gb', '10') ?? '10'));
$generatedStorageBytes = $calculateDirectorySize(GENERATED_ROOT);

$certificateUsagePercent = min(100.0, round(($totalGenerated / $certificateQuota) * 100, 1));
$storageUsagePercent = min(
    100.0,
    round(($generatedStorageBytes / ($storageQuotaGb * 1024 * 1024 * 1024)) * 100, 1)
);

$recentStmt = db()->prepare(
    'SELECT p.name, p.email, ct.name AS template_name, p.status, p.created_at
     FROM participants p
     INNER JOIN conferences c ON c.id = p.conference_id
     INNER JOIN certificate_types ct ON ct.id = p.certificate_type_id
     WHERE ' . $scope['sql'] . '
     ORDER BY p.created_at DESC
     LIMIT 8'
);
$recentStmt->execute($scope['params']);
$recentParticipants = $recentStmt->fetchAll();

$gettingStarted = [
    [
        'label' => 'Connect conference workspace',
        'done' => $totalConferences > 0,
    ],
    [
        'label' => 'Create first template',
        'done' => $totalTemplates > 0,
    ],
    [
        'label' => 'Import recipient list',
        'done' => $totalParticipants > 0,
    ],
    [
        'label' => 'Launch inaugural campaign',
        'done' => $totalEmails > 0,
    ],
];

$gettingStartedCompleted = count(array_filter(
    $gettingStarted,
    static fn (array $item): bool => ($item['done'] ?? false) === true
));

render_view('dashboard.php', [
    'pageTitle' => 'Dashboard',
    'currentPeriodLabel' => $currentPeriodLabel,
    'totalConferences' => $totalConferences,
    'totalParticipants' => $totalParticipants,
    'totalGenerated' => $totalGenerated,
    'totalEmails' => $totalEmails,
    'openRate' => $dashboardOpenRate,
    'totalTemplates' => $totalTemplates,
    'activeTemplates' => $activeTemplates,
    'trend' => [
        'certificates' => $calculateTrend((float) $currentGenerated, (float) $previousGenerated),
        'emails' => $calculateTrend((float) $currentEmails, (float) $previousEmails),
        'openRate' => round($dashboardOpenRate - $previousOpenRate, 1),
        'templates' => $calculateTrend((float) $currentTemplates, (float) $previousTemplates),
    ],
    'quota' => [
        'certificate_cap' => $certificateQuota,
        'certificate_percent' => $certificateUsagePercent,
        'storage_cap_gb' => $storageQuotaGb,
        'storage_used_bytes' => $generatedStorageBytes,
        'storage_percent' => $storageUsagePercent,
    ],
    'gettingStarted' => $gettingStarted,
    'gettingStartedCompleted' => $gettingStartedCompleted,
    'recentParticipants' => $recentParticipants,
]);
