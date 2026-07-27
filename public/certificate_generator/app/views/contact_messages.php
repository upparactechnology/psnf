<?php /** @var array $conferences */
/** @var int $selectedConferenceId */
/** @var string $search */
/** @var string $selectedStatusFilter */
/** @var string $categoryFilter */
/** @var array $messages */
/** @var bool $statusColumnExists */
/** @var array $statusOptions */ ?>
<section class="panel">
    <h3>Contact Messages</h3>

    <form method="get" class="form-grid two">
        <input type="hidden" name="page" value="contact-messages">

        <label>
            Conference
            <select name="conference_id">
                <option value="0" <?= (int) $selectedConferenceId === 0 ? 'selected' : '' ?>>All Accessible Conferences</option>
                <?php foreach ($conferences as $conf): ?>
                    <option value="<?= e((string) $conf['id']) ?>" <?= (int) $conf['id'] === (int) $selectedConferenceId ? 'selected' : '' ?>>
                        <?= e($conf['name']) ?> (<?= e((string) $conf['year']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Search
            <input type="text" name="search" value="<?= e((string) $search) ?>" placeholder="Search by name, category, or message">
        </label>

        <label>
            Status
            <select name="status">
                <option value="">All Statuses</option>
                <?php if ($statusColumnExists): ?>
                    <?php foreach ($statusOptions as $option): ?>
                        <option value="<?= e((string) $option) ?>" <?= strtolower((string) $selectedStatusFilter) === strtolower((string) $option) ? 'selected' : '' ?>>
                            <?= e(strtoupper((string) $option)) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </label>

        <label>
            Category
            <input type="text" name="category" value="<?= e((string) ($categoryFilter ?? '')) ?>" placeholder="Filter by category">
        </label>

        <div class="inline-actions align-end-actions">
            <button type="submit">Apply</button>
            <a class="button button-muted" href="<?= e(url('contact-messages')) ?>">Reset</a>
        </div>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Mobile Number</th>
                    <th>Category</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Conference</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($messages === []): ?>
                <tr>
                    <td colspan="10">No contact messages found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($messages as $row): ?>
                    <?php $rowStatus = strtolower((string) ($row['message_status'] ?? 'unread')); ?>
                    <?php
                    $defaultSubject = 'Re: ' . (string) ($row['category'] ?? 'Contact Message');
                    $defaultMessage = "Hello " . (string) ($row['contact_name'] ?? '') . ",\n\nThank you for contacting us. We received your message and will assist you.\n\nRegards,\nSupport Team";
                    ?>
                    <tr>
                        <td><?= e((string) ($row['id'] ?? '')) ?></td>
                        <td><?= e((string) ($row['contact_name'] ?? '')) ?></td>
                        <td><?= e((string) ($row['contact_email'] ?? '')) ?></td>
                        <td><?= e((string) ($row['mobile_number'] ?? '')) ?></td>
                        <td><?= e((string) ($row['category'] ?? '')) ?></td>
                        <td><?= nl2br(e((string) ($row['message_text'] ?? ''))) ?></td>
                        <td>
                            <?php if ($statusColumnExists): ?>
                                <strong><?= e(strtoupper($rowStatus)) ?></strong>
                            <?php else: ?>
                                <span class="small">Migration pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($row['conference_name'])): ?>
                                <?= e((string) $row['conference_name']) ?> (<?= e((string) $row['conference_year']) ?>)
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td><?= e((string) ($row['created_at'] ?? '')) ?></td>
                        <td>
                            <?php if ($statusColumnExists): ?>
                                <form method="post" class="inline-actions" style="margin-bottom: 8px;">
                                    <?= csrf_input(); ?>
                                    <input type="hidden" name="action" value="mark_status">
                                    <input type="hidden" name="message_id" value="<?= (int) ($row['id'] ?? 0) ?>">
                                    <select name="message_status">
                                        <?php foreach ($statusOptions as $option): ?>
                                            <option value="<?= e((string) $option) ?>" <?= $rowStatus === $option ? 'selected' : '' ?>>
                                                <?= e(strtoupper((string) $option)) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="button button-muted">Update</button>
                                </form>
                            <?php endif; ?>

                            <?php if (!empty($row['contact_email'])): ?>
                                <button
                                    type="button"
                                    class="button button-muted js-reply-open"
                                    data-message-id="<?= (int) ($row['id'] ?? 0) ?>"
                                    data-to-email="<?= e((string) ($row['contact_email'] ?? '')) ?>"
                                    data-subject="<?= e($defaultSubject) ?>"
                                    data-message="<?= e($defaultMessage) ?>"
                                    data-after-status="<?= $statusColumnExists ? e((string) ($rowStatus !== '' ? $rowStatus : 'replied')) : 'replied' ?>"
                                >
                                    Reply / Send Mail
                                </button>
                            <?php else: ?>
                                <span class="small">No email available</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<div id="replyModalOverlay" class="modal-overlay" aria-hidden="true">
    <div class="modal">
        <div class="inline-actions" style="justify-content: space-between; margin-bottom: 10px;">
            <h3 style="margin: 0;">Reply / Send Mail</h3>
            <button type="button" class="button button-muted" id="replyModalClose">Close</button>
        </div>

        <form method="post" enctype="multipart/form-data" class="form-grid" id="replyModalForm">
            <?= csrf_input(); ?>
            <input type="hidden" name="action" value="send_reply">
            <input type="hidden" name="message_id" id="reply_message_id" value="">

            <label>
                To Email
                <input type="email" name="to_email" id="reply_to_email" required>
            </label>

            <label>
                Subject
                <input type="text" name="email_subject" id="reply_subject" required>
            </label>

            <label>
                Message
                <textarea name="email_message" id="reply_message" rows="7" required></textarea>
            </label>

            <label>
                Attach PDF (optional)
                <input type="file" name="attachment_pdf" accept=".pdf,application/pdf">
            </label>

            <?php if ($statusColumnExists): ?>
                <label>
                    Status After Send
                    <select name="after_send_status" id="reply_after_status">
                        <option value="replied">REPLIED</option>
                        <option value="sent">SENT</option>
                        <option value="updated">UPDATED</option>
                        <option value="read">READ</option>
                        <option value="unread">UNREAD</option>
                    </select>
                </label>
            <?php endif; ?>

            <div>
                <button type="submit">Send Message</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    var modalOverlay = document.getElementById("replyModalOverlay");
    var closeBtn = document.getElementById("replyModalClose");
    var openButtons = document.querySelectorAll(".js-reply-open");
    var inputMessageId = document.getElementById("reply_message_id");
    var inputTo = document.getElementById("reply_to_email");
    var inputSubject = document.getElementById("reply_subject");
    var inputMessage = document.getElementById("reply_message");
    var inputAfterStatus = document.getElementById("reply_after_status");

    if (!modalOverlay || !closeBtn || !inputMessageId || !inputTo || !inputSubject || !inputMessage || openButtons.length === 0) {
        return;
    }

    function openModal(button) {
        inputMessageId.value = button.getAttribute("data-message-id") || "";
        inputTo.value = button.getAttribute("data-to-email") || "";
        inputSubject.value = button.getAttribute("data-subject") || "";
        inputMessage.value = button.getAttribute("data-message") || "";

        if (inputAfterStatus) {
            inputAfterStatus.value = button.getAttribute("data-after-status") || "replied";
        }

        modalOverlay.classList.add("open");
        modalOverlay.setAttribute("aria-hidden", "false");
        inputTo.focus();
    }

    function closeModal() {
        modalOverlay.classList.remove("open");
        modalOverlay.setAttribute("aria-hidden", "true");
    }

    openButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            openModal(button);
        });
    });

    closeBtn.addEventListener("click", closeModal);

    modalOverlay.addEventListener("click", function (event) {
        if (event.target === modalOverlay) {
            closeModal();
        }
    });

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape" && modalOverlay.classList.contains("open")) {
            closeModal();
        }
    });
})();
</script>
