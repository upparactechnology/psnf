USE certificate_system;

ALTER TABLE contact_messages
    ADD COLUMN IF NOT EXISTS contact_email VARCHAR(190) NULL AFTER contact_name,
    ADD COLUMN IF NOT EXISTS mobile_number VARCHAR(30) NULL AFTER contact_email;
