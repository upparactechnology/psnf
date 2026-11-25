<?php

declare(strict_types=1);

namespace Core;

class Migration
{
    private Database $db;
    private string $migrationsPath;

    public function __construct()
    {
        $this->db             = \Core\Application::$app->db;
        $this->migrationsPath = ROOT_PATH . '/database/migrations';
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

    /**
     * Cheap guard for the web request path: only scans/runs migrations when the
     * listing of the migrations directory changed since the last run.
     * Returns true when a run was performed.
     */
    public function runIfChanged(): bool
    {
        $cacheDir  = $this->storagePath() . '/cache';
        $hashFile  = $cacheDir . '/migrations.hash';
        $current   = $this->directoryHash();

        if (is_file($hashFile) && trim((string) @file_get_contents($hashFile)) === $current) {
            return false;
        }

        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0775, true);
        }

        // Only one request at a time may run migrations.
        $lock = @fopen($cacheDir . '/migrations.lock', 'c');
        if ($lock === false || !flock($lock, LOCK_EX | LOCK_NB)) {
            if ($lock) fclose($lock);
            return false;
        }

        try {
            // Re-check after acquiring the lock (another request may have finished).
            if (is_file($hashFile) && trim((string) @file_get_contents($hashFile)) === $current) {
                return false;
            }
            $this->run();
            // Hash is written even if some migrations failed: failures are logged to
            // storage/logs/migrations.log and retried on the next directory change or CLI run,
            // instead of being retried (and re-logged) on every request.
            @file_put_contents($hashFile, $current, LOCK_EX);
            return true;
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    public function run(bool $fresh = false): void
    {
        if ($fresh) {
            $this->rollbackAll();
            $this->out("\033[33m🗑  Dropped all tables.\033[0m");
        }

        $files   = $this->getMigrationFiles();
        $ran     = $this->getRanMigrations();
        $batch   = $this->getNextBatch();
        $count   = 0;
        $failed  = 0;

        foreach ($files as $file) {
            $name = basename($file, '.php');
            if (in_array($name, $ran, true)) continue;

            try {
                $this->runOne($file);
            } catch (\Throwable $e) {
                $failed++;
                $this->logFailure($name, $e);
                $this->out("\033[31m✘  Failed: $name — " . $e->getMessage() . "\033[0m");
                continue;
            }

            // Another process may have recorded it meanwhile; avoid duplicate rows.
            $exists = $this->db->selectOne("SELECT id FROM migrations WHERE migration = ? LIMIT 1", [$name]);
            if (!$exists) {
                $this->db->insert('migrations', ['migration' => $name, 'batch' => $batch]);
            }
            $this->out("\033[32m✔  Migrated: $name\033[0m");
            $count++;
        }

        if ($count === 0 && $failed === 0) {
            $this->out("\033[36mℹ  Nothing to migrate.\033[0m");
        } elseif ($failed > 0) {
            $this->out("\033[33m⚠  $failed migration(s) failed — see storage/logs/migrations.log\033[0m");
        }
    }

    /**
     * Load and execute a single migration file. Throws on failure.
     */
    private function runOne(string $file): void
    {
        $class = $this->getClassFromFile($file);
        $candidates = $class === '' ? [] : [$class, 'Database\\Migrations\\' . $class];

        $alreadyLoaded = false;
        foreach ($candidates as $c) {
            if (class_exists($c, false)) { $alreadyLoaded = true; break; }
        }

        $obj = null;
        if (!$alreadyLoaded) {
            $obj = require $file;
        }

        // Anonymous-class migration: `return new class { ... };`
        if (is_object($obj)) {
            if (!method_exists($obj, 'up')) {
                throw new \RuntimeException('Migration object has no up() method');
            }
            $obj->up();
            return;
        }

        $fqcn = null;
        foreach ($candidates as $c) {
            if (class_exists($c, false)) { $fqcn = $c; break; }
        }
        if ($fqcn === null) {
            throw new \RuntimeException("Class not found in migration file (" . ($class ?: 'none') . ")");
        }

        if (strpos($fqcn, 'Database\\Migrations\\') === 0) {
            // Namespaced classes take no constructor args and receive PDO in up()
            $migration = new $fqcn();
            $migration->up(Database::getInstance());
        } else {
            // Non-namespaced classes receive Core\Database in the constructor
            $migration = new $fqcn($this->db);
            $migration->up();
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

    private function directoryHash(): string
    {
        $parts = [];
        foreach ($this->getMigrationFiles() as $f) {
            $parts[] = basename($f) . ':' . (int) @filesize($f) . ':' . (int) @filemtime($f);
        }
        return hash('sha256', implode("\n", $parts));
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

    /**
     * Returns the name of the first named class declared in the file ('' for anonymous-only files).
     */
    private function getClassFromFile(string $file): string
    {
        $tokens = token_get_all((string) file_get_contents($file));
        $count  = count($tokens);
        for ($i = 0; $i < $count; $i++) {
            if (!is_array($tokens[$i]) || $tokens[$i][0] !== T_CLASS) continue;
            // Skip `::class` and `new class`
            for ($p = $i - 1; $p >= 0 && is_array($tokens[$p]) && $tokens[$p][0] === T_WHITESPACE; $p--);
            if ($p >= 0 && is_array($tokens[$p]) && in_array($tokens[$p][0], [T_DOUBLE_COLON, T_NEW], true)) continue;
            for ($j = $i + 1; $j < $count; $j++) {
                if (is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) continue;
                if (is_array($tokens[$j]) && $tokens[$j][0] === T_STRING) return $tokens[$j][1];
                break;
            }
        }
        return '';
    }

    private function storagePath(): string
    {
        return defined('STORAGE_PATH') ? STORAGE_PATH : ROOT_PATH . '/storage';
    }

    private function logFailure(string $name, \Throwable $e): void
    {
        $dir = $this->storagePath() . '/logs';
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $line = sprintf(
            "[%s] %s failed: %s in %s:%d\n",
            date('Y-m-d H:i:s'),
            $name,
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );
        @file_put_contents($dir . '/migrations.log', $line, FILE_APPEND | LOCK_EX);
    }

    private function out(string $msg): void
    {
        echo $msg . "\n";
    }
}
