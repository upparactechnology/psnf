USE certificate_system;

ALTER TABLE certificate_types
    ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER is_custom;

UPDATE certificate_types
SET is_active = 1
WHERE is_active IS NULL;
