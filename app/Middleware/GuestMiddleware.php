<?php

declare(strict_types=1);

namespace App\Middleware;

class GuestMiddleware
{
    public function handle(\Core\Request $request): ?string
    {
        if (\Core\Session::has('user')) {
            \Core\Application::$app->response->redirect(dashboard_url());
            exit();
        }
        return null;
    }
}
