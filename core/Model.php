<?php

declare(strict_types=1);

namespace Core;

abstract class Model
{
    protected static string $table     = '';
    protected static string $primaryKey = 'id';
    protected static bool   $softDelete = true;
    protected static bool   $timestamps = true;
    protected static bool   $tenantScope = false; // Set to true in tenant-aware models

    protected static function db(): Database
    {
        return \Core\Application::$app->db;
    }

    // ─── Query Helpers ────────────────────────────────────────────────────────

    protected static function tenantWhere(): array
    {
        $where  = [];
        $params = [];

        if (static::$tenantScope) {
            $tid = Database::getTenantId();
            $sid = Database::getSchoolId();
            $bid = Database::getBranchId();

            if ($tid) { $where[] = 'tenant_id = ?'; $params[] = $tid; }
            if ($sid) { $where[] = 'school_id = ?'; $params[] = $sid; }
            if ($bid) { $where[] = 'branch_id = ?'; $params[] = $bid; }
        }

        if (static::$softDelete) {
            $where[] = 'deleted_at IS NULL';
        }

        return [$where, $params];
    }

    public static function all(string $orderBy = 'id DESC'): array
    {
        [$where, $params] = static::tenantWhere();
        $clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        return static::db()->select("SELECT * FROM `" . static::$table . "` $clause ORDER BY $orderBy", $params);
    }

    public static function find(int|string $id): array|false
    {
        [$where, $params] = static::tenantWhere();
        $where[]  = static::$primaryKey . ' = ?';
        $params[] = $id;
        $clause   = 'WHERE ' . implode(' AND ', $where);
        return static::db()->selectOne("SELECT * FROM `" . static::$table . "` $clause LIMIT 1", $params);
    }

    public static function findBy(string $column, mixed $value): array|false
    {
        [$where, $params] = static::tenantWhere();
        $where[]  = "`$column` = ?";
        $params[] = $value;
        $clause   = 'WHERE ' . implode(' AND ', $where);
        return static::db()->selectOne("SELECT * FROM `" . static::$table . "` $clause LIMIT 1", $params);
    }

    public static function where(string $condition, array $params = [], string $orderBy = 'id DESC'): array
    {
        [$tWhere, $tParams] = static::tenantWhere();
        $allWhere  = array_merge($tWhere, [$condition]);
        $allParams = array_merge($tParams, $params);
        $clause    = 'WHERE ' . implode(' AND ', $allWhere);
        return static::db()->select("SELECT * FROM `" . static::$table . "` $clause ORDER BY $orderBy", $allParams);
    }

    public static function create(array $data): int|string
    {
        if (static::$timestamps) {
            $data['created_at'] = $data['created_at'] ?? now();
        }

        // Auto-inject tenant scope
        if (static::$tenantScope) {
            $data['tenant_id'] = $data['tenant_id'] ?? Database::getTenantId();
            $data['school_id'] = $data['school_id'] ?? Database::getSchoolId();
            $data['branch_id'] = $data['branch_id'] ?? Database::getBranchId();
        }

        return static::db()->insert(static::$table, $data);
    }

    public static function update(int|string $id, array $data): int
    {
        if (static::$timestamps) {
            $data['updated_at'] = now();
        }
        return static::db()->update(static::$table, $data, static::$primaryKey . ' = ?', [$id]);
    }

    public static function delete(int|string $id): int
    {
        if (static::$softDelete) {
            return static::db()->update(static::$table, ['deleted_at' => now()], static::$primaryKey . ' = ?', [$id]);
        }
        return static::db()->delete(static::$table, static::$primaryKey . ' = ?', [$id]);
    }

    public static function count(string $condition = '', array $params = []): int
    {
        [$tWhere, $tParams] = static::tenantWhere();
        if ($condition) { $tWhere[] = $condition; $tParams = array_merge($tParams, $params); }
        $clause = $tWhere ? 'WHERE ' . implode(' AND ', $tWhere) : '';
        $result = static::db()->selectOne("SELECT COUNT(*) as cnt FROM `" . static::$table . "` $clause", $tParams);
        return (int) ($result['cnt'] ?? 0);
    }

    public static function paginate(int $page = 1, int $perPage = 15, string $condition = '', array $params = [], string $orderBy = 'id DESC'): array
    {
        $offset = ($page - 1) * $perPage;
        [$tWhere, $tParams] = static::tenantWhere();
        if ($condition) { $tWhere[] = $condition; $tParams = array_merge($tParams, $params); }
        $clause = $tWhere ? 'WHERE ' . implode(' AND ', $tWhere) : '';

        $total = (int) (static::db()->selectOne("SELECT COUNT(*) as cnt FROM `" . static::$table . "` $clause", $tParams)['cnt'] ?? 0);
        $data  = static::db()->select("SELECT * FROM `" . static::$table . "` $clause ORDER BY $orderBy LIMIT $perPage OFFSET $offset", $tParams);

        return [
            'data'         => $data,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / $perPage),
            'from'         => $offset + 1,
            'to'           => min($offset + $perPage, $total),
        ];
    }

    public static function rawQuery(string $sql, array $params = []): array
    {
        return static::db()->select($sql, $params);
    }

    public static function rawQueryOne(string $sql, array $params = []): array|false
    {
        return static::db()->selectOne($sql, $params);
    }
}
