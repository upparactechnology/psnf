<?php
/**
 * Certificate Generator Schema Installer
 * Run: http://localhost/psnf/public/run_cert_schema.php
 * Safely creates all cert generator tables in psnf_drm.
 */
declare(strict_types=1);

define('ROOT_DIR',    dirname(__DIR__));
define('ROOT_PATH',   ROOT_DIR);
define('CORE_PATH',   ROOT_DIR . '/core');
define('APP_PATH',    ROOT_DIR . '/app');
define('CONFIG_PATH', ROOT_DIR . '/config');
define('VIEWS_PATH',  ROOT_DIR . '/resources/views');
define('STORAGE_PATH',ROOT_DIR . '/storage');

spl_autoload_register(function ($class) {
    $prefix = 'Core\\';
    $base_dir = CORE_PATH . '/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) === 0) {
        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        if (file_exists($file)) { require $file; return; }
    }
    $prefixApp = 'App\\';
    $base_dirApp = APP_PATH . '/';
    $lenApp = strlen($prefixApp);
    if (strncmp($prefixApp, $class, $lenApp) === 0) {
        $relative_class = substr($class, $lenApp);
        $file = $base_dirApp . str_replace('\\', '/', $relative_class) . '.php';
        if (file_exists($file)) { require $file; return; }
    }
});

require_once CORE_PATH . '/helpers.php';
$app = new \Core\Application(ROOT_DIR);
$pdo = \Core\Database::getInstance();

$results = [];

$tables = [
    'cert_users' => "
        CREATE TABLE IF NOT EXISTS `cert_users` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `full_name` VARCHAR(120) NOT NULL,
            `email` VARCHAR(190) NOT NULL UNIQUE,
            `password_hash` VARCHAR(255) NOT NULL,
            `role` ENUM('super_admin','admin','sub_admin') NOT NULL DEFAULT 'admin',
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;
    ",
    'cert_settings' => "
        CREATE TABLE IF NOT EXISTS `cert_settings` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `setting_key` VARCHAR(120) NOT NULL UNIQUE,
            `setting_value` TEXT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;
    ",
    'conferences' => "
        CREATE TABLE IF NOT EXISTS `conferences` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(255) NOT NULL,
            `year` INT NOT NULL,
            `description` TEXT NULL,
            `created_by` INT UNSIGNED NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `uq_conference_name_year` (`name`, `year`)
        ) ENGINE=InnoDB;
    ",
    'conference_admins' => "
        CREATE TABLE IF NOT EXISTS `conference_admins` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `conference_id` INT UNSIGNED NOT NULL,
            `user_id` INT UNSIGNED NOT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `uq_conference_admin` (`conference_id`, `user_id`),
            CONSTRAINT `fk_conf_admins_conf2` FOREIGN KEY (`conference_id`) REFERENCES `conferences`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
        ) ENGINE=InnoDB;
    ",
    'certificate_types' => "
        CREATE TABLE IF NOT EXISTS `certificate_types` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `conference_id` INT UNSIGNED NOT NULL,
            `name` VARCHAR(120) NOT NULL,
            `slug` VARCHAR(150) NOT NULL,
            `template_path` VARCHAR(255) NULL,
            `is_custom` TINYINT(1) NOT NULL DEFAULT 0,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `uq_certificate_type_slug2` (`conference_id`, `slug`),
            CONSTRAINT `fk_cert_types_conference2` FOREIGN KEY (`conference_id`) REFERENCES `conferences`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
        ) ENGINE=InnoDB;
    ",
    'field_mappings' => "
        CREATE TABLE IF NOT EXISTS `field_mappings` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `certificate_type_id` INT UNSIGNED NOT NULL,
            `field_key` VARCHAR(80) NOT NULL,
            `label` TEXT NOT NULL,
            `options_json` TEXT NULL,
            `x_pos` INT NOT NULL DEFAULT 0,
            `y_pos` INT NOT NULL DEFAULT 0,
            `font_size` INT NOT NULL DEFAULT 32,
            `align` ENUM('left','center') NOT NULL DEFAULT 'left',
            `color_hex` CHAR(7) NOT NULL DEFAULT '#000000',
            `max_width` INT NOT NULL DEFAULT 900,
            `line_height` INT NOT NULL DEFAULT 42,
            `underline` TINYINT(1) NOT NULL DEFAULT 0,
            `sort_order` INT NOT NULL DEFAULT 0,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_field_mappings_type_sort2` (`certificate_type_id`, `sort_order`),
            CONSTRAINT `fk_field_mappings_type2` FOREIGN KEY (`certificate_type_id`) REFERENCES `certificate_types`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
        ) ENGINE=InnoDB;
    ",
    'participants' => "
        CREATE TABLE IF NOT EXISTS `participants` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `conference_id` INT UNSIGNED NOT NULL,
            `certificate_type_id` INT UNSIGNED NOT NULL,
            `student_id` INT NULL,
            `name` VARCHAR(255) NOT NULL,
            `email` VARCHAR(190) NULL,
            `institute` VARCHAR(255) NULL,
            `title` TEXT NULL,
            `issued_date` DATE NULL,
            `extra_json` JSON NULL,
            `verify_code` VARCHAR(40) NOT NULL UNIQUE,
            `status` ENUM('pending','generated','emailed','failed') NOT NULL DEFAULT 'pending',
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_participants_lookup2` (`conference_id`, `certificate_type_id`, `status`),
            CONSTRAINT `fk_participants_conference2` FOREIGN KEY (`conference_id`) REFERENCES `conferences`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
            CONSTRAINT `fk_participants_type2` FOREIGN KEY (`certificate_type_id`) REFERENCES `certificate_types`(`id`) ON UPDATE CASCADE ON DELETE RESTRICT
        ) ENGINE=InnoDB;
    ",
    'generated_certificates' => "
        CREATE TABLE IF NOT EXISTS `generated_certificates` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `participant_id` INT UNSIGNED NOT NULL,
            `jpg_path` VARCHAR(255) NULL,
            `pdf_path` VARCHAR(255) NULL,
            `generated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `download_count` INT NOT NULL DEFAULT 0,
            UNIQUE KEY `uq_generated_participant2` (`participant_id`),
            CONSTRAINT `fk_generated_participant2` FOREIGN KEY (`participant_id`) REFERENCES `participants`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
        ) ENGINE=InnoDB;
    ",
    'email_logs' => "
        CREATE TABLE IF NOT EXISTS `email_logs` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `participant_id` INT UNSIGNED NOT NULL,
            `to_email` VARCHAR(190) NOT NULL,
            `subject_text` VARCHAR(255) NOT NULL,
            `message_text` TEXT NULL,
            `status` ENUM('sent','failed') NOT NULL,
            `error_message` TEXT NULL,
            `sent_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_email_logs_participant2` (`participant_id`),
            INDEX `idx_email_logs_status2` (`status`),
            CONSTRAINT `fk_email_logs_participant2` FOREIGN KEY (`participant_id`) REFERENCES `participants`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
        ) ENGINE=InnoDB;
    ",
    'email_schedules' => "
        CREATE TABLE IF NOT EXISTS `email_schedules` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `conference_id` INT UNSIGNED NOT NULL,
            `certificate_type_id` INT UNSIGNED NULL,
            `template_key` VARCHAR(120) NOT NULL,
            `subject_template` VARCHAR(255) NOT NULL,
            `message_template` TEXT NOT NULL,
            `cc_template` TEXT NULL,
            `bcc_template` TEXT NULL,
            `status` ENUM('active','paused') NOT NULL DEFAULT 'active',
            `interval_seconds` INT NOT NULL DEFAULT 60,
            `last_sent_at` DATETIME NULL,
            `last_participant_id` INT UNSIGNED NULL,
            `sent_count` INT UNSIGNED NOT NULL DEFAULT 0,
            `failed_count` INT UNSIGNED NOT NULL DEFAULT 0,
            `created_by` INT UNSIGNED NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT `fk_email_sch_conf2` FOREIGN KEY (`conference_id`) REFERENCES `conferences`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
        ) ENGINE=InnoDB;
    ",
    'contact_messages' => "
        CREATE TABLE IF NOT EXISTS `contact_messages` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `conference_id` INT UNSIGNED NULL,
            `contact_name` VARCHAR(190) NOT NULL,
            `contact_email` VARCHAR(190) NULL,
            `mobile_number` VARCHAR(30) NULL,
            `category` VARCHAR(190) NOT NULL,
            `message_text` TEXT NOT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_contact_messages_created_at2` (`created_at`)
        ) ENGINE=InnoDB;
    ",
    'certificates' => "
        CREATE TABLE IF NOT EXISTS `certificates` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `tenant_id` INT NOT NULL,
            `student_id` INT NOT NULL,
            `title` VARCHAR(255) NOT NULL,
            `certificate_type` VARCHAR(120) NULL,
            `file_path` VARCHAR(255) NULL,
            `issued_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_certificates_tenant` (`tenant_id`),
            INDEX `idx_certificates_student` (`student_id`)
        ) ENGINE=InnoDB;
    ",
];

$pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
foreach ($tables as $name => $sql) {
    try {
        $pdo->exec($sql);
        $results[] = "✅ $name — OK";
    } catch (\Throwable $e) {
        $results[] = "❌ $name — ERROR: " . $e->getMessage();
    }
}
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

// Seed default cert_settings
try {
    $stmt = $pdo->prepare("INSERT IGNORE INTO cert_settings (setting_key, setting_value) VALUES (?, ?)");
    foreach ([
        ['default_font_path', 'C:/Windows/Fonts/arial.ttf'],
        ['file_name_format', '{conference}_{year}_{category}_{name}_{id}'],
        ['download_format', 'pdf'],
        ['smtp_host', ''],
        ['smtp_port', '587'],
        ['smtp_from_email', 'no-reply@psnf.edu'],
        ['smtp_from_name', 'PSNF Certificate System'],
        ['email_subject_template', 'Your {{certificate_type}} Certificate - PSNF {{year}}'],
        ['email_message_template', "Dear {{name}},\n\nPlease find attached your {{certificate_type}} certificate.\n\nRegards,\nPSNF Team"],
        ['bulk_email_delay_min_seconds', '1'],
        ['bulk_email_delay_max_seconds', '3'],
    ] as [$k, $v]) {
        $stmt->execute([$k, $v]);
    }
    $results[] = "✅ cert_settings — defaults seeded";
} catch (\Throwable $e) {
    $results[] = "⚠️  cert_settings — " . $e->getMessage();
}

// Seed default PSNF conference
try {
    $pdo->exec("INSERT IGNORE INTO conferences (id, name, year, description) VALUES (1, 'PSNF Academic Year 2026', 2026, 'Default certificate event for PSNF school')");
    $results[] = "✅ conferences — default PSNF conference seeded (ID=1)";
} catch (\Throwable $e) {
    $results[] = "⚠️  conferences seed — " . $e->getMessage();
}

// Output
header('Content-Type: text/html; charset=utf-8');
echo "<!DOCTYPE html><html><head><title>Cert Schema Installer</title>
<style>body{font-family:monospace;background:#0f172a;color:#e2e8f0;padding:32px;line-height:1.8}
strong{color:#818cf8;font-size:18px}a{color:#34d399;font-weight:bold}
.ok{color:#4ade80}.err{color:#f87171}.warn{color:#fbbf24}</style></head><body>";
echo "<strong>PSNF Certificate Generator — Schema Installer</strong><br><br>";
foreach ($results as $r) {
    $cls = str_starts_with($r,'✅') ? 'ok' : (str_starts_with($r,'❌') ? 'err' : 'warn');
    echo "<span class='$cls'>$r</span><br>";
}
echo "<br><a href='/psnf/public/certificate_generator/'>→ Open Certificate Generator</a></body></html>";
