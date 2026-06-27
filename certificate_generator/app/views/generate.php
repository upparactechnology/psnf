<?php /** @var array $conferences */
/** @var int $selectedConferenceId */
/** @var array $certificateTypes */
/** @var int $selectedTypeId */
/** @var string $statusFilter */
/** @var string $dateRange */
/** @var string $searchTerm */
/** @var int $pageNumber */
/** @var int $totalRows */
/** @var int $totalPages */
/** @var int $rangeStart */
/** @var int $rangeEnd */
/** @var array $rows */
/** @var array $statusTotals */
/** @var int $totalDownloads */

$statusMeta = static function (array $row): array {
    $rawStatus = strtolower((string) ($row['status'] ?? 'pending'));
    $downloads = (int) ($row['download_count'] ?? 0);

    if ($downloads > 0) {
        return ['key' => 'viewed', 'label' => 'Viewed', 'class' => 'cert-status-viewed'];
    }

    if ($rawStatus === 'emailed') {
        return ['key' => 'sent', 'label' => 'Sent', 'class' => 'cert-status-sent'];
    }

    if ($rawStatus === 'generated' || !empty($row['generated_at'])) {
        return ['key' => 'issued', 'label' => 'Issued', 'class' => 'cert-status-issued'];
    }

    if ($rawStatus === 'failed') {
        return ['key' => 'failed', 'label' => 'Failed', 'class' => 'cert-status-failed'];
    }

    return ['key' => 'pending', 'label' => 'Pending', 'class' => 'cert-status-pending'];
};

$buildParams = static function (array $overrides = []) use ($selectedConferenceId, $selectedTypeId, $statusFilter, $dateRange, $searchTerm, $pageNumber): array {
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

    if ($params['status'] === 'all') {
        unset($params['status']);
    }

    if ($params['date_range'] === '30d') {
        unset($params['date_range']);
    }

    if (trim((string) $params['search']) === '') {
        unset($params['search']);
    }

    if ($params['page_no'] <= 1) {
        unset($params['page_no']);
    }

    foreach ($overrides as $key => $value) {
        $params[$key] = $value;
    }

    foreach (['certificate_type_id', 'status', 'date_range', 'search', 'page_no'] as $optionalKey) {
        if (!array_key_exists($optionalKey, $params)) {
            continue;
        }

        if ($optionalKey === 'certificate_type_id' && (int) $params[$optionalKey] <= 0) {
            unset($params[$optionalKey]);
        }

        if ($optionalKey === 'status' && (string) $params[$optionalKey] === 'all') {
            unset($params[$optionalKey]);
        }

        if ($optionalKey === 'date_range' && (string) $params[$optionalKey] === '30d') {
            unset($params[$optionalKey]);
        }

        if ($optionalKey === 'search' && trim((string) $params[$optionalKey]) === '') {
            unset($params[$optionalKey]);
        }

        if ($optionalKey === 'page_no' && (int) $params[$optionalKey] <= 1) {
            unset($params[$optionalKey]);
        }
    }

    return $params;
};

$formatDate = static function (?string $rawDate): string {
    if ($rawDate === null || $rawDate === '') {
        return '-';
    }

    $timestamp = strtotime($rawDate);
    if ($timestamp === false) {
        return '-';
    }

    return date('M d, Y', $timestamp);
};

$statusTabs = [
    'all' => 'All',
    'issued' => 'Issued',
    'sent' => 'Sent',
    'viewed' => 'Viewed',
];

$windowStart = max(1, (int) $pageNumber - 2);
$windowEnd = min((int) $totalPages, $windowStart + 4);
$windowStart = max(1, $windowEnd - 4);
?>

<section class="certificates-page-shell">
    <header class="certificates-page-header">
        <div>
            <h1>Certificates</h1>
            <p>Manage and track issued certificates across your organization.</p>
        </div>

        <form method="post" class="certificates-issue-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="generate_bulk">
            <input type="hidden" name="conference_id" value="<?= e((string) $selectedConferenceId) ?>">
            <input type="hidden" name="certificate_type_id" value="<?= e((string) $selectedTypeId) ?>">
            <input type="hidden" name="status" value="<?= e($statusFilter) ?>">
            <input type="hidden" name="date_range" value="<?= e($dateRange) ?>">
            <input type="hidden" name="search" value="<?= e($searchTerm) ?>">
            <input type="hidden" name="page_no" value="<?= e((string) $pageNumber) ?>">

            <button type="submit" class="button certificates-issue-btn" title="Issue certificates for selected template or all templates.">
                <span aria-hidden="true">+</span>
                <span>Issue New</span>
            </button>
        </form>
    </header>

    <section class="panel certificates-summary-panel">
        <div class="certificates-summary-grid">
            <article class="certificates-summary-card">
                <span>Total Certificates</span>
                <strong><?= e((string) ($statusTotals['all'] ?? 0)) ?></strong>
            </article>
            <article class="certificates-summary-card">
                <span>Issued</span>
                <strong><?= e((string) ($statusTotals['issued'] ?? 0)) ?></strong>
            </article>
            <article class="certificates-summary-card">
                <span>Viewed</span>
                <strong><?= e((string) ($statusTotals['viewed'] ?? 0)) ?></strong>
            </article>
            <article class="certificates-summary-card">
                <span>Total Downloads</span>
                <strong><?= e(number_format((int) $totalDownloads)) ?></strong>
            </article>
        </div>
    </section>

    <section class="panel certificates-filters-panel">
        <form method="get" class="certificates-filters-form">
            <input type="hidden" name="page" value="generate">
            <input type="hidden" name="conference_id" value="<?= e((string) $selectedConferenceId) ?>">
            <input type="hidden" name="certificate_type_id" value="<?= e((string) $selectedTypeId) ?>">
            <input type="hidden" name="status" value="<?= e($statusFilter) ?>">
            <input type="hidden" name="search" value="<?= e($searchTerm) ?>">

            <div class="certificates-filters-top">
                <div class="certificates-status-filter">
                    <span class="certificates-filter-label">Status</span>
                    <div class="certificates-status-tabs" role="tablist" aria-label="Certificate status">
                        <?php foreach ($statusTabs as $tabKey => $tabLabel): ?>
                            <?php $tabCount = (int) ($statusTotals[$tabKey] ?? 0); ?>
                            <button type="submit" name="status" value="<?= e($tabKey) ?>" class="certificates-status-tab <?= $statusFilter === $tabKey ? 'is-active' : '' ?>" aria-selected="<?= $statusFilter === $tabKey ? 'true' : 'false' ?>">
                                <?= e($tabLabel) ?>
                                <small><?= e((string) $tabCount) ?></small>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="certificates-filter-actions">
                    <label class="certificates-date-range">
                        <span>Date Range</span>
                        <select name="date_range" onchange="this.form.submit()">
                            <option value="7d" <?= $dateRange === '7d' ? 'selected' : '' ?>>Last 7 Days</option>
                            <option value="30d" <?= $dateRange === '30d' ? 'selected' : '' ?>>Last 30 Days</option>
                            <option value="90d" <?= $dateRange === '90d' ? 'selected' : '' ?>>Last 90 Days</option>
                            <option value="all" <?= $dateRange === 'all' ? 'selected' : '' ?>>All Time</option>
                        </select>
                    </label>

                    <details class="certificates-more-filters">
                        <summary>More Filters</summary>
                        <div class="certificates-more-grid">
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
                                Search
                                <input type="search" name="search" value="<?= e($searchTerm) ?>" placeholder="Search recipient, email, template...">
                            </label>
                        </div>

                        <div class="certificates-more-actions">
                            <button type="submit" class="button button-muted">Apply Filters</button>
                            <a class="button button-muted" href="<?= e(url('generate', ['conference_id' => (int) $selectedConferenceId])) ?>">Reset</a>
                        </div>
                    </details>

                    <a class="button button-muted certificates-export-btn" href="<?= e(url('generate', $buildParams(['export' => 'csv', 'page_no' => 1]))) ?>">Export CSV</a>
                </div>
            </div>
        </form>
    </section>

    <section class="panel certificates-table-panel">
        <div class="table-wrap certificates-table-wrap">
            <table class="certificates-table">
                <thead>
                    <tr>
                        <th class="cert-col-check"><input type="checkbox" aria-label="Select all" disabled></th>
                        <th>Recipient</th>
                        <th>Template Name</th>
                        <th>Date Issued</th>
                        <th>Downloads</th>
                        <th>Status</th>
                        <th class="cert-col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($rows === []): ?>
                    <tr>
                        <td colspan="7" class="cert-empty-row">No certificates found for current filters.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rows as $row): ?>
                        <?php
                        $avatarLetters = strtoupper(substr((string) ($row['name'] ?? 'U'), 0, 1));
                        $issuedDate = (string) ($row['issued_date'] ?? $row['generated_at'] ?? $row['created_at'] ?? '');
                        $meta = $statusMeta($row);
                        ?>
                        <tr>
                            <td class="cert-col-check"><input type="checkbox" aria-label="Select <?= e((string) ($row['name'] ?? 'recipient')) ?>"></td>
                            <td>
                                <div class="cert-recipient-cell">
                                    <span class="cert-recipient-avatar"><?= e($avatarLetters) ?></span>
                                    <div>
                                        <strong><?= e((string) ($row['name'] ?? 'Unknown')) ?></strong>
                                        <small><?= e((string) ($row['email'] ?? '-')) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="cert-template-cell">
                                    <span class="cert-template-icon" aria-hidden="true"></span>
                                    <span><?= e((string) ($row['template_name'] ?? 'Template')) ?></span>
                                </div>
                            </td>
                            <td><?= e($formatDate($issuedDate)) ?></td>
                            <td><?= e(number_format((int) ($row['download_count'] ?? 0))) ?></td>
                            <td>
                                <span class="cert-status-chip <?= e((string) $meta['class']) ?>">
                                    <span class="cert-status-dot" aria-hidden="true"></span>
                                    <?= e((string) $meta['label']) ?>
                                </span>
                            </td>
                            <td class="cert-col-actions">
                                <div class="cert-row-actions">
                                    <form method="post">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="generate_single">
                                        <input type="hidden" name="conference_id" value="<?= e((string) $selectedConferenceId) ?>">
                                        <input type="hidden" name="certificate_type_id" value="<?= e((string) $selectedTypeId) ?>">
                                        <input type="hidden" name="participant_id" value="<?= e((string) ($row['participant_id'] ?? 0)) ?>">
                                        <input type="hidden" name="status" value="<?= e($statusFilter) ?>">
                                        <input type="hidden" name="date_range" value="<?= e($dateRange) ?>">
                                        <input type="hidden" name="search" value="<?= e($searchTerm) ?>">
                                        <input type="hidden" name="page_no" value="<?= e((string) $pageNumber) ?>">
                                        <button type="submit" class="button button-muted cert-action-btn">Issue</button>
                                    </form>

                                    <?php if (!empty($row['jpg_path'])): ?>
                                        <a class="button button-muted cert-action-btn" href="<?= e(url('download-file', ['participant_id' => (int) ($row['participant_id'] ?? 0), 'format' => 'jpg'])) ?>">JPG</a>
                                    <?php endif; ?>
                                    <?php if (!empty($row['pdf_path'])): ?>
                                        <a class="button button-muted cert-action-btn" href="<?= e(url('download-file', ['participant_id' => (int) ($row['participant_id'] ?? 0), 'format' => 'pdf'])) ?>">PDF</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="certificates-table-footer">
            <p>Showing <?= e((string) $rangeStart) ?> to <?= e((string) $rangeEnd) ?> of <?= e((string) $totalRows) ?> results</p>
            <nav class="certificates-pagination" aria-label="Certificates pagination">
                <?php $prevPage = max(1, (int) $pageNumber - 1); ?>
                <a class="cert-page-link <?= (int) $pageNumber <= 1 ? 'is-disabled' : '' ?>" href="<?= e(url('generate', $buildParams(['page_no' => $prevPage]))) ?>" aria-label="Previous page">&lsaquo;</a>

                <?php for ($page = $windowStart; $page <= $windowEnd; $page++): ?>
                    <a class="cert-page-link <?= (int) $page === (int) $pageNumber ? 'is-active' : '' ?>" href="<?= e(url('generate', $buildParams(['page_no' => $page]))) ?>"><?= e((string) $page) ?></a>
                <?php endfor; ?>

                <?php $nextPage = min((int) $totalPages, (int) $pageNumber + 1); ?>
                <a class="cert-page-link <?= (int) $pageNumber >= (int) $totalPages ? 'is-disabled' : '' ?>" href="<?= e(url('generate', $buildParams(['page_no' => $nextPage]))) ?>" aria-label="Next page">&rsaquo;</a>
            </nav>
        </div>
    </section>
</section>
