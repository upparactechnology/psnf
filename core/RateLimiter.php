<?php

declare(strict_types=1);

namespace Core;

class RateLimiter
{
    private Database $db;
    private int $maxAttempts;
    private int $decayMinutes;

    public function __construct(int $maxAttempts = 5, int $decayMinutes = 15)
    {
        $this->db           = \Core\Application::$app->db;
        $this->maxAttempts  = $maxAttempts;
        $this->decayMinutes = $decayMinutes;
    }

    public function tooManyAttempts(string $key): bool
    {
        return $this->attempts($key) >= $this->maxAttempts;
    }

    public function hit(string $key): int
    {
        $this->clearExpired($key);

        $existing = $this->db->selectOne(
            "SELECT * FROM rate_limits WHERE `key` = ? LIMIT 1",
            [$key]
        );

        if ($existing) {
            $attempts = (int) $existing['attempts'] + 1;
            $this->db->update('rate_limits', [
                'attempts'   => $attempts,
                'updated_at' => now(),
            ], '`key` = ?', [$key]);
            return $attempts;
        }

        $expiry = date('Y-m-d H:i:s', strtotime("+{$this->decayMinutes} minutes"));
        $this->db->insert('rate_limits', [
            'key'        => $key,
            'attempts'   => 1,
            'expires_at' => $expiry,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return 1;
    }

    public function attempts(string $key): int
    {
        $this->clearExpired($key);
        $row = $this->db->selectOne("SELECT attempts FROM rate_limits WHERE `key` = ? LIMIT 1", [$key]);
        return (int) ($row['attempts'] ?? 0);
    }

    public function clear(string $key): void
    {
        $this->db->query("DELETE FROM rate_limits WHERE `key` = ?", [$key]);
    }

    public function remainingAttempts(string $key): int
    {
        return max(0, $this->maxAttempts - $this->attempts($key));
    }

    public function availableIn(string $key): int
    {
        $row = $this->db->selectOne("SELECT expires_at FROM rate_limits WHERE `key` = ? LIMIT 1", [$key]);
        if (!$row) return 0;
        return max(0, strtotime($row['expires_at']) - time());
    }

    private function clearExpired(string $key): void
    {
        $this->db->query("DELETE FROM rate_limits WHERE `key` = ? AND expires_at < NOW()", [$key]);
    }

    public static function key(string $action, string $identifier): string
    {
        return $action . ':' . sha1($identifier);
    }
}
