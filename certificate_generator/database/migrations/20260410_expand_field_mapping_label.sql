-- Expand field_mappings.label so long template content is not truncated.
-- Add options_json to store rich text style settings for template text boxes.
USE certificate_system;

ALTER TABLE field_mappings
    MODIFY label TEXT NOT NULL;

ALTER TABLE field_mappings
    ADD COLUMN IF NOT EXISTS options_json TEXT NULL AFTER label;
