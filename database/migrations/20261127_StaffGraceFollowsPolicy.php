<?php

/**
 * users.grace_period used to default to 5 (DB default + user form pre-fill), so every
 * staff member silently overrode the Staff Settings grace policy (15 min standard /
 * 10 min custom shift) with 5 min. NULL now means "follow the policy".
 *  - column default becomes NULL
 *  - existing 5s (the forced default) are cleared to NULL
 * Idempotent.
 */
class StaffGraceFollowsPolicy
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    private function hasColumn(string $table, string $column): bool
    {
        return (bool) $this->db->selectOne(
            "SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?",
            [$table, $column]
        );
    }

    public function up(): void
    {
        if (!$this->hasColumn('users', 'grace_period')) {
            return;
        }
        $this->db->query("ALTER TABLE `users` MODIFY COLUMN `grace_period` INT UNSIGNED NULL DEFAULT NULL");
        $this->db->query("UPDATE `users` SET `grace_period` = NULL WHERE `grace_period` = 5");
    }

    public function down(): void
    {
        if (!$this->hasColumn('users', 'grace_period')) {
            return;
        }
        $this->db->query("ALTER TABLE `users` MODIFY COLUMN `grace_period` INT UNSIGNED NULL DEFAULT 5");
    }
}
