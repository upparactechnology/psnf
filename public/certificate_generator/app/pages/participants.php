<?php
declare(strict_types=1);

use App\Services\CsvService;
use App\Services\CertificateGenerator;
use App\Services\EmailService;
use App\Services\PdfService;

$selectedConferenceId = 1;
$conferenceStmt = db()->prepare('SELECT * FROM conferences WHERE id = 1 LIMIT 1');
$conferenceStmt->execute();
$conference = $conferenceStmt->fetch();

$typeStmt = db()->prepare('SELECT * FROM certificate_types WHERE conference_id = :conference_id ORDER BY name ASC');
$typeStmt->execute(['conference_id' => $selectedConferenceId]);
$certificateTypes = $typeStmt->fetchAll();

$selectedTypeId = (int) ($_GET['certificate_type_id'] ?? ($_POST['certificate_type_id'] ?? 0));
$editParticipantId = (int) ($_GET['edit_id'] ?? 0);
$searchTerm = trim((string) ($_GET['search'] ?? ($_POST['search'] ?? '')));
$statusFilter = strtolower(trim((string) ($_GET['status'] ?? ($_POST['status'] ?? ''))));
$pageNumber = max(1, (int) ($_GET['page_no'] ?? ($_POST['page_no'] ?? 1)));
$pageSize = 25;

$allowedStatuses = ['', 'active', 'pending', 'generated', 'emailed', 'failed'];
if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = '';
}

$listParams = [
    'conference_id' => $selectedConferenceId,
    'certificate_type_id' => $selectedTypeId,
    'page_no' => $pageNumber,
];
if ($searchTerm !== '') {
    $listParams['search'] = $searchTerm;
}
if ($statusFilter !== '') {
    $listParams['status'] = $statusFilter;
}

if (is_post_request()) {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');
    $subjectTemplate = setting('email_subject_template', 'Your {{certificate_type}} Certificate - {{conference}} {{year}}')
        ?? 'Your {{certificate_type}} Certificate - {{conference}} {{year}}';
    $messageTemplate = setting('email_message_template', "Dear {{name}},\n\nPlease find attached your {{certificate_type}} certificate for {{conference}} {{year}}.\n\nRegards,\nConference Team")
        ?? "Dear {{name}},\n\nPlease find attached your {{certificate_type}} certificate for {{conference}} {{year}}.\n\nRegards,\nConference Team";
    $shouldSendAttachment = (string) (setting('email_send_attachment', '1') ?? '1') === '1';

    $resolveAttachmentAbsolutePath = static function (string $relativePath): string {
        $attachmentNormalized = str_replace('\\', '/', trim($relativePath));
        if ($attachmentNormalized === '') {
            return '';
        }

        $rootNormalized = str_replace('\\', '/', rtrim(APP_ROOT, '/'));

        if (preg_match('#^[A-Za-z]:/#', $attachmentNormalized) === 1 || str_starts_with($attachmentNormalized, '/')) {
            return $attachmentNormalized;
        }

        if (str_starts_with($attachmentNormalized, $rootNormalized . '/')) {
            return $attachmentNormalized;
        }

        return $rootNormalized . '/' . ltrim($attachmentNormalized, '/');
    };

    if ($action === 'upload_csv') {
        if (!isset($_FILES['csv_file']) || (int) $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            flash('error', 'CSV upload failed.');
            redirect(url('participants', $listParams));
        }

        $tmpPath = (string) $_FILES['csv_file']['tmp_name'];
        $originalName = safe_filename((string) $_FILES['csv_file']['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if ($extension !== 'csv') {
            flash('error', 'Only CSV files are allowed.');
            redirect(url('participants', $listParams));
        }

        $storedName = date('Ymd_His') . '_' . $originalName;
        $storedPath = CSV_UPLOAD_ROOT . '/' . $storedName;

        if (!move_uploaded_file($tmpPath, $storedPath)) {
            flash('error', 'Unable to save CSV file on server.');
            redirect(url('participants', $listParams));
        }

        $service = new CsvService(db());
        $defaultType = $selectedTypeId > 0 ? $selectedTypeId : null;

        try {
            $result = $service->importParticipants($storedPath, $selectedConferenceId, $defaultType);
            $message = 'CSV imported. Inserted: ' . $result['inserted'] . ', Failed: ' . $result['failed'] . '.';
            flash($result['failed'] > 0 ? 'warning' : 'success', $message);

            if (!empty($result['errors'])) {
                $previewErrors = array_slice($result['errors'], 0, 6);
                flash('info', 'CSV notes: ' . implode(' | ', $previewErrors));
            }
        } catch (\Throwable $exception) {
            flash('error', 'CSV import failed: ' . $exception->getMessage());
        }

        redirect(url('participants', $listParams));
    }

    if ($action === 'add_students') {
        $studentIds = $_POST['student_ids'] ?? [];
        $manualTypeId = (int) ($_POST['manual_certificate_type_id'] ?? 0);
        $issuedDate = trim((string) ($_POST['issued_date'] ?? ''));
        if ($issuedDate === '') {
            $issuedDate = date('Y-m-d');
        }

        if ($manualTypeId <= 0) {
            flash('error', 'Please select a Certificate Template.');
            redirect(url('participants', $listParams));
        }

        if (empty($studentIds) || !is_array($studentIds)) {
            flash('error', 'Please select at least one student.');
            redirect(url('participants', $listParams));
        }

        $inserted = 0;
        $skipped = 0;

        $studentQuery = db()->prepare('
            SELECT s.id, s.first_name, s.middle_name, s.last_name, s.class, s.section,
                   (SELECT g.email FROM guardians g INNER JOIN guardian_student gs ON g.id = gs.guardian_id WHERE gs.student_id = s.id LIMIT 1) AS guardian_email
            FROM students s
            WHERE s.id = :student_id
        ');

        $checkStmt = db()->prepare('
            SELECT id FROM participants
            WHERE student_id = :student_id AND certificate_type_id = :certificate_type_id
        ');

        $insertStmt = db()->prepare('
            INSERT INTO participants
            (conference_id, certificate_type_id, student_id, name, email, institute, title, issued_date, verify_code, status, created_at)
            VALUES
            (1, :certificate_type_id, :student_id, :name, :email, :institute, :title, :issued_date, :verify_code, \'pending\', NOW())
        ');

        foreach ($studentIds as $studentId) {
            $studentId = (int) $studentId;

            $checkStmt->execute([
                'student_id' => $studentId,
                'certificate_type_id' => $manualTypeId,
            ]);
            if ($checkStmt->fetch() !== false) {
                $skipped++;
                continue;
            }

            $studentQuery->execute(['student_id' => $studentId]);
            $student = $studentQuery->fetch();
            if ($student === false) {
                continue;
            }

            $fullName = trim($student['first_name'] . ' ' . $student['middle_name'] . ' ' . $student['last_name']);
            if ($fullName === '') {
                $fullName = trim($student['first_name'] . ' ' . $student['last_name']);
            }

            $classSection = trim(($student['class'] ?? '') . ' ' . ($student['section'] ?? ''));

            $insertStmt->execute([
                'certificate_type_id' => $manualTypeId,
                'student_id' => $studentId,
                'name' => $fullName,
                'email' => $student['guardian_email'] ?? '',
                'institute' => $classSection !== '' ? $classSection : 'Pearl Special Needs Foundation',
                'title' => '',
                'issued_date' => $issuedDate,
                'verify_code' => bin2hex(random_bytes(8)),
            ]);
            $inserted++;

            // Auto-generate individual certificate for this specific student immediately
            $participantId = (int) db()->lastInsertId();
            if ($participantId > 0) {
                try {
                    $pdfService = new PdfService();
                    $generator = new CertificateGenerator(db(), $pdfService);
                    $generator->generateForParticipant($participantId);
                } catch (\Throwable $e) {
                    // Continue with next student if one fails
                }
            }
        }

        flash('success', "Assigned and generated certificate(s) for $inserted student(s). Skipped $skipped (already assigned).");
        redirect(url('participants', $listParams));
    }

    if ($action === 'add_manual') {
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $institute = trim((string) ($_POST['institute'] ?? ''));
        $title = trim((string) ($_POST['title'] ?? ''));
        $issuedDate = trim((string) ($_POST['issued_date'] ?? ''));
        $groupNamesRaw = trim((string) ($_POST['group_names'] ?? ''));
        $manualTypeId = (int) ($_POST['manual_certificate_type_id'] ?? 0);

        // Allow pasting comma-separated names directly in the main name field.
        if ($name !== '' && strpbrk($name, ',;') !== false && $groupNamesRaw === '') {
            $parts = preg_split('/[,;]+/', $name) ?: [];
            $parts = array_values(array_filter(array_map(static fn ($value): string => trim((string) $value), $parts), static fn ($value): bool => $value !== ''));
            if ($parts !== []) {
                $name = $parts[0];
                if (count($parts) > 1) {
                    $groupNamesRaw = implode(', ', array_slice($parts, 1));
                }
            }
        }

        if ($name === '' || $manualTypeId <= 0) {
            flash('error', 'Name and certificate type are required for manual add.');
            redirect(url('participants', $listParams));
        }

        $groupNames = [];
        if ($groupNamesRaw !== '') {
            $chunks = preg_split('/,|;|\r\n|\n/', $groupNamesRaw) ?: [];
            $seen = [];
            foreach ($chunks as $chunk) {
                $candidate = trim((string) $chunk);
                if ($candidate === '') {
                    continue;
                }

                $normalized = strtolower($candidate);
                if ($normalized === strtolower($name) || isset($seen[$normalized])) {
                    continue;
                }

                $seen[$normalized] = true;
                $groupNames[] = $candidate;
            }
        }

        $extraPayload = [];
        if ($groupNames !== []) {
            $extraPayload['authors'] = $groupNames;
        }

        $insertStmt = db()->prepare(
            'INSERT INTO participants
             (conference_id, certificate_type_id, name, email, institute, title, issued_date, extra_json, verify_code, status, created_at)
             VALUES
             (:conference_id, :certificate_type_id, :name, :email, :institute, :title, :issued_date, :extra_json, :verify_code, :status, NOW())'
        );

        $insertStmt->execute([
            'conference_id' => $selectedConferenceId,
            'certificate_type_id' => $manualTypeId,
            'name' => $name,
            'email' => $email,
            'institute' => $institute,
            'title' => $title,
            'issued_date' => $issuedDate !== '' ? $issuedDate : date('Y-m-d'),
            'extra_json' => json_encode($extraPayload, JSON_UNESCAPED_UNICODE),
            'verify_code' => bin2hex(random_bytes(8)),
            'status' => 'pending',
        ]);

        flash('success', 'Participant added successfully.');
        redirect(url('participants', $listParams));
    }

    if ($action === 'update_participant') {
        $participantId = (int) ($_POST['participant_id'] ?? 0);
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $institute = trim((string) ($_POST['institute'] ?? ''));
        $title = trim((string) ($_POST['title'] ?? ''));
        $issuedDate = trim((string) ($_POST['issued_date'] ?? ''));
        $groupNamesRaw = trim((string) ($_POST['group_names'] ?? ''));
        $manualTypeId = (int) ($_POST['manual_certificate_type_id'] ?? 0);

        if ($participantId <= 0 || $manualTypeId <= 0 || $name === '') {
            flash('error', 'Participant, name and category are required for update.');
            $editRedirectParams = $listParams;
            $editRedirectParams['edit_id'] = $participantId;
            redirect(url('participants', $editRedirectParams));
        }

        // Allow pasting comma-separated names directly in the main name field.
        if ($name !== '' && strpbrk($name, ',;') !== false && $groupNamesRaw === '') {
            $parts = preg_split('/[,;]+/', $name) ?: [];
            $parts = array_values(array_filter(array_map(static fn ($value): string => trim((string) $value), $parts), static fn ($value): bool => $value !== ''));
            if ($parts !== []) {
                $name = $parts[0];
                if (count($parts) > 1) {
                    $groupNamesRaw = implode(', ', array_slice($parts, 1));
                }
            }
        }

        $existingStmt = db()->prepare('SELECT * FROM participants WHERE id = :id AND conference_id = :conference_id LIMIT 1');
        $existingStmt->execute([
            'id' => $participantId,
            'conference_id' => $selectedConferenceId,
        ]);
        $existing = $existingStmt->fetch();

        if ($existing === false) {
            flash('error', 'Participant not found for update.');
            redirect(url('participants', $listParams));
        }

        $groupNames = [];
        if ($groupNamesRaw !== '') {
            $chunks = preg_split('/,|;|\r\n|\n/', $groupNamesRaw) ?: [];
            $seen = [];
            foreach ($chunks as $chunk) {
                $candidate = trim((string) $chunk);
                if ($candidate === '') {
                    continue;
                }

                $normalized = strtolower($candidate);
                if ($normalized === strtolower($name) || isset($seen[$normalized])) {
                    continue;
                }

                $seen[$normalized] = true;
                $groupNames[] = $candidate;
            }
        }

        $extraPayload = [];
        if (!empty($existing['extra_json'])) {
            $decoded = json_decode((string) $existing['extra_json'], true);
            if (is_array($decoded)) {
                $extraPayload = $decoded;
            }
        }

        // Keep unrelated metadata but reset all group-name keys before saving updated list.
        $groupKeys = ['authors', 'participants', 'coauthors', 'co_authors', 'group_names', 'participant_names', 'names'];
        foreach ($groupKeys as $groupKey) {
            unset($extraPayload[$groupKey]);
        }

        foreach (array_keys($extraPayload) as $extraKey) {
            if (preg_match('/^(author|authors|participant|participants|coauthor|coauthors|co_author|co_authors)\d+$/', (string) $extraKey) === 1) {
                unset($extraPayload[$extraKey]);
            }
        }

        if ($groupNames !== []) {
            $extraPayload['authors'] = $groupNames;
        }

        $updateStmt = db()->prepare(
            'UPDATE participants
             SET certificate_type_id = :certificate_type_id,
                 name = :name,
                 email = :email,
                 institute = :institute,
                 title = :title,
                 issued_date = :issued_date,
                 extra_json = :extra_json,
                 status = :status
             WHERE id = :id AND conference_id = :conference_id'
        );

        $updateStmt->execute([
            'certificate_type_id' => $manualTypeId,
            'name' => $name,
            'email' => $email,
            'institute' => $institute,
            'title' => $title,
            'issued_date' => $issuedDate !== '' ? $issuedDate : date('Y-m-d'),
            'extra_json' => json_encode($extraPayload, JSON_UNESCAPED_UNICODE),
            'status' => 'pending',
            'id' => $participantId,
            'conference_id' => $selectedConferenceId,
        ]);

        // Existing files become stale after participant details change.
        $deleteGeneratedStmt = db()->prepare('DELETE FROM generated_certificates WHERE participant_id = :participant_id');
        $deleteGeneratedStmt->execute([
            'participant_id' => $participantId,
        ]);

        flash('success', 'Participant updated successfully. Regenerate certificate to apply changes.');
        redirect(url('participants', $listParams));
    }

    if ($action === 'delete_participant') {
        $participantId = (int) ($_POST['participant_id'] ?? 0);
        if ($participantId > 0) {
            $deleteStmt = db()->prepare('DELETE FROM participants WHERE id = :id AND conference_id = :conference_id');
            $deleteStmt->execute([
                'id' => $participantId,
                'conference_id' => $selectedConferenceId,
            ]);

            flash('success', 'Participant deleted.');
        }

        redirect(url('participants', $listParams));
    }

    if ($action === 'generate_single') {
        $participantId = (int) ($_POST['participant_id'] ?? 0);

        if ($participantId <= 0) {
            flash('error', 'Invalid recipient selected for certificate issue.');
            redirect(url('participants', $listParams));
        }

        $eligibleStmt = db()->prepare('SELECT id FROM participants WHERE id = :id AND conference_id = :conference_id LIMIT 1');
        $eligibleStmt->execute([
            'id' => $participantId,
            'conference_id' => $selectedConferenceId,
        ]);

        if ($eligibleStmt->fetch() === false) {
            flash('error', 'Recipient not found in selected conference.');
            redirect(url('participants', $listParams));
        }

        $generator = new CertificateGenerator(db(), new PdfService());
        try {
            $generator->generateForParticipant($participantId);
            flash('success', 'Certificate issued for recipient #' . $participantId . '.');
        } catch (\Throwable $exception) {
            flash('error', 'Issue failed: ' . $exception->getMessage());
        }

        redirect(url('participants', $listParams));
    }

    if ($action === 'send_single_email') {
        $participantId = (int) ($_POST['participant_id'] ?? 0);

        if ($participantId <= 0) {
            flash('error', 'Invalid recipient selected for email delivery.');
            redirect(url('participants', $listParams));
        }

        $targetStmt = db()->prepare(
            'SELECT p.id AS participant_id, p.name, p.email, p.title, p.status,
                    c.name AS conference_name, c.year,
                    ct.name AS certificate_type_name,
                    gc.jpg_path, gc.pdf_path
             FROM participants p
             INNER JOIN conferences c ON c.id = p.conference_id
             INNER JOIN certificate_types ct ON ct.id = p.certificate_type_id
             LEFT JOIN generated_certificates gc ON gc.participant_id = p.id
             WHERE p.id = :participant_id AND p.conference_id = :conference_id
             LIMIT 1'
        );
        $targetStmt->execute([
            'participant_id' => $participantId,
            'conference_id' => $selectedConferenceId,
        ]);
        $target = $targetStmt->fetch();

        if ($target === false) {
            flash('error', 'Recipient not found in selected conference.');
            redirect(url('participants', $listParams));
        }

        $email = trim((string) ($target['email'] ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Recipient does not have a valid email address.');
            redirect(url('participants', $listParams));
        }

        $attachmentAbsolute = null;
        if ($shouldSendAttachment) {
            $attachmentRelative = !empty($target['pdf_path']) ? (string) $target['pdf_path'] : (string) ($target['jpg_path'] ?? '');
            $attachmentAbsolute = $resolveAttachmentAbsolutePath($attachmentRelative);

            if ($attachmentAbsolute === '' || !is_file($attachmentAbsolute)) {
                try {
                    $generator = new CertificateGenerator(db(), new PdfService());
                    $generated = $generator->generateForParticipant($participantId);
                    $attachmentRelative = !empty($generated['pdf_path']) ? (string) $generated['pdf_path'] : (string) ($generated['jpg_path'] ?? '');
                    $attachmentAbsolute = $resolveAttachmentAbsolutePath($attachmentRelative);
                } catch (\Throwable $exception) {
                    flash('error', 'Email send blocked: certificate could not be issued automatically. ' . $exception->getMessage());
                    redirect(url('participants', $listParams));
                }
            }

            if ($attachmentAbsolute === '' || !is_file($attachmentAbsolute)) {
                flash('error', 'Email send blocked: certificate file not found for this recipient.');
                redirect(url('participants', $listParams));
            }
        }

        $replacements = [
            '{{name}}' => (string) ($target['name'] ?? ''),
            '{{certificate_type}}' => (string) ($target['certificate_type_name'] ?? ''),
            '{{conference}}' => (string) ($target['conference_name'] ?? ''),
            '{{year}}' => (string) ($target['year'] ?? ''),
            '{{title}}' => (string) ($target['title'] ?? ''),
        ];
        $renderedSubject = strtr($subjectTemplate, $replacements);
        $renderedMessage = strtr($messageTemplate, $replacements);

        $emailService = new EmailService(db());
        $result = $emailService->sendCertificate($target, $attachmentAbsolute, $renderedSubject, $renderedMessage);
        $deliveryStatus = $result['ok'] ? 'sent' : 'failed';

        $logStmt = db()->prepare(
            'INSERT INTO email_logs
             (participant_id, to_email, subject_text, message_text, status, error_message, sent_at)
             VALUES
             (:participant_id, :to_email, :subject_text, :message_text, :status, :error_message, NOW())'
        );
        $logStmt->execute([
            'participant_id' => $participantId,
            'to_email' => $email,
            'subject_text' => $renderedSubject,
            'message_text' => $renderedMessage,
            'status' => $deliveryStatus,
            'error_message' => $result['error'],
        ]);

        $statusStmt = db()->prepare('UPDATE participants SET status = :status WHERE id = :id AND conference_id = :conference_id');
        $statusStmt->execute([
            'status' => $result['ok'] ? 'emailed' : 'failed',
            'id' => $participantId,
            'conference_id' => $selectedConferenceId,
        ]);

        if ($result['ok']) {
            flash('success', 'Certificate email sent to ' . $email . '.');
        } else {
            flash('error', 'Email send failed for ' . $email . ': ' . ($result['error'] ?? 'Unknown error'));
        }

        redirect(url('participants', $listParams));
    }

    if ($action === 'generate_selected') {
        $selectedIds = $_POST['participant_ids'] ?? [];
        if (!is_array($selectedIds)) {
            $selectedIds = [];
        }

        $participantIds = array_values(array_unique(array_filter(array_map(static fn ($value): int => (int) $value, $selectedIds), static fn (int $value): bool => $value > 0)));
        if ($participantIds === []) {
            flash('warning', 'Select at least one recipient to generate certificates.');
            redirect(url('participants', $listParams));
        }

        $generator = new CertificateGenerator(db(), new PdfService());
        $eligibleStmt = db()->prepare('SELECT id FROM participants WHERE id = :id AND conference_id = :conference_id LIMIT 1');
        $generated = 0;
        $failed = 0;

        foreach ($participantIds as $participantId) {
            $eligibleStmt->execute([
                'id' => $participantId,
                'conference_id' => $selectedConferenceId,
            ]);

            if ($eligibleStmt->fetch() === false) {
                $failed++;
                continue;
            }

            try {
                $generator->generateForParticipant($participantId);
                $generated++;
            } catch (\Throwable $exception) {
                $failed++;
            }
        }

        flash('success', 'Bulk issue completed. Success: ' . $generated . ', Failed: ' . $failed . '.');
        redirect(url('participants', $listParams));
    }

    if ($action === 'delete_selected') {
        $selectedIds = $_POST['participant_ids'] ?? [];
        if (!is_array($selectedIds)) {
            $selectedIds = [];
        }

        $participantIds = array_values(array_unique(array_filter(array_map(static fn ($value): int => (int) $value, $selectedIds), static fn (int $value): bool => $value > 0)));
        if ($participantIds === []) {
            flash('warning', 'Select at least one recipient to remove.');
            redirect(url('participants', $listParams));
        }

        $deleteStmt = db()->prepare('DELETE FROM participants WHERE id = :id AND conference_id = :conference_id');
        $deleted = 0;
        foreach ($participantIds as $participantId) {
            $deleteStmt->execute([
                'id' => $participantId,
                'conference_id' => $selectedConferenceId,
            ]);
            $deleted += $deleteStmt->rowCount();
        }

        flash('success', 'Removed ' . $deleted . ' recipient(s).');
        redirect(url('participants', $listParams));
    }
}

$where = ['p.conference_id = :conference_id'];
$params = ['conference_id' => $selectedConferenceId];

if ($selectedTypeId > 0) {
    $where[] = 'p.certificate_type_id = :certificate_type_id';
    $params['certificate_type_id'] = $selectedTypeId;
}

if ($statusFilter === 'active') {
    $where[] = 'p.status IN (\'generated\', \'emailed\')';
} elseif ($statusFilter !== '') {
    $where[] = 'p.status = :status_filter';
    $params['status_filter'] = $statusFilter;
}

if ($searchTerm !== '') {
    $where[] = '(
        CONVERT(COALESCE(p.name, \'\') USING utf8mb4) COLLATE utf8mb4_general_ci LIKE :search_name
        OR CONVERT(COALESCE(p.email, \'\') USING utf8mb4) COLLATE utf8mb4_general_ci LIKE :search_email
        OR CONVERT(COALESCE(ct.name, \'\') USING utf8mb4) COLLATE utf8mb4_general_ci LIKE :search_category
        OR CONVERT(COALESCE(p.verify_code, \'\') USING utf8mb4) COLLATE utf8mb4_general_ci LIKE :search_verify
        OR CONVERT(COALESCE(CAST(p.extra_json AS CHAR), \'\') USING utf8mb4) COLLATE utf8mb4_general_ci LIKE :search_extra
    )';

    $searchLike = '%' . $searchTerm . '%';
    $params['search_name'] = $searchLike;
    $params['search_email'] = $searchLike;
    $params['search_category'] = $searchLike;
    $params['search_verify'] = $searchLike;
    $params['search_extra'] = $searchLike;
}

$whereSql = implode(' AND ', $where);

$countStmt = db()->prepare(
    'SELECT COUNT(*)
     FROM participants p
     INNER JOIN certificate_types ct ON ct.id = p.certificate_type_id
     WHERE ' . $whereSql
);
$countStmt->execute($params);
$totalRows = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalRows / $pageSize));

if ($pageNumber > $totalPages) {
    $pageNumber = $totalPages;
}

$offset = ($pageNumber - 1) * $pageSize;

$listQuery =
    'SELECT p.*, ct.name AS certificate_type_name,
            gc.jpg_path, gc.pdf_path, COALESCE(gc.download_count, 0) AS download_count,
            gc.generated_at
     FROM participants p
     INNER JOIN certificate_types ct ON ct.id = p.certificate_type_id
     LEFT JOIN generated_certificates gc ON gc.participant_id = p.id
     WHERE ' . $whereSql . '
     ORDER BY COALESCE(gc.generated_at, p.updated_at, p.created_at) DESC, p.id DESC
     LIMIT :limit OFFSET :offset';
$listStmt = db()->prepare($listQuery);
foreach ($params as $key => $value) {
    $listStmt->bindValue(':' . $key, $value);
}
$listStmt->bindValue(':limit', $pageSize, PDO::PARAM_INT);
$listStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$listStmt->execute();
$participants = $listStmt->fetchAll();

$summaryWhere = ['conference_id = :conference_id'];
$summaryParams = ['conference_id' => $selectedConferenceId];
if ($selectedTypeId > 0) {
    $summaryWhere[] = 'certificate_type_id = :certificate_type_id';
    $summaryParams['certificate_type_id'] = $selectedTypeId;
}

$summaryStmt = db()->prepare(
    'SELECT COUNT(*) AS total_count,
            SUM(CASE WHEN status IN (\'generated\', \'emailed\') THEN 1 ELSE 0 END) AS active_count,
            SUM(CASE WHEN status = \'pending\' THEN 1 ELSE 0 END) AS pending_count
     FROM participants
     WHERE ' . implode(' AND ', $summaryWhere)
);
$summaryStmt->execute($summaryParams);
$summary = $summaryStmt->fetch() ?: [
    'total_count' => 0,
    'active_count' => 0,
    'pending_count' => 0,
];

$rangeStart = $totalRows === 0 ? 0 : (($pageNumber - 1) * $pageSize) + 1;
$rangeEnd = $totalRows === 0 ? 0 : min($totalRows, $pageNumber * $pageSize);

$editingParticipant = null;

foreach ($participants as &$row) {
    $names = [];
    $primaryName = trim((string) ($row['name'] ?? ''));
    if ($primaryName !== '') {
        $names[] = $primaryName;
    }

    $extra = [];
    if (!empty($row['extra_json'])) {
        $decoded = json_decode((string) $row['extra_json'], true);
        if (is_array($decoded)) {
            $extra = $decoded;
        }
    }

    $authorCandidates = [];
    if (isset($extra['authors'])) {
        $authorCandidates = is_array($extra['authors'])
            ? $extra['authors']
            : (preg_split('/,|;|\r\n|\n/', (string) $extra['authors']) ?: []);
    }

    if (is_array($authorCandidates)) {
        $seen = [];
        foreach ($names as $value) {
            $seen[strtolower($value)] = true;
        }

        foreach ($authorCandidates as $candidate) {
            $value = trim((string) $candidate);
            if ($value === '') {
                continue;
            }

            $normalized = strtolower($value);
            if (isset($seen[$normalized])) {
                continue;
            }

            $seen[$normalized] = true;
            $names[] = $value;
        }
    }

    $row['group_names_display'] = implode(', ', $names);
    $row['group_names_input'] = count($names) > 1 ? implode(', ', array_slice($names, 1)) : '';
    $row['cert_count'] = (int) ($row['download_count'] ?? 0);

    $activityAt = (string) ($row['generated_at'] ?? $row['updated_at'] ?? $row['created_at'] ?? '');
    $activityTimestamp = strtotime($activityAt);
    $row['last_activity_at'] = $activityTimestamp !== false ? date('M d, Y', $activityTimestamp) : '-';

    $normalizedStatus = strtolower((string) ($row['status'] ?? 'pending'));
    if ($normalizedStatus === 'emailed') {
        $row['last_activity_note'] = 'Email delivered';
    } elseif ($normalizedStatus === 'generated') {
        $row['last_activity_note'] = 'Certificate issued';
    } elseif ($normalizedStatus === 'failed') {
        $row['last_activity_note'] = 'Delivery failed';
    } else {
        $row['last_activity_note'] = 'Pending processing';
    }

    if ($editParticipantId > 0 && (int) $row['id'] === $editParticipantId) {
        $editingParticipant = $row;
    }
}
unset($row);

if ((string) ($_GET['export'] ?? '') === 'csv') {
    $fileName = safe_filename((string) ($conference['name'] ?? 'conference') . '_recipients_page_' . $pageNumber . '_' . date('Ymd_His')) . '.csv';
    stream_csv_download($fileName, ['ID', 'Recipient', 'Groups / Tags', 'Email', 'Category', 'Status', 'Last Activity', 'Downloads'], $participants, static function (array $row): array {
        return [
            (string) ($row['id'] ?? ''),
            (string) ($row['name'] ?? ''),
            (string) ($row['group_names_display'] ?? ''),
            (string) ($row['email'] ?? ''),
            (string) ($row['certificate_type_name'] ?? ''),
            (string) ($row['status'] ?? ''),
            (string) ($row['last_activity_at'] ?? ''),
            (string) ($row['cert_count'] ?? '0'),
        ];
    });
}

$studentsStmt = db()->query('
    SELECT id, first_name, middle_name, last_name, class, section, admission_number
    FROM students
    WHERE deleted_at IS NULL AND is_active = 1
    ORDER BY class ASC, section ASC, first_name ASC, last_name ASC
');
$students = $studentsStmt->fetchAll();

render_view('participants.php', [
    'pageTitle' => 'Students',
    'students' => $students,
    'conference' => $conference,
    'selectedConferenceId' => $selectedConferenceId,
    'certificateTypes' => $certificateTypes,
    'selectedTypeId' => $selectedTypeId,
    'searchTerm' => $searchTerm,
    'statusFilter' => $statusFilter,
    'editParticipantId' => $editParticipantId,
    'editingParticipant' => $editingParticipant,
    'participants' => $participants,
    'pageNumber' => $pageNumber,
    'pageSize' => $pageSize,
    'totalRows' => $totalRows,
    'totalPages' => $totalPages,
    'rangeStart' => $rangeStart,
    'rangeEnd' => $rangeEnd,
    'activeRecipientCount' => (int) ($summary['active_count'] ?? 0),
    'pendingRecipientCount' => (int) ($summary['pending_count'] ?? 0),
    'allRecipientCount' => (int) ($summary['total_count'] ?? 0),
    'headerMeta' => [
        'actions' => [
            [
                'label' => 'Export CSV',
                'url' => url('participants', ['conference_id' => $selectedConferenceId, 'certificate_type_id' => $selectedTypeId, 'search' => $searchTerm, 'status' => $statusFilter, 'page_no' => $pageNumber, 'export' => 'csv']),
                'class' => 'button-muted',
            ],
        ],
    ],
]);
