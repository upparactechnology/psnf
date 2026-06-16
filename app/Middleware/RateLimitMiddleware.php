<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\RateLimiter;

class RateLimitMiddleware
{
    private int $maxAttempts;
    private int $decayMinutes;

    public function __construct(mixed $maxAttempts = 60, mixed $decayMinutes = 1)
    {
        $this->maxAttempts  = (int) $maxAttempts;
        $this->decayMinutes = (int) $decayMinutes;
    }

    public function handle(\Core\Request $request): ?string
    {
        $limiter = new RateLimiter($this->maxAttempts, $this->decayMinutes);
        $key     = RateLimiter::key('route', $request->ip());

        if ($limiter->tooManyAttempts($key)) {
            http_response_code(429);
            return json_encode(['success' => false, 'message' => 'Too Many Requests. Slow down.']);
        }

        $limiter->hit($key);
        return null;
    }
}
