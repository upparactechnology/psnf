<?php

declare(strict_types=1);

class AddStampImageToReportCardSettings
{
    public function __construct(private \Core\Database $db) {}

    public function up(): void
    {
        $cols = $this->db->select("SHOW COLUMNS FROM report_card_settings LIKE 'stamp_image'");
        if (empty($cols)) {
            $this->db->query("ALTER TABLE `report_card_settings` ADD COLUMN `stamp_image` LONGTEXT DEFAULT NULL AFTER `stamp_text`");
        }
    }

    public function down(): void
    {
        $cols = $this->db->select("SHOW COLUMNS FROM report_card_settings LIKE 'stamp_image'");
        if (!empty($cols)) {
            $this->db->query("ALTER TABLE `report_card_settings` DROP COLUMN `stamp_image`");
        }
    }
}
