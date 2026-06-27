<?php
$buildRecipientParams = static function (array $overrides = []) use ($selectedConferenceId, $selectedTypeId, $searchTerm, $statusFilter, $pageNumber): array {
    $params = [
        'conference_id' => $selectedConferenceId,
        'certificate_type_id' => $selectedTypeId,
        'search' => $searchTerm,
        'status' => $statusFilter,
        'page_no' => $pageNumber,
    ];

    if ((int) $params['certificate_type_id'] <= 0) {
        unset($params['certificate_type_id']);
    }

    if (trim((string) $params['search']) === '') {
        unset($params['search']);
    }

    if ((string) $params['status'] === '') {
        unset($params['status']);
    }

    if ((int) $params['page_no'] <= 1) {
        unset($params['page_no']);
    }

    foreach ($overrides as $key => $value) {
        $params[$key] = $value;
    }

    foreach (['certificate_type_id', 'search', 'status', 'page_no'] as $optionalKey) {
        if (!array_key_exists($optionalKey, $params)) {
            continue;
        }

        if ($optionalKey === 'certificate_type_id' && (int) $params[$optionalKey] <= 0) {
            unset($params[$optionalKey]);
        }

        if ($optionalKey === 'search' && trim((string) $params[$optionalKey]) === '') {
            unset($params[$optionalKey]);
        }

        if ($optionalKey === 'status' && (string) $params[$optionalKey] === '') {
            unset($params[$optionalKey]);
        }

        if ($optionalKey === 'page_no' && (int) $params[$optionalKey] <= 1) {
            unset($params[$optionalKey]);
        }
    }

    return $params;
};

$avatarFromName = static function (string $rawName): string {
    $parts = preg_split('/\s+/', trim($rawName)) ?: [];
    $initials = '';
    foreach ($parts as $part) {
        if ($part === '') {
            continue;
        }

        $initials .= strtoupper(substr($part, 0, 1));
        if (strlen($initials) >= 2) {
            break;
        }
    }

    return $initials !== '' ? $initials : 'U';
};

$statusChipMeta = static function (string $rawStatus): array {
    $status = strtolower($rawStatus);

    if ($status === 'generated' || $status === 'emailed') {
        return ['label' => 'Active', 'class' => 'recipient-status-active'];
    }

    if ($status === 'failed') {
        return ['label' => 'Failed', 'class' => 'recipient-status-failed'];
    }

    return ['label' => 'Pending', 'class' => 'recipient-status-pending'];
};

$windowStart = max(1, (int) $pageNumber - 2);
$windowEnd = min((int) $totalPages, $windowStart + 4);
$windowStart = max(1, $windowEnd - 4);
?>

<section class="recipients-page-shell" id="recipientsPage">
    <header class="recipients-page-header">
        <div>
            <h1>Students &amp; Certificates</h1>
            <p>Assign students to certificate templates and manage their generated credentials.</p>
        </div>

        <div class="recipients-header-actions">
            <a class="button" href="#studentAssignPanel">Assign Students</a>
        </div>
    </header>

    <section class="panel recipients-filter-panel">
        <form method="get" class="recipients-filter-form">
            <input type="hidden" name="page" value="participants">
            <input type="hidden" name="conference_id" value="<?= e((string) $selectedConferenceId) ?>">

            <div class="recipients-filter-main">
                <label class="recipients-filter-select">
                    <span>All Templates</span>
                    <select name="certificate_type_id" onchange="this.form.submit()">
                        <option value="0" <?= (int) $selectedTypeId === 0 ? 'selected' : '' ?>>All Templates</option>
                        <?php foreach ($certificateTypes as $type): ?>
                            <option value="<?= e((string) $type['id']) ?>" <?= (int) $type['id'] === (int) $selectedTypeId ? 'selected' : '' ?>>
                                <?= e((string) $type['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <div class="recipients-status-filter">
                    <span>Status:</span>
                    <button type="submit" name="status" value="active" class="recipient-status-tab <?= ($statusFilter === 'active') ? 'is-active' : '' ?>">Active</button>
                    <button type="submit" name="status" value="pending" class="recipient-status-tab <?= ($statusFilter === 'pending') ? 'is-active' : '' ?>">Pending</button>
                    <button type="submit" name="status" value="" class="recipient-status-tab <?= ($statusFilter === '') ? 'is-active' : '' ?>">All</button>
                </div>

                <label class="recipients-search-input">
                    <span class="sr-only">Search students</span>
                    <input type="search" name="search" value="<?= e((string) $searchTerm) ?>" placeholder="Search student, email, verify code...">
                </label>

                <button type="submit" class="button button-muted recipients-filter-apply">Apply</button>
            </div>

            <aside class="recipients-active-card">
                <span>Active Credentials</span>
                <strong><?= e(number_format((int) ($activeRecipientCount ?? 0))) ?></strong>
                <small>Pending <?= e(number_format((int) ($pendingRecipientCount ?? 0))) ?></small>
            </aside>
        </form>
    </section>

    <section class="panel recipients-form-panels" id="studentAssignPanel">
        <details open>
            <summary>Assign Students to Certificate Template</summary>
            <form method="post" class="form-grid top-gap-sm" style="display: flex; flex-direction: column; gap: 15px; grid-column: span 2;">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="add_students">
                <input type="hidden" name="conference_id" value="<?= e((string) $selectedConferenceId) ?>">
                <input type="hidden" name="search" value="<?= e((string) ($searchTerm ?? '')) ?>">
                <input type="hidden" name="status" value="<?= e((string) ($statusFilter ?? '')) ?>">
                <input type="hidden" name="page_no" value="<?= e((string) $pageNumber) ?>">

                <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 250px;">
                        <label style="font-weight: 600; margin-bottom: 5px; display: block;">Select Template / Category</label>
                        <select name="manual_certificate_type_id" required style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc; font-family: inherit;">
                            <option value="">-- Choose Template --</option>
                            <?php foreach ($certificateTypes as $type): ?>
                                <option value="<?= e((string) $type['id']) ?>" <?= (int) $type['id'] === (int) $selectedTypeId ? 'selected' : '' ?>>
                                    <?= e($type['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="flex: 1; min-width: 200px;">
                        <label style="font-weight: 600; margin-bottom: 5px; display: block;">Issue Date</label>
                        <input type="date" name="issued_date" value="<?= e(date('Y-m-d')) ?>" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc; font-family: inherit;">
                    </div>
                </div>

                <div>
                    <label style="font-weight: 600; margin-bottom: 5px; display: block;">Select Students</label>
                    <div style="max-height: 250px; overflow-y: auto; border: 1px solid #ccc; border-radius: 4px; padding: 15px; background: #fff;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 10px;">
                            <?php foreach ($students as $student): ?>
                                <?php
                                $sName = trim($student['first_name'] . ' ' . $student['middle_name'] . ' ' . $student['last_name']);
                                if ($sName === '') {
                                    $sName = trim($student['first_name'] . ' ' . $student['last_name']);
                                }
                                $classInfo = trim(($student['class'] ?? '') . ' ' . ($student['section'] ?? ''));
                                $admissionInfo = !empty($student['admission_number']) ? ' (Adm: ' . $student['admission_number'] . ')' : '';
                                ?>
                                <label style="display: flex; align-items: center; gap: 8px; font-weight: normal; cursor: pointer; user-select: none;">
                                    <input type="checkbox" name="student_ids[]" value="<?= e((string) $student['id']) ?>">
                                    <span style="font-size: 14px;">
                                        <strong><?= e($sName) ?></strong>
                                        <?php if ($classInfo !== ''): ?>
                                            <small style="color: #666; font-size: 11px;">[<?= e($classInfo) ?>]</small>
                                        <?php endif; ?>
                                        <small style="color: #888; font-size: 10px; display: block;"><?= e($admissionInfo) ?></small>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <button type="submit" class="button">Assign Selected Students</button>
                    <button type="button" class="button button-muted" onclick="var cbs=document.querySelectorAll('#studentAssignPanel input[type=checkbox]'); cbs.forEach(cb=>cb.checked=!cb.checked)">Toggle Selection</button>
                </div>
            </form>
        </details>
    </section>

    <?php if (!empty($editingParticipant)): ?>
        <section class="panel recipients-edit-panel">
            <h3>Edit Recipient #<?= e((string) $editingParticipant['id']) ?></h3>
            <form method="post" class="form-grid two">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="update_participant">
                <input type="hidden" name="conference_id" value="<?= e((string) $selectedConferenceId) ?>">
                <input type="hidden" name="certificate_type_id" value="<?= e((string) $selectedTypeId) ?>">
                <input type="hidden" name="participant_id" value="<?= e((string) $editingParticipant['id']) ?>">
                <input type="hidden" name="search" value="<?= e((string) ($searchTerm ?? '')) ?>">
                <input type="hidden" name="status" value="<?= e((string) ($statusFilter ?? '')) ?>">
                <input type="hidden" name="page_no" value="<?= e((string) $pageNumber) ?>">

                <label>
                    Student Name
                    <input type="text" name="name" required value="<?= e((string) ($editingParticipant['name'] ?? '')) ?>">
                </label>

                <label class="full-span">
                    Group Participant Names (comma separated)
                    <textarea name="group_names" rows="3" placeholder="Co Author 1, Co Author 2, Co Author 3"><?= e((string) ($editingParticipant['group_names_input'] ?? '')) ?></textarea>
                </label>

                <label>
                    Email
                    <input type="email" name="email" value="<?= e((string) ($editingParticipant['email'] ?? '')) ?>">
                </label>

                <label>
                    Class &amp; Section
                    <input type="text" name="institute" value="<?= e((string) ($editingParticipant['institute'] ?? '')) ?>">
                </label>

                <label>
                    Title
                    <input type="text" name="title" value="<?= e((string) ($editingParticipant['title'] ?? '')) ?>">
                </label>

                <label>
                    Date
                    <input type="date" name="issued_date" value="<?= e((string) ($editingParticipant['issued_date'] ?? date('Y-m-d'))) ?>">
                </label>

                <label>
                    Category
                    <select name="manual_certificate_type_id" required>
                        <option value="">Select Category</option>
                        <?php foreach ($certificateTypes as $type): ?>
                            <option value="<?= e((string) $type['id']) ?>" <?= (int) $type['id'] === (int) ($editingParticipant['certificate_type_id'] ?? 0) ? 'selected' : '' ?>>
                                <?= e($type['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <div class="inline-actions full-span-actions">
                    <button type="submit">Update Recipient</button>
                    <a class="button button-muted" href="<?= e(url('participants', $buildRecipientParams())) ?>">Cancel Edit</a>
                </div>
            </form>
        </section>
    <?php endif; ?>

    <section class="panel recipients-table-panel">
        <div class="table-wrap recipients-table-wrap">
            <table class="recipients-table" id="recipientsTable">
                <thead>
                    <tr>
                        <th class="recipient-col-check"><input type="checkbox" id="recipientSelectAll" aria-label="Select all recipients"></th>
                        <th>Student Name</th>
                        <th>Templates &amp; Status</th>
                        <th>Downloads</th>
                        <th>Last Activity</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($participants === []): ?>
                    <tr>
                        <td colspan="6" class="recipient-empty-row">No students found for the selected filters.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($participants as $row): ?>
                        <?php
                        $avatar = $avatarFromName((string) ($row['name'] ?? 'U'));
                        $statusMeta = $statusChipMeta((string) ($row['status'] ?? 'pending'));
                        ?>
                        <tr>
                            <td class="recipient-col-check">
                                <input type="checkbox" class="recipient-row-check" data-recipient-checkbox value="<?= e((string) ($row['id'] ?? 0)) ?>" aria-label="Select <?= e((string) ($row['name'] ?? 'recipient')) ?>">
                            </td>
                            <td>
                                <div class="recipient-identity">
                                    <span class="recipient-avatar"><?= e($avatar) ?></span>
                                    <div>
                                        <strong><?= e((string) ($row['name'] ?? 'Unknown')) ?></strong>
                                        <small><?= e((string) ($row['email'] ?? '-')) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="recipient-tags">
                                    <span class="recipient-tag"><?= e((string) ($row['certificate_type_name'] ?? 'General')) ?></span>
                                    <?php if (!empty($row['group_names_display'])): ?>
                                        <span class="recipient-tag recipient-tag-muted"><?= e((string) $row['group_names_display']) ?></span>
                                    <?php endif; ?>
                                    <span class="recipient-status-chip <?= e((string) ($statusMeta['class'] ?? 'recipient-status-pending')) ?>"><?= e((string) ($statusMeta['label'] ?? 'Pending')) ?></span>
                                </div>
                            </td>
                            <td class="recipient-certs-cell"><?= e(str_pad((string) ((int) ($row['cert_count'] ?? 0)), 2, '0', STR_PAD_LEFT)) ?></td>
                            <td>
                                <div class="recipient-activity">
                                    <strong><?= e((string) ($row['last_activity_at'] ?? '-')) ?></strong>
                                    <small><?= e((string) ($row['last_activity_note'] ?? 'Pending processing')) ?></small>
                                </div>
                            </td>
                            <td>
                                <div class="recipient-actions">
                                    <form method="post">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="generate_single">
                                        <input type="hidden" name="conference_id" value="<?= e((string) $selectedConferenceId) ?>">
                                        <input type="hidden" name="certificate_type_id" value="<?= e((string) $selectedTypeId) ?>">
                                        <input type="hidden" name="search" value="<?= e((string) ($searchTerm ?? '')) ?>">
                                        <input type="hidden" name="status" value="<?= e((string) ($statusFilter ?? '')) ?>">
                                        <input type="hidden" name="page_no" value="<?= e((string) $pageNumber) ?>">
                                        <input type="hidden" name="participant_id" value="<?= e((string) ($row['id'] ?? 0)) ?>">
                                        <button type="submit" class="button button-muted recipient-action-btn">Issue</button>
                                    </form>

                                    <form method="post" data-confirm="Send certificate email to this recipient?">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="send_single_email">
                                        <input type="hidden" name="conference_id" value="<?= e((string) $selectedConferenceId) ?>">
                                        <input type="hidden" name="certificate_type_id" value="<?= e((string) $selectedTypeId) ?>">
                                        <input type="hidden" name="search" value="<?= e((string) ($searchTerm ?? '')) ?>">
                                        <input type="hidden" name="status" value="<?= e((string) ($statusFilter ?? '')) ?>">
                                        <input type="hidden" name="page_no" value="<?= e((string) $pageNumber) ?>">
                                        <input type="hidden" name="participant_id" value="<?= e((string) ($row['id'] ?? 0)) ?>">
                                        <?php $recipientEmail = trim((string) ($row['email'] ?? '')); ?>
                                        <button type="submit" class="button button-muted recipient-action-btn" <?= $recipientEmail === '' ? 'disabled title="Recipient email is missing."' : '' ?>>Send Email</button>
                                    </form>

                                    <a class="button button-muted recipient-action-btn" href="<?= e(url('participants', $buildRecipientParams(['edit_id' => (int) ($row['id'] ?? 0)]))) ?>">Edit</a>

                                    <form method="post" data-confirm="Delete recipient?">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="delete_participant">
                                        <input type="hidden" name="conference_id" value="<?= e((string) $selectedConferenceId) ?>">
                                        <input type="hidden" name="certificate_type_id" value="<?= e((string) $selectedTypeId) ?>">
                                        <input type="hidden" name="search" value="<?= e((string) ($searchTerm ?? '')) ?>">
                                        <input type="hidden" name="status" value="<?= e((string) ($statusFilter ?? '')) ?>">
                                        <input type="hidden" name="page_no" value="<?= e((string) $pageNumber) ?>">
                                        <input type="hidden" name="participant_id" value="<?= e((string) ($row['id'] ?? 0)) ?>">
                                        <button type="submit" class="button button-muted recipient-action-btn recipient-action-danger">Remove</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="recipients-table-footer">
            <p>Showing <?= e((string) ($rangeStart ?? 0)) ?> - <?= e((string) ($rangeEnd ?? 0)) ?> of <?= e(number_format((int) ($totalRows ?? 0))) ?> students</p>

            <nav class="recipients-pagination" aria-label="Recipients pagination">
                <?php $prevPage = max(1, (int) ($pageNumber ?? 1) - 1); ?>
                <a class="recipient-page-link <?= (int) ($pageNumber ?? 1) <= 1 ? 'is-disabled' : '' ?>" href="<?= e(url('participants', $buildRecipientParams(['page_no' => $prevPage]))) ?>">Previous</a>

                <?php for ($page = $windowStart; $page <= $windowEnd; $page++): ?>
                    <a class="recipient-page-link <?= (int) $page === (int) ($pageNumber ?? 1) ? 'is-active' : '' ?>" href="<?= e(url('participants', $buildRecipientParams(['page_no' => $page]))) ?>"><?= e((string) $page) ?></a>
                <?php endfor; ?>

                <?php $nextPage = min((int) ($totalPages ?? 1), (int) ($pageNumber ?? 1) + 1); ?>
                <a class="recipient-page-link <?= (int) ($pageNumber ?? 1) >= (int) ($totalPages ?? 1) ? 'is-disabled' : '' ?>" href="<?= e(url('participants', $buildRecipientParams(['page_no' => $nextPage]))) ?>">Next</a>
            </nav>
        </div>
    </section>

    <form method="post" id="recipientBulkActionForm" class="recipient-bulk-hidden-form">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="conference_id" value="<?= e((string) $selectedConferenceId) ?>">
        <input type="hidden" name="certificate_type_id" value="<?= e((string) $selectedTypeId) ?>">
        <input type="hidden" name="search" value="<?= e((string) ($searchTerm ?? '')) ?>">
        <input type="hidden" name="status" value="all">
        <input type="hidden" name="page_no" value="<?= e((string) $pageNumber) ?>">
        <input type="hidden" name="action" id="recipientBulkActionInput" value="">
    </form>

    <div class="recipient-bulk-bar" id="recipientBulkBar" hidden>
        <strong><span id="recipientBulkCount">0</span> selected</strong>
        <button type="button" class="button button-muted recipient-bulk-btn" data-recipient-bulk-action="generate_selected">Send Certificate</button>
        <button type="button" class="button button-muted recipient-bulk-btn" data-recipient-bulk-action="delete_selected">Remove</button>
        <button type="button" class="button button-muted recipient-bulk-btn" data-recipient-bulk-clear>×</button>
    </div>
</section>
