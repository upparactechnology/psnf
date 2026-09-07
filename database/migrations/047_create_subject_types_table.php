<?php

declare(strict_types=1);

class CreateSubjectTypesTable
{
    public function __construct(private \Core\Database $db) {}

    public function up(): void
    {
        // Change `type` column from ENUM to VARCHAR to support dynamic types
        $columns = $this->db->select("DESCRIBE subjects");
        $fields = array_column($columns, 'Field');
        if (in_array('type', $fields)) {
            $this->db->query("ALTER TABLE `subjects` MODIFY COLUMN `type` VARCHAR(50) DEFAULT 'academic'");
        }

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `subject_types` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id` INT UNSIGNED NOT NULL,
                `name` VARCHAR(100) NOT NULL,
                `slug` VARCHAR(100) NOT NULL,
                `color` VARCHAR(20) DEFAULT '#6366F1',
                `sort_order` INT DEFAULT 0,
                `is_active` TINYINT(1) DEFAULT 1,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY `unique_tenant_slug` (`tenant_id`, `slug`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Seed default types for all existing tenants
        $tenants = $this->db->select("SELECT id FROM tenants");
        $defaults = [
            ['name' => 'Academic',      'slug' => 'academic',      'color' => '#3B82F6', 'sort_order' => 1],
            ['name' => 'Therapy',       'slug' => 'therapy',       'color' => '#8B5CF6', 'sort_order' => 2],
            ['name' => 'Life Skills',   'slug' => 'life-skills',   'color' => '#10B981', 'sort_order' => 3],
            ['name' => 'Co-Curricular', 'slug' => 'co-curricular', 'color' => '#F59E0B', 'sort_order' => 4],
            ['name' => 'Vocational',    'slug' => 'vocational',    'color' => '#EF4444', 'sort_order' => 5],
        ];

        foreach ($tenants as $tenant) {
            $tenantId = $tenant['id'];
            foreach ($defaults as $type) {
                try {
                    $this->db->insert('subject_types', [
                        'tenant_id'  => $tenantId,
                        'name'       => $type['name'],
                        'slug'       => $type['slug'],
                        'color'      => $type['color'],
                        'sort_order' => $type['sort_order'],
                        'is_active'  => 1,
                    ]);
                } catch (\Throwable $e) {
                    // Skip if duplicate
                }
            }
        }
    }

    public function down(): void
    {
        $this->db->query("DROP TABLE IF EXISTS `subject_types`");
    }
}
