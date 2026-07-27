<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

use App\Services\EmailService;

$usage = "Usage: php scripts/send_bulk_emails.php --conference=ID [--type=ID] [--batch=N] [--sleep=SEC] [--template-key=KEY] [--max=N] [--dry-run]\n";

$options = getopt('', [
	'conference:',
	'type::',
	'batch::',
	'sleep::',
	'template-key::',
	'max::',
	'dry-run::',
]);

$conferenceId = isset($options['conference']) ? (int) $options['conference'] : 0;
if ($conferenceId <= 0) {
	fwrite(STDERR, $usage);
	exit(1);
}

$typeId = isset($options['type']) ? (int) $options['type'] : 0;
$batchSize = isset($options['batch']) ? max(1, (int) $options['batch']) : max(1, (int) (setting('bulk_email_batch_size', '25') ?? 25));
$maxToSend = isset($options['max']) ? max(1, (int) $options['max']) : 0;
$dryRun = array_key_exists('dry-run', $options);

$delayMin = max(0, (int) (setting('bulk_email_delay_min_seconds', '0') ?? 0));
$delayMax = max($delayMin, (int) (setting('bulk_email_delay_max_seconds', '0') ?? 0));
if (isset($options['sleep'])) {
	$delayMin = max(0, (int) $options['sleep']);
	$delayMax = $delayMin;
}

$templates = load_email_templates();
$templateKey = trim((string) ($options['template-key'] ?? ''));
$selectedTemplate = $templateKey !== ''
	? (find_email_template($templates, $templateKey) ?? null)
	: null;

if ($selectedTemplate === null) {
	$selectedTemplate = $templates[0] ?? default_email_templates()[0];
}

$subjectTemplate = trim((string) ($selectedTemplate['subject_template'] ?? (setting('email_subject_template', '') ?? '')));
$messageTemplate = trim((string) ($selectedTemplate['message_template'] ?? (setting('email_message_template', '') ?? '')));
$ccTemplate = trim((string) ($selectedTemplate['cc_template'] ?? (setting('email_cc', '') ?? '')));
$bccTemplate = trim((string) ($selectedTemplate['bcc_template'] ?? (setting('email_bcc', '') ?? '')));

set_setting('email_cc', $ccTemplate);
set_setting('email_bcc', $bccTemplate);

$shouldSendAttachment = (string) (setting('email_send_attachment', '1') ?? '1') === '1';

$logFile = APP_ROOT . '/storage/bulk_email.log';
$logDir = dirname($logFile);
if (!is_dir($logDir)) {
	@mkdir($logDir, 0777, true);
}

$bulkLog = static function (string $message) use ($logFile): void {
	$line = '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n";
	@file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
};

$bulkLog('CLI bulk send start conference=' . $conferenceId . ' type=' . $typeId . ' batch=' . $batchSize . ' dry=' . ($dryRun ? '1' : '0'));

$emailService = new EmailService(db());
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

$sent = 0;
$failed = 0;
$offset = 0;

while (true) {
	if ($maxToSend > 0 && ($sent + $failed) >= $maxToSend) {
		break;
	}

	$params = ['conference_id' => $conferenceId];
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

	if ($typeId > 0) {
		$query .= ' AND p.certificate_type_id = :certificate_type_id';
		$params['certificate_type_id'] = $typeId;
	}

	$query .= ' ORDER BY p.id ASC LIMIT :limit OFFSET :offset';

	$stmt = db()->prepare($query);
	foreach ($params as $key => $value) {
		$stmt->bindValue(':' . $key, $value);
	}
	$stmt->bindValue(':limit', $batchSize, PDO::PARAM_INT);
	$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
	$stmt->execute();

	$targets = $stmt->fetchAll();
	if ($targets === []) {
		break;
	}

	foreach ($targets as $target) {
		if ($maxToSend > 0 && ($sent + $failed) >= $maxToSend) {
			break 2;
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
			fwrite(STDOUT, "Attachment missing for participant #" . (int) $target['participant_id'] . "\n");
			continue;
		}

		$attachmentToSend = $shouldSendAttachment ? $attachmentAbsolute : null;
		$result = ['ok' => true, 'error' => null];

		if (!$dryRun) {
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
			fwrite(STDOUT, "Sent to " . (string) $target['email'] . "\n");
		} else {
			$failed++;
			$statusStmt->execute([
				'status' => 'failed',
				'id' => (int) $target['participant_id'],
			]);
			$bulkLog('Send failed participant_id=' . (int) $target['participant_id'] . ' error=' . (string) ($result['error'] ?? 'unknown'));
			fwrite(STDOUT, "Failed for " . (string) $target['email'] . ": " . (string) ($result['error'] ?? 'unknown') . "\n");
		}

		if ($delayMax > 0) {
			$delaySeconds = $delayMin === $delayMax ? $delayMin : random_int($delayMin, $delayMax);
			if ($delaySeconds > 0) {
				sleep($delaySeconds);
			}
		}
	}

	$offset += count($targets);
}

if ($mailer !== null) {
	try {
		$mailer->smtpClose();
	} catch (Throwable $e) {
		// ignore
	}
}

$summary = 'CLI bulk send completed. Sent: ' . $sent . ', Failed: ' . $failed . '.';
$bulkLog($summary);
fwrite(STDOUT, $summary . "\n");

exit($failed > 0 ? 2 : 0);