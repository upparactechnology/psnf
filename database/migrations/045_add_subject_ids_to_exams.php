<?php

declare(strict_types=1);

class AddSubjectIdsToExams
{
    public function __construct(private \Core\Database $db) {}

    public function up(): void
    {
        $columns = $this->db->select("DESCRIBE exams");
        $fields = array_column($columns, 'Field');

        if (!in_array('subject_ids', $fields)) {
            $this->db->query("ALTER TABLE exams ADD COLUMN subject_ids TEXT DEFAULT NULL");
        }
    }

    public function down(): void
    {
        // No down needed
    }
}
