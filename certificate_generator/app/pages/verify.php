<?php
declare(strict_types=1);

$code = trim((string) ($_GET['code'] ?? ''));
$record = null;

if ($code !== '') {
    $stmt = db()->prepare(
        'SELECT p.name, p.institute, p.title, p.issued_date, p.verify_code,
                c.name AS conference_name, c.year, ct.name AS category_name,
                gc.jpg_path, gc.pdf_path, gc.generated_at
         FROM participants p
         INNER JOIN conferences c ON c.id = p.conference_id
         INNER JOIN certificate_types ct ON ct.id = p.certificate_type_id
         LEFT JOIN generated_certificates gc ON gc.participant_id = p.id
         WHERE p.verify_code = :verify_code
         LIMIT 1'
    );
    $stmt->execute(['verify_code' => $code]);
    $record = $stmt->fetch() ?: null;
}

$verifyUrl = BASE_URL . '/index.php?page=verify&code=' . urlencode($code);
$qrImageUrl = $code !== ''
    ? 'https://chart.googleapis.com/chart?cht=qr&chs=200x200&chl=' . urlencode($verifyUrl)
    : '';

render_view('verify.php', [
    'pageTitle' => 'Certificate Verification',
    'code' => $code,
    'record' => $record,
    'verifyUrl' => $verifyUrl,
    'qrImageUrl' => $qrImageUrl,
], false);
