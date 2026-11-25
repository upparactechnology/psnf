<?php

namespace Database\Migrations;

use PDO;

class AddRollNumberToStudents
{
    public function up(PDO $pdo): void
    {
        // Idempotent: only add what is missing
        $col = $pdo->query("SHOW COLUMNS FROM `students` LIKE 'roll_number'")->fetch();
        if (!$col) {
            $pdo->exec("ALTER TABLE `students` ADD COLUMN `roll_number` VARCHAR(50) DEFAULT NULL AFTER `admission_number`");
        }

        $idx = $pdo->query("SHOW INDEX FROM `students` WHERE Key_name = 'idx_students_roll_number'")->fetch();
        if (!$idx) {
            $pdo->exec("ALTER TABLE `students` ADD INDEX `idx_students_roll_number` (`roll_number`)");
        }
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("ALTER TABLE `students` DROP INDEX `idx_students_roll_number`");
        $pdo->exec("ALTER TABLE `students` DROP COLUMN `roll_number`");
    }
}
