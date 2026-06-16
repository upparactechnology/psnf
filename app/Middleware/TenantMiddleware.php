<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Database;

class TenantMiddleware
{
    public function handle(\Core\Request $request): ?string
    {
        $user = auth();
        if (!$user) return null;

        // Ensure tenant scope is always set from session
        Database::setTenantScope(
            $user['tenant_id'] ?? null,
            $user['school_id'] ?? null,
            $user['branch_id'] ?? null
        );

        return null;
    }
}
