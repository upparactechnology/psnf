<?php

declare(strict_types=1);

class AddYearAndGroupToExams
{
    public function __construct(private \Core\Database $db) {}

    public function up(): void
    {
        $columns = $this->db->select("DESCRIBE exams");
        $fields = array_column($columns, 'Field');

        if (!in_array('academic_year_id', $fields)) {
            $this->db->query("ALTER TABLE exams ADD COLUMN academic_year_id INT UNSIGNED DEFAULT NULL");
        }
        if (!in_array('main_group_id', $fields)) {
            $this->db->query("ALTER TABLE exams ADD COLUMN main_group_id INT UNSIGNED DEFAULT NULL");
        }

        // Backfill existing exams to active year (3) and group (3)
        $this->db->query("UPDATE exams SET academic_year_id = 3, main_group_id = 3 WHERE academic_year_id IS NULL");
    }

    public function down(): void
    {
        // No down needed
    }
}
