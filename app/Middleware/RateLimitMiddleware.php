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
        // Separate bucket per client IP *and* route, so e.g. login attempts and
        // public form submissions don't consume each other's allowance.
        $key     = RateLimiter::key('route', $request->ip() . '|' . $request->getMethod() . ' ' . $request->getPath());

        if ($limiter->tooManyAttempts($key)) {
            http_response_code(429);
            return json_encode(['success' => false, 'message' => 'Too Many Requests. Slow down.']);
        }

        $limiter->hit($key);
        return null;
    }
}
