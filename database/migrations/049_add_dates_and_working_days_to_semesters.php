<?php

declare(strict_types=1);

class AddDatesAndWorkingDaysToSemesters
{
    public function __construct(private \Core\Database $db) {}

    public function up(): void
    {
        $columns = $this->db->select("DESCRIBE academic_semesters");
        $fields = array_column($columns, 'Field');

        if (!in_array('start_date', $fields)) {
            $this->db->query("ALTER TABLE academic_semesters ADD COLUMN start_date DATE DEFAULT NULL");
        }
        if (!in_array('end_date', $fields)) {
            $this->db->query("ALTER TABLE academic_semesters ADD COLUMN end_date DATE DEFAULT NULL");
        }
        if (!in_array('total_working_days', $fields)) {
            $this->db->query("ALTER TABLE academic_semesters ADD COLUMN total_working_days INT DEFAULT NULL");
        }
    }

    public function down(): void
    {
        // No down needed
    }
}
