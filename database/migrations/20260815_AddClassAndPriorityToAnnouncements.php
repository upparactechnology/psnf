<?php

declare(strict_types=1);

return new class {
    public function up(): void
    {
        $pdo = \Core\Database::getInstance();

        // Add class_name column for class-specific announcements
        $pdo->exec("
            ALTER TABLE announcements 
            ADD COLUMN class_name VARCHAR(50) DEFAULT NULL AFTER target_audience
        ");

        // Add priority column (normal, urgent, critical)
        $pdo->exec("
            ALTER TABLE announcements 
            ADD COLUMN priority ENUM('normal', 'urgent', 'critical') NOT NULL DEFAULT 'normal' AFTER class_name
        ");
    }

    public function down(): void
    {
        $pdo = \Core\Database::getInstance();
        $pdo->exec("ALTER TABLE announcements DROP COLUMN class_name");
        $pdo->exec("ALTER TABLE announcements DROP COLUMN priority");
    }
};
