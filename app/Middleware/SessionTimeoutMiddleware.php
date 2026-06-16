<?php

declare(strict_types=1);

namespace App\Middleware;

class SessionTimeoutMiddleware
{
    public function handle(\Core\Request $request): ?string
    {
        $timeout = config('auth.session_timeout', 1200);
        $last    = \Core\Session::lastActivity();

        if ($last && (time() - $last) > $timeout) {
            \Core\Session::destroy();
            if ($request->isAjax() || $request->isHtmx()) {
                http_response_code(401);
                return json_encode(['success' => false, 'message' => 'Session expired.', 'redirect' => url('login')]);
            }
            \Core\Session::flash('error', 'Your session has expired. Please log in again.');
            \Core\Application::$app->response->redirect('/login');
            exit();
        }

        \Core\Session::updateActivity();
        return null;
    }
}
