<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\{Session, Database};

class KioskAuthMiddleware
{
    public function handle(\Core\Request $request): ?string
    {
        $path = $request->getPath();

        // If user is already logged in, proceed with normal auth checks
        if (Session::has('user')) {
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

            // Refresh user roles & permissions from DB
            $freshUser = \App\Models\User::withRoles((int) $user['id']);
            if ($freshUser) {
                $freshUser['id'] = $user['id'];
                Session::set('user', $freshUser);
            }

            return null; // Authorized
        }

        // User is NOT logged in — allow guest access for face-kiosk only
        if ($path === '/attendance/face-kiosk') {
            Session::set('kiosk_guest', true);
            return null; // Allow access without auth
        }

        // For all other paths, try remember token then redirect to login
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
}
