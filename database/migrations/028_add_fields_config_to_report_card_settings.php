<?php

declare(strict_types=1);

class AddFieldsConfigToReportCardSettings
{
    public function __construct(private \Core\Database $db) {}

    public function up(): void
    {
        $cols = $this->db->select("SHOW COLUMNS FROM `report_card_settings` LIKE 'fields_config'");
        if (empty($cols)) {
            $this->db->query("ALTER TABLE `report_card_settings` ADD COLUMN `fields_config` JSON DEFAULT NULL AFTER `trustees_config`");
        }
    }

    public function down(): void
    {
        $this->db->query("ALTER TABLE `report_card_settings` DROP COLUMN `fields_config`");
    }
}
