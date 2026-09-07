<?php

declare(strict_types=1);

class AddMedicalCertificateToAttendanceTable
{
    public function __construct(private \Core\Database $db) {}

    public function up(): void
    {
        $cols = $this->db->select("SHOW COLUMNS FROM `attendance` LIKE 'medical_certificate'");
        if (empty($cols)) {
            $this->db->query("
                ALTER TABLE `attendance` 
                ADD COLUMN `medical_certificate` VARCHAR(500) NULL AFTER `remarks`
            ");
        }
    }

    public function down(): void
    {
        $this->db->query("
            ALTER TABLE `attendance` 
            DROP COLUMN `medical_certificate`
        ");
    }
}
