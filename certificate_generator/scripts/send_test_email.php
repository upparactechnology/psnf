<?php
declare(strict_types=1);

// Usage: php scripts/send_test_email.php recipient@example.com
// If you prefer to hardcode a recipient, set $DIRECT_RECIPIENT below.

require_once __DIR__ . '/../app/bootstrap.php';

// Direct recipient override (leave empty to use CLI arg)
$DIRECT_RECIPIENT = 'hetshah6315@gmail.com';

if ($DIRECT_RECIPIENT !== '') {
    $recipient = $DIRECT_RECIPIENT;
} else {
    if ($argc < 2) {
        echo "Usage: php scripts/send_test_email.php recipient@example.com\n";
        exit(1);
    }

    $recipient = $argv[1];
}

if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
    echo "Invalid email: {$recipient}\n";
    exit(1);
}

use App\Services\EmailService;

// Optional: enable SMTP debug in settings so PHPMailer logs to error_log
set_setting('smtp_debug', '1');

$emailService = new EmailService(db());

$subject = 'SMTP Debug Test - ' . date('Y-m-d H:i:s');
$message = "This is a test message to verify SMTP settings and debug output.\n\nRegards,\n" . APP_NAME;

$tmp = sys_get_temp_dir() . '/email_test_' . bin2hex(random_bytes(4)) . '.txt';
file_put_contents($tmp, "This is a small test attachment.\n");

try {
    $participant = [ 'email' => $recipient, 'name' => 'Test Recipient', 'certificate_type_name' => APP_NAME, 'conference_name' => APP_NAME, 'year' => date('Y') ];
    $res = $emailService->sendCertificate($participant, $tmp, $subject, $message);
    if ($res['ok']) {
        echo "Test email sent to {$recipient}. Check inbox/spam.\n";
    } else {
        echo "Test send reported failure: " . ($res['error'] ?? 'unknown') . "\n";
    }
} catch (Throwable $e) {
    echo 'Exception: ' . $e->getMessage() . PHP_EOL;
}

@unlink($tmp);

exit(0);
