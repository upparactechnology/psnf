<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\{Session, Database};

class AuthMiddleware
{
    public function handle(\Core\Request $request): ?string
    {
        // Try JWT from header for API
        $token = $request->bearerToken();
        if ($token) {
            $payload = \Core\JWT::decode($token);
            if ($payload) {
                Database::setTenantScope($payload['tenant_id'], $payload['school_id'], $payload['branch_id']);
                // Mock session user for JWT API requests so auth_id() and auth() helpers work
                if (!Session::has('user')) {
                    $db = \Core\Application::$app->db;
                    $user = $db->selectOne("SELECT id, name, email, phone, tenant_id, school_id, branch_id, is_active FROM users WHERE id = ? AND deleted_at IS NULL", [$payload['sub']]);
                    if ($user) {
                        $user['roles'] = $payload['roles'] ?? [];
                        Session::set('user', $user);
                    }
                }
                return null; // Authorized via JWT
            }
        }

        // Session auth
        if (!Session::has('user')) {
            // Try remember token
            $authService = new \App\Services\AuthService();
            $user = $authService->checkRememberToken();
            if ($user) {
                Database::setTenantScope($user['tenant_id'], $user['school_id'], $user['branch_id']);
                return null;
            }

            Session::flash('error', 'Please log in to continue.');

            if ($request->isAjax() || $request->isHtmx()) {
                http_response_code(401);
                return json_encode(['success' => false, 'redirect' => url('login')]);
            }

            \Core\Application::$app->response->redirect('/login');
            exit();
        }

        // Check session timeout
        $timeout = config('auth.session_timeout', 1200);
        $last    = Session::lastActivity();

        if ($last && (time() - $last) > $timeout) {
            Session::destroy();
            Session::flash('error', 'Session expired. Please log in again.');
            \Core\Application::$app->response->redirect('/login');
            exit();
        }

        Session::updateActivity();

        // Re-set tenant scope from session
        $user = Session::get('user');
        Database::setTenantScope($user['tenant_id'], $user['school_id'], $user['branch_id']);

        return null;
    }
}
