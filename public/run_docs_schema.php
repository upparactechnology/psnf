<?php
/**
 * Documents Workspace Schema Installer
 * Run: http://localhost/psnf/public/run_docs_schema.php
 * Safely creates all documents workspace tables in psnf_drm.
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
    'staff_documents' => "
        CREATE TABLE IF NOT EXISTS `staff_documents` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `staff_id` INT UNSIGNED NOT NULL,
            `type` VARCHAR(100) NOT NULL,
            `title` VARCHAR(191) NOT NULL,
            `file_name` VARCHAR(255) NOT NULL,
            `stored_name` VARCHAR(255) NOT NULL,
            `mime_type` VARCHAR(100) NOT NULL,
            `file_size` BIGINT UNSIGNED NOT NULL DEFAULT 0,
            `status` ENUM('pending','verified','rejected','expired') NOT NULL DEFAULT 'pending',
            `expiry_date` DATE NULL,
            `verified_by` INT UNSIGNED NULL,
            `verified_at` DATETIME NULL,
            `notes` TEXT NULL,
            `created_by` INT UNSIGNED NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            `deleted_at` TIMESTAMP NULL DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",
    'parent_documents' => "
        CREATE TABLE IF NOT EXISTS `parent_documents` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `parent_id` INT UNSIGNED NOT NULL,
            `type` VARCHAR(100) NOT NULL,
            `title` VARCHAR(191) NOT NULL,
            `file_name` VARCHAR(255) NOT NULL,
            `stored_name` VARCHAR(255) NOT NULL,
            `mime_type` VARCHAR(100) NOT NULL,
            `file_size` BIGINT UNSIGNED NOT NULL DEFAULT 0,
            `status` ENUM('pending','verified','rejected','expired') NOT NULL DEFAULT 'pending',
            `expiry_date` DATE NULL,
            `verified_by` INT UNSIGNED NULL,
            `verified_at` DATETIME NULL,
            `notes` TEXT NULL,
            `created_by` INT UNSIGNED NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            `deleted_at` TIMESTAMP NULL DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",
    'driver_documents' => "
        CREATE TABLE IF NOT EXISTS `driver_documents` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `driver_id` INT UNSIGNED NOT NULL,
            `type` VARCHAR(100) NOT NULL,
            `title` VARCHAR(191) NOT NULL,
            `file_name` VARCHAR(255) NOT NULL,
            `stored_name` VARCHAR(255) NOT NULL,
            `mime_type` VARCHAR(100) NOT NULL,
            `file_size` BIGINT UNSIGNED NOT NULL DEFAULT 0,
            `status` ENUM('pending','verified','rejected','expired') NOT NULL DEFAULT 'pending',
            `expiry_date` DATE NULL,
            `verified_by` INT UNSIGNED NULL,
            `verified_at` DATETIME NULL,
            `notes` TEXT NULL,
            `created_by` INT UNSIGNED NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            `deleted_at` TIMESTAMP NULL DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",
    'document_templates' => "
        CREATE TABLE IF NOT EXISTS `document_templates` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(191) NOT NULL,
            `type` VARCHAR(100) NOT NULL,
            `logo_path` VARCHAR(255) NULL,
            `qr_enabled` TINYINT(1) NOT NULL DEFAULT 1,
            `signature_path` VARCHAR(255) NULL,
            `watermark_path` VARCHAR(255) NULL,
            `header_text` TEXT NULL,
            `footer_text` TEXT NULL,
            `content` LONGTEXT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",
    'document_versions' => "
        CREATE TABLE IF NOT EXISTS `document_versions` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `document_type` ENUM('student', 'staff', 'parent', 'driver') NOT NULL,
            `document_id` INT UNSIGNED NOT NULL,
            `version` INT NOT NULL DEFAULT 1,
            `file_name` VARCHAR(255) NOT NULL,
            `stored_name` VARCHAR(255) NOT NULL,
            `file_size` BIGINT UNSIGNED NOT NULL,
            `uploaded_by` INT UNSIGNED NULL,
            `uploaded_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `notes` VARCHAR(255) NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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

// Seed some sample data for documents dashboard if empty
try {
    $c = $pdo->query("SELECT COUNT(*) FROM document_templates")->fetchColumn();
    if ($c == 0) {
        $pdo->exec("INSERT INTO document_templates (name, type, qr_enabled, header_text, footer_text) VALUES 
            ('Standard Student ID Card', 'id_card', 1, 'PSNF Special School', 'Valid for Academic Year 2026'),
            ('Sports Participation Template', 'certificate', 0, 'Sports Day 2026', 'Congratulations to all participants'),
            ('Official Academic Fee Receipt', 'receipt', 1, 'Billing & Accounts Department', 'Non-refundable Fee Receipt')
        ");
        $results[] = "✅ document_templates — Sample templates seeded";
    }
} catch (\Throwable $e) {
    $results[] = "⚠️ Seeding document_templates failed: " . $e->getMessage();
}

// Output
header('Content-Type: text/html; charset=utf-8');
echo "<!DOCTYPE html><html><head><title>Docs Schema Installer</title>
<style>body{font-family:monospace;background:#0f172a;color:#e2e8f0;padding:32px;line-height:1.8}
strong{color:#38bdf8;font-size:18px}a{color:#34d399;font-weight:bold}
.ok{color:#4ade80}.err{color:#f87171}.warn{color:#fbbf24}</style></head><body>";
echo "<strong>PSNF Documents Workspace — Schema Installer</strong><br><br>";
foreach ($results as $r) {
    $cls = str_starts_with($r,'✅') ? 'ok' : (str_starts_with($r,'❌') ? 'err' : 'warn');
    echo "<span class='$cls'>$r</span><br>";
}
echo "<br><a href='/psnf/public/documents'>→ Open Documents Workspace</a></body></html>";
