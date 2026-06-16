<?php

declare(strict_types=1);

namespace Core;

class Migration
{
    private Database $db;
    private string $migrationsPath;
    private string $seedsPath;

    public function __construct()
    {
        $this->db             = \Core\Application::$app->db;
        $this->migrationsPath = ROOT_PATH . '/database/migrations';
        $this->seedsPath      = ROOT_PATH . '/database/seeds';
        $this->createMigrationsTable();
    }

    private function createMigrationsTable(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `migrations` (
                `id`         INT AUTO_INCREMENT PRIMARY KEY,
                `migration`  VARCHAR(255) NOT NULL,
                `batch`      INT NOT NULL DEFAULT 1,
                `ran_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function run(bool $fresh = false): void
    {
        if ($fresh) {
            $this->rollbackAll();
            echo "\033[33m🗑  Dropped all tables.\033[0m\n";
        }

        $files   = $this->getMigrationFiles();
        $ran     = $this->getRanMigrations();
        $batch   = $this->getNextBatch();
        $count   = 0;

        foreach ($files as $file) {
            $name = basename($file, '.php');
            if (in_array($name, $ran)) continue;

            require_once $file;
            $class = $this->getClassFromFile($file);

            if (!class_exists($class)) {
                echo "\033[31m✘  Class not found: $class\033[0m\n";
                continue;
            }

            $migration = new $class($this->db);
            $migration->up();

            $this->db->insert('migrations', ['migration' => $name, 'batch' => $batch]);
            echo "\033[32m✔  Migrated: $name\033[0m\n";
            $count++;
        }

        if ($count === 0) {
            echo "\033[36mℹ  Nothing to migrate.\033[0m\n";
        }
    }

    public function seed(): void
    {
        $files = glob($this->seedsPath . '/*.php') ?: [];
        foreach ($files as $file) {
            require_once $file;
            $class = $this->getClassFromFile($file);
            if (class_exists($class)) {
                $seeder = new $class($this->db);
                $seeder->run();
                echo "\033[32m✔  Seeded: " . basename($file, '.php') . "\033[0m\n";
            }
        }
    }

    private function rollbackAll(): void
    {
        $this->db->query("SET FOREIGN_KEY_CHECKS = 0");
        $tables = $this->db->select("SHOW TABLES");
        foreach ($tables as $table) {
            $name = reset($table);
            if ($name === 'migrations') continue;
            $this->db->query("DROP TABLE IF EXISTS `$name`");
        }
        $this->db->query("SET FOREIGN_KEY_CHECKS = 1");
        $this->db->query("TRUNCATE TABLE `migrations`");
    }

    private function getMigrationFiles(): array
    {
        $files = glob($this->migrationsPath . '/*.php') ?: [];
        sort($files);
        return $files;
    }

    private function getRanMigrations(): array
    {
        $rows = $this->db->select("SELECT migration FROM migrations ORDER BY id ASC");
        return array_column($rows, 'migration');
    }

    private function getNextBatch(): int
    {
        $row = $this->db->selectOne("SELECT MAX(batch) as batch FROM migrations");
        return ((int) ($row['batch'] ?? 0)) + 1;
    }

    private function getClassFromFile(string $file): string
    {
        $content = file_get_contents($file);
        if (preg_match('/class\s+(\w+)/', $content, $m)) {
            return $m[1];
        }
        return '';
    }
}
