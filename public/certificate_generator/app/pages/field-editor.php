<?php
declare(strict_types=1);

$selectedConferenceId = 1;
$conferenceStmt = db()->prepare('SELECT * FROM conferences WHERE id = 1 LIMIT 1');
$conferenceStmt->execute();
$conference = $conferenceStmt->fetch();

$typeStmt = db()->prepare('SELECT * FROM certificate_types WHERE conference_id = :conference_id ORDER BY name ASC');
$typeStmt->execute(['conference_id' => $selectedConferenceId]);
$certificateTypes = $typeStmt->fetchAll();

$selectedTypeId = (int) ($_GET['certificate_type_id'] ?? ($_POST['certificate_type_id'] ?? ($certificateTypes[0]['id'] ?? 0)));
$selectedType = null;
foreach ($certificateTypes as $type) {
    if ((int) $type['id'] === $selectedTypeId) {
        $selectedType = $type;
        break;
    }
}

if ($selectedType === null) {
    flash('error', 'Certificate type not found for selected conference.');
    redirect(url('certificate-types', ['conference_id' => $selectedConferenceId]));
}

$templatePath = (string) ($selectedType['template_path'] ?? '');
$templateExists = $templatePath !== '' && is_file(APP_ROOT . '/' . $templatePath);

if ((int) ($_GET['download_template'] ?? 0) === 1) {
    if (!$templateExists) {
        flash('error', 'Template image not found for selected type.');
        redirect(url('field-editor', ['conference_id' => $selectedConferenceId, 'certificate_type_id' => $selectedTypeId]));
    }

    $absoluteTemplate = APP_ROOT . '/' . ltrim(str_replace('\\', '/', $templatePath), '/');
    $downloadName = safe_filename(
        (string) ($conference['name'] ?? 'conference') . '_' .
        (string) ($conference['year'] ?? date('Y')) . '_' .
        (string) ($selectedType['slug'] ?? 'template')
    ) . '.jpg';

    header('Content-Type: image/jpeg');
    header('Content-Disposition: attachment; filename="' . $downloadName . '"');
    header('Content-Length: ' . filesize($absoluteTemplate));
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
    readfile($absoluteTemplate);
    exit;
}

if (is_post_request()) {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'upload_template') {
        if (!isset($_FILES['template']) || (int) $_FILES['template']['error'] !== UPLOAD_ERR_OK) {
            flash('error', 'Template upload failed.');
            redirect(url('field-editor', ['conference_id' => $selectedConferenceId, 'certificate_type_id' => $selectedTypeId]));
        }

        $file = $_FILES['template'];
        $tmpPath = (string) ($file['tmp_name'] ?? '');
        $size = (int) ($file['size'] ?? 0);

        if ($size > MAX_UPLOAD_SIZE) {
            flash('error', 'Template file exceeds max size limit (10MB).');
            redirect(url('field-editor', ['conference_id' => $selectedConferenceId, 'certificate_type_id' => $selectedTypeId]));
        }

        $mime = mime_content_type($tmpPath) ?: '';
        if (!in_array($mime, ALLOWED_IMAGE_TYPES, true)) {
            flash('error', 'Only JPG templates are allowed.');
            redirect(url('field-editor', ['conference_id' => $selectedConferenceId, 'certificate_type_id' => $selectedTypeId]));
        }

        $year = (string) ($conference['year'] ?? date('Y'));
        $slug = (string) ($selectedType['slug'] ?? '');
        if ($slug === '') {
            $slug = slugify((string) ($selectedType['name'] ?? ('template-' . $selectedTypeId)));
        }

        $outputDir = TEMPLATES_ROOT . '/' . $year . '/' . $slug;
        if (!is_dir($outputDir) && !mkdir($outputDir, 0777, true) && !is_dir($outputDir)) {
            flash('error', 'Unable to create template folder.');
            redirect(url('field-editor', ['conference_id' => $selectedConferenceId, 'certificate_type_id' => $selectedTypeId]));
        }

        $targetAbsolutePath = $outputDir . '/template.jpg';
        if (!move_uploaded_file($tmpPath, $targetAbsolutePath)) {
            flash('error', 'Unable to save template file.');
            redirect(url('field-editor', ['conference_id' => $selectedConferenceId, 'certificate_type_id' => $selectedTypeId]));
        }

        $relativePath = 'templates/' . $year . '/' . $slug . '/template.jpg';
        $updateStmt = db()->prepare('UPDATE certificate_types SET template_path = :template_path WHERE id = :id AND conference_id = :conference_id');
        $updateStmt->execute([
            'template_path' => $relativePath,
            'id' => $selectedTypeId,
            'conference_id' => $selectedConferenceId,
        ]);

        flash('success', 'Template image uploaded successfully.');
        redirect(url('field-editor', ['conference_id' => $selectedConferenceId, 'certificate_type_id' => $selectedTypeId]));
    }

    if ($action === 'save_mappings') {
        $mappingsJson = (string) ($_POST['mappings_json'] ?? '[]');
        $mappings = json_decode($mappingsJson, true);

        if (!is_array($mappings)) {
            flash('error', 'Invalid mappings payload.');
            redirect(url('field-editor', ['conference_id' => $selectedConferenceId, 'certificate_type_id' => $selectedTypeId]));
        }

        db()->beginTransaction();

        try {
            $deleteStmt = db()->prepare('DELETE FROM field_mappings WHERE certificate_type_id = :certificate_type_id');
            $deleteStmt->execute(['certificate_type_id' => $selectedTypeId]);

            $insertStmt = db()->prepare(
                'INSERT INTO field_mappings
                  (certificate_type_id, field_key, label, options_json, x_pos, y_pos, font_size, align, color_hex, max_width, line_height, underline, sort_order, created_at)
                 VALUES
                  (:certificate_type_id, :field_key, :label, :options_json, :x_pos, :y_pos, :font_size, :align, :color_hex, :max_width, :line_height, :underline, :sort_order, NOW())'
            );

            $order = 0;
            foreach ($mappings as $row) {
                $fieldKey = trim((string) ($row['field_key'] ?? ''));
                if ($fieldKey === '') {
                    continue;
                }

                $insertStmt->execute([
                    'certificate_type_id' => $selectedTypeId,
                    'field_key' => $fieldKey,
                    'label' => trim((string) ($row['label'] ?? $fieldKey)),
                    'options_json' => isset($row['options_json']) && is_string($row['options_json'])
                        ? $row['options_json']
                        : json_encode($row['options_json'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'x_pos' => (int) ($row['x_pos'] ?? 100),
                    'y_pos' => (int) ($row['y_pos'] ?? 100),
                    'font_size' => max(8, (int) ($row['font_size'] ?? 32)),
                    'align' => in_array((string) ($row['align'] ?? 'left'), ['left', 'center'], true)
                        ? (string) $row['align']
                        : 'left',
                    'color_hex' => normalize_hex_color((string) ($row['color_hex'] ?? '#000000')),
                    'max_width' => max(120, (int) ($row['max_width'] ?? 900)),
                    'line_height' => max(12, (int) ($row['line_height'] ?? 42)),
                    'underline' => (int) (!empty($row['underline']) ? 1 : 0),
                    'sort_order' => $order++,
                ]);
            }

            // Mapping changes should invalidate previously generated artifacts for this certificate type.
            $staleDeleteStmt = db()->prepare(
                'DELETE gc
                 FROM generated_certificates gc
                 INNER JOIN participants p ON p.id = gc.participant_id
                 WHERE p.certificate_type_id = :certificate_type_id'
            );
            $staleDeleteStmt->execute([
                'certificate_type_id' => $selectedTypeId,
            ]);

            $pendingStmt = db()->prepare(
                'UPDATE participants
                 SET status = :status
                 WHERE conference_id = :conference_id
                   AND certificate_type_id = :certificate_type_id'
            );
            $pendingStmt->execute([
                'status' => 'pending',
                'conference_id' => $selectedConferenceId,
                'certificate_type_id' => $selectedTypeId,
            ]);

            db()->commit();
            flash('success', 'Field mappings saved. Existing generated files were marked stale; regenerate certificates to apply updates.');
        } catch (\Throwable $exception) {
            db()->rollBack();
            flash('error', 'Failed to save mappings: ' . $exception->getMessage());
        }

        redirect(url('field-editor', ['conference_id' => $selectedConferenceId, 'certificate_type_id' => $selectedTypeId]));
    }
}

$mappingsStmt = db()->prepare('SELECT * FROM field_mappings WHERE certificate_type_id = :certificate_type_id ORDER BY sort_order ASC, id ASC');
$mappingsStmt->execute(['certificate_type_id' => $selectedTypeId]);
$fieldMappings = $mappingsStmt->fetchAll();

$availableFonts = available_font_choices();

render_view('field-editor.php', [
    'pageTitle' => 'Certificate Editor',
    'conference' => $conference,
    'selectedConferenceId' => $selectedConferenceId,
    'certificateTypes' => $certificateTypes,
    'selectedTypeId' => $selectedTypeId,
    'selectedType' => $selectedType,
    'fieldMappings' => $fieldMappings,
    'availableFonts' => $availableFonts,
    'templatePath' => $templatePath,
    'templateExists' => $templateExists,
]);
