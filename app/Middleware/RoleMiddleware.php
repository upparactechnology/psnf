<?php

declare(strict_types=1);

namespace App\Middleware;

class RoleMiddleware
{
    private array $roles;

    public function __construct(string ...$roles)
    {
        $this->roles = $roles;
    }

    public function handle(\Core\Request $request): ?string
    {
        $user = auth();
        if (!$user) {
            \Core\Application::$app->response->redirect('/login');
            exit();
        }

        // Super admin bypasses all role checks
        if (in_array('super_admin', $user['roles'] ?? [])) {
            return null;
        }

        $userRoles = $user['roles'] ?? [];
        $hasRole   = !empty(array_intersect($this->roles, $userRoles));

        if (!$hasRole) {
            if ($request->isAjax() || $request->isHtmx()) {
                http_response_code(403);
                return json_encode(['success' => false, 'message' => 'Insufficient role.']);
            }
            \Core\Application::$app->response->abort(403);
            exit();
        }

        return null;
    }
}
