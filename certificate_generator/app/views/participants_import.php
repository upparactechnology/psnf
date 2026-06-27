<?php /** @var array $conference */ ?>
<section class="panel">
    <h3>Import Recipients</h3>
    <p class="small">Conference: <?= e((string) ($conference['name'] ?? '')) ?></p>
    <?= render_messages(); ?>
</section>

<?php if (empty($previewHeaders) && empty($previewSheets)): ?>
    <section class="panel">
        <h3>Upload Spreadsheet</h3>
        <form method="post" enctype="multipart/form-data" class="form-grid two">
            <?= csrf_input(); ?>
            <input type="hidden" name="action" value="preview_upload">

            <label>
                Select file (CSV / XLSX / XLS / ODS)
                <input type="file" name="import_file" accept=".csv,.xls,.xlsx,.ods" required>
            </label>

            <label>
                Header row number
                <input type="number" name="header_row" value="1" min="1">
                <span class="small">Set which row contains column headers (1 = first row).</span>
            </label>

            <label class="full-span">
                Default certificate type (optional)
                <select name="default_certificate_type_id">
                    <option value="">-- none --</option>
                    <?php foreach ($certificateTypes as $ct): ?>
                        <option value="<?= e((string) $ct['id']) ?>"><?= e((string) $ct['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <button class="full-span-submit">Upload and Preview</button>
        </form>
    </section>

<?php elseif (!empty($previewSheets) && empty($previewHeaders)): ?>
    <section class="panel">
        <h3>Select Sheet</h3>
        <form method="post" class="form-grid two">
            <?= csrf_input(); ?>
            <input type="hidden" name="action" value="preview_upload">
            <input type="hidden" name="temp_path" value="<?= e((string) $tempUploadName) ?>">
            <input type="hidden" name="original_name" value="<?= e((string) $originalUploadName) ?>">

            <label>
                Detected sheets
                <select name="sheet_name">
                    <?php foreach ($previewSheets as $sheet): ?>
                        <option value="<?= e((string) $sheet) ?>" <?= (string) $sheet === (string) $selectedSheet ? 'selected' : '' ?>>
                            <?= e((string) $sheet) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Header row number
                <input type="number" name="header_row" value="1" min="1">
                <span class="small">Set which row contains column headers on the selected sheet.</span>
            </label>

            <label class="full-span">
                Default certificate type (optional)
                <select name="default_certificate_type_id">
                    <option value="">-- none --</option>
                    <?php foreach ($certificateTypes as $ct): ?>
                        <option value="<?= e((string) $ct['id']) ?>"><?= e((string) $ct['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <button class="full-span-submit">Preview Selected Sheet</button>
        </form>
    </section>

<?php else: ?>
    <section class="panel">
        <h3>Map Columns</h3>
        <p class="small">Map each detected file column to a participant field. Leave blank to skip.</p>

        <form method="post" class="form-grid">
            <?= csrf_input(); ?>
            <input type="hidden" name="action" value="execute_import">
            <input type="hidden" name="temp_path" value="<?= e((string) $tempUploadName) ?>">
            <input type="hidden" name="original_name" value="<?= e((string) $originalUploadName) ?>">
            <input type="hidden" name="header_row" value="<?= e((string) ((int) ($headerRow ?? 1))) ?>">
            <?php if (!empty($selectedSheet)): ?>
                <input type="hidden" name="sheet_name" value="<?= e((string) $selectedSheet) ?>">
            <?php endif; ?>

            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Detected Column</th>
                            <th>Map To</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($previewHeaders as $col): ?>
                            <tr>
                                <td><?= e((string) $col) ?></td>
                                <td>
                                    <select name="mapping[<?= e((string) $col) ?>]">
                                        <option value="">-- skip --</option>
                                        <option value="name">Name</option>
                                        <option value="email">Email</option>
                                        <option value="institute">Institute</option>
                                        <option value="title">Title</option>
                                        <option value="category">Certificate type (category)</option>
                                        <option value="date">Issued date</option>
                                        <option value="extra:<?= e((string) $col) ?>">Extra: <?= e((string) $col) ?></option>
                                        <option value="authors">Authors / Co-authors</option>
                                    </select>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <label>
                Default certificate type (used when category is not mapped or empty)
                <select name="default_certificate_type_id">
                    <option value="">-- required if category is not provided for rows --</option>
                    <?php foreach ($certificateTypes as $ct): ?>
                        <option value="<?= e((string) $ct['id']) ?>"><?= e((string) $ct['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <button type="submit">Execute Import</button>
        </form>
    </section>
<?php endif; ?>

<section class="panel">
    <a class="button button-muted" href="<?= e(url('participants', ['conference_id' => (int) ($conference['id'] ?? 0)])) ?>">Back to Recipients</a>
</section>
