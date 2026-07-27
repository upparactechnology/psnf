<?php
declare(strict_types=1);

$selectedConferenceId = 1;
$conferenceStmt = db()->prepare('SELECT * FROM conferences WHERE id = 1 LIMIT 1');
$conferenceStmt->execute();
$conference = $conferenceStmt->fetch();

$downloadTypeId = (int) ($_GET['download_type_id'] ?? 0);
if ($downloadTypeId > 0) {
    $downloadTypeStmt = db()->prepare(
        'SELECT id, name, slug, template_path
         FROM certificate_types
         WHERE id = :id AND conference_id = :conference_id
         LIMIT 1'
    );
    $downloadTypeStmt->execute([
        'id' => $downloadTypeId,
        'conference_id' => $selectedConferenceId,
    ]);
    $downloadType = $downloadTypeStmt->fetch();

    if ($downloadType === false) {
        flash('error', 'Template not found for download.');
        redirect(url('certificate-types', ['conference_id' => $selectedConferenceId]));
    }

    $relativePath = (string) ($downloadType['template_path'] ?? '');
    $absolutePath = APP_ROOT . '/' . ltrim(str_replace('\\', '/', $relativePath), '/');

    if ($relativePath === '' || !is_file($absolutePath)) {
        flash('error', 'Template image is not available for download.');
        redirect(url('certificate-types', ['conference_id' => $selectedConferenceId]));
    }

    $downloadName = safe_filename(
        (string) ($conference['name'] ?? 'conference') . '_' .
        (string) ($conference['year'] ?? date('Y')) . '_' .
        (string) ($downloadType['slug'] ?? 'template')
    ) . '.jpg';

    header('Content-Type: image/jpeg');
    header('Content-Disposition: attachment; filename="' . $downloadName . '"');
    header('Content-Length: ' . filesize($absolutePath));
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
    readfile($absolutePath);
    exit;
}

if (is_post_request()) {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'download_all_templates') {
        if (!class_exists('ZipArchive')) {
            flash('error', 'ZIP download is not supported on this server.');
            redirect(url('certificate-types', ['conference_id' => $selectedConferenceId]));
        }

        $downloadAllStmt = db()->prepare(
            'SELECT id, name, slug, template_path
             FROM certificate_types
             WHERE conference_id = :conference_id
             ORDER BY name ASC'
        );
        $downloadAllStmt->execute(['conference_id' => $selectedConferenceId]);
        $downloadRows = $downloadAllStmt->fetchAll();

        $tmpDir = UPLOADS_ROOT . '/tmp';
        if (!is_dir($tmpDir) && !mkdir($tmpDir, 0777, true) && !is_dir($tmpDir)) {
            flash('error', 'Unable to create temporary ZIP folder.');
            redirect(url('certificate-types', ['conference_id' => $selectedConferenceId]));
        }

        $zipName = safe_filename((string) ($conference['name'] ?? 'conference') . '_' . (string) ($conference['year'] ?? date('Y')) . '_templates')
            . '_' . date('Ymd_His') . '.zip';
        $zipAbsolutePath = $tmpDir . '/' . $zipName;

        $zip = new ZipArchive();
        if ($zip->open($zipAbsolutePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            flash('error', 'Unable to create template ZIP archive.');
            redirect(url('certificate-types', ['conference_id' => $selectedConferenceId]));
        }

        $added = 0;
        foreach ($downloadRows as $downloadRow) {
            $relativePath = (string) ($downloadRow['template_path'] ?? '');
            $absolutePath = APP_ROOT . '/' . ltrim(str_replace('\\', '/', $relativePath), '/');
            if ($relativePath === '' || !is_file($absolutePath)) {
                continue;
            }

            $entryName = safe_filename((string) ($downloadRow['slug'] ?? $downloadRow['name'] ?? ('template_' . $downloadRow['id']))) . '.jpg';
            if ($zip->addFile($absolutePath, $entryName)) {
                $added++;
            }
        }

        $zip->close();

        if ($added === 0) {
            if (is_file($zipAbsolutePath)) {
                unlink($zipAbsolutePath);
            }
            flash('error', 'No template images available to include in ZIP.');
            redirect(url('certificate-types', ['conference_id' => $selectedConferenceId]));
        }

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($zipAbsolutePath) . '"');
        header('Content-Length: ' . filesize($zipAbsolutePath));
        readfile($zipAbsolutePath);
        unlink($zipAbsolutePath);
        exit;
    }

    if ($action === 'create_type') {
        $name = trim((string) ($_POST['name'] ?? ''));
        if ($name === '') {
            flash('error', 'Type name is required.');
            redirect(url('certificate-types', ['conference_id' => $selectedConferenceId]));
        }

        $slug = slugify($name);
        $existsStmt = db()->prepare('SELECT COUNT(*) FROM certificate_types WHERE conference_id = :conference_id AND slug = :slug');
        $existsStmt->execute([
            'conference_id' => $selectedConferenceId,
            'slug' => $slug,
        ]);
        if ((int) $existsStmt->fetchColumn() > 0) {
            flash('error', 'Certificate type with same slug already exists.');
            redirect(url('certificate-types', ['conference_id' => $selectedConferenceId]));
        }

        $insertStmt = db()->prepare(
            'INSERT INTO certificate_types (conference_id, name, slug, is_custom, is_active, created_at)
             VALUES (:conference_id, :name, :slug, :is_custom, :is_active, NOW())'
        );
        $insertStmt->execute([
            'conference_id' => $selectedConferenceId,
            'name' => $name,
            'slug' => $slug,
            'is_custom' => 1,
            'is_active' => 1,
        ]);

        flash('success', 'Certificate type created.');
        redirect(url('certificate-types', ['conference_id' => $selectedConferenceId]));
    }

    if ($action === 'upload_template') {
        $certificateTypeId = (int) ($_POST['certificate_type_id'] ?? 0);

        $typeStmt = db()->prepare('SELECT * FROM certificate_types WHERE id = :id AND conference_id = :conference_id LIMIT 1');
        $typeStmt->execute([
            'id' => $certificateTypeId,
            'conference_id' => $selectedConferenceId,
        ]);
        $type = $typeStmt->fetch();

        if ($type === false) {
            flash('error', 'Certificate type not found.');
            redirect(url('certificate-types', ['conference_id' => $selectedConferenceId]));
        }

        if (!isset($_FILES['template']) || (int) $_FILES['template']['error'] !== UPLOAD_ERR_OK) {
            flash('error', 'Template upload failed.');
            redirect(url('certificate-types', ['conference_id' => $selectedConferenceId]));
        }

        $file = $_FILES['template'];
        $tmpPath = (string) $file['tmp_name'];
        $size = (int) $file['size'];

        if ($size > MAX_UPLOAD_SIZE) {
            flash('error', 'Template file exceeds max size limit (10MB).');
            redirect(url('certificate-types', ['conference_id' => $selectedConferenceId]));
        }

        $mime = mime_content_type($tmpPath) ?: '';
        if (!in_array($mime, ALLOWED_IMAGE_TYPES, true)) {
            flash('error', 'Only JPG templates are allowed.');
            redirect(url('certificate-types', ['conference_id' => $selectedConferenceId]));
        }

        $year = (string) $conference['year'];
        $slug = (string) $type['slug'];

        $outputDir = TEMPLATES_ROOT . '/' . $year . '/' . $slug;
        if (!is_dir($outputDir) && !mkdir($outputDir, 0777, true) && !is_dir($outputDir)) {
            flash('error', 'Unable to create template folder.');
            redirect(url('certificate-types', ['conference_id' => $selectedConferenceId]));
        }

        $targetAbsolutePath = $outputDir . '/template.jpg';
        if (!move_uploaded_file($tmpPath, $targetAbsolutePath)) {
            flash('error', 'Unable to save template file.');
            redirect(url('certificate-types', ['conference_id' => $selectedConferenceId]));
        }

        $relativePath = 'templates/' . $year . '/' . $slug . '/template.jpg';
        $updateStmt = db()->prepare('UPDATE certificate_types SET template_path = :template_path WHERE id = :id');
        $updateStmt->execute([
            'template_path' => $relativePath,
            'id' => $certificateTypeId,
        ]);

        flash('success', 'Template uploaded successfully.');
        redirect(url('certificate-types', ['conference_id' => $selectedConferenceId]));
    }

    if ($action === 'delete_type') {
        $certificateTypeId = (int) ($_POST['certificate_type_id'] ?? 0);

        $deleteStmt = db()->prepare('DELETE FROM certificate_types WHERE id = :id AND conference_id = :conference_id');
        $deleteStmt->execute([
            'id' => $certificateTypeId,
            'conference_id' => $selectedConferenceId,
        ]);

        flash('success', 'Certificate type deleted.');
        redirect(url('certificate-types', ['conference_id' => $selectedConferenceId]));
    }

    if ($action === 'toggle_type_active') {
        $certificateTypeId = (int) ($_POST['certificate_type_id'] ?? 0);
        if ($certificateTypeId <= 0) {
            flash('error', 'Template not found.');
            redirect(url('certificate-types', ['conference_id' => $selectedConferenceId]));
        }

        $toggleStmt = db()->prepare(
            'UPDATE certificate_types
             SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END,
                 updated_at = NOW()
             WHERE id = :id AND conference_id = :conference_id
             LIMIT 1'
        );
        $toggleStmt->execute([
            'id' => $certificateTypeId,
            'conference_id' => $selectedConferenceId,
        ]);

        flash('success', 'Template status updated.');
        redirect(url('certificate-types', ['conference_id' => $selectedConferenceId]));
    }

    if ($action === 'duplicate_type') {
        $certificateTypeId = (int) ($_POST['certificate_type_id'] ?? 0);

        $sourceStmt = db()->prepare(
            'SELECT *
             FROM certificate_types
             WHERE id = :id AND conference_id = :conference_id
             LIMIT 1'
        );
        $sourceStmt->execute([
            'id' => $certificateTypeId,
            'conference_id' => $selectedConferenceId,
        ]);
        $sourceType = $sourceStmt->fetch();

        if ($sourceType === false) {
            flash('error', 'Template to duplicate was not found.');
            redirect(url('certificate-types', ['conference_id' => $selectedConferenceId]));
        }

        $newName = trim((string) ($sourceType['name'] ?? 'Template')) . ' Copy';
        $slugSeed = slugify((string) ($sourceType['slug'] ?? '') . '-copy');
        $slug = $slugSeed;
        $counter = 2;

        $slugExistsStmt = db()->prepare(
            'SELECT COUNT(*)
             FROM certificate_types
             WHERE conference_id = :conference_id
               AND slug = :slug'
        );

        while (true) {
            $slugExistsStmt->execute([
                'conference_id' => $selectedConferenceId,
                'slug' => $slug,
            ]);

            if ((int) $slugExistsStmt->fetchColumn() === 0) {
                break;
            }

            $slug = $slugSeed . '-' . $counter;
            $counter++;
        }

        $insertStmt = db()->prepare(
            'INSERT INTO certificate_types (conference_id, name, slug, is_custom, is_active, created_at)
             VALUES (:conference_id, :name, :slug, :is_custom, :is_active, NOW())'
        );
        $insertStmt->execute([
            'conference_id' => $selectedConferenceId,
            'name' => $newName,
            'slug' => $slug,
            'is_custom' => 1,
            'is_active' => (int) ($sourceType['is_active'] ?? 1) === 1 ? 1 : 0,
        ]);

        $newTypeId = (int) db()->lastInsertId();
        $sourceTemplatePath = (string) ($sourceType['template_path'] ?? '');

        if ($sourceTemplatePath !== '' && is_file(APP_ROOT . '/' . $sourceTemplatePath)) {
            $year = (string) ($conference['year'] ?? date('Y'));
            $outputDir = TEMPLATES_ROOT . '/' . $year . '/' . $slug;

            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0777, true);
            }

            $targetAbsolutePath = $outputDir . '/template.jpg';
            if (@copy(APP_ROOT . '/' . $sourceTemplatePath, $targetAbsolutePath)) {
                $newTemplatePath = 'templates/' . $year . '/' . $slug . '/template.jpg';

                $updateStmt = db()->prepare('UPDATE certificate_types SET template_path = :template_path WHERE id = :id');
                $updateStmt->execute([
                    'template_path' => $newTemplatePath,
                    'id' => $newTypeId,
                ]);
            }
        }

        flash('success', 'Template duplicated successfully.');
        redirect(url('certificate-types', ['conference_id' => $selectedConferenceId]));
    }
}

$typesStmt = db()->prepare(
    'SELECT ct.*, (SELECT COUNT(*) FROM participants p WHERE p.certificate_type_id = ct.id) AS participant_count
     FROM certificate_types ct
     WHERE ct.conference_id = :conference_id
     ORDER BY ct.name ASC'
);
$typesStmt->execute(['conference_id' => $selectedConferenceId]);
$certificateTypes = $typesStmt->fetchAll();

$activeTemplateCount = 0;
foreach ($certificateTypes as $certificateType) {
    if ((int) ($certificateType['is_active'] ?? 1) === 1) {
        $activeTemplateCount++;
    }
}

if ((string) ($_GET['export'] ?? '') === 'csv') {
    $fileName = safe_filename((string) ($conference['name'] ?? 'conference') . '_certificate_types_' . date('Ymd_His')) . '.csv';
    stream_csv_download($fileName, ['ID', 'Name', 'Slug', 'Active', 'Custom', 'Template Path', 'Created At'], $certificateTypes, static function (array $row): array {
        return [
            (string) ($row['id'] ?? ''),
            (string) ($row['name'] ?? ''),
            (string) ($row['slug'] ?? ''),
            (string) ((int) ($row['is_active'] ?? 0) === 1 ? 'Active' : 'Inactive'),
            (string) ((int) ($row['is_custom'] ?? 0) === 1 ? 'Yes' : 'No'),
            (string) ($row['template_path'] ?? ''),
            (string) ($row['created_at'] ?? ''),
        ];
    });
}

$issuancesStmt = db()->prepare(
    'SELECT COUNT(*)
     FROM generated_certificates gc
     INNER JOIN participants p ON p.id = gc.participant_id
     WHERE p.conference_id = :conference_id'
);
$issuancesStmt->execute(['conference_id' => $selectedConferenceId]);
$totalIssuances = (int) $issuancesStmt->fetchColumn();

render_view('certificate-types.php', [
    'pageTitle' => 'Templates',
    'conference' => $conference,
    'selectedConferenceId' => $selectedConferenceId,
    'certificateTypes' => $certificateTypes,
    'activeTemplateCount' => $activeTemplateCount,
    'totalIssuances' => $totalIssuances,
    'headerMeta' => [
        'actions' => [
            [
                'label' => 'Export CSV',
                'url' => url('certificate-types', ['conference_id' => $selectedConferenceId, 'export' => 'csv']),
                'class' => 'button-muted',
            ],
        ],
    ],
]);
