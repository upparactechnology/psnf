<?php

namespace Database\Migrations;

use Core\Application;

class AddCurriculumTemplateIdToClasses
{
    public function up(): void
    {
        $db = Application::$app->db;
        $db->query("ALTER TABLE `classes` ADD COLUMN `curriculum_template_id` INT UNSIGNED NULL DEFAULT NULL AFTER `main_group_id`");
        try {
            $db->query("ALTER TABLE `classes` ADD CONSTRAINT `fk_classes_curriculum_template_id` FOREIGN KEY (`curriculum_template_id`) REFERENCES `curriculum_templates` (`id`) ON DELETE SET NULL");
        } catch (\Throwable $e) {}
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
