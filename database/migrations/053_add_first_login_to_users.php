<?php
class AddFirstLoginToUsers
{
    public function __construct(private \Core\Database $db) {}
    public function up(): void
    {
        $this->db->query("ALTER TABLE `users` ADD COLUMN `first_login` TINYINT(1) NOT NULL DEFAULT 1 AFTER `is_active`");

        // Mark all existing users as already set up (only NEW parent users will get first_login=1)
        $this->db->query("UPDATE `users` SET `first_login` = 0");
    }
    public function down(): void
    {
        $this->db->query("ALTER TABLE `users` DROP COLUMN `first_login`");
    }
}
