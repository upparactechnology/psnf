<?php

declare(strict_types=1);

class CreateExamsTable
{
    public function __construct(private \Core\Database $db) {}

    public function up(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `exams` (
                `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id`      INT UNSIGNED NOT NULL,
                `school_id`      INT UNSIGNED NOT NULL,
                `branch_id`      INT UNSIGNED NOT NULL,
                `name`           VARCHAR(100) NOT NULL,
                `semester`       VARCHAR(50) NOT NULL,
                `max_marks`      DECIMAL(5,2) NOT NULL DEFAULT 100.00,
                `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Seed default exams if table is empty
        $tenants = $this->db->select("SELECT id FROM tenants");
        foreach ($tenants as $t) {
            $tId = (int)$t['id'];
            $exists = $this->db->selectOne("SELECT 1 FROM exams WHERE tenant_id = ? LIMIT 1", [$tId]);
            if (!$exists) {
                // Semester 1 defaults
                $this->db->insert('exams', [
                    'tenant_id' => $tId,
                    'school_id' => 1,
                    'branch_id' => 1,
                    'name' => 'Unit Test - 1',
                    'semester' => 'Semester 1',
                    'max_marks' => 20.00
                ]);
                $this->db->insert('exams', [
                    'tenant_id' => $tId,
                    'school_id' => 1,
                    'branch_id' => 1,
                    'name' => 'Semester 1',
                    'semester' => 'Semester 1',
                    'max_marks' => 40.00
                ]);
                $this->db->insert('exams', [
                    'tenant_id' => $tId,
                    'school_id' => 1,
                    'branch_id' => 1,
                    'name' => 'Project/Practical 1',
                    'semester' => 'Semester 1',
                    'max_marks' => 15.00
                ]);

                // Semester 2 defaults
                $this->db->insert('exams', [
                    'tenant_id' => $tId,
                    'school_id' => 1,
                    'branch_id' => 1,
                    'name' => 'Unit Test - 2',
                    'semester' => 'Semester 2',
                    'max_marks' => 20.00
                ]);
                $this->db->insert('exams', [
                    'tenant_id' => $tId,
                    'school_id' => 1,
                    'branch_id' => 1,
                    'name' => 'Semester 2',
                    'semester' => 'Semester 2',
                    'max_marks' => 40.00
                ]);
                $this->db->insert('exams', [
                    'tenant_id' => $tId,
                    'school_id' => 1,
                    'branch_id' => 1,
                    'name' => 'Project/Practical 2',
                    'semester' => 'Semester 2',
                    'max_marks' => 15.00
                ]);
            }
        }
    }

    public function down(): void
    {
        $this->db->query("DROP TABLE IF EXISTS `exams`");
    }
}
