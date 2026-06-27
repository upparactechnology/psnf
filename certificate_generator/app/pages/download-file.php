<?php
declare(strict_types=1);

$participantId = (int) ($_GET['participant_id'] ?? 0);
$format = (string) ($_GET['format'] ?? 'pdf');
$format = in_array($format, ['pdf', 'jpg'], true) ? $format : 'pdf';

if ($participantId <= 0) {
    http_response_code(400);
    echo 'Invalid participant selected.';
    exit;
}

$stmt = db()->prepare(
    'SELECT p.id, p.name, gc.jpg_path, gc.pdf_path
     FROM participants p
     LEFT JOIN generated_certificates gc ON gc.participant_id = p.id
     WHERE p.id = :participant_id
     LIMIT 1'
);
$stmt->execute(['participant_id' => $participantId]);
$row = $stmt->fetch();

if ($row === false) {
    http_response_code(404);
    echo 'Certificate record not found.';
    exit;
}

$relative = $format === 'pdf' ? (string) ($row['pdf_path'] ?? '') : (string) ($row['jpg_path'] ?? '');
if ($relative === '') {
    http_response_code(404);
    echo strtoupper($format) . ' file not generated yet.';
    exit;
}

$absolute = APP_ROOT . '/' . ltrim(str_replace('\\', '/', $relative), '/');
if (!is_file($absolute)) {
    http_response_code(404);
    echo 'File not found on disk.';
    exit;
}

try {
    $incrementStmt = db()->prepare(
        'UPDATE generated_certificates
         SET download_count = COALESCE(download_count, 0) + 1
         WHERE participant_id = :participant_id'
    );
    $incrementStmt->execute(['participant_id' => $participantId]);
} catch (Throwable $e) {
    if (APP_ENV === 'development') {
        error_log('Failed to update download_count: ' . $e->getMessage());
    }
}

$contentType = $format === 'pdf' ? 'application/pdf' : 'image/jpeg';
$fileName = safe_filename((string) $row['name']) . '_' . (int) $row['id'] . '.' . $format;

header('Content-Type: ' . $contentType);
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . filesize($absolute));
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
readfile($absolute);
exit;
