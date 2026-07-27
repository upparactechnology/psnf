<?php
$formatNumber = static function (int $value): string {
    return number_format($value);
};

$formatCompact = static function (int $value): string {
    if ($value >= 1000000) {
        return rtrim(rtrim(number_format($value / 1000000, 1), '0'), '.') . 'M+';
    }

    if ($value >= 1000) {
        return rtrim(rtrim(number_format($value / 1000, 1), '0'), '.') . 'k+';
    }

    return number_format($value);
};

$formatAge = static function (?string $createdAt): string {
    if ($createdAt === null || $createdAt === '') {
        return 'recently';
    }

    $timestamp = strtotime($createdAt);
    if ($timestamp === false) {
        return 'recently';
    }

    $seconds = max(0, time() - $timestamp);

    if ($seconds < 3600) {
        return max(1, (int) floor($seconds / 60)) . 'm ago';
    }

    if ($seconds < 86400) {
        return (int) floor($seconds / 3600) . 'h ago';
    }

    if ($seconds < 604800) {
        return (int) floor($seconds / 86400) . 'd ago';
    }

    if ($seconds < 2592000) {
        return (int) floor($seconds / 604800) . 'w ago';
    }

    return (int) floor($seconds / 2592000) . 'mo ago';
};

$resolveCategory = static function (string $name): string {
    $value = strtolower($name);

    if (str_contains($value, 'education') || str_contains($value, 'academic') || str_contains($value, 'school') || str_contains($value, 'student') || str_contains($value, 'bonafide')) {
        return 'education';
    }

    if (str_contains($value, 'corporate') || str_contains($value, 'employee') || str_contains($value, 'business') || str_contains($value, 'work')) {
        return 'corporate';
    }

    if (str_contains($value, 'event') || str_contains($value, 'conference') || str_contains($value, 'workshop') || str_contains($value, 'summit')) {
        return 'events';
    }

    if (str_contains($value, 'member') || str_contains($value, 'membership') || str_contains($value, 'club')) {
        return 'membership';
    }

    return 'general';
};

$categoryLabels = [
    'education' => 'Education',
    'corporate' => 'Corporate',
    'events' => 'Events',
    'membership' => 'Membership',
    'general' => 'General',
];
?>

<section class="templates-page-shell" data-template-shell>

    <section class="templates-toolbar-panel panel">
        <div class="templates-toolbar-row">
            <div class="templates-search-box" style="flex-grow: 1; max-width: 500px;">
                <label class="sr-only" for="templatesSearch">Search templates</label>
                <input id="templatesSearch" type="search" placeholder="Search templates..." data-template-search autocomplete="off">
            </div>

            <div class="templates-toolbar-actions">
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="action" value="download_all_templates">
                    <input type="hidden" name="conference_id" value="<?= e((string) $selectedConferenceId) ?>">
                    <button type="submit" class="button button-muted">Download All (ZIP)</button>
                </form>
                <a class="button button-muted" href="<?= e(url('field-editor', ['conference_id' => (int) $selectedConferenceId])) ?>">Open Editor</a>
            </div>
        </div>

        <div class="templates-categories-row">
            <div class="templates-category-pills" role="tablist" aria-label="Template categories">
                <button type="button" class="templates-pill is-active" data-template-filter="all" aria-selected="true">All Templates</button>
                <button type="button" class="templates-pill" data-template-filter="education" aria-selected="false">Education</button>
                <button type="button" class="templates-pill" data-template-filter="corporate" aria-selected="false">Corporate</button>
                <button type="button" class="templates-pill" data-template-filter="events" aria-selected="false">Events</button>
                <button type="button" class="templates-pill" data-template-filter="membership" aria-selected="false">Membership</button>
            </div>

            <p class="templates-sort-indicator">Sort by <strong>Last Modified</strong></p>
        </div>
    </section>

    <div class="templates-market-grid" id="templatesMarketGrid">
        <article class="template-start-card" id="templateStartCard">
            <div class="template-start-icon" aria-hidden="true">+</div>
            <h3>Start from Blank</h3>
            <p>Create a fresh template and launch it in the field editor.</p>

            <form method="post" class="template-start-form">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="create_type">
                <input type="hidden" name="conference_id" value="<?= e((string) $selectedConferenceId) ?>">

                <input type="text" name="name" placeholder="e.g. Bonafide Certificate" required>
                <button type="submit" class="button">Create</button>
            </form>
        </article>

        <?php foreach ($certificateTypes as $type): ?>
            <?php
            $templateRelativePath = (string) ($type['template_path'] ?? '');
            $templateAbsolutePath = APP_ROOT . '/' . ltrim(str_replace('\\', '/', $templateRelativePath), '/');
            $templateExists = $templateRelativePath !== '' && is_file($templateAbsolutePath);
            $isActive = (int) ($type['is_active'] ?? 1) === 1;
            $templateVersion = $templateExists ? (string) (filemtime($templateAbsolutePath) ?: time()) : '0';
            $usageCount = (int) ($type['participant_count'] ?? 0);
            $categoryKey = $resolveCategory((string) ($type['name'] ?? ''));
            $categoryLabel = $categoryLabels[$categoryKey] ?? 'General';
            $ageLabel = $formatAge((string) ($type['created_at'] ?? ''));
            $searchKey = strtolower((string) ($type['name'] ?? '') . ' ' . (string) ($type['slug'] ?? '') . ' ' . $categoryLabel);
            ?>
            <article class="template-market-card" data-template-card data-template-name="<?= e($searchKey) ?>" data-template-category="<?= e($categoryKey) ?>">
                <div class="template-market-preview">
                    <?php if ($templateExists): ?>
                        <img src="<?= e($templateRelativePath) ?>?v=<?= e($templateVersion) ?>" alt="<?= e((string) $type['name']) ?> preview">
                    <?php else: ?>
                        <div class="template-preview-empty">Upload a JPG preview to publish this design card.</div>
                    <?php endif; ?>

                    <span class="template-market-badge"><?= e($categoryLabel) ?></span>
                    <span class="template-market-badge" style="left:auto; right:10px; background:<?= $isActive ? '#198754' : '#dc3545' ?>;">
                        <?= $isActive ? 'Active' : 'Inactive' ?>
                    </span>
                </div>

                <div class="template-market-body">
                    <div class="template-market-head">
                        <h4><?= e((string) $type['name']) ?></h4>

                        <div class="dropdown template-card-dropdown" data-dropdown>
                            <button type="button" class="template-menu-trigger" data-dropdown-trigger aria-label="Template actions">&#8942;</button>
                            <div class="dropdown-menu template-card-menu" data-dropdown-menu>
                                <a href="<?= e(url('field-editor', ['conference_id' => (int) $selectedConferenceId, 'certificate_type_id' => (int) $type['id']])) ?>">Open Editor</a>

                                <?php if ($templateExists): ?>
                                    <a href="<?= e(url('certificate-types', ['conference_id' => (int) $selectedConferenceId, 'download_type_id' => (int) $type['id']])) ?>">Download JPG</a>
                                <?php endif; ?>

                                <form method="post" class="template-menu-form">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="action" value="duplicate_type">
                                    <input type="hidden" name="conference_id" value="<?= e((string) $selectedConferenceId) ?>">
                                    <input type="hidden" name="certificate_type_id" value="<?= e((string) $type['id']) ?>">
                                    <button type="submit">Duplicate Template</button>
                                </form>

                                <form method="post" class="template-menu-form">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="action" value="toggle_type_active">
                                    <input type="hidden" name="conference_id" value="<?= e((string) $selectedConferenceId) ?>">
                                    <input type="hidden" name="certificate_type_id" value="<?= e((string) $type['id']) ?>">
                                    <button type="submit"><?= $isActive ? 'Deactivate' : 'Activate' ?> Template</button>
                                </form>

                                <?php if ((int) $type['is_custom'] === 1): ?>
                                    <form method="post" class="template-menu-form" data-confirm="Delete this template?">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="delete_type">
                                        <input type="hidden" name="conference_id" value="<?= e((string) $selectedConferenceId) ?>">
                                        <input type="hidden" name="certificate_type_id" value="<?= e((string) $type['id']) ?>">
                                        <button type="submit" class="template-menu-danger">Delete Template</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <p class="template-market-meta">
                        <span>Used <?= e($formatNumber($usageCount)) ?> times</span>
                        <span><?= e($ageLabel) ?></span>
                    </p>

                    <details class="template-upload-toggle">
                        <summary><?= $templateExists ? 'Replace preview' : 'Upload preview (JPG)' ?></summary>
                        <form method="post" enctype="multipart/form-data" class="template-upload-form">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="action" value="upload_template">
                            <input type="hidden" name="conference_id" value="<?= e((string) $selectedConferenceId) ?>">
                            <input type="hidden" name="certificate_type_id" value="<?= e((string) $type['id']) ?>">

                            <input type="file" name="template" accept="image/jpeg" required>
                            <button type="submit" class="button button-muted">Upload</button>
                        </form>
                    </details>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if ($certificateTypes === []): ?>
        <div class="empty-state templates-empty-state">
            <h4>No templates yet</h4>
            <p>Create your first template from the card above to begin issuing certificates.</p>
        </div>
    <?php endif; ?>

    <div class="templates-no-results" data-template-no-results hidden>
        No templates matched your search or selected category.
    </div>

    <footer class="templates-page-footer">
        <div class="templates-footer-stat">
            <strong><?= e($formatNumber((int) ($activeTemplateCount ?? 0))) ?></strong>
            <span>Active Templates</span>
        </div>
        <div class="templates-footer-stat">
            <strong><?= e($formatCompact((int) ($totalIssuances ?? 0))) ?></strong>
            <span>Total Issuances</span>
        </div>
        <p>Need help with design? <a href="<?= e(url('field-editor', ['conference_id' => (int) $selectedConferenceId])) ?>">Open Template Editor</a></p>
    </footer>
</section>
