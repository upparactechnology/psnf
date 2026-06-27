<?php
/** @var array<int, array<string, mixed>> $conferences */
/** @var array<int, array<string, mixed>> $certificateTypes */
/** @var array<int, array<string, mixed>> $emailTemplates */
/** @var array<int, array<string, mixed>> $emailSchedules */
/** @var array<int, array<string, mixed>> $emailLogs */
/** @var string $selectedTemplateKey */
/** @var string $templateName */
/** @var string $subjectTemplate */
/** @var string $messageTemplate */
/** @var string $ccTemplate */
/** @var string $bccTemplate */
/** @var int $bulkEmailDelayMin */
/** @var int $bulkEmailDelayMax */
/** @var int $selectedConferenceId */
/** @var int $selectedTypeId */
?>
<section class="grid-2">
    <article class="panel">
        <h3>Email Target</h3>
        <form method="get" class="form-grid two">
            <input type="hidden" name="page" value="emails">
            <input type="hidden" name="template_key" value="<?= e((string) $selectedTemplateKey) ?>">

            <!-- Conference filter removed -->

            <label>
                Category
                <select name="certificate_type_id" onchange="this.form.submit()">
                    <option value="0">All Categories</option>
                    <?php foreach ($certificateTypes as $type): ?>
                        <option value="<?= e((string) $type['id']) ?>" <?= (int) $type['id'] === (int) $selectedTypeId ? 'selected' : '' ?>>
                            <?= e($type['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
        </form>

        <p class="small">Template variables: {{name}}, {{certificate_type}}, {{conference}}, {{year}}</p>
    </article>

    <article class="panel">
        <h3>SMTP Status</h3>
        <p>Configure SMTP in System Settings for reliable bulk email sending through PHPMailer.</p>
        <ul>
            <li>Host: <?= e(setting('smtp_host', 'not set') ?? 'not set') ?></li>
            <li>Port: <?= e(setting('smtp_port', 'not set') ?? 'not set') ?></li>
            <li>From: <?= e(setting('smtp_from_email', 'not set') ?? 'not set') ?></li>
        </ul>

        <form method="post" class="form-inline top-gap-sm">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <label>Test email</label>
            <input type="email" name="test_email" placeholder="you@example.com" required>
            <button type="submit" name="action" value="send_test" class="button">Send Test</button>
        </form>
    </article>
</section>

<section class="panel">
    <h3>Email Template & Send</h3>
    <form method="post" class="form-grid">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="conference_id" value="<?= e((string) $selectedConferenceId) ?>">
        <input type="hidden" name="certificate_type_id" value="<?= e((string) $selectedTypeId) ?>">
        <input type="hidden" name="template_key" id="email_template_key" value="<?= e((string) $selectedTemplateKey) ?>">

        <label>
            Template Name
            <input type="text" name="template_name" id="email_template_name" value="<?= e((string) $templateName) ?>" required>
        </label>

        <label>
            Saved Templates
            <select id="email_template_selector" name="template_selector">
                <?php foreach ($emailTemplates as $template): ?>
                    <option
                        value="<?= e((string) ($template['key'] ?? '')) ?>"
                        data-name="<?= e((string) ($template['name'] ?? '')) ?>"
                        data-subject="<?= e((string) ($template['subject_template'] ?? '')) ?>"
                        data-message="<?= e((string) ($template['message_template'] ?? '')) ?>"
                        data-cc="<?= e((string) ($template['cc_template'] ?? '')) ?>"
                        data-bcc="<?= e((string) ($template['bcc_template'] ?? '')) ?>"
                        <?= (string) ($template['key'] ?? '') === (string) $selectedTemplateKey ? 'selected' : '' ?>
                    >
                        <?= e((string) ($template['name'] ?? 'Template')) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Email Subject
            <input type="text" id="email_subject_template" name="subject_template" value="<?= e($subjectTemplate) ?>" required>
        </label>

        <label>
            Email Message
            <textarea id="email_message_template" name="message_template" rows="8" required><?= e($messageTemplate) ?></textarea>
        </label>

        <label>
            CC (optional)
            <input type="text" id="email_cc_template" name="cc_template" value="<?= e((string) ($ccTemplate ?? '')) ?>" placeholder="cc1@example.com, cc2@example.com">
        </label>

        <label>
            BCC (optional)
            <input type="text" id="email_bcc_template" name="bcc_template" value="<?= e((string) ($bccTemplate ?? '')) ?>" placeholder="bcc1@example.com, bcc2@example.com">
        </label>

        <p class="small">Bulk pacing: random delay between <?= e((string) $bulkEmailDelayMin) ?> and <?= e((string) $bulkEmailDelayMax) ?> seconds per recipient.</p>

        <div class="inline-actions">
            <button type="submit" name="action" value="save_template" class="button button-muted">Save Template</button>
            <button type="submit" name="action" value="save_template_new" class="button button-muted">Save As New</button>
            <button type="submit" name="action" value="delete_template" class="button button-danger">Delete Template</button>
            <button type="submit" name="action" value="send_bulk">Send Bulk Emails</button>
        </div>
    </form>
</section>

<section class="panel">
    <h3>Scheduled Sends (1 per minute)</h3>
    <p class="small">Creates an automated campaign that sends one certificate per minute for the selected category. Uses the selected saved template.</p>

    <form method="post" class="form-grid two">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="conference_id" value="<?= e((string) $selectedConferenceId) ?>">

        <label>
            Category
            <select name="schedule_certificate_type_id">
                <option value="0">All Categories</option>
                <?php foreach ($certificateTypes as $type): ?>
                    <option value="<?= e((string) $type['id']) ?>" <?= (int) $type['id'] === (int) $selectedTypeId ? 'selected' : '' ?>>
                        <?= e($type['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Template
            <select name="schedule_template_key">
                <?php foreach ($emailTemplates as $template): ?>
                    <option value="<?= e((string) ($template['key'] ?? '')) ?>" <?= (string) ($template['key'] ?? '') === (string) $selectedTemplateKey ? 'selected' : '' ?>>
                        <?= e((string) ($template['name'] ?? 'Template')) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Interval
            <input type="text" value="Every 1 minute" disabled>
        </label>

        <div class="inline-actions">
            <button type="submit" name="action" value="create_schedule">Start Schedule</button>
        </div>
    </form>

    <div class="table-wrap top-gap-sm">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Category</th>
                    <th>Template</th>
                    <th>Status</th>
                    <th>Sent</th>
                    <th>Failed</th>
                    <th>Last Sent</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($emailSchedules === []): ?>
                <tr>
                    <td colspan="8">No schedules yet.</td>
                </tr>
            <?php else: ?>
                <?php
                $templateNameByKey = [];
                foreach ($emailTemplates as $template) {
                    $key = (string) ($template['key'] ?? '');
                    if ($key !== '') {
                        $templateNameByKey[$key] = (string) ($template['name'] ?? $key);
                    }
                }
                ?>
                <?php foreach ($emailSchedules as $schedule): ?>
                    <?php
                    $status = (string) ($schedule['status'] ?? 'paused');
                    $badgeClass = $status === 'active' ? 'badge-success' : 'badge-danger';
                    $templateKey = (string) ($schedule['template_key'] ?? '');
                    $templateLabel = $templateNameByKey[$templateKey] ?? ($templateKey !== '' ? $templateKey : 'Custom');
                    ?>
                    <tr>
                        <td><?= e((string) $schedule['id']) ?></td>
                        <td><?= e((string) ($schedule['certificate_type_name'] ?? 'All Categories')) ?></td>
                        <td><?= e($templateLabel) ?></td>
                        <td><span class="badge <?= e($badgeClass) ?>"><?= e($status) ?></span></td>
                        <td><?= e((string) ($schedule['sent_count'] ?? 0)) ?></td>
                        <td><?= e((string) ($schedule['failed_count'] ?? 0)) ?></td>
                        <td><?= e((string) ($schedule['last_sent_at'] ?? '-')) ?></td>
                        <td>
                            <form method="post" class="form-inline">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="conference_id" value="<?= e((string) $selectedConferenceId) ?>">
                                <input type="hidden" name="schedule_id" value="<?= e((string) $schedule['id']) ?>">
                                <?php if ($status === 'active'): ?>
                                    <input type="hidden" name="next_status" value="paused">
                                    <button type="submit" name="action" value="toggle_schedule" class="button button-muted">Pause</button>
                                <?php else: ?>
                                    <input type="hidden" name="next_status" value="active">
                                    <button type="submit" name="action" value="toggle_schedule" class="button">Resume</button>
                                <?php endif; ?>
                                <button type="submit" name="action" value="reset_schedule" class="button button-muted">Reset</button>
                                <button type="submit" name="action" value="delete_schedule" class="button button-danger" onclick="return confirm('Delete this schedule?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="panel">
    <h3>Email Logs</h3>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Participant</th>
                    <th>To</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Error</th>
                    <th>Sent At</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($emailLogs === []): ?>
                <tr>
                    <td colspan="7">No email logs yet.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($emailLogs as $row): ?>
                    <tr>
                        <td><?= e((string) $row['id']) ?></td>
                        <td><?= e($row['participant_name']) ?></td>
                        <td><?= e($row['to_email']) ?></td>
                        <td><?= e($row['category_name']) ?></td>
                        <td>
                            <?php if ($row['status'] === 'sent'): ?>
                                <span class="badge badge-success">sent</span>
                            <?php else: ?>
                                <span class="badge badge-danger">failed</span>
                            <?php endif; ?>
                        </td>
                        <td class="small"><?= e((string) $row['error_message']) ?></td>
                        <td><?= e((string) $row['sent_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<script>
(function () {
    var selector = document.getElementById('email_template_selector');
    var templateKey = document.getElementById('email_template_key');
    var templateName = document.getElementById('email_template_name');
    var subject = document.getElementById('email_subject_template');
    var message = document.getElementById('email_message_template');
    var cc = document.getElementById('email_cc_template');
    var bcc = document.getElementById('email_bcc_template');

    if (!selector || !templateKey || !templateName || !subject || !message || !cc || !bcc) {
        return;
    }

    var applyTemplate = function () {
        var option = selector.options[selector.selectedIndex];
        if (!option) {
            return;
        }

        templateKey.value = option.value || '';
        templateName.value = option.getAttribute('data-name') || '';
        subject.value = option.getAttribute('data-subject') || '';
        message.value = option.getAttribute('data-message') || '';
        cc.value = option.getAttribute('data-cc') || '';
        bcc.value = option.getAttribute('data-bcc') || '';
    };

    selector.addEventListener('change', applyTemplate);
})();
</script>
