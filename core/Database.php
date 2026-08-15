<?php

declare(strict_types=1);

namespace Core;

class Database
{
    private static ?\PDO $pdo = null;
    private static ?int $tenantId  = null;
    private static ?int $schoolId  = null;
    private static ?int $branchId  = null;
    private int $queryCount = 0;

    public function __construct()
    {
        if (self::$pdo === null) {
            $this->connect();
        }
    }

    private function connect(): void
    {
        $cfg = config('database');
        $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['dbname']};charset=utf8mb4";

        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ];

        self::$pdo = new \PDO($dsn, $cfg['username'], $cfg['password'], $options);
    }

    public static function getInstance(): \PDO
    {
        if (self::$pdo === null) {
            (new self())->connect();
        }
        return self::$pdo;
    }

    public static function setTenantScope(?int $tenantId, ?int $schoolId = null, ?int $branchId = null): void
    {
        self::$tenantId = $tenantId;
        self::$schoolId = $schoolId;
        self::$branchId = $branchId;
    }

    public static function getTenantId(): ?int  { return self::$tenantId; }
    public static function getSchoolId(): ?int  { return self::$schoolId; }
    public static function getBranchId(): ?int  { return self::$branchId; }

    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($params);
        $this->queryCount++;
        return $stmt;
    }

    public function select(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function selectOne(string $sql, array $params = []): array|false
    {
        return $this->query($sql, $params)->fetch();
    }

    public function insert(string $table, array $data): int|string
    {
        $data = array_filter($data, fn($k) => !str_starts_with((string)$k, '_'), ARRAY_FILTER_USE_KEY);
        $cols = implode(', ', array_map(fn($k) => "`$k`", array_keys($data)));
        $plh  = implode(', ', array_fill(0, count($data), '?'));
        $this->query("INSERT INTO `$table` ($cols) VALUES ($plh)", array_values($data));
        return self::$pdo->lastInsertId();
    }

    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $data = array_filter($data, fn($k) => !str_starts_with((string)$k, '_'), ARRAY_FILTER_USE_KEY);
        $set  = implode(', ', array_map(fn($k) => "`$k` = ?", array_keys($data)));
        $stmt = $this->query("UPDATE `$table` SET $set WHERE $where", [...array_values($data), ...$whereParams]);
        return $stmt->rowCount();
    }

    public function delete(string $table, string $where, array $whereParams = []): int
    {
        $stmt = $this->query("DELETE FROM `$table` WHERE $where", $whereParams);
        return $stmt->rowCount();
    }

    public function softDelete(string $table, int|string $id): int
    {
        return $this->update($table, ['deleted_at' => now()], 'id = ?', [$id]);
    }

    public function beginTransaction(): void { self::$pdo->beginTransaction(); }
    public function commit(): void           { self::$pdo->commit(); }
    public function rollback(): void         { self::$pdo->rollBack(); }

    public function transaction(callable $callback): mixed
    {
        $this->beginTransaction();
        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function tableExists(string $table): bool
    {
        $dbname = config('database.dbname');
        $result = $this->selectOne(
            "SELECT COUNT(*) as cnt FROM information_schema.tables WHERE table_schema = ? AND table_name = ?",
            [$dbname, $table]
        );
        return ($result['cnt'] ?? 0) > 0;
    }

    public function getLastInsertId(): int|string { return self::$pdo->lastInsertId(); }

    public function getQueryCount(): int { return $this->queryCount; }
}
