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
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap: wrap; gap: 10px;">
                        <label style="font-weight: 600; margin: 0;">Select Students (<span id="visibleStudentCount"><?= count($students) ?></span> available)</label>
                        
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <!-- Select All Checkbox -->
                            <label style="display: flex; align-items: center; gap: 6px; font-weight: 600; font-size: 13px; cursor: pointer; user-select: none; color: #4f46e5;">
                                <input type="checkbox" id="selectAllStudentsCheckbox" onchange="toggleSelectAllStudents(this.checked)">
                                <span>Select All</span>
                            </label>
                        </div>
                    </div>

                    <!-- Instant Live Filter Search Input -->
                    <div style="margin-bottom: 10px; position: relative;">
                        <input type="text" id="studentFilterInput" oninput="filterStudentsList()" placeholder="🔍 Type to search student name, admission no, class..." 
                               style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 13px; font-family: inherit; outline: none; transition: border-color 0.2s;"
                               onfocus="this.style.borderColor='#6366f1';" onblur="this.style.borderColor='#cbd5e1';">
                    </div>

                    <div style="max-height: 250px; overflow-y: auto; border: 1px solid #ccc; border-radius: 6px; padding: 15px; background: #fff;">
                        <div id="studentsGridContainer" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 10px;">
                            <?php foreach ($students as $student): ?>
                                <?php
                                $sName = trim($student['first_name'] . ' ' . $student['middle_name'] . ' ' . $student['last_name']);
                                if ($sName === '') {
                                    $sName = trim($student['first_name'] . ' ' . $student['last_name']);
                                }
                                $classInfo = trim(($student['class'] ?? '') . ' ' . ($student['section'] ?? ''));
                                $admissionInfo = !empty($student['admission_number']) ? ' (Adm: ' . $student['admission_number'] . ')' : '';
                                $searchData = strtolower($sName . ' ' . $classInfo . ' ' . $student['admission_number']);
                                ?>
                                <label class="student-item-label" data-search="<?= e($searchData) ?>" style="display: flex; align-items: center; gap: 8px; font-weight: normal; cursor: pointer; user-select: none;">
                                    <input type="checkbox" name="student_ids[]" value="<?= e((string) $student['id']) ?>" class="student-checkbox">
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
                        <div id="noStudentsFoundMsg" style="display: none; padding: 20px; text-align: center; color: #64748b; font-size: 13px;">
                            No students match your search criteria.
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <button type="submit" class="button">Assign Selected Students</button>
                    <button type="button" class="button button-muted" onclick="toggleSelectAllStudents(!document.getElementById('selectAllStudentsCheckbox').checked); document.getElementById('selectAllStudentsCheckbox').checked = !document.getElementById('selectAllStudentsCheckbox').checked;">Toggle Selection</button>
                </div>
            </form>
        </details>
    </section>

<script>
function filterStudentsList() {
    const q = document.getElementById('studentFilterInput').value.toLowerCase().trim();
    const items = document.querySelectorAll('#studentsGridContainer .student-item-label');
    let visibleCount = 0;

    items.forEach(item => {
        const text = item.getAttribute('data-search') || '';
        if (!q || text.includes(q)) {
            item.style.display = 'flex';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });

    const noMsg = document.getElementById('noStudentsFoundMsg');
    if (noMsg) {
        noMsg.style.display = visibleCount === 0 ? 'block' : 'none';
    }

    const countSpan = document.getElementById('visibleStudentCount');
    if (countSpan) {
        countSpan.textContent = visibleCount;
    }

    updateSelectAllCheckboxState();
}

function toggleSelectAllStudents(checked) {
    const items = document.querySelectorAll('#studentsGridContainer .student-item-label');
    items.forEach(item => {
        if (item.style.display !== 'none') {
            const cb = item.querySelector('input[type="checkbox"]');
            if (cb) cb.checked = checked;
        }
    });
    updateSelectAllCheckboxState();
}

function updateSelectAllCheckboxState() {
    const visibleCbs = Array.from(document.querySelectorAll('#studentsGridContainer .student-item-label'))
        .filter(item => item.style.display !== 'none')
        .map(item => item.querySelector('input[type="checkbox"]'))
        .filter(Boolean);

    const selectAllCb = document.getElementById('selectAllStudentsCheckbox');
    if (selectAllCb && visibleCbs.length > 0) {
        selectAllCb.checked = visibleCbs.every(cb => cb.checked);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const grid = document.getElementById('studentsGridContainer');
    if (grid) {
        grid.addEventListener('change', function(e) {
            if (e.target && e.target.classList.contains('student-checkbox')) {
                updateSelectAllCheckboxState();
            }
        });
    }
});
</script>

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
        <!-- Certificate Category Filter & Student Search Bar -->
        <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; border-top-left-radius: 12px; border-top-right-radius: 12px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 12px; flex: 1; min-width: 280px;">
                <!-- Category Dropdown Filter -->
                <div style="display: flex; align-items: center; gap: 8px;">
                    <label for="tableCategoryFilter" style="font-weight: 600; font-size: 13px; color: #475569; white-space: nowrap;">Category:</label>
                    <select id="tableCategoryFilter" onchange="filterTableRows()" style="padding: 7px 12px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 13px; font-family: inherit; background: #ffffff; color: #1e293b; cursor: pointer;">
                        <option value="">All Categories</option>
                        <?php foreach ($certificateTypes as $type): ?>
                            <option value="<?= e(strtolower($type['name'])) ?>"><?= e($type['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Instant Name Search Input -->
                <div style="flex: 1; max-width: 380px; position: relative;">
                    <input type="text" id="tableNameSearch" oninput="filterTableRows()" placeholder="🔍 Search student name, email..." 
                           style="width: 100%; padding: 7px 12px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 13px; font-family: inherit; background: #ffffff; outline: none; transition: border-color 0.2s;"
                           onfocus="this.style.borderColor='#6366f1';" onblur="this.style.borderColor='#cbd5e1';">
                </div>
            </div>

            <div style="font-size: 13px; color: #64748b; font-weight: 500;">
                Showing <span id="tableVisibleCount" style="font-weight: 700; color: #4f46e5;"><?= count($participants) ?></span> records
            </div>
        </div>

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

<script>
function filterTableRows() {
    const category = document.getElementById('tableCategoryFilter').value.toLowerCase().trim();
    const query = document.getElementById('tableNameSearch').value.toLowerCase().trim();
    const rows = document.querySelectorAll('#recipientsTable tbody tr');
    let visibleCount = 0;

    rows.forEach(row => {
        if (row.classList.contains('recipient-empty-row')) return;

        const studentName = (row.querySelector('.recipient-identity strong')?.textContent || '').toLowerCase();
        const studentEmail = (row.querySelector('.recipient-identity small')?.textContent || '').toLowerCase();
        const certCategory = (row.querySelector('.recipient-tag')?.textContent || '').toLowerCase();

        const matchesCategory = !category || certCategory.includes(category);
        const matchesQuery = !query || studentName.includes(query) || studentEmail.includes(query);

        if (matchesCategory && matchesQuery) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const countSpan = document.getElementById('tableVisibleCount');
    if (countSpan) {
        countSpan.textContent = visibleCount;
    }
}
</script>
