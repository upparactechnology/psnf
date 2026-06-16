<?php

declare(strict_types=1);

namespace App\Middleware;

class PermissionMiddleware
{
    private array $permissions;

    public function __construct(string ...$permissions)
    {
        $this->permissions = $permissions;
    }

    public function handle(\Core\Request $request): ?string
    {
        $user = auth();
        if (!$user) {
            \Core\Application::$app->response->redirect('/login');
            exit();
        }

        // Super admin bypasses all
        if (in_array('super_admin', $user['roles'] ?? [])) {
            return null;
        }

        $userPerms = $user['permissions'] ?? [];
        $hasAll    = !array_diff($this->permissions, $userPerms);

        if (!$hasAll) {
            if ($request->isAjax() || $request->isHtmx()) {
                http_response_code(403);
                return json_encode(['success' => false, 'message' => 'Permission denied.']);
            }
            \Core\Application::$app->response->abort(403);
            exit();
        }

        return null;
    }
}
