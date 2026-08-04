<?php

class AddCertificateTypeToStudentDocuments
{
    public function __construct(private \Core\Database $db) {}

    public function up(): void
    {
        $this->db->query("
            ALTER TABLE `student_documents` 
            MODIFY COLUMN `type` ENUM('birth_certificate','aadhar','medical_report','disability_certificate','transfer_certificate','photo','certificate','other') NOT NULL DEFAULT 'other'
        ");
    }

    public function down(): void
    {
        $this->db->query("
            UPDATE `student_documents` SET `type` = 'other' WHERE `type` = 'certificate'
        ");
        $this->db->query("
            ALTER TABLE `student_documents` 
            MODIFY COLUMN `type` ENUM('birth_certificate','aadhar','medical_report','disability_certificate','transfer_certificate','photo','other') NOT NULL DEFAULT 'other'
        ");
    }
}
