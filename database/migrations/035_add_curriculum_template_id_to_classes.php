<?php

namespace Database\Migrations;

use Core\Application;

class AddCurriculumTemplateIdToClasses
{
    public function up(): void
    {
        $db = Application::$app->db;

        if (empty($db->select("SHOW COLUMNS FROM `classes` LIKE 'curriculum_template_id'"))) {
            $db->query("ALTER TABLE `classes` ADD COLUMN `curriculum_template_id` INT UNSIGNED NULL DEFAULT NULL AFTER `main_group_id`");
        }

        $fk = $db->selectOne(
            "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'classes'
               AND CONSTRAINT_NAME = 'fk_classes_curriculum_template_id' AND CONSTRAINT_TYPE = 'FOREIGN KEY'"
        );
        if (!$fk) {
            try {
                $db->query("ALTER TABLE `classes` ADD CONSTRAINT `fk_classes_curriculum_template_id` FOREIGN KEY (`curriculum_template_id`) REFERENCES `curriculum_templates` (`id`) ON DELETE SET NULL");
            } catch (\Throwable $e) {
                // curriculum_templates may not exist yet; the FK is optional
            }
        }
    }

    public function down(): void
    {
        $db = Application::$app->db;
        try {
            $db->query("ALTER TABLE `classes` DROP FOREIGN KEY `fk_classes_curriculum_template_id`");
        } catch (\Throwable $e) {}
        $db->query("ALTER TABLE `classes` DROP COLUMN `curriculum_template_id`");
    }
}
