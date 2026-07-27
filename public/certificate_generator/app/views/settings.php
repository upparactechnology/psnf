<section class="panel">
    <h3>Global Configuration</h3>
    <p class="small">Filename placeholders: {conference}, {year}, {category}, {name}, {id}</p>
    <p class="small">Email placeholders: {{name}}, {{certificate_type}}, {{conference}}, {{year}}</p>
    <p class="small">Font status: <?= $fontExists ? '<span class="badge badge-success">Font file found</span>' : '<span class="badge badge-danger">Font path missing</span>' ?></p>
    <p class="small">Total available font families in editor: <?= e((string) count($availableFonts)) ?></p>
</section>

<section class="panel">
    <h3>Custom Font Upload</h3>
    <p class="small">Upload TTF/OTF files. Regular is required. Bold, italic, and bold italic are optional.</p>

    <form method="post" enctype="multipart/form-data" class="form-grid two">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="upload_custom_font">

        <label>
            Font Display Name
            <input type="text" name="custom_font_name" placeholder="Bai Jamjuree" required>
        </label>

        <label>
            Font Slug (optional)
            <input type="text" name="custom_font_slug" placeholder="bai-jamjuree">
        </label>

        <label>
            Regular (.ttf/.otf)
            <input type="file" name="font_file_regular" accept=".ttf,.otf" required>
        </label>

        <label>
            Bold (.ttf/.otf)
            <input type="file" name="font_file_bold" accept=".ttf,.otf">
        </label>

        <label>
            Italic (.ttf/.otf)
            <input type="file" name="font_file_italic" accept=".ttf,.otf">
        </label>

        <label>
            Bold Italic (.ttf/.otf)
            <input type="file" name="font_file_bold_italic" accept=".ttf,.otf">
        </label>

        <button type="submit">Upload / Update Custom Font</button>
    </form>

    <hr>

    <h4>Installed Custom Fonts</h4>
    <?php if ($customFonts === []): ?>
        <p class="small">No custom fonts uploaded yet.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Styles</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customFonts as $font): ?>
                        <?php $styles = array_keys((array) ($font['files'] ?? [])); ?>
                        <tr>
                            <td><?= e((string) ($font['name'] ?? '')) ?></td>
                            <td><span class="small"><?= e((string) ($font['slug'] ?? '')) ?></span></td>
                            <td><?= e(implode(', ', $styles)) ?></td>
                            <td>
                                <form method="post" onsubmit="return confirm('Remove this custom font?');">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="action" value="remove_custom_font">
                                    <input type="hidden" name="custom_font_slug" value="<?= e((string) ($font['slug'] ?? '')) ?>">
                                    <button type="submit" class="button button-danger">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section class="panel">
    <form method="post" class="form-grid">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="save_settings">

        <?php foreach ($settingDefinitions as $key => $definition): ?>
            <?php $value = (string) ($settings[$key] ?? $definition['default']); ?>
            <label>
                <?= e($definition['label']) ?>
                <?php if ($definition['type'] === 'textarea'): ?>
                    <textarea name="<?= e($key) ?>" rows="6"><?= e($value) ?></textarea>
                <?php elseif ($definition['type'] === 'select'): ?>
                    <select name="<?= e($key) ?>">
                        <?php foreach ($definition['options'] as $optionValue => $optionLabel): ?>
                            <option value="<?= e((string) $optionValue) ?>" <?= (string) $optionValue === $value ? 'selected' : '' ?>>
                                <?= e((string) $optionLabel) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <input type="<?= e($definition['type']) ?>" name="<?= e($key) ?>" value="<?= e($value) ?>">
                <?php endif; ?>
            </label>
        <?php endforeach; ?>

        <button type="submit">Save Settings</button>
    </form>
</section>
