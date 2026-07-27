<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

use App\Services\EmailService;

if (function_exists('set_time_limit')) {
    @set_time_limit(0);
}
@ini_set('max_execution_time', '0');
@ignore_user_abort(true);

$logFile = APP_ROOT . '/storage/scheduled_email.log';
$logDir = dirname($logFile);
if (!is_dir($logDir)) {
    @mkdir($logDir, 0777, true);
}

$log = static function (string $message) use ($logFile): void {
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n";
    @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
};

$shouldSendAttachment = (string) (setting('email_send_attachment', '1') ?? '1') === '1';

$log('Schedule runner start');

$scheduleStmt = db()->prepare(
    'SELECT es.*, c.name AS conference_name, c.year, ct.name AS certificate_type_name
    FROM email_schedules es
    INNER JOIN conferences c ON c.id = es.conference_id
    LEFT JOIN certificate_types ct ON ct.id = es.certificate_type_id
    WHERE es.status = "active"
    ORDER BY es.id ASC'
);
$scheduleStmt->execute();
$schedules = $scheduleStmt->fetchAll();

if ($schedules === []) {
    $log('No schedules due');
    exit(0);
}

$emailService = new EmailService(db());
$perRunLimit = max(1, (int) (setting('scheduled_email_batch_size', '5') ?? 5));

$logStmt = db()->prepare(
    'INSERT INTO email_logs
     (participant_id, to_email, subject_text, message_text, status, error_message, sent_at)
     VALUES
     (:participant_id, :to_email, :subject_text, :message_text, :status, :error_message, NOW())'
);

$statusStmt = db()->prepare('UPDATE participants SET status = :status WHERE id = :id');

$scheduleUpdateStmt = db()->prepare(
    'UPDATE email_schedules
     SET last_participant_id = :last_participant_id,
         last_sent_at = NOW(),
         sent_count = sent_count + :sent_delta,
         failed_count = failed_count + :failed_delta,
         updated_at = NOW()
     WHERE id = :id'
);

foreach ($schedules as $schedule) {
    $scheduleId = (int) ($schedule['id'] ?? 0);
    $conferenceId = (int) ($schedule['conference_id'] ?? 0);
    $certificateTypeId = (int) ($schedule['certificate_type_id'] ?? 0);

    if ($scheduleId <= 0 || $conferenceId <= 0) {
        continue;
    }

    set_setting('email_cc', (string) ($schedule['cc_template'] ?? ''));
    set_setting('email_bcc', (string) ($schedule['bcc_template'] ?? ''));

    $params = [
        'conference_id' => $conferenceId,
    ];

    $query =
        'SELECT p.id AS participant_id, p.name, p.email, p.status, p.title,
                c.name AS conference_name, c.year,
                ct.name AS certificate_type_name, gc.jpg_path, gc.pdf_path
         FROM participants p
         INNER JOIN conferences c ON c.id = p.conference_id
         INNER JOIN certificate_types ct ON ct.id = p.certificate_type_id
         INNER JOIN generated_certificates gc ON gc.participant_id = p.id
         WHERE p.conference_id = :conference_id
           AND p.email IS NOT NULL AND p.email <> ""
           AND p.status IN ("pending", "generated")';

    if ($certificateTypeId > 0) {
        $query .= ' AND p.certificate_type_id = :certificate_type_id';
        $params['certificate_type_id'] = $certificateTypeId;
    }

    $query .= ' ORDER BY p.id ASC LIMIT :limit';

    $targetStmt = db()->prepare($query);
    foreach ($params as $key => $value) {
        $targetStmt->bindValue(':' . $key, $value);
    }
    $targetStmt->bindValue(':limit', $perRunLimit, PDO::PARAM_INT);
    $targetStmt->execute();
    $targets = $targetStmt->fetchAll();

    if ($targets === []) {
        continue;
    }

    $subjectTemplate = (string) ($schedule['subject_template'] ?? '');
    $messageTemplate = (string) ($schedule['message_template'] ?? '');

    $mailer = null;
    if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        try {
            $mailer = $emailService->createMailer();
        } catch (Throwable $e) {
            $mailer = null;
        }
    }

    foreach ($targets as $target) {
        $replacements = [
            '{{name}}' => (string) ($target['name'] ?? ''),
            '{{certificate_type}}' => (string) ($target['certificate_type_name'] ?? ''),
            '{{conference}}' => (string) ($target['conference_name'] ?? ''),
            '{{year}}' => (string) ($target['year'] ?? ''),
            '{{title}}' => (string) ($target['title'] ?? ''),
        ];

        $renderedSubject = strtr($subjectTemplate, $replacements);
        $renderedMessage = strtr($messageTemplate, $replacements);

        $attachmentRelative = !empty($target['pdf_path']) ? (string) $target['pdf_path'] : (string) ($target['jpg_path'] ?? '');
        $attachmentNormalized = str_replace('\\', '/', trim($attachmentRelative));
        $rootNormalized = str_replace('\\', '/', rtrim(APP_ROOT, '/'));

        if ($attachmentNormalized === '') {
            $attachmentAbsolute = '';
        } elseif (preg_match('#^[A-Za-z]:/#', $attachmentNormalized) === 1 || str_starts_with($attachmentNormalized, '/')) {
            $attachmentAbsolute = $attachmentNormalized;
        } elseif (str_starts_with($attachmentNormalized, $rootNormalized . '/')) {
            $attachmentAbsolute = $attachmentNormalized;
        } else {
            $attachmentAbsolute = $rootNormalized . '/' . ltrim($attachmentNormalized, '/');
        }

        if ($shouldSendAttachment && ($attachmentAbsolute === '' || !is_file($attachmentAbsolute))) {
            $statusStmt->execute([
                'status' => 'failed',
                'id' => (int) $target['participant_id'],
            ]);

            $logStmt->execute([
                'participant_id' => (int) $target['participant_id'],
                'to_email' => (string) $target['email'],
                'subject_text' => $renderedSubject,
                'message_text' => $renderedMessage,
                'status' => 'failed',
                'error_message' => 'Attachment not found: ' . $attachmentAbsolute,
            ]);

            $scheduleUpdateStmt->execute([
                'last_participant_id' => (int) $target['participant_id'],
                'sent_delta' => 0,
                'failed_delta' => 1,
                'id' => $scheduleId,
            ]);

            $log('Schedule ' . $scheduleId . ' attachment missing for participant ' . (int) $target['participant_id']);
            continue;
        }

        $attachmentToSend = $shouldSendAttachment ? $attachmentAbsolute : null;

        if ($mailer !== null) {
            $result = $emailService->sendUsingMailer(
                $mailer,
                (string) $target['email'],
                (string) $target['name'],
                $renderedSubject,
                $renderedMessage,
                $attachmentToSend
            );
        } else {
            $result = $emailService->sendCertificate($target, $attachmentToSend, $renderedSubject, $renderedMessage);
        }

        $status = $result['ok'] ? 'sent' : 'failed';
        $logStmt->execute([
            'participant_id' => (int) $target['participant_id'],
            'to_email' => (string) $target['email'],
            'subject_text' => $renderedSubject,
            'message_text' => $renderedMessage,
            'status' => $status,
            'error_message' => $result['error'],
        ]);

        $statusStmt->execute([
            'status' => $result['ok'] ? 'emailed' : 'failed',
            'id' => (int) $target['participant_id'],
        ]);

        $scheduleUpdateStmt->execute([
            'last_participant_id' => (int) $target['participant_id'],
            'sent_delta' => $result['ok'] ? 1 : 0,
            'failed_delta' => $result['ok'] ? 0 : 1,
            'id' => $scheduleId,
        ]);

        $log('Schedule ' . $scheduleId . ' sent=' . ($result['ok'] ? '1' : '0') . ' participant=' . (int) $target['participant_id']);
    }

    if ($mailer !== null) {
        try {
            $mailer->smtpClose();
        } catch (Throwable $e) {
            // ignore
        }
    }
}

$log('Schedule runner complete');
