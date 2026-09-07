<?php
/** @var array<int, array<string, mixed>> $conferences */
/** @var array<int, array<string, mixed>> $certificateTypes */
/** @var array<int, array<string, mixed>> $participants */
/** @var int $selectedConferenceId */
/** @var int $selectedTypeId */
/** @var int $totalRows */
/** @var int $totalPages */
/** @var int $pageNumber */
/** @var int $pageSize */
/** @var string $searchTerm */
/** @var string $printingEmail */
?>
<section class="grid-2">
    <article class="panel">
        <h3>Print Queue</h3>
        <p class="small">Select an event and category to view generated certificates. Send individual or all certificates to the printing service or to parents.</p>

        <form method="get" class="form-grid two">
            <input type="hidden" name="page" value="printer">

            <label>
                Event
                <select name="conference_id" onchange="this.form.submit()">
                    <?php foreach ($conferences as $conf): ?>
                        <option value="<?= e((string) $conf['id']) ?>" <?= (int) $conf['id'] === (int) $selectedConferenceId ? 'selected' : '' ?>>
                            <?= e((string) ($conf['name'] ?? '')) ?> <?= e((string) ($conf['year'] ?? '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Category
                <select name="certificate_type_id" onchange="this.form.submit()">
                    <option value="0">All Categories</option>
                    <?php foreach ($certificateTypes as $type): ?>
                        <option value="<?= e((string) $type['id']) ?>" <?= (int) $type['id'] === (int) $selectedTypeId ? 'selected' : '' ?>>
                            <?= e((string) ($type['name'] ?? '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
        </form>

        <form method="get" class="form-inline top-gap-sm">
            <input type="hidden" name="page" value="printer">
            <input type="hidden" name="conference_id" value="<?= e((string) $selectedConferenceId) ?>">
            <input type="hidden" name="certificate_type_id" value="<?= e((string) $selectedTypeId) ?>">
            <input type="search" name="search" value="<?= e($searchTerm) ?>" placeholder="Search by name or email...">
            <button type="submit" class="button button-muted">Search</button>
        </form>
    </article>

    <article class="panel">
        <h3>Printing Service</h3>
        <p class="small">
            <?php if ($printingEmail !== ''): ?>
                Sending to: <strong><?= e($printingEmail) ?></strong>
            <?php else: ?>
                <span class="badge badge-danger">Not configured</span> — set Printing Email in <a href="<?= e(url('settings')) ?>">System Settings</a>.
            <?php endif; ?>
        </p>

        <?php if ($printingEmail !== ''): ?>
            <form method="post" class="form-inline top-gap-sm" onsubmit="return confirm('Send ALL certificates on this page to the printing email?')">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="conference_id" value="<?= e((string) $selectedConferenceId) ?>">
                <input type="hidden" name="certificate_type_id" value="<?= e((string) $selectedTypeId) ?>">
                <button type="submit" name="action" value="send_to_printer_bulk" class="button">Send All to Printer</button>
            </form>
        <?php endif; ?>
    </article>
</section>

<section class="panel">
    <h3>Certificates (<?= e((string) $totalRows) ?> total)</h3>

    <?php if ($participants === []): ?>
        <p class="small">No generated certificates found for this filter.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Category</th>
                        <th>Generated</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($participants as $row): ?>
                        <tr>
                            <td><?= e((string) ($row['name'] ?? '')) ?></td>
                            <td><?= e((string) ($row['email'] ?? '')) ?></td>
                            <td><?= e((string) ($row['certificate_type_name'] ?? '')) ?></td>
                            <td><?= e((string) ($row['generated_at'] ?? '')) ?></td>
                            <td>
                                <?php
                                $status = (string) ($row['status'] ?? '');
                                $statusBadge = 'badge-success';
                                if ($status === 'failed') { $statusBadge = 'badge-danger'; }
                                elseif ($status === 'pending') { $statusBadge = 'badge-muted'; }
                                elseif ($status === 'printed') { $statusBadge = 'badge-info'; }
                                ?>
                                <span class="badge <?= e($statusBadge) ?>"><?= e($status ?: 'generated') ?></span>
                            </td>
                            <td>
                                <div class="inline-actions">
                                    <?php if ($printingEmail !== '' && filter_var($printingEmail, FILTER_VALIDATE_EMAIL)): ?>
                                        <form method="post" class="form-inline" style="display:inline">
                                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="conference_id" value="<?= e((string) $selectedConferenceId) ?>">
                                            <input type="hidden" name="certificate_type_id" value="<?= e((string) $selectedTypeId) ?>">
                                            <input type="hidden" name="participant_id" value="<?= e((string) $row['id']) ?>">
                                            <input type="hidden" name="search" value="<?= e($searchTerm) ?>">
                                            <button type="submit" name="action" value="send_to_printer_single" class="button button-muted" onclick="return confirm('Send this certificate to the printing service?')">Send to Printer</button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if (!empty($row['email']) && filter_var((string) $row['email'], FILTER_VALIDATE_EMAIL)): ?>
                                        <form method="post" class="form-inline" style="display:inline">
                                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="conference_id" value="<?= e((string) $selectedConferenceId) ?>">
                                            <input type="hidden" name="certificate_type_id" value="<?= e((string) $selectedTypeId) ?>">
                                            <input type="hidden" name="participant_id" value="<?= e((string) $row['id']) ?>">
                                            <input type="hidden" name="search" value="<?= e($searchTerm) ?>">
                                            <button type="submit" name="action" value="send_to_parent_single" class="button" onclick="return confirm('Send this certificate to the parent email <?= e((string) ($row['email'] ?? '')) ?>?')">Send to Parent</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="pagination top-gap-sm">
                <?php
                $baseParams = [
                    'page' => 'printer',
                    'conference_id' => $selectedConferenceId,
                    'certificate_type_id' => $selectedTypeId,
                    'search' => $searchTerm,
                ];
                for ($p = 1; $p <= $totalPages; $p++):
                    $pageParams = array_merge($baseParams, ['page_no' => $p]);
                ?>
                    <a href="<?= e(url('printer', $pageParams)) ?>" class="button <?= $p === $pageNumber ? '' : 'button-muted' ?>"><?= $p ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>
