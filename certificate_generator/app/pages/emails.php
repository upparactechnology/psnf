<?php
declare(strict_types=1);

use App\Services\EmailService;

$selectedConferenceId = 1;
$conferenceStmt = db()->prepare('SELECT * FROM conferences WHERE id = 1 LIMIT 1');
$conferenceStmt->execute();
$conference = $conferenceStmt->fetch();

$typeStmt = db()->prepare('SELECT * FROM certificate_types WHERE conference_id = :conference_id ORDER BY name ASC');
$typeStmt->execute(['conference_id' => $selectedConferenceId]);
$certificateTypes = $typeStmt->fetchAll();

$selectedTypeId = (int) ($_GET['certificate_type_id'] ?? ($_POST['certificate_type_id'] ?? ($certificateTypes[0]['id'] ?? 0)));

$emailTemplates = load_email_templates();
$selectedTemplateKey = trim((string) ($_GET['template_key'] ?? ($_POST['template_key'] ?? (string) ($emailTemplates[0]['key'] ?? ''))));
$selectedTemplate = find_email_template($emailTemplates, $selectedTemplateKey) ?? ($emailTemplates[0] ?? default_email_templates()[0]);
$selectedTemplateKey = (string) ($selectedTemplate['key'] ?? '');

$bulkEmailDelayMin = max(0, (int) (setting('bulk_email_delay_min_seconds', '1') ?? 1));
$bulkEmailDelayMax = max($bulkEmailDelayMin, (int) (setting('bulk_email_delay_max_seconds', '3') ?? 3));

$subjectTemplate = trim((string) ($_POST['subject_template'] ?? ($_GET['subject_template'] ?? ($selectedTemplate['subject_template'] ?? (setting('email_subject_template', 'Your {{certificate_type}} Certificate - {{conference}} {{year}}') ?? 'Your {{certificate_type}} Certificate - {{conference}} {{year}}')))));
$messageTemplate = trim((string) ($_POST['message_template'] ?? ($_GET['message_template'] ?? ($selectedTemplate['message_template'] ?? (setting('email_message_template', "Dear {{name}},\n\nPlease find attached your {{certificate_type}} certificate for {{conference}} {{year}}.\n\nRegards,\nConference Team") ?? "Dear {{name}},\n\nPlease find attached your {{certificate_type}} certificate for {{conference}} {{year}}.\n\nRegards,\nConference Team")))));
$ccTemplate = trim((string) ($_POST['cc_template'] ?? ($_GET['cc_template'] ?? ($selectedTemplate['cc_template'] ?? (setting('email_cc', '') ?? '')))));
$bccTemplate = trim((string) ($_POST['bcc_template'] ?? ($_GET['bcc_template'] ?? ($selectedTemplate['bcc_template'] ?? (setting('email_bcc', '') ?? '')))));
$templateName = trim((string) ($_POST['template_name'] ?? ($_GET['template_name'] ?? ($selectedTemplate['name'] ?? 'Custom Template'))));
$currentTemplate = [
    'key' => $selectedTemplateKey,
    'name' => $templateName,
    'subject_template' => $subjectTemplate,
    'message_template' => $messageTemplate,
    'cc_template' => $ccTemplate,
    'bcc_template' => $bccTemplate,
];

$shouldSendAttachment = (string) (setting('email_send_attachment', '1') ?? '1') === '1';
$bulkEmailBatchSize = max(1, (int) (setting('bulk_email_batch_size', '5') ?? 5));
$bulkEmailBatchMaxSeconds = max(1, (int) (setting('bulk_email_batch_max_seconds', '8') ?? 8));

$runBulkEmailBatch = static function (array &$job) use ($shouldSendAttachment, $bulkEmailDelayMin, $bulkEmailDelayMax, $bulkEmailBatchSize, $bulkEmailBatchMaxSeconds): array {
    if (function_exists('set_time_limit')) {
        @set_time_limit(0);
    }
    @ini_set('max_execution_time', '0');
    @ignore_user_abort(true);

    $conferenceId = (int) ($job['conference_id'] ?? 0);
    $certificateTypeId = (int) ($job['certificate_type_id'] ?? 0);
        $logFile = APP_ROOT . '/storage/bulk_email.log';
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }
        $bulkLog = static function (string $message) use ($logFile): void {
            $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n";
            @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
        };
    $subjectTemplate = (string) ($job['subject_template'] ?? '');
    $messageTemplate = (string) ($job['message_template'] ?? '');
    $ccTemplate = (string) ($job['cc_template'] ?? '');
    $bccTemplate = (string) ($job['bcc_template'] ?? '');

    if ($conferenceId <= 0) {
        return ['done' => true, 'sent' => 0, 'failed' => 0, 'total' => 0];
    }

    set_setting('email_cc', $ccTemplate);
    set_setting('email_bcc', $bccTemplate);

    $params = ['conference_id' => $conferenceId];
    $countQuery =
        'SELECT COUNT(*)
         FROM participants p
         INNER JOIN generated_certificates gc ON gc.participant_id = p.id
         WHERE p.conference_id = :conference_id
           AND p.email IS NOT NULL AND p.email <> \'\'';

    if ($certificateTypeId > 0) {
        $countQuery .= ' AND p.certificate_type_id = :certificate_type_id';
        $params['certificate_type_id'] = $certificateTypeId;
    }

    if (!isset($job['total'])) {
        $countStmt = db()->prepare($countQuery);
        $countStmt->execute($params);
        $job['total'] = (int) $countStmt->fetchColumn();
    }

    $offset = max(0, (int) ($job['offset'] ?? 0));
    $limit = max(1, (int) ($job['batch_size'] ?? $bulkEmailBatchSize));

    if ($job['total'] <= 0 || $offset >= $job['total']) {
        return ['done' => true, 'sent' => 0, 'failed' => 0, 'total' => (int) $job['total']];
    }

    $query =
        'SELECT p.id AS participant_id, p.name, p.email, p.status, p.title,
                c.name AS conference_name, c.year,
                ct.name AS certificate_type_name, gc.jpg_path, gc.pdf_path
         FROM participants p
         INNER JOIN conferences c ON c.id = p.conference_id
         INNER JOIN certificate_types ct ON ct.id = p.certificate_type_id
         INNER JOIN generated_certificates gc ON gc.participant_id = p.id
         WHERE p.conference_id = :conference_id
           AND p.email IS NOT NULL AND p.email <> \'\'';

    if ($certificateTypeId > 0) {
        $query .= ' AND p.certificate_type_id = :certificate_type_id';
    }

    $query .= ' ORDER BY p.id ASC LIMIT :limit OFFSET :offset';

    $stmt = db()->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue(':' . $key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $targets = $stmt->fetchAll();

    if ($targets === []) {
        return ['done' => true, 'sent' => 0, 'failed' => 0, 'total' => (int) $job['total']];
    }

    $emailService = new EmailService(db());
    $sent = 0;
    $failed = 0;
    $batchStart = microtime(true);
    $bulkLog('Bulk batch start job=' . (string) ($job['job_id'] ?? '-') . ' offset=' . $offset . ' limit=' . $limit);

    $mailer = null;
    if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        try {
            $mailer = $emailService->createMailer();
        } catch (Throwable $e) {
            $mailer = null;
        }
    }

    $logStmt = db()->prepare(
        'INSERT INTO email_logs
         (participant_id, to_email, subject_text, message_text, status, error_message, sent_at)
         VALUES
         (:participant_id, :to_email, :subject_text, :message_text, :status, :error_message, NOW())'
    );

    $statusStmt = db()->prepare('UPDATE participants SET status = :status WHERE id = :id');

    $lastTargetIndex = array_key_last($targets);

    foreach ($targets as $index => $target) {
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
            $failed++;
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

            $bulkLog('Attachment missing participant_id=' . (int) $target['participant_id'] . ' path=' . $attachmentAbsolute);

            continue;
        }

        $attachmentToSend = $shouldSendAttachment ? $attachmentAbsolute : null;
        if ($mailer !== null) {
            $result = $emailService->sendUsingMailer($mailer, (string) $target['email'], (string) $target['name'], $renderedSubject, $renderedMessage, $attachmentToSend);
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

        if ($result['ok']) {
            $sent++;
            $statusStmt->execute([
                'status' => 'emailed',
                'id' => (int) $target['participant_id'],
            ]);
        } else {
            $failed++;
            $statusStmt->execute([
                'status' => 'failed',
                'id' => (int) $target['participant_id'],
            ]);

            $bulkLog('Send failed participant_id=' . (int) $target['participant_id'] . ' error=' . (string) ($result['error'] ?? 'unknown'));
        }

        if ($bulkEmailDelayMax > 0 && $lastTargetIndex !== null && $index < $lastTargetIndex) {
            $delayMin = min($bulkEmailDelayMin, $bulkEmailDelayMax);
            $delayMax = max($bulkEmailDelayMin, $bulkEmailDelayMax);
            $delaySeconds = random_int($delayMin, $delayMax);
            $elapsed = microtime(true) - $batchStart;
            if ($delaySeconds > 0 && $elapsed < $bulkEmailBatchMaxSeconds) {
                usleep($delaySeconds * 1000000);
            }
        }
    }

    if ($mailer !== null) {
        try {
            $mailer->smtpClose();
        } catch (Throwable $e) {
            // ignore
        }
    }

    $job['offset'] = $offset + count($targets);
    $job['sent'] = (int) ($job['sent'] ?? 0) + $sent;
    $job['failed'] = (int) ($job['failed'] ?? 0) + $failed;

    $bulkLog('Bulk batch end job=' . (string) ($job['job_id'] ?? '-') . ' sent=' . $sent . ' failed=' . $failed);

    return [
        'done' => $job['offset'] >= (int) $job['total'],
        'sent' => $sent,
        'failed' => $failed,
        'total' => (int) $job['total'],
    ];
};

$continueBulk = !is_post_request() && (string) ($_GET['bulk_continue'] ?? '') === '1';
$continueJobId = trim((string) ($_GET['bulk_job'] ?? ''));
if ($continueBulk && isset($_SESSION['bulk_email_job']) && is_array($_SESSION['bulk_email_job'])) {
    $job = $_SESSION['bulk_email_job'];
    if ($continueJobId !== '' && (string) ($job['job_id'] ?? '') !== $continueJobId) {
        unset($_SESSION['bulk_email_job']);
    } else {
        $result = $runBulkEmailBatch($job);
        $_SESSION['bulk_email_job'] = $job;

        if (!empty($result['done'])) {
            $summary = 'Bulk email completed. Sent: ' . (int) ($job['sent'] ?? 0) . ', Failed: ' . (int) ($job['failed'] ?? 0) . '.';
            unset($_SESSION['bulk_email_job']);
            flash((int) ($job['failed'] ?? 0) > 0 ? 'warning' : 'success', $summary);
            redirect(url('emails', ['conference_id' => (int) ($job['conference_id'] ?? 0), 'certificate_type_id' => (int) ($job['certificate_type_id'] ?? 0)]));
        }

        redirect(url('emails', [
            'conference_id' => (int) ($job['conference_id'] ?? 0),
            'certificate_type_id' => (int) ($job['certificate_type_id'] ?? 0),
            'bulk_continue' => 1,
            'bulk_job' => (string) ($job['job_id'] ?? ''),
        ]));
    }
}

if (is_post_request()) {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'send_test') {
        $testEmail = trim((string) ($_POST['test_email'] ?? ''));
        if ($testEmail === '' || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Enter a valid test email address.');
            redirect(url('emails', ['conference_id' => $selectedConferenceId, 'certificate_type_id' => $selectedTypeId]));
        }

        $emailService = new EmailService(db());
        $subject = 'Test: ' . ($subjectTemplate ?: 'Certificate Delivery Test');
        $message = "This is a test message to verify email delivery settings.\n\nRegards,\n" . APP_NAME;

        // Create a small temporary attachment for testing only when enabled in settings.
        $tmp = null;
        if ($shouldSendAttachment) {
            $tmp = sys_get_temp_dir() . '/certificate_test_' . bin2hex(random_bytes(4)) . '.txt';
            file_put_contents($tmp, "This is a test attachment for email delivery.\n");
        }

        $participant = [
            'email' => $testEmail,
            'name' => 'Test Recipient',
            'certificate_type_name' => (string) ($conference['name'] ?? 'Certificate'),
            'conference_name' => (string) ($conference['name'] ?? ''),
            'year' => (string) ($conference['year'] ?? date('Y')),
        ];

        try {
            $res = $emailService->sendCertificate($participant, $tmp, $subject, $message);
            if ($res['ok']) {
                flash('success', 'Test email sent to ' . e($testEmail) . '. Check inbox/spam.');
            } else {
                flash('error', 'Test email failed: ' . ($res['error'] ?? 'Unknown error'));
            }
        } catch (Throwable $e) {
            flash('error', 'Test email failed: ' . $e->getMessage());
        }

        if ($tmp !== null) {
            @unlink($tmp);
        }
        redirect(url('emails', ['conference_id' => $selectedConferenceId, 'certificate_type_id' => $selectedTypeId]));
    }

    if ($action === 'save_template' || $action === 'save_template_new') {
        $createNewTemplate = $action === 'save_template_new';
        $templateName = trim((string) ($_POST['template_name'] ?? $templateName));
        $templateKey = trim((string) ($_POST['template_key'] ?? $selectedTemplateKey));
        $subjectTemplate = trim((string) ($_POST['subject_template'] ?? $subjectTemplate));
        $messageTemplate = trim((string) ($_POST['message_template'] ?? $messageTemplate));
        $ccTemplate = trim((string) ($_POST['cc_template'] ?? $ccTemplate));
        $bccTemplate = trim((string) ($_POST['bcc_template'] ?? $bccTemplate));

        if ($templateName === '' || $subjectTemplate === '' || $messageTemplate === '') {
            flash('error', 'Template name, subject, and message are required.');
            redirect(url('emails', ['conference_id' => $selectedConferenceId, 'certificate_type_id' => $selectedTypeId, 'template_key' => $selectedTemplateKey]));
        }

        $existingTemplates = load_email_templates();
        $persistedKey = $createNewTemplate ? slugify($templateName) : ($templateKey !== '' ? $templateKey : slugify($templateName));
        if ($persistedKey === '') {
            $persistedKey = 'template-' . (count($existingTemplates) + 1);
        }

        $persistedTemplate = [
            'key' => $persistedKey,
            'name' => $templateName,
            'subject_template' => $subjectTemplate,
            'message_template' => $messageTemplate,
            'cc_template' => $ccTemplate,
            'bcc_template' => $bccTemplate,
        ];

        $updatedTemplates = [];
        $replaced = false;
        foreach ($existingTemplates as $template) {
            if (!$createNewTemplate && (string) ($template['key'] ?? '') === $persistedKey) {
                $updatedTemplates[] = $persistedTemplate;
                $replaced = true;
                continue;
            }

            $updatedTemplates[] = $template;
        }

        if (!$replaced) {
            $suffix = 2;
            $baseKey = $persistedKey;
            $existingKeys = array_map(static fn (array $item): string => (string) ($item['key'] ?? ''), $updatedTemplates);
            while (in_array($persistedKey, $existingKeys, true)) {
                $persistedKey = $baseKey . '-' . $suffix;
                $suffix++;
            }
            $persistedTemplate['key'] = $persistedKey;
            $updatedTemplates[] = $persistedTemplate;
        }

        save_email_templates($updatedTemplates);
        set_setting('email_subject_template', $subjectTemplate);
        set_setting('email_message_template', $messageTemplate);
        set_setting('email_cc', $ccTemplate);
        set_setting('email_bcc', $bccTemplate);

        flash('success', 'Email template saved.');
        redirect(url('emails', ['conference_id' => $selectedConferenceId, 'certificate_type_id' => $selectedTypeId, 'template_key' => $persistedKey]));
    }

    if ($action === 'delete_template') {
        $templateKey = trim((string) ($_POST['template_key'] ?? $selectedTemplateKey));
        if ($templateKey === '') {
            flash('error', 'Select a template to delete.');
            redirect(url('emails', ['conference_id' => $selectedConferenceId, 'certificate_type_id' => $selectedTypeId]));
        }

        $remainingTemplates = [];
        $removed = false;
        foreach (load_email_templates() as $template) {
            if ((string) ($template['key'] ?? '') === $templateKey) {
                $removed = true;
                continue;
            }

            $remainingTemplates[] = $template;
        }

        if (!$removed) {
            flash('error', 'Template not found.');
            redirect(url('emails', ['conference_id' => $selectedConferenceId, 'certificate_type_id' => $selectedTypeId]));
        }

        save_email_templates($remainingTemplates);
        $nextTemplate = $remainingTemplates[0] ?? default_email_templates()[0];
        flash('success', 'Email template deleted.');
        redirect(url('emails', ['conference_id' => $selectedConferenceId, 'certificate_type_id' => $selectedTypeId, 'template_key' => (string) ($nextTemplate['key'] ?? '')]));
    }

    if ($action === 'send_bulk') {
        $templateKey = trim((string) ($_POST['template_key'] ?? $selectedTemplateKey));
        $templateName = trim((string) ($_POST['template_name'] ?? $templateName));
        $subjectTemplate = trim((string) ($_POST['subject_template'] ?? $subjectTemplate));
        $messageTemplate = trim((string) ($_POST['message_template'] ?? $messageTemplate));
        $ccTemplate = trim((string) ($_POST['cc_template'] ?? $ccTemplate));
        $bccTemplate = trim((string) ($_POST['bcc_template'] ?? $bccTemplate));
        $jobId = bin2hex(random_bytes(8));
        $job = [
            'job_id' => $jobId,
            'conference_id' => $selectedConferenceId,
            'certificate_type_id' => $selectedTypeId,
            'subject_template' => $subjectTemplate,
            'message_template' => $messageTemplate,
            'cc_template' => $ccTemplate,
            'bcc_template' => $bccTemplate,
            'offset' => 0,
            'sent' => 0,
            'failed' => 0,
            'batch_size' => $bulkEmailBatchSize,
        ];

        $_SESSION['bulk_email_job'] = $job;
        $result = $runBulkEmailBatch($job);
        $_SESSION['bulk_email_job'] = $job;

        if (!empty($result['done'])) {
            $summary = 'Bulk email completed. Sent: ' . (int) ($job['sent'] ?? 0) . ', Failed: ' . (int) ($job['failed'] ?? 0) . '.';
            unset($_SESSION['bulk_email_job']);
            flash((int) ($job['failed'] ?? 0) > 0 ? 'warning' : 'success', $summary);
            redirect(url('emails', ['conference_id' => $selectedConferenceId, 'certificate_type_id' => $selectedTypeId]));
        }

        redirect(url('emails', [
            'conference_id' => $selectedConferenceId,
            'certificate_type_id' => $selectedTypeId,
            'bulk_continue' => 1,
            'bulk_job' => $jobId,
        ]));
    }

    if ($action === 'create_schedule') {
        $scheduleTypeId = (int) ($_POST['schedule_certificate_type_id'] ?? $selectedTypeId);
        $scheduleTypeId = $scheduleTypeId > 0 ? $scheduleTypeId : 0;
        $templateKey = trim((string) ($_POST['schedule_template_key'] ?? $selectedTemplateKey));
        $scheduleTemplate = find_email_template($emailTemplates, $templateKey) ?? ($emailTemplates[0] ?? default_email_templates()[0]);
        $templateKey = (string) ($scheduleTemplate['key'] ?? $templateKey);
        $subjectTemplate = trim((string) ($scheduleTemplate['subject_template'] ?? $subjectTemplate));
        $messageTemplate = trim((string) ($scheduleTemplate['message_template'] ?? $messageTemplate));
        $ccTemplate = trim((string) ($scheduleTemplate['cc_template'] ?? $ccTemplate));
        $bccTemplate = trim((string) ($scheduleTemplate['bcc_template'] ?? $bccTemplate));

        if ($subjectTemplate === '' || $messageTemplate === '') {
            flash('error', 'Subject and message are required for scheduled sending.');
            redirect(url('emails', ['conference_id' => $selectedConferenceId, 'certificate_type_id' => $selectedTypeId]));
        }

        $user = current_user();
        $createdBy = $user !== null ? (int) ($user['id'] ?? 0) : 0;

        $scheduleStmt = db()->prepare(
            'INSERT INTO email_schedules
             (conference_id, certificate_type_id, template_key, subject_template, message_template, cc_template, bcc_template,
              status, interval_seconds, created_by, created_at)
             VALUES
             (:conference_id, :certificate_type_id, :template_key, :subject_template, :message_template, :cc_template, :bcc_template,
              :status, :interval_seconds, :created_by, NOW())'
        );
        $scheduleStmt->execute([
            'conference_id' => $selectedConferenceId,
            'certificate_type_id' => $scheduleTypeId > 0 ? $scheduleTypeId : null,
            'template_key' => $templateKey !== '' ? $templateKey : 'custom',
            'subject_template' => $subjectTemplate,
            'message_template' => $messageTemplate,
            'cc_template' => $ccTemplate,
            'bcc_template' => $bccTemplate,
            'status' => 'active',
            'interval_seconds' => 60,
            'created_by' => $createdBy > 0 ? $createdBy : null,
        ]);

        flash('success', 'Scheduled send created and activated.');
        redirect(url('emails', ['conference_id' => $selectedConferenceId, 'certificate_type_id' => $selectedTypeId]));
    }

    if ($action === 'toggle_schedule') {
        $scheduleId = (int) ($_POST['schedule_id'] ?? 0);
        $nextStatus = (string) ($_POST['next_status'] ?? 'paused');
        $nextStatus = $nextStatus === 'active' ? 'active' : 'paused';

        if ($scheduleId > 0) {
            $toggleStmt = db()->prepare(
                'UPDATE email_schedules
                 SET status = :status, updated_at = NOW()
                 WHERE id = :id AND conference_id = :conference_id'
            );
            $toggleStmt->execute([
                'status' => $nextStatus,
                'id' => $scheduleId,
                'conference_id' => $selectedConferenceId,
            ]);
        }

        flash('success', 'Schedule updated.');
        redirect(url('emails', ['conference_id' => $selectedConferenceId, 'certificate_type_id' => $selectedTypeId]));
    }

    if ($action === 'reset_schedule') {
        $scheduleId = (int) ($_POST['schedule_id'] ?? 0);
        if ($scheduleId > 0) {
            $resetStmt = db()->prepare(
                'UPDATE email_schedules
                 SET last_participant_id = NULL,
                     last_sent_at = NULL,
                     sent_count = 0,
                     failed_count = 0,
                     updated_at = NOW()
                 WHERE id = :id AND conference_id = :conference_id'
            );
            $resetStmt->execute([
                'id' => $scheduleId,
                'conference_id' => $selectedConferenceId,
            ]);
        }

        flash('success', 'Schedule progress reset.');
        redirect(url('emails', ['conference_id' => $selectedConferenceId, 'certificate_type_id' => $selectedTypeId]));
    }

    if ($action === 'delete_schedule') {
        $scheduleId = (int) ($_POST['schedule_id'] ?? 0);
        if ($scheduleId > 0) {
            $deleteStmt = db()->prepare('DELETE FROM email_schedules WHERE id = :id AND conference_id = :conference_id');
            $deleteStmt->execute([
                'id' => $scheduleId,
                'conference_id' => $selectedConferenceId,
            ]);
        }

        flash('success', 'Schedule deleted.');
        redirect(url('emails', ['conference_id' => $selectedConferenceId, 'certificate_type_id' => $selectedTypeId]));
    }
}

$logStmt = db()->prepare(
    'SELECT e.*, p.name AS participant_name, ct.name AS category_name
     FROM email_logs e
     INNER JOIN participants p ON p.id = e.participant_id
     INNER JOIN certificate_types ct ON ct.id = p.certificate_type_id
     WHERE p.conference_id = :conference_id
             AND (:certificate_type_id_filter = 0 OR p.certificate_type_id = :certificate_type_id_match)
     ORDER BY e.id DESC
     LIMIT 100'
);
$logStmt->execute([
    'conference_id' => $selectedConferenceId,
        'certificate_type_id_filter' => $selectedTypeId,
        'certificate_type_id_match' => $selectedTypeId,
]);
$emailLogs = $logStmt->fetchAll();

$scheduleStmt = db()->prepare(
    'SELECT es.*, ct.name AS certificate_type_name
     FROM email_schedules es
     LEFT JOIN certificate_types ct ON ct.id = es.certificate_type_id
     WHERE es.conference_id = :conference_id
     ORDER BY es.id DESC'
);
$scheduleStmt->execute(['conference_id' => $selectedConferenceId]);
$emailSchedules = $scheduleStmt->fetchAll();

if ((string) ($_GET['export'] ?? '') === 'csv') {
    $exportLogStmt = db()->prepare(
        'SELECT e.*, p.name AS participant_name, ct.name AS category_name
         FROM email_logs e
         INNER JOIN participants p ON p.id = e.participant_id
         INNER JOIN certificate_types ct ON ct.id = p.certificate_type_id
         WHERE p.conference_id = :conference_id
             AND (:certificate_type_id_filter = 0 OR p.certificate_type_id = :certificate_type_id_match)
         ORDER BY e.id DESC'
    );
    $exportLogStmt->execute([
        'conference_id' => $selectedConferenceId,
        'certificate_type_id_filter' => $selectedTypeId,
        'certificate_type_id_match' => $selectedTypeId,
    ]);
    $exportRows = $exportLogStmt->fetchAll();

    $fileName = safe_filename((string) ($conference['name'] ?? 'conference') . '_email_logs_' . date('Ymd_His')) . '.csv';
    stream_csv_download($fileName, ['ID', 'Participant', 'To', 'Category', 'Status', 'Error', 'Sent At'], $exportRows, static function (array $row): array {
        return [
            (string) ($row['id'] ?? ''),
            (string) ($row['participant_name'] ?? ''),
            (string) ($row['to_email'] ?? ''),
            (string) ($row['category_name'] ?? ''),
            (string) ($row['status'] ?? ''),
            (string) ($row['error_message'] ?? ''),
            (string) ($row['sent_at'] ?? ''),
        ];
    });
}

render_view('emails.php', [
    'pageTitle' => 'Email Certificate Delivery',
    'conference' => $conference,
    'certificateTypes' => $certificateTypes,
    'selectedConferenceId' => $selectedConferenceId,
    'selectedTypeId' => $selectedTypeId,
    'selectedTemplateKey' => $selectedTemplateKey,
    'emailTemplates' => $emailTemplates,
    'bulkEmailDelayMin' => $bulkEmailDelayMin,
    'bulkEmailDelayMax' => $bulkEmailDelayMax,
    'subjectTemplate' => $subjectTemplate,
    'messageTemplate' => $messageTemplate,
    'ccTemplate' => $ccTemplate,
    'bccTemplate' => $bccTemplate,
    'templateName' => $templateName,
    'emailLogs' => $emailLogs,
    'emailSchedules' => $emailSchedules,
    'headerMeta' => [
        'actions' => [
            [
                'label' => 'Export CSV',
                'url' => url('emails', ['conference_id' => $selectedConferenceId, 'certificate_type_id' => $selectedTypeId, 'template_key' => $selectedTemplateKey, 'export' => 'csv']),
                'class' => 'button-muted',
            ],
        ],
    ],
]);
