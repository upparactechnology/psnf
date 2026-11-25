<?php

namespace Database\Migrations;

use PDO;

class AddBusTransportToAttendance
{
    public function up(PDO $pdo): void
    {
        $col = $pdo->query("SHOW COLUMNS FROM `attendance` LIKE 'use_bus_transport'")->fetch();
        if (!$col) {
            $pdo->exec("ALTER TABLE `attendance` ADD COLUMN `use_bus_transport` TINYINT(1) NOT NULL DEFAULT 0 AFTER `remarks`");
        }
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("ALTER TABLE `attendance` DROP COLUMN `use_bus_transport`");
    }
}
