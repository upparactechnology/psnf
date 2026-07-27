<?php
$buildAnalyticsParams = static function (array $overrides = []) use ($selectedConferenceId, $selectedTypeId, $selectedRange, $selectedFormat, $selectedSearch, $pageNumber): array {
    $params = [
        'conference_id' => $selectedConferenceId,
        'certificate_type_id' => $selectedTypeId,
        'range' => $selectedRange,
        'format' => $selectedFormat,
        'search' => $selectedSearch,
        'page_no' => $pageNumber,
    ];

    foreach ($overrides as $key => $value) {
        $params[$key] = $value;
    }

    if ((int) ($params['certificate_type_id'] ?? 0) <= 0) {
        unset($params['certificate_type_id']);
    }

    if (trim((string) ($params['search'] ?? '')) === '') {
        unset($params['search']);
    }

    if ((string) ($params['range'] ?? '') === '30d') {
        unset($params['range']);
    }

    if ((string) ($params['format'] ?? '') === 'pdf') {
        unset($params['format']);
    }

    if ((int) ($params['page_no'] ?? 1) <= 1) {
        unset($params['page_no']);
    }

    return $params;
};

$formatChange = static function (float $delta): array {
    $rounded = round($delta, 1);

    if ($rounded > 0) {
        return ['label' => '+' . $rounded . '%', 'class' => 'is-positive'];
    }

    if ($rounded < 0) {
        return ['label' => $rounded . '%', 'class' => 'is-negative'];
    }

    return ['label' => '0%', 'class' => 'is-neutral'];
};

$issuedDelta = $formatChange((float) ($issuedGrowth ?? 0));
$downloadDelta = $formatChange((float) ($downloadGrowth ?? 0));
$sentDelta = $formatChange((float) ($sentGrowth ?? 0));

$maxGenerated = 1;
$maxDownloads = 1;
foreach ($dailySeries as $point) {
    $maxGenerated = max($maxGenerated, (int) ($point['generated_total'] ?? 0));
    $maxDownloads = max($maxDownloads, (int) ($point['download_total'] ?? 0));
}

$rangeLabels = [
    '7d' => 'Last 7 Days',
    '30d' => 'Last 30 Days',
    '90d' => 'Last 90 Days',
    'all' => 'All Time',
];

$eventClassByType = [
    'generated' => 'analytics-event-generated',
    'email_sent' => 'analytics-event-sent',
    'email_failed' => 'analytics-event-failed',
];

$windowStart = max(1, (int) ($pageNumber ?? 1) - 2);
$windowEnd = min((int) ($totalPages ?? 1), $windowStart + 4);
$windowStart = max(1, $windowEnd - 4);
?>

<section class="analytics-page-shell" id="analyticsPage">
    <header class="analytics-page-header">
        <div>
            <h1>Analytics Overview</h1>
            <p>Track issuance throughput, engagement, and file distribution performance.</p>
        </div>

        <form method="post" class="analytics-zip-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="download_zip">
            <input type="hidden" name="conference_id" value="<?= e((string) $selectedConferenceId) ?>">
            <input type="hidden" name="certificate_type_id" value="<?= e((string) $selectedTypeId) ?>">
            <input type="hidden" name="range" value="<?= e((string) $selectedRange) ?>">
            <input type="hidden" name="format" value="<?= e((string) $selectedFormat) ?>">
            <input type="hidden" name="search" value="<?= e((string) $selectedSearch) ?>">
            <input type="hidden" name="page_no" value="<?= e((string) $pageNumber) ?>">
            <button type="submit" class="button analytics-zip-btn">Download ZIP (<?= e(strtoupper((string) $selectedFormat)) ?>)</button>
        </form>
    </header>

    <section class="panel analytics-filters-panel">
        <form method="get" class="analytics-filters-form">
            <input type="hidden" name="page" value="downloads">

            <!-- Workspace filter removed -->

            <label>
                Template
                <select name="certificate_type_id">
                    <option value="0" <?= (int) $selectedTypeId === 0 ? 'selected' : '' ?>>All Templates</option>
                    <?php foreach ($certificateTypes as $type): ?>
                        <option value="<?= e((string) $type['id']) ?>" <?= (int) $type['id'] === (int) $selectedTypeId ? 'selected' : '' ?>>
                            <?= e((string) $type['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Time Range
                <select name="range">
                    <option value="7d" <?= $selectedRange === '7d' ? 'selected' : '' ?>>Last 7 Days</option>
                    <option value="30d" <?= $selectedRange === '30d' ? 'selected' : '' ?>>Last 30 Days</option>
                    <option value="90d" <?= $selectedRange === '90d' ? 'selected' : '' ?>>Last 90 Days</option>
                    <option value="all" <?= $selectedRange === 'all' ? 'selected' : '' ?>>All Time</option>
                </select>
            </label>

            <label>
                File Format
                <select name="format">
                    <option value="pdf" <?= $selectedFormat === 'pdf' ? 'selected' : '' ?>>PDF</option>
                    <option value="jpg" <?= $selectedFormat === 'jpg' ? 'selected' : '' ?>>JPG</option>
                </select>
            </label>

            <label class="analytics-search-field">
                Search
                <input type="search" name="search" value="<?= e((string) $selectedSearch) ?>" placeholder="Participant, email, verify code...">
            </label>

            <div class="analytics-filter-actions">
                <button type="submit" class="button button-muted">Apply</button>
                <a class="button button-muted" href="<?= e(url('downloads', ['conference_id' => (int) $selectedConferenceId])) ?>">Reset</a>
            </div>
        </form>
    </section>

    <section class="analytics-stats-grid">
        <article class="analytics-stat-card">
            <span>Certificates Issued</span>
            <strong><?= e(number_format((int) ($totalGenerated ?? 0))) ?></strong>
            <small class="<?= e((string) ($issuedDelta['class'] ?? 'is-neutral')) ?>"><?= e((string) ($issuedDelta['label'] ?? '0%')) ?> vs previous range</small>
        </article>

        <article class="analytics-stat-card">
            <span>Emails Sent</span>
            <strong><?= e(number_format((int) ($sentEmails ?? 0))) ?></strong>
            <small class="<?= e((string) ($sentDelta['class'] ?? 'is-neutral')) ?>"><?= e((string) ($sentDelta['label'] ?? '0%')) ?> growth, <?= e(number_format((int) ($failedEmails ?? 0))) ?> failed</small>
        </article>

        <article class="analytics-stat-card">
            <span>Total Downloads</span>
            <strong><?= e(number_format((int) ($totalDownloads ?? 0))) ?></strong>
            <small class="<?= e((string) ($downloadDelta['class'] ?? 'is-neutral')) ?>"><?= e((string) ($downloadDelta['label'] ?? '0%')) ?> vs previous range</small>
        </article>

        <article class="analytics-stat-card">
            <span>Active Recipients</span>
            <strong><?= e(number_format((int) ($activeRecipients ?? 0))) ?></strong>
            <small><?= e(number_format((int) ($pendingRecipients ?? 0))) ?> pending of <?= e(number_format((int) ($totalRecipients ?? 0))) ?></small>
        </article>
    </section>

    <div class="analytics-main-grid">
        <article class="panel analytics-trend-panel">
            <div class="analytics-panel-heading">
                <h3>Issuance Trend</h3>
                <span><?= e((string) ($rangeLabels[$selectedRange] ?? 'Custom Range')) ?></span>
            </div>

            <?php if ($dailySeries === []): ?>
                <p class="analytics-empty">No trend data available.</p>
            <?php else: ?>
                <div class="analytics-trend-list">
                    <?php foreach ($dailySeries as $point): ?>
                        <?php
                        $generatedPercent = ((int) ($point['generated_total'] ?? 0) / $maxGenerated) * 100;
                        $downloadPercent = ((int) ($point['download_total'] ?? 0) / $maxDownloads) * 100;
                        ?>
                        <div class="analytics-trend-row">
                            <span class="analytics-trend-date"><?= e((string) ($point['day_label'] ?? '-')) ?></span>
                            <div class="analytics-trend-bars">
                                <span class="analytics-trend-bar analytics-trend-bar-issued" style="width: <?= e(number_format($generatedPercent, 2, '.', '')) ?>%"></span>
                                <span class="analytics-trend-bar analytics-trend-bar-downloads" style="width: <?= e(number_format($downloadPercent, 2, '.', '')) ?>%"></span>
                            </div>
                            <span class="analytics-trend-values"><?= e(number_format((int) ($point['generated_total'] ?? 0))) ?> / <?= e(number_format((int) ($point['download_total'] ?? 0))) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </article>

        <article class="panel analytics-template-panel">
            <div class="analytics-panel-heading">
                <h3>Top Templates</h3>
                <span>By issued certificates</span>
            </div>

            <?php if ($templateBreakdown === []): ?>
                <p class="analytics-empty">No template activity in this range.</p>
            <?php else: ?>
                <ul class="analytics-template-list">
                    <?php foreach ($templateBreakdown as $template): ?>
                        <li>
                            <div>
                                <strong><?= e((string) ($template['name'] ?? 'Template')) ?></strong>
                                <small><?= e(number_format((int) ($template['recipient_total'] ?? 0))) ?> recipients</small>
                            </div>
                            <div class="analytics-template-metrics">
                                <span><?= e(number_format((int) ($template['generated_total'] ?? 0))) ?> issued</span>
                                <span><?= e(number_format((int) ($template['download_total'] ?? 0))) ?> downloads</span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </article>

        <article class="panel analytics-event-panel">
            <div class="analytics-panel-heading">
                <h3>Recent Activity</h3>
                <span>Latest events</span>
            </div>

            <?php if ($recentEvents === []): ?>
                <p class="analytics-empty">No recent activity.</p>
            <?php else: ?>
                <ul class="analytics-event-list">
                    <?php foreach ($recentEvents as $event): ?>
                        <?php
                        $rawDate = (string) ($event['event_at'] ?? '');
                        $timestamp = strtotime($rawDate);
                        $dateLabel = $timestamp ? date('d M Y H:i', $timestamp) : '-';
                        $eventType = (string) ($event['event_type'] ?? 'generated');
                        $eventClass = $eventClassByType[$eventType] ?? 'analytics-event-generated';
                        ?>
                        <li>
                            <span class="analytics-event-dot <?= e($eventClass) ?>"></span>
                            <div>
                                <strong><?= e((string) ($event['event_label'] ?? 'Event')) ?></strong>
                                <p><?= e((string) ($event['participant_name'] ?? 'Participant')) ?> · <?= e((string) ($event['certificate_type_name'] ?? 'Template')) ?></p>
                                <small><?= e($dateLabel) ?> · <?= e((string) ($event['meta'] ?? '')) ?></small>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </article>
    </div>

    <section class="panel analytics-table-panel">
        <div class="analytics-panel-heading">
            <h3>Generated Files</h3>
            <span>Showing <?= e((string) ($rangeStart ?? 0)) ?> - <?= e((string) ($rangeEnd ?? 0)) ?> of <?= e(number_format((int) ($totalRows ?? 0))) ?></span>
        </div>

        <div class="table-wrap analytics-table-wrap">
            <table class="analytics-files-table">
                <thead>
                    <tr>
                        <th>Recipient</th>
                        <th>Template</th>
                        <th>Generated At</th>
                        <th>Downloads</th>
                        <th>Files</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($rows === []): ?>
                    <tr>
                        <td colspan="5" class="analytics-empty-row">No generated certificates found for selected filters.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td>
                                <div class="analytics-recipient-cell">
                                    <strong><?= e((string) ($row['name'] ?? '-')) ?></strong>
                                    <small><?= e((string) ($row['email'] ?? '-')) ?></small>
                                    <small>Code: <?= e((string) ($row['verify_code'] ?? '-')) ?></small>
                                </div>
                            </td>
                            <td><?= e((string) ($row['certificate_type_name'] ?? '-')) ?></td>
                            <td><?= e((string) ($row['generated_at'] ?? '-')) ?></td>
                            <td><?= e(number_format((int) ($row['download_count'] ?? 0))) ?></td>
                            <td>
                                <div class="analytics-file-actions">
                                    <?php if (!empty($row['jpg_path'])): ?>
                                        <a class="button button-muted" href="<?= e(url('download-file', ['participant_id' => (int) $row['participant_id'], 'format' => 'jpg'])) ?>">JPG</a>
                                    <?php endif; ?>
                                    <?php if (!empty($row['pdf_path'])): ?>
                                        <a class="button button-muted" href="<?= e(url('download-file', ['participant_id' => (int) $row['participant_id'], 'format' => 'pdf'])) ?>">PDF</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="analytics-table-footer">
            <?php $prevPage = max(1, (int) ($pageNumber ?? 1) - 1); ?>
            <?php $nextPage = min((int) ($totalPages ?? 1), (int) ($pageNumber ?? 1) + 1); ?>
            <a class="analytics-page-link <?= (int) ($pageNumber ?? 1) <= 1 ? 'is-disabled' : '' ?>" href="<?= e(url('downloads', $buildAnalyticsParams(['page_no' => $prevPage]))) ?>">Previous</a>

            <div class="analytics-page-list">
                <?php for ($page = $windowStart; $page <= $windowEnd; $page++): ?>
                    <a class="analytics-page-link <?= (int) $page === (int) ($pageNumber ?? 1) ? 'is-active' : '' ?>" href="<?= e(url('downloads', $buildAnalyticsParams(['page_no' => $page]))) ?>"><?= e((string) $page) ?></a>
                <?php endfor; ?>
            </div>

            <a class="analytics-page-link <?= (int) ($pageNumber ?? 1) >= (int) ($totalPages ?? 1) ? 'is-disabled' : '' ?>" href="<?= e(url('downloads', $buildAnalyticsParams(['page_no' => $nextPage]))) ?>">Next</a>
        </div>
    </section>
</section>
