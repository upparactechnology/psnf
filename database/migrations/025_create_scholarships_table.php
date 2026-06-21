<?php

declare(strict_types=1);

class CreateScholarshipsTable
{
    public function __construct(private \Core\Database $db) {}

    public function up(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `scholarships` (
                `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id`    INT UNSIGNED NOT NULL,
                `school_id`    INT UNSIGNED NOT NULL,
                `branch_id`    INT UNSIGNED NOT NULL,
                `student_id`   INT UNSIGNED NOT NULL,
                `name`         VARCHAR(150) NOT NULL,
                `amount`       DECIMAL(10,2) NOT NULL,
                `type`         ENUM('fixed', 'percentage') NOT NULL DEFAULT 'fixed',
                `status`       ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
                `remarks`      TEXT NULL,
                `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Seed a couple of mock scholarships
        $tenant = $this->db->selectOne("SELECT id FROM tenants WHERE slug = 'psnf'");
        if ($tenant) {
            $tid = (int) $tenant['id'];
            $school = $this->db->selectOne("SELECT id FROM schools WHERE tenant_id = ? LIMIT 1", [$tid]);
            $sid = $school ? (int) $school['id'] : 1;
            $branch = $this->db->selectOne("SELECT id FROM branches WHERE school_id = ? LIMIT 1", [$sid]);
            $bid = $branch ? (int) $branch['id'] : 1;

            $students = $this->db->select("SELECT id FROM students WHERE tenant_id = ? AND deleted_at IS NULL LIMIT 2", [$tid]);
            if ($students) {
                $this->db->insert('scholarships', [
                    'tenant_id'  => $tid,
                    'school_id'  => $sid,
                    'branch_id'  => $bid,
                    'student_id' => $students[0]['id'],
                    'name'       => 'NGO Financial Aid Program',
                    'amount'     => 5000.00,
                    'type'       => 'fixed',
                    'status'     => 'active',
                    'remarks'    => 'Awarded based on NGO sponsorship.'
                ]);
                if (isset($students[1])) {
                    $this->db->insert('scholarships', [
                        'tenant_id'  => $tid,
                        'school_id'  => $sid,
                        'branch_id'  => $bid,
                        'student_id' => $students[1]['id'],
                        'name'       => 'Special Needs Merit Grant',
                        'amount'     => 50.00,
                        'type'       => 'percentage',
                        'status'     => 'active',
                        'remarks'    => 'Merit award for active speech therapy progress.'
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        $this->db->query("DROP TABLE IF EXISTS `scholarships`");
    }
}
