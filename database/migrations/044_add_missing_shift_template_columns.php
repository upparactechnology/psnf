<?php

declare(strict_types=1);

class AddMissingShiftTemplateColumns
{
    public function __construct(private \Core\Database $db) {}

    public function up(): void
    {
        // 1. Add missing columns to shift_templates
        $columns = $this->db->select("DESCRIBE shift_templates");
        $fields = array_column($columns, 'Field');

        if (!in_array('working_days_per_month', $fields)) {
            $this->db->query("ALTER TABLE shift_templates ADD COLUMN working_days_per_month int(11) DEFAULT 0");
        }
        if (!in_array('working_days_json', $fields)) {
            $this->db->query("ALTER TABLE shift_templates ADD COLUMN working_days_json text DEFAULT NULL");
        }
        if (!in_array('late_limit_count', $fields)) {
            $this->db->query("ALTER TABLE shift_templates ADD COLUMN late_limit_count int(11) DEFAULT 3");
        }
        if (!in_array('late_deduction_percent', $fields)) {
            $this->db->query("ALTER TABLE shift_templates ADD COLUMN late_deduction_percent decimal(5,2) DEFAULT 10.00");
        }
        if (!in_array('half_day_deduction_percent', $fields)) {
            $this->db->query("ALTER TABLE shift_templates ADD COLUMN half_day_deduction_percent decimal(5,2) DEFAULT 50.00");
        }
        if (!in_array('lec_grace_minutes', $fields)) {
            $this->db->query("ALTER TABLE shift_templates ADD COLUMN lec_grace_minutes int(11) DEFAULT 5");
        }

        // Update default row if empty or missing json
        $this->db->query("
            UPDATE shift_templates 
            SET working_days_json = '{\"01\":20,\"02\":12,\"03\":0,\"04\":0,\"05\":0,\"06\":0,\"07\":31,\"08\":0,\"09\":0,\"10\":0,\"11\":0,\"12\":0}' 
            WHERE id = 1 AND (working_days_json IS NULL OR working_days_json = '')
        ");

        // 2. Add assessment_type to curriculum_sections
        $secColumns = $this->db->select("DESCRIBE curriculum_sections");
        $secFields = array_column($secColumns, 'Field');
        if (!in_array('assessment_type', $secFields)) {
            $this->db->query("ALTER TABLE curriculum_sections ADD COLUMN assessment_type varchar(50) NOT NULL DEFAULT 'Grade'");
        }
    }

    public function down(): void
    {
        // No down needed for column adds
    }
}
