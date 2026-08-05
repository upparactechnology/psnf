<?php
$formatCompact = static function (int $value): string {
    if ($value >= 1000000) {
        return rtrim(rtrim(number_format($value / 1000000, 1), '0'), '.') . 'm';
    }

    if ($value >= 1000) {
        return rtrim(rtrim(number_format($value / 1000, 1), '0'), '.') . 'k';
    }

    return number_format($value);
};

$formatBytes = static function (int $bytes): string {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $value = (float) max(0, $bytes);
    $unit = 0;

    while ($value >= 1024 && $unit < count($units) - 1) {
        $value /= 1024;
        $unit++;
    }

    $decimals = $value >= 100 ? 0 : 1;
    return number_format($value, $decimals) . $units[$unit];
};

$trend = $trend ?? [];
$quota = $quota ?? [];
$gettingStarted = $gettingStarted ?? [];
$quotaActionUrl = user_role() === 'super_admin' ? url('settings') : url('downloads');

$certificatePercent = (float) ($quota['certificate_percent'] ?? 0.0);
$storagePercent = (float) ($quota['storage_percent'] ?? 0.0);
$storageUsedBytes = (int) ($quota['storage_used_bytes'] ?? 0);
$storageCapGb = (float) ($quota['storage_cap_gb'] ?? 10.0);
$certificateCap = (int) ($quota['certificate_cap'] ?? 25000);

$statusMeta = static function (string $rawStatus): array {
    $normalized = strtolower($rawStatus);

    if ($normalized === 'emailed' || $normalized === 'generated') {
        return ['label' => 'Issued', 'class' => 'status-issued'];
    }

    if ($normalized === 'failed') {
        return ['label' => 'Failed', 'class' => 'status-failed'];
    }

    return ['label' => 'Processing', 'class' => 'status-processing'];
};

$sparkline = static function (float $primary): array {
    $base = max(14.0, min(100.0, $primary));

    return [
        max(12.0, round($base * 0.34, 1)),
        max(16.0, round($base * 0.46, 1)),
        max(20.0, round($base * 0.56, 1)),
        max(24.0, round($base * 0.74, 1)),
        max(28.0, round($base, 1)),
    ];
};

$certificateSpark = $sparkline($certificatePercent);
$emailSpark = $sparkline(min(100.0, $totalParticipants > 0 ? ($totalEmails / $totalParticipants) * 100 : 14.0));
$openRateSpark = $sparkline((float) ($openRate ?? 0));
$templateSpark = $sparkline(min(100.0, $totalTemplates > 0 ? ($activeTemplates / $totalTemplates) * 100 : 14.0));

$trendClass = static function (float $value): string {
    if ($value > 0.0) {
        return 'is-up';
    }

    if ($value < 0.0) {
        return 'is-down';
    }

    return 'is-flat';
};
?>

<section class="dashboard-shell">
    <div class="dashboard-toolbar">
        <div class="dashboard-date-pill">
            <span class="dashboard-date-icon" aria-hidden="true"></span>
            <span><?= e((string) ($currentPeriodLabel ?? 'Current period')) ?></span>
        </div>
    </div>

    <section class="dashboard-metrics" aria-label="Dashboard metrics">
        <article class="dashboard-metric-card">
            <header>
                <span class="metric-icon metric-icon-issued" aria-hidden="true"></span>
                <p class="metric-trend <?= e($trendClass((float) ($trend['certificates'] ?? 0.0))) ?>">
                    <?= ((float) ($trend['certificates'] ?? 0.0)) > 0 ? '+' : '' ?><?= e(number_format((float) ($trend['certificates'] ?? 0.0), 1)) ?>%
                </p>
            </header>
            <p class="metric-label">Certificates Issued</p>
            <h3><?= e(number_format((int) ($totalGenerated ?? 0))) ?></h3>
            <div class="metric-bars" aria-hidden="true">
                <?php foreach ($certificateSpark as $value): ?>
                    <span style="height: <?= e((string) $value) ?>%"></span>
                <?php endforeach; ?>
            </div>
        </article>

        <article class="dashboard-metric-card">
            <header>
                <span class="metric-icon metric-icon-email" aria-hidden="true"></span>
                <p class="metric-trend <?= e($trendClass((float) ($trend['emails'] ?? 0.0))) ?>">
                    <?= ((float) ($trend['emails'] ?? 0.0)) > 0 ? '+' : '' ?><?= e(number_format((float) ($trend['emails'] ?? 0.0), 1)) ?>%
                </p>
            </header>
            <p class="metric-label">Emails Sent</p>
            <h3><?= e($formatCompact((int) ($totalEmails ?? 0))) ?></h3>
            <div class="metric-bars" aria-hidden="true">
                <?php foreach ($emailSpark as $value): ?>
                    <span style="height: <?= e((string) $value) ?>%"></span>
                <?php endforeach; ?>
            </div>
        </article>

        <article class="dashboard-metric-card">
            <header>
                <span class="metric-icon metric-icon-open" aria-hidden="true"></span>
                <p class="metric-trend <?= e($trendClass((float) ($trend['openRate'] ?? 0.0))) ?>">
                    <?= ((float) ($trend['openRate'] ?? 0.0)) > 0 ? '+' : '' ?><?= e(number_format((float) ($trend['openRate'] ?? 0.0), 1)) ?>%
                </p>
            </header>
            <p class="metric-label">Open Rate</p>
            <h3><?= e(number_format((float) ($openRate ?? 0.0), 1)) ?>%</h3>
            <div class="metric-bars" aria-hidden="true">
                <?php foreach ($openRateSpark as $value): ?>
                    <span style="height: <?= e((string) $value) ?>%"></span>
                <?php endforeach; ?>
            </div>
        </article>

        <article class="dashboard-metric-card">
            <header>
                <span class="metric-icon metric-icon-template" aria-hidden="true"></span>
                <p class="metric-trend <?= e($trendClass((float) ($trend['templates'] ?? 0.0))) ?>">
                    <?= ((float) ($trend['templates'] ?? 0.0)) > 0 ? '+' : '' ?><?= e(number_format((float) ($trend['templates'] ?? 0.0), 1)) ?>%
                </p>
            </header>
            <p class="metric-label">Active Templates</p>
            <h3><?= e(number_format((int) ($activeTemplates ?? 0))) ?></h3>
            <p class="metric-footnote"><?= e(number_format((int) ($totalTemplates ?? 0))) ?> total templates</p>
            <div class="metric-bars" aria-hidden="true">
                <?php foreach ($templateSpark as $value): ?>
                    <span style="height: <?= e((string) $value) ?>%"></span>
                <?php endforeach; ?>
            </div>
        </article>
        <article class="dashboard-metric-card" style="border-color: rgba(16,185,129,0.3);">
            <header>
                <span class="metric-icon" aria-hidden="true" style="background: rgba(16,185,129,0.1); color: #10b981;">👨‍🎓</span>
                <p class="metric-trend is-flat">ERP</p>
            </header>
            <p class="metric-label">Total Students</p>
            <h3><?= e(number_format((int) ($totalStudents ?? 0))) ?></h3>
            <p class="metric-footnote"><a href="/psnf/public/academics/students" style="color:#10b981">View in ERP →</a></p>
        </article>

        <article class="dashboard-metric-card" style="border-color: rgba(245,158,11,0.3);">
            <header>
                <span class="metric-icon" aria-hidden="true" style="background: rgba(245,158,11,0.1); color: #f59e0b;">💰</span>
                <p class="metric-trend is-flat">ERP</p>
            </header>
            <p class="metric-label">Fee Invoices</p>
            <h3><?= e(number_format((int) ($totalInvoices ?? 0))) ?></h3>
            <p class="metric-footnote"><?= (int)($pendingInvoices ?? 0) ?> pending · <a href="/psnf/public/fees" style="color:#f59e0b">Open Finance →</a></p>
        </article>

        <article class="dashboard-metric-card" style="border-color: rgba(99,102,241,0.3);">
            <header>
                <span class="metric-icon" aria-hidden="true" style="background: rgba(99,102,241,0.1); color: #6366f1;">🧾</span>
                <p class="metric-trend is-flat">ERP</p>
            </header>
            <p class="metric-label">Receipts</p>
            <h3><?= e(number_format((int) ($totalReceipts ?? 0))) ?></h3>
            <p class="metric-footnote"><a href="/psnf/public/receipts" style="color:#6366f1">View Receipts →</a></p>
        </article>
    </section>

    <section class="dashboard-content-grid">
        <article class="panel dashboard-table-panel">
            <div class="dashboard-panel-heading">
                <h3>Recent Certificate Issuances</h3>
                <a href="<?= e(url('participants')) ?>">View All</a>
            </div>

            <div class="table-wrap dashboard-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Recipient</th>
                            <th>Template</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (($recentParticipants ?? []) === []): ?>
                        <tr>
                            <td colspan="4" style="text-align:center; padding: 24px; color: #94a3b8;">
                                No certificates issued yet.
                                <a href="<?= e(url('participants')) ?>" style="color:#6366f1; margin-left:8px">Add students →</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentParticipants as $row): ?>
                            <?php
                            $name = trim((string) ($row['name'] ?? 'Recipient'));
                            $email = trim((string) ($row['email'] ?? ''));
                            $meta = $statusMeta((string) ($row['status'] ?? 'pending'));
                            $createdAtRaw = (string) ($row['created_at'] ?? '');
                            $createdAt = strtotime($createdAtRaw);
                            $dateLabel = $createdAt !== false ? date('M j, Y', $createdAt) : '-';
                            $initials = '';
                            foreach (preg_split('/\s+/', $name) ?: [] as $part) {
                                if ($part === '') continue;
                                $initials .= strtoupper(substr($part, 0, 1));
                                if (strlen($initials) >= 2) break;
                            }
                            if ($initials === '') $initials = 'NA';
                            ?>
                            <tr>
                                <td>
                                    <div class="recipient-cell">
                                        <span class="recipient-avatar"><?= e($initials) ?></span>
                                        <div>
                                            <strong><?= e($name) ?></strong>
                                            <small><?= e($email !== '' ? $email : 'No email available') ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?= e((string) ($row['template_name'] ?? '-')) ?></td>
                                <td><?= e($dateLabel) ?></td>
                                <td>
                                    <span class="status-chip <?= e((string) $meta['class']) ?>"><?= e((string) $meta['label']) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </article>

        <aside class="dashboard-side-column">
            <article class="panel dashboard-checklist-card">
                <div class="dashboard-panel-heading">
                    <h3>Getting Started</h3>
                    <span><?= e((string) ($gettingStartedCompleted ?? 0)) ?>/<?= e((string) count($gettingStarted)) ?></span>
                </div>

                <ul class="checklist">
                    <?php foreach ($gettingStarted as $item): ?>
                        <li class="<?= (($item['done'] ?? false) === true) ? 'done' : '' ?>">
                            <span class="checklist-dot" aria-hidden="true"></span>
                            <?php if (!empty($item['url'])): ?>
                                <a href="<?= e($item['url']) ?>" style="color:inherit;"><?= e((string) ($item['label'] ?? 'Task')) ?></a>
                            <?php else: ?>
                                <span><?= e((string) ($item['label'] ?? 'Task')) ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </article>

            <!-- ERP Quick Links -->
            <article class="panel" style="margin-top: 16px; padding: 20px;">
                <div class="dashboard-panel-heading" style="margin-bottom: 12px;">
                    <h3 style="font-size: 13px;">ERP Quick Links</h3>
                </div>
                <ul style="list-style:none; padding:0; margin:0; space-y:8px; font-size:13px; display:flex; flex-direction:column; gap:8px;">
                    <li><a href="/psnf/public/academics/students" style="color:#6366f1;">👨‍🎓 Students</a></li>
                    <li><a href="/psnf/public/fees" style="color:#f59e0b;">💰 Fee Invoices</a></li>
                    <li><a href="/psnf/public/receipts" style="color:#10b981;">🧾 Receipts</a></li>
                    <li><a href="/psnf/public/certificates" style="color:#a855f7;">📜 ERP Certificates</a></li>
                    <li><a href="/psnf/public/dashboard" style="color:#94a3b8;">← Back to ERP Dashboard</a></li>
                </ul>
            </article>
        </aside>
    </section>
</section>
