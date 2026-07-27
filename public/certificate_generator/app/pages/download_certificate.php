<?php
declare(strict_types=1);

$pageAlerts = [];

$conferencesStmt = db()->query('SELECT id, name, year FROM conferences ORDER BY year DESC, name ASC');
$conferences = $conferencesStmt->fetchAll();
$selectedConferenceId = (int) ($_GET['conference_id'] ?? ($_POST['conference_id'] ?? ($conferences[0]['id'] ?? 0)));

if ($selectedConferenceId <= 0 && $conferences !== []) {
    $selectedConferenceId = (int) $conferences[0]['id'];
}

$conferenceIds = array_map(static fn (array $row): int => (int) $row['id'], $conferences);
if ($selectedConferenceId > 0 && !in_array($selectedConferenceId, $conferenceIds, true)) {
    $pageAlerts[] = ['type' => 'error', 'message' => 'Selected conference not found.'];
    $selectedConferenceId = $conferences === [] ? 0 : (int) $conferences[0]['id'];
}

$certificateTypes = [];
if ($selectedConferenceId > 0) {
    $certificateTypesStmt = db()->prepare(
        "SELECT id, name
         FROM certificate_types
         WHERE conference_id = :conference_id
           AND is_active = 1
           AND COALESCE(template_path, '') <> ''
         ORDER BY name ASC"
    );
    $certificateTypesStmt->execute(['conference_id' => $selectedConferenceId]);
    $certificateTypes = $certificateTypesStmt->fetchAll();
}

$contactName = trim((string) ($_POST['contact_name'] ?? ''));
$contactEmail = trim((string) ($_POST['contact_email'] ?? ''));
$contactMobileNumber = trim((string) ($_POST['mobile_number'] ?? ''));
$contactCategory = trim((string) ($_POST['contact_category'] ?? ''));
$contactMessage = trim((string) ($_POST['contact_message'] ?? ''));

$extractNameList = null;
$extractNameList = static function ($value) use (&$extractNameList): array {
    if (is_array($value)) {
        $result = [];
        foreach ($value as $item) {
            if (is_scalar($item)) {
                $candidate = trim((string) $item);
                if ($candidate !== '') {
                    $result[] = $candidate;
                }
            }
        }

        return $result;
    }

    if (!is_scalar($value)) {
        return [];
    }

    $raw = trim((string) $value);
    if ($raw === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        return $extractNameList($decoded);
    }

    $parts = preg_split('/,|;|\||\r\n|\n/', $raw) ?: [];
    $result = [];
    foreach ($parts as $part) {
        $candidate = trim((string) $part);
        if ($candidate !== '') {
            $result[] = $candidate;
        }
    }

    return $result;
};

$appendUniqueName = static function (array &$names, string $candidate): void {
    $candidate = trim($candidate);
    if ($candidate === '') {
        return;
    }

    $normalizedCandidate = strtolower($candidate);
    foreach ($names as $existing) {
        if (strtolower((string) $existing) === $normalizedCandidate) {
            return;
        }
    }

    $names[] = $candidate;
};

$appendNamesFromSource = static function (array &$names, array $source) use ($extractNameList, $appendUniqueName): void {
    $priorityKeys = ['authors', 'participants', 'coauthors', 'co_authors', 'group_names', 'participant_names', 'names'];
    foreach ($priorityKeys as $groupKey) {
        if (!array_key_exists($groupKey, $source)) {
            continue;
        }

        foreach ($extractNameList($source[$groupKey]) as $value) {
            $appendUniqueName($names, $value);
        }
    }

    $indexed = [];
    foreach ($source as $sourceKey => $sourceValue) {
        if (!is_string($sourceKey)) {
            continue;
        }

        $normalizedKey = strtolower(trim($sourceKey));
        if (preg_match('/^(author|authors|participant|participants|coauthor|coauthors|co_author|co_authors)(\d+)$/', $normalizedKey, $match) !== 1) {
            continue;
        }

        $indexed[] = [
            'index' => (int) $match[2],
            'value' => $sourceValue,
        ];
    }

    usort($indexed, static fn (array $left, array $right): int => $left['index'] <=> $right['index']);
    foreach ($indexed as $indexedRow) {
        foreach ($extractNameList($indexedRow['value']) as $value) {
            $appendUniqueName($names, $value);
        }
    }
};

$collectParticipantNames = static function (array $row) use ($appendUniqueName, $appendNamesFromSource): array {
    $names = [];
    $appendUniqueName($names, (string) ($row['name'] ?? ''));

    $extra = [];
    if (!empty($row['extra_json'])) {
        $decoded = json_decode((string) $row['extra_json'], true);
        if (is_array($decoded)) {
            $extra = $decoded;
        }
    }

    $appendNamesFromSource($names, $row);
    $appendNamesFromSource($names, $extra);

    return $names;
};

// AJAX search endpoint - only generated certificates are returned
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    ob_start();
    $q = trim((string) ($_GET['q'] ?? ''));
    $typeId = (int) ($_GET['type_id'] ?? 0);
    $conferenceId = (int) ($_GET['conference_id'] ?? $selectedConferenceId);

    try {
        $results = [];
        if ($conferenceId > 0 && $q !== '') {
            $tokens = preg_split('/\s+/', $q) ?: [$q];
            $where = [
                'p.conference_id = :conference_id',
                "COALESCE(gc.pdf_path, '') <> ''",
                'ct.is_active = 1',
                "COALESCE(ct.template_path, '') <> ''",
            ];
            $params = ['conference_id' => $conferenceId];

            if ($typeId > 0) {
                $where[] = 'p.certificate_type_id = :type_id';
                $params['type_id'] = $typeId;
            }

            foreach ($tokens as $i => $token) {
                $key = 'tok' . $i;
                $where[] = "(CONVERT(CONCAT_WS(' ', COALESCE(p.name, ''), COALESCE(p.title, ''), COALESCE(p.email, ''), COALESCE(CAST(p.extra_json AS CHAR), '')) USING utf8mb4) COLLATE utf8mb4_general_ci LIKE :$key)";
                $params[$key] = '%' . $token . '%';
            }

            $sql = 'SELECT p.id, p.name, p.title, p.email, p.extra_json, ct.name AS category, gc.pdf_path
                    FROM participants p
                    INNER JOIN certificate_types ct ON ct.id = p.certificate_type_id
                    INNER JOIN generated_certificates gc ON gc.participant_id = p.id
                    WHERE ' . implode(' AND ', $where) . '
                    ORDER BY gc.generated_at DESC, p.id DESC
                    LIMIT 25';

            $stmt = db()->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();

            foreach ($rows as $r) {
                $participantNames = $collectParticipantNames($r);
                $namesDisplay = $participantNames !== []
                    ? implode(', ', $participantNames)
                    : (trim((string) $r['name']) !== '' ? (string) $r['name'] : (string) $r['title']);

                $results[] = [
                    'id' => (int) $r['id'],
                    'label' => $namesDisplay,
                    'name' => (string) $r['name'],
                    'names' => $participantNames,
                    'names_display' => $namesDisplay,
                    'title' => (string) $r['title'],
                    'email' => (string) $r['email'],
                    'category' => (string) $r['category'],
                    'pdf' => (string) ($r['pdf_path'] ?? ''),
                ];
            }
        }

        $buffer = ob_get_clean();
        $payload = ['results' => $results];
        if (trim((string) $buffer) !== '') {
            $payload['debug_html'] = $buffer;
        }

        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    } catch (\Throwable $e) {
        $buffer = ob_get_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'error' => $e->getMessage(),
            'debug_html' => $buffer,
            'results' => [],
        ]);
        exit;
    }
}

if (is_post_request()) {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'download') {
        $participantId = (int) ($_POST['participant_id'] ?? 0);

        if ($participantId <= 0) {
            $pageAlerts[] = ['type' => 'error', 'message' => 'Please select a generated certificate first.'];
        } else {
            $stmt = db()->prepare(
                'SELECT p.id, p.name, p.conference_id, gc.pdf_path
                 FROM participants p
                 INNER JOIN generated_certificates gc ON gc.participant_id = p.id
                 WHERE p.id = :participant_id
                   AND p.conference_id = :conference_id
                 LIMIT 1'
            );
            $stmt->execute([
                'participant_id' => $participantId,
                'conference_id' => $selectedConferenceId,
            ]);
            $row = $stmt->fetch();

            if ($row === false) {
                $pageAlerts[] = ['type' => 'error', 'message' => 'Generated certificate not found for selected participant.'];
            } else {
                $relative = (string) ($row['pdf_path'] ?? '');
                $absolute = APP_ROOT . '/' . ltrim(str_replace('\\', '/', $relative), '/');

                if ($relative === '' || !is_file($absolute)) {
                    $pageAlerts[] = ['type' => 'error', 'message' => 'Generated PDF file is missing on server.'];
                } else {
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

                    $fileName = safe_filename((string) $row['name']) . '_' . (int) $row['id'] . '.pdf';
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename="' . $fileName . '"');
                    header('Content-Length: ' . filesize($absolute));
                    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
                    header('Pragma: no-cache');
                    header('Expires: 0');
                    readfile($absolute);
                    exit;
                }
            }
        }
    }

    if ($action === 'contact_submit') {
        if ($contactName === '' || $contactEmail === '' || $contactMobileNumber === '' || $contactCategory === '' || $contactMessage === '') {
            $pageAlerts[] = ['type' => 'error', 'message' => 'Please fill Name, Email, Mobile Number, Category, and Message in the contact form.'];
        } elseif (!filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
            $pageAlerts[] = ['type' => 'error', 'message' => 'Please enter a valid email address.'];
        } elseif (preg_match('/^[0-9+\-\s()]{7,20}$/', $contactMobileNumber) !== 1) {
            $pageAlerts[] = ['type' => 'error', 'message' => 'Please enter a valid mobile number.'];
        } else {
            try {
                $insert = db()->prepare(
                    'INSERT INTO contact_messages (conference_id, contact_name, contact_email, mobile_number, category, message_text, created_at)
                     VALUES (:conference_id, :contact_name, :contact_email, :mobile_number, :category, :message_text, NOW())'
                );
                $insert->execute([
                    'conference_id' => $selectedConferenceId > 0 ? $selectedConferenceId : null,
                    'contact_name' => $contactName,
                    'contact_email' => $contactEmail,
                    'mobile_number' => $contactMobileNumber,
                    'category' => $contactCategory,
                    'message_text' => $contactMessage,
                ]);

                $pageAlerts[] = ['type' => 'success', 'message' => 'Thank you. Your message has been submitted.'];
                // Preserve submitted values for email sending
                $submittedName = $contactName;
                $submittedEmail = $contactEmail;
                $submittedMobile = $contactMobileNumber;
                $submittedCategory = $contactCategory;
                $submittedMessage = $contactMessage;
                $contactName = '';
                $contactEmail = '';
                $contactMobileNumber = '';
                $contactCategory = '';
                $contactMessage = '';
                // Send notification email to site owner via PHPMailer (EmailService)
                try {
                    $to = 'upparactechnology@gmail.com';
                    $subject = 'Contact form: ' . ($submittedName !== '' ? $submittedName : 'No name provided');
                    $bodyLines = [];
                    $bodyLines[] = 'Name: ' . $submittedName;
                    $bodyLines[] = 'Email: ' . $submittedEmail;
                    $bodyLines[] = 'Mobile: ' . $submittedMobile;
                    $bodyLines[] = 'Category: ' . $submittedCategory;
                    $bodyLines[] = '';
                    $bodyLines[] = 'Message:';
                    $bodyLines[] = $submittedMessage;
                    $body = implode("\r\n", $bodyLines);

                    $emailService = new \App\Services\EmailService(db());
                    $mailer = $emailService->createMailer();

                    // Prepare mail
                    $mailer->clearAddresses();
                    $mailer->clearAttachments();
                    $mailer->addAddress($to);
                    $mailer->Subject = $subject;
                    $mailer->isHTML(false);
                    $mailer->Body = $body;

                    // Set Reply-To to the user who submitted the form
                    if ($submittedEmail !== '') {
                        try {
                            $mailer->addReplyTo($submittedEmail, $submittedName ?: '');
                        } catch (\Throwable $ignored) {
                        }
                    }

                    $sentOk = false;
                    try {
                        $mailer->send();
                        $sentOk = true;
                    } catch (\Throwable $e) {
                        $sentOk = false;
                        $err = $e->getMessage();
                    }

                    $logDir = APP_ROOT . '/storage';
                    if (!is_dir($logDir)) {
                        @mkdir($logDir, 0777, true);
                    }
                    $logFile = $logDir . '/contact_email.log';
                    $entry = [
                        'at' => date('Y-m-d H:i:s'),
                        'to' => $to,
                        'from' => $mailer->From ?? setting('smtp_from_email', ''),
                        'contact_email' => $submittedEmail,
                        'sent' => $sentOk,
                        'error' => $sentOk ? null : ($err ?? 'unknown'),
                    ];
                    @file_put_contents($logFile, json_encode($entry, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND | LOCK_EX);
                } catch (\Throwable $e) {
                    $logDir = APP_ROOT . '/storage';
                    if (!is_dir($logDir)) {
                        @mkdir($logDir, 0777, true);
                    }
                    @file_put_contents($logDir . '/contact_email_error.log', '[' . date('Y-m-d H:i:s') . '] ' . $e->getMessage() . PHP_EOL, FILE_APPEND | LOCK_EX);
                }
            } catch (\Throwable $e) {
                $pageAlerts[] = ['type' => 'error', 'message' => 'Unable to submit message right now. Please try again.'];
            }
        }
    }
}

render_view('download_certificate.php', [
    'pageTitle' => 'Download Certificate',
    'conferences' => $conferences,
    'conference_id' => $selectedConferenceId,
    'certificateTypes' => $certificateTypes,
    'pageAlerts' => $pageAlerts,
    'contactName' => $contactName,
    'contactEmail' => $contactEmail,
    'contactMobileNumber' => $contactMobileNumber,
    'contactCategory' => $contactCategory,
    'contactMessage' => $contactMessage,
]);
