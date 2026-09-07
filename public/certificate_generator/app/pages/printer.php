<?php
declare(strict_types=1);

use App\Services\EmailService;

$selectedConferenceId = (int) ($_GET['conference_id'] ?? ($_POST['conference_id'] ?? 0));
$conferenceStmt = db()->prepare('SELECT * FROM conferences ORDER BY year DESC, name ASC');
$conferenceStmt->execute();
$conferences = $conferenceStmt->fetchAll();

if ($selectedConferenceId <= 0 && $conferences !== []) {
    $selectedConferenceId = (int) ($conferences[0]['id'] ?? 0);
}

$conference = null;
foreach ($conferences as $conf) {
    if ((int) ($conf['id'] ?? 0) === $selectedConferenceId) {
        $conference = $conf;
        break;
    }
}

$typeStmt = db()->prepare('SELECT * FROM certificate_types WHERE conference_id = :conference_id ORDER BY name ASC');
$typeStmt->execute(['conference_id' => $selectedConferenceId]);
$certificateTypes = $typeStmt->fetchAll();

$selectedTypeId = (int) ($_GET['certificate_type_id'] ?? ($_POST['certificate_type_id'] ?? 0));
$searchTerm = trim((string) ($_GET['search'] ?? ($_POST['search'] ?? '')));
$pageNumber = max(1, (int) ($_GET['page_no'] ?? ($_POST['page_no'] ?? 1)));
$pageSize = 25;

$printingEmail = trim((string) (setting('printing_email', '') ?? ''));

if (is_post_request()) {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'send_to_printer_single') {
        $participantId = (int) ($_POST['participant_id'] ?? 0);

        if ($printingEmail === '' || !filter_var($printingEmail, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Set a valid Printing Service Email in System Settings first.');
            redirect(url('printer', ['conference_id' => $selectedConferenceId, 'certificate_type_id' => $selectedTypeId]));
        }

        if ($participantId <= 0) {
            flash('error', 'Invalid participant.');
            redirect(url('printer', ['conference_id' => $selectedConferenceId, 'certificate_type_id' => $selectedTypeId]));
        }

        $targetStmt = db()->prepare(
            'SELECT p.id AS participant_id, p.name, p.email, p.title,
                    c.name AS conference_name, c.year,
                    ct.name AS certificate_type_name, gc.pdf_path, gc.jpg_path
             FROM participants p
             INNER JOIN conferences c ON c.id = p.conference_id
             INNER JOIN certificate_types ct ON ct.id = p.certificate_type_id
             LEFT JOIN generated_certificates gc ON gc.participant_id = p.id
             WHERE p.id = :participant_id AND p.conference_id = :conference_id
             LIMIT 1'
        );
        $targetStmt->execute(['participant_id' => $participantId, 'conference_id' => $selectedConferenceId]);
        $target = $targetStmt->fetch();

        if ($target === false || empty($target['pdf_path']) && empty($target['jpg_path'])) {
            flash('error', 'Certificate not found or not yet generated.');
            redirect(url('printer', ['conference_id' => $selectedConferenceId, 'certificate_type_id' => $selectedTypeId]));
        }

        $rootNormalized = str_replace('\\', '/', rtrim(APP_ROOT, '/'));
        $attachmentRelative = !empty($target['pdf_path']) ? (string) $target['pdf_path'] : (string) ($target['jpg_path'] ?? '');
        $attachmentNormalized = str_replace('\\', '/', trim($attachmentRelative));

        if ($attachmentNormalized === '') {
            $attachmentAbsolute = '';
        } elseif (preg_match('#^[A-Za-z]:/#', $attachmentNormalized) === 1 || str_starts_with($attachmentNormalized, '/')) {
            $attachmentAbsolute = $attachmentNormalized;
        } elseif (str_starts_with($attachmentNormalized, $rootNormalized . '/')) {
            $attachmentAbsolute = $attachmentNormalized;
        } else {
            $attachmentAbsolute = $rootNormalized . '/' . ltrim($attachmentNormalized, '/');
        }

        if ($attachmentAbsolute === '' || !is_file($attachmentAbsolute)) {
            flash('error', 'Certificate file not found on disk.');
            redirect(url('printer', ['conference_id' => $selectedConferenceId, 'certificate_type_id' => $selectedTypeId]));
        }

        $printSubject = 'Printing: ' . (string) ($target['name'] ?? '') . ' - ' . (string) ($target['certificate_type_name'] ?? '') . ' - ' . (string) ($target['conference_name'] ?? '') . ' ' . (string) ($target['year'] ?? '');
        $printMessage = 'Certificate for printing.\n\nParticipant: ' . (string) ($target['name'] ?? '') . '\nCategory: ' . (string) ($target['certificate_type_name'] ?? '') . '\nEvent: ' . (string) ($target['conference_name'] ?? '') . ' ' . (string) ($target['year'] ?? '');

        $emailService = new EmailService(db());
        $participant = ['email' => $printingEmail, 'name' => 'Printing Service', 'certificate_type_name' => (string) ($target['certificate_type_name'] ?? ''), 'conference_name' => (string) ($target['conference_name'] ?? ''), 'year' => (string) ($target['year'] ?? '')];
        $result = $emailService->sendCertificate($participant, $attachmentAbsolute, $printSubject, $printMessage);

        $logStmt = db()->prepare(
            'INSERT INTO email_logs (participant_id, to_email, subject_text, message_text, status, error_message, sent_at) VALUES (:participant_id, :to_email, :subject_text, :message_text, :status, :error_message, NOW())'
        );
        $logStmt->execute([
            'participant_id' => $participantId,
            'to_email' => $printingEmail,
            'subject_text' => $printSubject,
            'message_text' => $printMessage,
            'status' => $result['ok'] ? 'sent' : 'failed',
            'error_message' => $result['ok'] ? null : ($result['error'] ?? 'Unknown error'),
        ]);

        if ($result['ok']) {
            db()->prepare('UPDATE participants SET status = :status WHERE id = :id')->execute(['status' => 'printed', 'id' => $participantId]);
            flash('success', 'Sent to printer: ' . e((string) ($target['name'] ?? '')));
        } else {
            flash('error', 'Failed to send: ' . ($result['error'] ?? 'Unknown error'));
        }

        redirect(url('printer', ['conference_id' => $selectedConferenceId, 'certificate_type_id' => $selectedTypeId, 'search' => $searchTerm]));
    }

    if ($action === 'send_to_parent_single') {
        $participantId = (int) ($_POST['participant_id'] ?? 0);

        if ($participantId <= 0) {
            flash('error', 'Invalid participant.');
            redirect(url('printer', ['conference_id' => $selectedConferenceId, 'certificate_type_id' => $selectedTypeId]));
        }

        $targetStmt = db()->prepare(
            'SELECT p.id AS participant_id, p.name, p.email, p.title,
                    c.name AS conference_name, c.year,
                    ct.name AS certificate_type_name, gc.pdf_path, gc.jpg_path
             FROM participants p
             INNER JOIN conferences c ON c.id = p.conference_id
             INNER JOIN certificate_types ct ON ct.id = p.certificate_type_id
             LEFT JOIN generated_certificates gc ON gc.participant_id = p.id
             WHERE p.id = :participant_id AND p.conference_id = :conference_id
             LIMIT 1'
        );
        $targetStmt->execute(['participant_id' => $participantId, 'conference_id' => $selectedConferenceId]);
        $target = $targetStmt->fetch();

        if ($target === false) {
            flash('error', 'Participant not found.');
            redirect(url('printer', ['conference_id' => $selectedConferenceId, 'certificate_type_id' => $selectedTypeId]));
        }

        $email = trim((string) ($target['email'] ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Participant does not have a valid email address.');
            redirect(url('printer', ['conference_id' => $selectedConferenceId, 'certificate_type_id' => $selectedTypeId]));
        }

        $rootNormalized = str_replace('\\', '/', rtrim(APP_ROOT, '/'));
        $attachmentRelative = !empty($target['pdf_path']) ? (string) $target['pdf_path'] : (string) ($target['jpg_path'] ?? '');
        $attachmentNormalized = str_replace('\\', '/', trim($attachmentRelative));

        if ($attachmentNormalized === '') {
            $attachmentAbsolute = '';
        } elseif (preg_match('#^[A-Za-z]:/#', $attachmentNormalized) === 1 || str_starts_with($attachmentNormalized, '/')) {
            $attachmentAbsolute = $attachmentNormalized;
        } elseif (str_starts_with($attachmentNormalized, $rootNormalized . '/')) {
            $attachmentAbsolute = $attachmentNormalized;
        } else {
            $attachmentAbsolute = $rootNormalized . '/' . ltrim($attachmentNormalized, '/');
        }

        if ($attachmentAbsolute === '' || !is_file($attachmentAbsolute)) {
            flash('error', 'Certificate file not found on disk.');
            redirect(url('printer', ['conference_id' => $selectedConferenceId, 'certificate_type_id' => $selectedTypeId]));
        }

        $subjectTemplate = setting('email_subject_template', 'Your {{certificate_type}} Certificate - {{conference}} {{year}}') ?? 'Your {{certificate_type}} Certificate - {{conference}} {{year}}';
        $messageTemplate = setting('email_message_template', "Dear {{name}},\n\nPlease find attached your {{certificate_type}} certificate for {{conference}} {{year}}.\n\nRegards,\nConference Team") ?? "Dear {{name}},\n\nPlease find attached your {{certificate_type}} certificate for {{conference}} {{year}}.\n\nRegards,\nConference Team";

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

        $logStmt = db()->prepare(
            'INSERT INTO email_logs (participant_id, to_email, subject_text, message_text, status, error_message, sent_at) VALUES (:participant_id, :to_email, :subject_text, :message_text, :status, :error_message, NOW())'
        );
        $logStmt->execute([
            'participant_id' => $participantId,
            'to_email' => $email,
            'subject_text' => $renderedSubject,
            'message_text' => $renderedMessage,
            'status' => $result['ok'] ? 'sent' : 'failed',
            'error_message' => $result['ok'] ? null : ($result['error'] ?? 'Unknown error'),
        ]);

        if ($result['ok']) {
            db()->prepare('UPDATE participants SET status = :status WHERE id = :id')->execute(['status' => 'emailed', 'id' => $participantId]);
            flash('success', 'Sent to ' . e($email) . ': ' . e((string) ($target['name'] ?? '')));
        } else {
            flash('error', 'Failed to send: ' . ($result['error'] ?? 'Unknown error'));
        }

        redirect(url('printer', ['conference_id' => $selectedConferenceId, 'certificate_type_id' => $selectedTypeId, 'search' => $searchTerm]));
    }

    if ($action === 'send_to_printer_bulk') {
        if ($printingEmail === '' || !filter_var($printingEmail, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Set a valid Printing Service Email in System Settings first.');
            redirect(url('printer', ['conference_id' => $selectedConferenceId, 'certificate_type_id' => $selectedTypeId]));
        }

        $bulkQuery =
            'SELECT p.id AS participant_id, p.name, p.email, p.title,
                    c.name AS conference_name, c.year,
                    ct.name AS certificate_type_name, gc.pdf_path, gc.jpg_path
             FROM participants p
             INNER JOIN conferences c ON c.id = p.conference_id
             INNER JOIN certificate_types ct ON ct.id = p.certificate_type_id
             INNER JOIN generated_certificates gc ON gc.participant_id = p.id
             WHERE p.conference_id = :conference_id
               AND p.email IS NOT NULL AND p.email <> \'\'';

        $bulkParams = ['conference_id' => $selectedConferenceId];
        if ($selectedTypeId > 0) {
            $bulkQuery .= ' AND p.certificate_type_id = :certificate_type_id';
            $bulkParams['certificate_type_id'] = $selectedTypeId;
        }
        $bulkQuery .= ' ORDER BY p.id ASC';

        $bulkStmt = db()->prepare($bulkQuery);
        $bulkStmt->execute($bulkParams);
        $bulkTargets = $bulkStmt->fetchAll();

        if ($bulkTargets === []) {
            flash('warning', 'No generated certificates found to send for printing.');
            redirect(url('printer', ['conference_id' => $selectedConferenceId, 'certificate_type_id' => $selectedTypeId]));
        }

        $emailService = new EmailService(db());
        $sent = 0;
        $failed = 0;
        $rootNormalized = str_replace('\\', '/', rtrim(APP_ROOT, '/'));

        $logStmt = db()->prepare(
            'INSERT INTO email_logs (participant_id, to_email, subject_text, message_text, status, error_message, sent_at) VALUES (:participant_id, :to_email, :subject_text, :message_text, :status, :error_message, NOW())'
        );

        foreach ($bulkTargets as $target) {
            $attachmentRelative = !empty($target['pdf_path']) ? (string) $target['pdf_path'] : (string) ($target['jpg_path'] ?? '');
            $attachmentNormalized = str_replace('\\', '/', trim($attachmentRelative));

            if ($attachmentNormalized === '') {
                $attachmentAbsolute = '';
            } elseif (preg_match('#^[A-Za-z]:/#', $attachmentNormalized) === 1 || str_starts_with($attachmentNormalized, '/')) {
                $attachmentAbsolute = $attachmentNormalized;
            } elseif (str_starts_with($attachmentNormalized, $rootNormalized . '/')) {
                $attachmentAbsolute = $attachmentNormalized;
            } else {
                $attachmentAbsolute = $rootNormalized . '/' . ltrim($attachmentNormalized, '/');
            }

            if ($attachmentAbsolute === '' || !is_file($attachmentAbsolute)) {
                $failed++;
                $logStmt->execute([
                    'participant_id' => (int) $target['participant_id'],
                    'to_email' => $printingEmail,
                    'subject_text' => 'Printing: ' . (string) ($target['name'] ?? ''),
                    'message_text' => 'Certificate not found.',
                    'status' => 'failed',
                    'error_message' => 'Certificate file not found.',
                ]);
                continue;
            }

            $printSubject = 'Printing: ' . (string) ($target['name'] ?? '') . ' - ' . (string) ($target['certificate_type_name'] ?? '') . ' - ' . (string) ($target['conference_name'] ?? '') . ' ' . (string) ($target['year'] ?? '');
            $printMessage = 'Certificate for printing.\n\nParticipant: ' . (string) ($target['name'] ?? '') . '\nCategory: ' . (string) ($target['certificate_type_name'] ?? '');

            $participant = ['email' => $printingEmail, 'name' => 'Printing Service', 'certificate_type_name' => (string) ($target['certificate_type_name'] ?? ''), 'conference_name' => (string) ($target['conference_name'] ?? ''), 'year' => (string) ($target['year'] ?? '')];
            $result = $emailService->sendCertificate($participant, $attachmentAbsolute, $printSubject, $printMessage);

            if ($result['ok']) {
                $sent++;
                db()->prepare('UPDATE participants SET status = :status WHERE id = :id')->execute(['status' => 'printed', 'id' => (int) $target['participant_id']]);
            } else {
                $failed++;
            }

            $logStmt->execute([
                'participant_id' => (int) $target['participant_id'],
                'to_email' => $printingEmail,
                'subject_text' => $printSubject,
                'message_text' => $printMessage,
                'status' => $result['ok'] ? 'sent' : 'failed',
                'error_message' => $result['ok'] ? null : ($result['error'] ?? 'Unknown error'),
            ]);

            usleep(200000);
        }

        $summary = 'Bulk print sent: ' . $sent . ' certificate(s) to ' . e($printingEmail) . '.';
        if ($failed > 0) {
            $summary .= ' Failed: ' . $failed . '.';
        }
        flash($failed > 0 ? 'warning' : 'success', $summary);
        redirect(url('printer', ['conference_id' => $selectedConferenceId, 'certificate_type_id' => $selectedTypeId]));
    }
}

$listWhere = ['p.conference_id = :conference_id', 'gc.pdf_path IS NOT NULL', "gc.pdf_path <> ''"];
$listParams = ['conference_id' => $selectedConferenceId];

if ($selectedTypeId > 0) {
    $listWhere[] = 'p.certificate_type_id = :certificate_type_id';
    $listParams['certificate_type_id'] = $selectedTypeId;
}

if ($searchTerm !== '') {
    $listWhere[] = '(p.name LIKE :search OR p.email LIKE :search)';
    $listParams['search'] = '%' . $searchTerm . '%';
}

$countStmt = db()->prepare(
    'SELECT COUNT(*)
     FROM participants p
     INNER JOIN generated_certificates gc ON gc.participant_id = p.id
     WHERE ' . implode(' AND ', $listWhere)
);
$countStmt->execute($listParams);
$totalRows = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalRows / $pageSize));
$pageNumber = min($pageNumber, $totalPages);
$offset = ($pageNumber - 1) * $pageSize;

$query =
    'SELECT p.id, p.name, p.email, p.title, p.status,
            ct.name AS certificate_type_name, ct.id AS certificate_type_id,
            gc.pdf_path, gc.jpg_path, gc.generated_at
     FROM participants p
     INNER JOIN certificate_types ct ON ct.id = p.certificate_type_id
     INNER JOIN generated_certificates gc ON gc.participant_id = p.id
     WHERE ' . implode(' AND ', $listWhere)
     . ' ORDER BY ct.name ASC, p.name ASC
     LIMIT :limit OFFSET :offset';

$stmt = db()->prepare($query);
foreach ($listParams as $key => $value) {
    $stmt->bindValue(':' . $key, $value);
}
$stmt->bindValue(':limit', $pageSize, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$participants = $stmt->fetchAll();

render_view('printer.php', [
    'pageTitle' => 'Print Queue',
    'conferences' => $conferences,
    'certificateTypes' => $certificateTypes,
    'selectedConferenceId' => $selectedConferenceId,
    'selectedTypeId' => $selectedTypeId,
    'conference' => $conference,
    'participants' => $participants,
    'totalRows' => $totalRows,
    'totalPages' => $totalPages,
    'pageNumber' => $pageNumber,
    'pageSize' => $pageSize,
    'searchTerm' => $searchTerm,
    'printingEmail' => $printingEmail,
    'headerMeta' => [
        'actions' => [
            [
                'label' => 'Send All to Printer',
                'url' => '#',
                'class' => 'button',
                'id' => 'bulk-print-btn',
            ],
        ],
    ],
]);
