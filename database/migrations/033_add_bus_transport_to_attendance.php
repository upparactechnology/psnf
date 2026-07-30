<?php

namespace Database\Migrations;

use Core\Database;
use PDO;

class AddBusTransportToAttendance
{
    public function up(PDO $pdo): void
    {
        $pdo->exec("ALTER TABLE `attendance` ADD COLUMN `use_bus_transport` TINYINT(1) NOT NULL DEFAULT 0 AFTER `remarks`");
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("ALTER TABLE `attendance` DROP COLUMN `use_bus_transport`");
    }
}
