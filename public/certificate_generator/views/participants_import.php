<?php /** @var array $conference */ ?>
<div class="container">
    <h1>Import participants for <?php echo htmlspecialchars($conference['name'] ?? ''); ?></h1>

    <?php echo render_messages(); ?>

    <?php if (empty($previewHeaders)): ?>
        <form method="post" enctype="multipart/form-data">
            <?php echo csrf_input(); ?>
            <input type="hidden" name="action" value="preview_upload" />
            <div class="form-group">
                <label>Select file (CSV / XLSX / XLS / ODS)</label>
                <input type="file" name="import_file" accept=".csv,.xls,.xlsx,.ods" required />
            </div>
            <div class="form-group">
                <label>Default certificate type (optional)</label>
                <select name="default_certificate_type_id">
                    <option value="">-- none --</option>
                    <?php foreach ($certificateTypes as $ct): ?>
                        <option value="<?php echo (int)$ct['id']; ?>"><?php echo htmlspecialchars($ct['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="btn">Upload &amp; Preview</button>
        </form>
    <?php else: ?>
        <h2>Detected columns</h2>
        <form method="post">
            <?php echo csrf_input(); ?>
            <input type="hidden" name="action" value="execute_import" />
            <input type="hidden" name="temp_path" value="<?php echo htmlspecialchars($tempUploadName); ?>" />
            <input type="hidden" name="original_name" value="<?php echo htmlspecialchars($originalUploadName); ?>" />

            <p>Map the detected file columns to database fields. Leave blank to skip a column.</p>

            <table class="table">
                <thead>
                    <tr>
                        <th>Detected Column</th>
                        <th>Map to</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($previewHeaders as $col): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($col); ?></td>
                        <td>
                            <select name="mapping[<?php echo htmlspecialchars($col); ?>]">
                                <option value="">-- skip --</option>
                                <option value="name">Name</option>
                                <option value="email">Email</option>
                                <option value="institute">Institute</option>
                                <option value="title">Title</option>
                                <option value="category">Certificate type (category)</option>
                                <option value="date">Issued date</option>
                                <option value="extra:<?php echo htmlspecialchars($col); ?>">Extra: <?php echo htmlspecialchars($col); ?></option>
                            </select>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <div class="form-group">
                <label>Default certificate type (used when category not mapped or empty)</label>
                <select name="default_certificate_type_id">
                    <option value="">-- required if category not provided for rows --</option>
                    <?php foreach ($certificateTypes as $ct): ?>
                        <option value="<?php echo (int)$ct['id']; ?>"><?php echo htmlspecialchars($ct['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button class="btn">Execute Import</button>
        </form>
    <?php endif; ?>

    <p><a href="<?php echo url('participants', ['conference_id' => $conference['id']]); ?>">Back to participants</a></p>
</div>
