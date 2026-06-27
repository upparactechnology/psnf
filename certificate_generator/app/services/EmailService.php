<?php
declare(strict_types=1);

namespace App\Services;

use PDO;

class EmailService
{
    public function __construct(private PDO $pdo)
    {
        $this->loadMailDependencies();
    }

    public function sendCertificate(array $participant, ?string $attachmentAbsolutePath, string $subjectTemplate, string $messageTemplate): array
    {
        $replacements = [
            '{{name}}' => (string) ($participant['name'] ?? ''),
            '{{certificate_type}}' => (string) ($participant['certificate_type_name'] ?? ''),
            '{{conference}}' => (string) ($participant['conference_name'] ?? ''),
            '{{year}}' => (string) ($participant['year'] ?? ''),
        ];

        $subject = strtr($subjectTemplate, $replacements);
        $message = strtr($messageTemplate, $replacements);

        $attachmentPath = trim((string) ($attachmentAbsolutePath ?? ''));
        if ($attachmentPath !== '' && !is_file($attachmentPath)) {
            return ['ok' => false, 'error' => 'Attachment file not found: ' . $attachmentPath];
        }

        $email = trim((string) ($participant['email'] ?? ''));
        if ($email === '') {
            return ['ok' => false, 'error' => 'Participant email is empty.'];
        }

        $error = null;
        try {
            if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
                $this->sendUsingPhpMailer($email, (string) ($participant['name'] ?? ''), $subject, $message, $attachmentPath !== '' ? $attachmentPath : null);
            } else {
                $this->sendUsingMailFunction($email, $subject, $message, $attachmentPath !== '' ? $attachmentPath : null);
            }
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
        }

        $ok = $error === null;

        // Append a plain log entry to storage/email.log for quick auditing.
        $logDir = APP_ROOT . '/storage';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }

        $logFile = $logDir . '/email.log';
        $entry = [
            'at' => date('Y-m-d H:i:s'),
            'to' => $email,
            'name' => (string) ($participant['name'] ?? ''),
            'subject' => $subject,
            'attachment' => $attachmentPath !== '' ? $attachmentPath : null,
            'status' => $ok ? 'sent' : 'failed',
            'error' => $error,
        ];

        @file_put_contents($logFile, json_encode($entry, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND | LOCK_EX);

        return ['ok' => $ok, 'error' => $error];
    }

    public function sendDirectMessage(string $toEmail, string $toName, string $subject, string $message, ?string $attachmentAbsolutePath = null): array
    {
        $email = trim($toEmail);
        $name = trim($toName);
        $subject = trim($subject);
        $message = trim($message);
        $attachmentPath = trim((string) ($attachmentAbsolutePath ?? ''));

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'error' => 'Valid recipient email is required.'];
        }

        if ($subject === '') {
            return ['ok' => false, 'error' => 'Email subject is required.'];
        }

        if ($message === '') {
            return ['ok' => false, 'error' => 'Email message is required.'];
        }

        if ($attachmentPath !== '' && !is_file($attachmentPath)) {
            return ['ok' => false, 'error' => 'Attachment file not found: ' . $attachmentPath];
        }

        $error = null;
        try {
            if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
                $this->sendUsingPhpMailer($email, $name, $subject, $message, $attachmentPath !== '' ? $attachmentPath : null);
            } else {
                $this->sendUsingMailFunction($email, $subject, $message, $attachmentPath !== '' ? $attachmentPath : null);
            }
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
        }

        $ok = $error === null;
        $logDir = APP_ROOT . '/storage';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }

        $logFile = $logDir . '/email.log';
        $entry = [
            'at' => date('Y-m-d H:i:s'),
            'to' => $email,
            'name' => $name,
            'subject' => $subject,
            'attachment' => $attachmentPath !== '' ? $attachmentPath : null,
            'status' => $ok ? 'sent' : 'failed',
            'error' => $error,
            'source' => 'contact_messages',
        ];
        @file_put_contents($logFile, json_encode($entry, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND | LOCK_EX);

        return ['ok' => $ok, 'error' => $error];
    }

    private function loadMailDependencies(): void
    {
        $composerAutoload = APP_ROOT . '/vendor/autoload.php';
        if (is_file($composerAutoload)) {
            require_once $composerAutoload;
            return;
        }

        $phpMailerBase = APP_ROOT . '/lib/PHPMailer/src';
        if (is_file($phpMailerBase . '/PHPMailer.php')) {
            require_once $phpMailerBase . '/Exception.php';
            require_once $phpMailerBase . '/PHPMailer.php';
            require_once $phpMailerBase . '/SMTP.php';
        }
    }

    private function sendUsingPhpMailer(string $toEmail, string $toName, string $subject, string $message, ?string $attachmentPath): void
    {
        $phpMailerClass = '\\PHPMailer\\PHPMailer\\PHPMailer';
        if (!class_exists($phpMailerClass)) {
            throw new \RuntimeException('PHPMailer class not available.');
        }

        $mailer = new $phpMailerClass(true);

        $smtpHost = setting('smtp_host', '') ?? '';
        if ($smtpHost !== '') {
            $mailer->isSMTP();
            $mailer->Host = $smtpHost;
            $mailer->Port = (int) (setting('smtp_port', '587') ?? 587);
            $mailer->SMTPAuth = true;
            $mailer->Username = setting('smtp_username', '') ?? '';
            $mailer->Password = setting('smtp_password', '') ?? '';

            $secure = strtolower((string) (setting('smtp_secure', 'tls') ?? 'tls'));
            if (in_array($secure, ['ssl', 'tls'], true)) {
                $mailer->SMTPSecure = $secure;
            }
            // Optional SMTP debug controlled by setting 'smtp_debug' (set to '1' to enable)
            if ((string) (setting('smtp_debug', '0') ?? '0') === '1') {
                $mailer->SMTPDebug = 2;
                // Write PHPMailer SMTP debug to a persistent file for easier access on shared hosts.
                $debugFile = APP_ROOT . '/storage/phpmailer_debug.log';
                if (!is_dir(dirname($debugFile))) {
                    @mkdir(dirname($debugFile), 0777, true);
                }
                $mailer->Debugoutput = static function ($str, $level) use ($debugFile) {
                    $line = '[' . date('Y-m-d H:i:s') . '] ' . trim((string) $str) . "\n";
                    @file_put_contents($debugFile, $line, FILE_APPEND | LOCK_EX);
                };
            }
        }

        $fromEmail = setting('smtp_from_email', 'no-reply@example.com') ?? 'no-reply@example.com';
        $fromName = setting('smtp_from_name', APP_NAME) ?? APP_NAME;

        // Ensure PHPMailer presents a valid hostname for Message-ID and HELO/ EHLO.
        $fromHost = parse_url('mailto://' . $fromEmail, PHP_URL_HOST) ?: null;
        $heloHost = (string) (setting('smtp_helo_host', $fromHost ?: '') ?? '');
        if ($heloHost === '') {
            // fallback to domain part of from email
            $parts = explode('@', $fromEmail);
            $heloHost = $parts[1] ?? gethostname();
        }
        $mailer->Hostname = $heloHost;
        // Optional custom HELO string
        if (!empty($heloHost)) {
            $mailer->Helo = $heloHost;
        }

        $mailer->setFrom($fromEmail, $fromName);
        // Set envelope sender for better bounce handling (optional setting)
        $bounce = setting('smtp_bounce_email', '') ?? '';
        if ($bounce !== '') {
            $mailer->Sender = $bounce;
        }
        // Add List-Unsubscribe header if configured
        $listUnsub = setting('smtp_list_unsubscribe', '') ?? '';
        if ($listUnsub !== '') {
            $mailer->addCustomHeader('List-Unsubscribe', $listUnsub);
        }

        $mailer->addAddress($toEmail, $toName);
        foreach ($this->getConfiguredEmails('email_cc') as $ccEmail) {
            $mailer->addCC($ccEmail);
        }
        foreach ($this->getConfiguredEmails('email_bcc') as $bccEmail) {
            $mailer->addBCC($bccEmail);
        }
        $mailer->Subject = $subject;
        $mailer->isHTML(true);
        $mailer->Body = nl2br(e($message));
        $mailer->AltBody = $message;
        if ($attachmentPath !== null && trim($attachmentPath) !== '') {
            $mailer->addAttachment($attachmentPath, basename($attachmentPath));
        }

        $mailer->send();
    }

    /**
     * Create and configure a PHPMailer instance for reuse in bulk sends.
     * Throws RuntimeException if PHPMailer is not available.
     * @return \PHPMailer\PHPMailer\PHPMailer
     */
    public function createMailer()
    {
        $phpMailerClass = '\\PHPMailer\\PHPMailer\\PHPMailer';
        if (!class_exists($phpMailerClass)) {
            throw new \RuntimeException('PHPMailer class not available.');
        }

        $mailer = new $phpMailerClass(true);

        $smtpHost = setting('smtp_host', '') ?? '';
        if ($smtpHost !== '') {
            $mailer->isSMTP();
            $mailer->Host = $smtpHost;
            $mailer->Port = (int) (setting('smtp_port', '587') ?? 587);
            $mailer->SMTPAuth = true;
            $mailer->Username = setting('smtp_username', '') ?? '';
            $mailer->Password = setting('smtp_password', '') ?? '';

            $secure = strtolower((string) (setting('smtp_secure', 'tls') ?? 'tls'));
            if (in_array($secure, ['ssl', 'tls'], true)) {
                $mailer->SMTPSecure = $secure;
            }

            // Keep SMTP connection alive between sends for bulk operations
            $mailer->SMTPKeepAlive = true;

            if ((string) (setting('smtp_debug', '0') ?? '0') === '1') {
                $mailer->SMTPDebug = 2;
                $debugFile = APP_ROOT . '/storage/phpmailer_debug.log';
                if (!is_dir(dirname($debugFile))) {
                    @mkdir(dirname($debugFile), 0777, true);
                }
                $mailer->Debugoutput = static function ($str, $level) use ($debugFile) {
                    $line = '[' . date('Y-m-d H:i:s') . '] ' . trim((string) $str) . "\n";
                    @file_put_contents($debugFile, $line, FILE_APPEND | LOCK_EX);
                };
            }
        }

        $fromEmail = setting('smtp_from_email', 'no-reply@example.com') ?? 'no-reply@example.com';
        $fromName = setting('smtp_from_name', APP_NAME) ?? APP_NAME;
        // Ensure PHPMailer presents a valid hostname for Message-ID and HELO/ EHLO.
        $fromHost = parse_url('mailto://' . $fromEmail, PHP_URL_HOST) ?: null;
        $heloHost = (string) (setting('smtp_helo_host', $fromHost ?: '') ?? '');
        if ($heloHost === '') {
            // fallback to domain part of from email
            $parts = explode('@', $fromEmail);
            $heloHost = $parts[1] ?? gethostname();
        }
        $mailer->Hostname = $heloHost;
        // Optional custom HELO string
        if (!empty($heloHost)) {
            $mailer->Helo = $heloHost;
        }

        $mailer->setFrom($fromEmail, $fromName);

        // Optional bounce and list-unsubscribe headers
        $bounce = setting('smtp_bounce_email', '') ?? '';
        if ($bounce !== '') {
            $mailer->Sender = $bounce;
        }

        $listUnsub = setting('smtp_list_unsubscribe', '') ?? '';
        if ($listUnsub !== '') {
            $mailer->addCustomHeader('List-Unsubscribe', $listUnsub);
        }

        return $mailer;
    }

    /**
     * Send using an existing PHPMailer instance (keeps connection open).
     * Returns ['ok'=>bool,'error'=>string|null]
     */
    public function sendUsingMailer($mailer, string $toEmail, string $toName, string $subject, string $message, ?string $attachmentPath): array
    {
        try {
            // Clear all previous recipients/attachments before reusing this mailer.
            $mailer->clearAllRecipients();
            $mailer->clearAttachments();

            $mailer->addAddress($toEmail, $toName);
            foreach ($this->getConfiguredEmails('email_cc') as $ccEmail) {
                $mailer->addCC($ccEmail);
            }
            foreach ($this->getConfiguredEmails('email_bcc') as $bccEmail) {
                $mailer->addBCC($bccEmail);
            }
            $mailer->Subject = $subject;
            $mailer->isHTML(true);
            $mailer->Body = nl2br(e($message));
            $mailer->AltBody = $message;
            if ($attachmentPath !== null && trim($attachmentPath) !== '') {
                $mailer->addAttachment($attachmentPath, basename($attachmentPath));
            }

            $mailer->send();

            // Keep connection alive; caller should close with smtpClose()
            return ['ok' => true, 'error' => null];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    private function sendUsingMailFunction(string $toEmail, string $subject, string $message, ?string $attachmentPath): void
    {
        $fromEmail = setting('smtp_from_email', 'no-reply@example.com') ?? 'no-reply@example.com';
        $ccList = $this->getConfiguredEmails('email_cc');
        $bccList = $this->getConfiguredEmails('email_bcc');
        $attachmentPath = trim((string) ($attachmentPath ?? ''));

        if ($attachmentPath === '') {
            $headers = [];
            $headers[] = 'From: ' . $fromEmail;
            if ($ccList !== []) {
                $headers[] = 'Cc: ' . implode(', ', $ccList);
            }
            if ($bccList !== []) {
                $headers[] = 'Bcc: ' . implode(', ', $bccList);
            }
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-Type: text/plain; charset=UTF-8';
            $headers[] = 'Content-Transfer-Encoding: 8bit';

            $result = mail($toEmail, $subject, $message, implode("\r\n", $headers));
            if (!$result) {
                throw new \RuntimeException('mail() failed. Configure PHPMailer/SMTP in settings for reliable delivery.');
            }
            return;
        }

        $extension = strtolower((string) pathinfo($attachmentPath, PATHINFO_EXTENSION));
        $attachmentMime = $extension === 'jpg' || $extension === 'jpeg' ? 'image/jpeg' : 'application/pdf';

        $separator = md5((string) microtime(true));
        $eol = "\r\n";

        $headers = [];
        $headers[] = 'From: ' . $fromEmail;
        if ($ccList !== []) {
            $headers[] = 'Cc: ' . implode(', ', $ccList);
        }
        if ($bccList !== []) {
            $headers[] = 'Bcc: ' . implode(', ', $bccList);
        }
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: multipart/mixed; boundary="' . $separator . '"';

        $body = '--' . $separator . $eol;
        $body .= 'Content-Type: text/plain; charset="UTF-8"' . $eol;
        $body .= 'Content-Transfer-Encoding: 7bit' . $eol . $eol;
        $body .= $message . $eol;

        $fileData = chunk_split(base64_encode((string) file_get_contents($attachmentPath)));

        $body .= '--' . $separator . $eol;
    $body .= 'Content-Type: ' . $attachmentMime . '; name="' . basename($attachmentPath) . '"' . $eol;
        $body .= 'Content-Transfer-Encoding: base64' . $eol;
        $body .= 'Content-Disposition: attachment; filename="' . basename($attachmentPath) . '"' . $eol . $eol;
        $body .= $fileData . $eol;
        $body .= '--' . $separator . '--';

        $result = mail($toEmail, $subject, $body, implode($eol, $headers));
        if (!$result) {
            throw new \RuntimeException('mail() failed. Configure PHPMailer/SMTP in settings for reliable delivery.');
        }
    }

    /**
     * @return array<int, string>
     */
    private function getConfiguredEmails(string $settingKey): array
    {
        $raw = trim((string) (setting($settingKey, '') ?? ''));
        if ($raw === '') {
            return [];
        }

        $chunks = preg_split('/[,;\r\n]+/', $raw) ?: [];
        $emails = [];
        foreach ($chunks as $chunk) {
            $email = trim((string) $chunk);
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            $emails[strtolower($email)] = $email;
        }

        return array_values($emails);
    }
}
