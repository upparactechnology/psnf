<?php
declare(strict_types=1);

use App\Services\EmailService;

$conferences = fetch_conferences_for_user();
$selectedConferenceId = (int) ($_GET['conference_id'] ?? 0);
$search = trim((string) ($_GET['search'] ?? ''));
$selectedStatusFilter = strtolower(trim((string) ($_GET['status'] ?? '')));
$categoryFilter = trim((string) ($_GET['category'] ?? ''));

if ($selectedConferenceId > 0 && !user_has_conference_access($selectedConferenceId)) {
    $selectedConferenceId = 0;
    flash('warning', 'Conference access denied for selected filter. Showing all accessible messages.');
}

$statusColumnExists = false;
try {
    $colStmt = db()->query("SHOW COLUMNS FROM contact_messages LIKE 'message_status'");
    $statusColumnExists = $colStmt !== false && $colStmt->fetch() !== false;
} catch (\Throwable $e) {
    $statusColumnExists = false;
}

$statusOptions = ['unread', 'read', 'sent', 'replied', 'updated'];

if (is_post_request()) {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');
    $messageId = (int) ($_POST['message_id'] ?? 0);

    $baseParams = ['page' => 'contact-messages'];
    if ($selectedConferenceId > 0) {
        $baseParams['conference_id'] = (string) $selectedConferenceId;
    }
    if ($search !== '') {
        $baseParams['search'] = $search;
    }
    if ($selectedStatusFilter !== '') {
        $baseParams['status'] = $selectedStatusFilter;
    }
    if ($categoryFilter !== '') {
        $baseParams['category'] = $categoryFilter;
    }

    if ($messageId <= 0) {
        flash('error', 'Invalid message selected.');
        redirect('index.php?' . http_build_query($baseParams));
    }

    $messageStmt = db()->prepare(
        'SELECT cm.*
         FROM contact_messages cm
         WHERE cm.id = :id
         LIMIT 1'
    );
    $messageStmt->execute(['id' => $messageId]);
    $message = $messageStmt->fetch();

    if ($message === false) {
        flash('error', 'Message not found.');
        redirect('index.php?' . http_build_query($baseParams));
    }

    $messageConferenceId = (int) ($message['conference_id'] ?? 0);
    if ($messageConferenceId > 0 && !user_has_conference_access($messageConferenceId)) {
        flash('error', 'You do not have access to this message conference.');
        redirect('index.php?' . http_build_query($baseParams));
    }

    if ($action === 'mark_status') {
        if (!$statusColumnExists) {
            flash('error', 'Status column is not available yet. Please run latest migration.');
            redirect('index.php?' . http_build_query($baseParams));
        }

        $newStatus = strtolower(trim((string) ($_POST['message_status'] ?? '')));
        if (!in_array($newStatus, $statusOptions, true)) {
            flash('error', 'Invalid status selected.');
            redirect('index.php?' . http_build_query($baseParams));
        }

        $updateStmt = db()->prepare(
            'UPDATE contact_messages
             SET message_status = :status, last_action_at = NOW()
             WHERE id = :id'
        );
        $updateStmt->execute([
            'status' => $newStatus,
            'id' => $messageId,
        ]);

        flash('success', 'Message status updated to ' . strtoupper($newStatus) . '.');
        redirect('index.php?' . http_build_query($baseParams));
    }

    if ($action === 'send_reply') {
        $toEmail = trim((string) ($_POST['to_email'] ?? ($message['contact_email'] ?? '')));
        $subject = trim((string) ($_POST['email_subject'] ?? ''));
        $body = trim((string) ($_POST['email_message'] ?? ''));
        $markStatus = strtolower(trim((string) ($_POST['after_send_status'] ?? 'replied')));
        if (!in_array($markStatus, $statusOptions, true)) {
            $markStatus = 'replied';
        }

        if ($toEmail === '' || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Valid recipient email is required.');
            redirect('index.php?' . http_build_query($baseParams));
        }
        if ($subject === '' || $body === '') {
            flash('error', 'Subject and message body are required.');
            redirect('index.php?' . http_build_query($baseParams));
        }

        $attachmentAbsolute = null;
        if (isset($_FILES['attachment_pdf']) && is_array($_FILES['attachment_pdf']) && (int) ($_FILES['attachment_pdf']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $upload = $_FILES['attachment_pdf'];
            $errorCode = (int) ($upload['error'] ?? UPLOAD_ERR_NO_FILE);
            if ($errorCode !== UPLOAD_ERR_OK) {
                flash('error', 'Attachment upload failed.');
                redirect('index.php?' . http_build_query($baseParams));
            }

            $tmpPath = (string) ($upload['tmp_name'] ?? '');
            $originalName = (string) ($upload['name'] ?? 'attachment.pdf');
            $extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
            if ($extension !== 'pdf') {
                flash('error', 'Only PDF attachment is allowed.');
                redirect('index.php?' . http_build_query($baseParams));
            }

            $uploadsDir = APP_ROOT . '/uploads/contact_attachments';
            if (!is_dir($uploadsDir)) {
                @mkdir($uploadsDir, 0777, true);
            }

            $targetName = date('Ymd_His') . '_' . $messageId . '_' . safe_filename(pathinfo($originalName, PATHINFO_FILENAME)) . '.pdf';
            $targetPath = $uploadsDir . '/' . $targetName;
            if (!@move_uploaded_file($tmpPath, $targetPath)) {
                flash('error', 'Unable to save uploaded PDF.');
                redirect('index.php?' . http_build_query($baseParams));
            }
            $attachmentAbsolute = $targetPath;
        }

        $emailService = new EmailService(db());
        $result = $emailService->sendDirectMessage(
            $toEmail,
            (string) ($message['contact_name'] ?? ''),
            $subject,
            $body,
            $attachmentAbsolute
        );

        if (!$result['ok']) {
            flash('error', 'Email send failed: ' . (string) ($result['error'] ?? 'Unknown error'));
            redirect('index.php?' . http_build_query($baseParams));
        }

        if ($statusColumnExists) {
            $updateStmt = db()->prepare(
                'UPDATE contact_messages
                 SET message_status = :status, last_action_at = NOW()
                 WHERE id = :id'
            );
            $updateStmt->execute([
                'status' => $markStatus,
                'id' => $messageId,
            ]);
        }

        flash('success', 'Email sent successfully.');
        redirect('index.php?' . http_build_query($baseParams));
    }
}

$where = ['1=1'];
$params = [];

if ($selectedConferenceId > 0) {
    $where[] = 'cm.conference_id = :conference_id';
    $params['conference_id'] = $selectedConferenceId;
}

if ($search !== '') {
    $where[] = '(cm.contact_name LIKE :search OR cm.contact_email LIKE :search OR cm.mobile_number LIKE :search OR cm.category LIKE :search OR cm.message_text LIKE :search)';
    $params['search'] = '%' . $search . '%';
}

if ($statusColumnExists && $selectedStatusFilter !== '' && in_array($selectedStatusFilter, $statusOptions, true)) {
    $where[] = 'LOWER(COALESCE(cm.message_status, \'unread\')) = :status_filter';
    $params['status_filter'] = $selectedStatusFilter;
}

if ($categoryFilter !== '') {
    $where[] = 'cm.category LIKE :category_filter';
    $params['category_filter'] = '%' . $categoryFilter . '%';
}

$sql =
    'SELECT cm.*, c.name AS conference_name, c.year AS conference_year
     FROM contact_messages cm
     LEFT JOIN conferences c ON c.id = cm.conference_id
     WHERE ' . implode(' AND ', $where) . '
     ORDER BY cm.id DESC
     LIMIT 500';

$messages = [];
try {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $messages = $stmt->fetchAll();
} catch (\Throwable $e) {
    flash('error', 'Unable to load contact messages. Please run the latest database migration.');
}

if ((string) ($_GET['export'] ?? '') === 'csv') {
    $exportStmt = db()->prepare(
        'SELECT cm.*, c.name AS conference_name, c.year AS conference_year
         FROM contact_messages cm
         LEFT JOIN conferences c ON c.id = cm.conference_id
         WHERE ' . implode(' AND ', $where) . '
         ORDER BY cm.id DESC'
    );
    $exportStmt->execute($params);
    $exportRows = $exportStmt->fetchAll();

    $fileName = 'contact_messages_' . date('Ymd_His') . '.csv';
    stream_csv_download($fileName, ['ID', 'Conference', 'Name', 'Email', 'Mobile', 'Category', 'Message', 'Created At'], $exportRows, static function (array $row): array {
        return [
            (string) ($row['id'] ?? ''),
            trim((string) ($row['conference_name'] ?? '')) . (($row['conference_year'] ?? '') !== '' ? ' (' . (string) $row['conference_year'] . ')' : ''),
            (string) ($row['contact_name'] ?? ''),
            (string) ($row['contact_email'] ?? ''),
            (string) ($row['mobile_number'] ?? ''),
            (string) ($row['category'] ?? ''),
            (string) ($row['message_text'] ?? ''),
            (string) ($row['created_at'] ?? ''),
        ];
    });
}

render_view('contact_messages.php', [
    'pageTitle' => 'Contact Messages',
    'conferences' => $conferences,
    'selectedConferenceId' => $selectedConferenceId,
    'search' => $search,
    'selectedStatusFilter' => $selectedStatusFilter,
    'categoryFilter' => $categoryFilter,
    'messages' => $messages,
    'statusColumnExists' => $statusColumnExists,
    'statusOptions' => $statusOptions,
    'headerMeta' => [
        'actions' => [
            [
                'label' => 'Export CSV',
                'url' => url('contact-messages', ['conference_id' => $selectedConferenceId, 'search' => $search, 'status' => $selectedStatusFilter, 'category' => $categoryFilter, 'export' => 'csv']),
                'class' => 'button-muted',
            ],
        ],
    ],
]);
