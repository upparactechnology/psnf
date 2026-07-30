<?php

namespace Database\Migrations;

use Core\Database;
use PDO;

class AddRollNumberToStudents
{
    public function up(PDO $pdo): void
    {
        // Add roll_number column to students table
        $pdo->exec("ALTER TABLE `students` ADD COLUMN `roll_number` VARCHAR(50) DEFAULT NULL AFTER `admission_number`");
        
        // Add index to roll_number for faster searches
        $pdo->exec("ALTER TABLE `students` ADD INDEX `idx_students_roll_number` (`roll_number`)");
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("ALTER TABLE `students` DROP INDEX `idx_students_roll_number`");
        $pdo->exec("ALTER TABLE `students` DROP COLUMN `roll_number`");
    }
}
