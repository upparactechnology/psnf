USE certificate_system;

ALTER TABLE contact_messages
    ADD COLUMN IF NOT EXISTS message_status VARCHAR(30) NOT NULL DEFAULT 'unread' AFTER message_text,
    ADD COLUMN IF NOT EXISTS last_action_at DATETIME NULL AFTER message_status;

