<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Session;

/**
 * Central CSRF verification. Run by Core\Router for every POST/PUT/PATCH/DELETE.
 *
 * The token is the per-session value created by Core\View::csrf()/csrfToken()
 * (session key "csrf_token"). It is accepted from the POST field "_csrf"
 * (or legacy "csrf_token") or from the X-CSRF-Token header (added globally by
 * the layouts for fetch/XHR/htmx).
 */
class CsrfMiddleware
{
    /** Paths (after base-path stripping) that are called without a browser session. */
    private const EXEMPT_PATHS = [
        '/api/v1/auth/login',          // Flutter staff app login (JSON, no cookies)
        '/api/v1/auth/driver-login',   // Flutter driver app login
        '/forms/student-enrollment',   // public enrollment form (no account)
    ];

    public function handle(\Core\Request $request): ?string
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return null;
        }

        if ($this->isExempt($request)) {
            return null;
        }

        $expected = (string) Session::get('csrf_token', '');
        $supplied = $_POST['_csrf']
            ?? $_POST['csrf_token']
            ?? $_SERVER['HTTP_X_CSRF_TOKEN']
            ?? $_SERVER['HTTP_X_XSRF_TOKEN']
            ?? '';
        if (!is_string($supplied)) {
            $supplied = '';
        }

        if ($expected !== '' && $supplied !== '' && hash_equals($expected, $supplied)) {
            return null;
        }

        return $this->reject($request);
    }

    private function isExempt(\Core\Request $request): bool
    {
        $path = $request->getPath();

        if (in_array($path, self::EXEMPT_PATHS, true)) {
            return true;
        }

        if (str_starts_with($path, '/api/')) {
            // Token-authenticated API calls (Flutter apps) carry no ambient
            // credentials; AuthMiddleware validates the JWT itself.
            if ($request->bearerToken()) {
                return true;
            }
            // No cookies at all => nothing a cross-site request could ride on.
            if (empty($_COOKIE['PSNF_SESSION']) && empty($_COOKIE['remember_token'])) {
                return true;
            }
        }

        return false;
    }

    private function reject(\Core\Request $request): string
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $isJson = $request->wantsJson() || $request->isHtmx()
            || str_contains($contentType, 'application/json')
            || isset($_SERVER['HTTP_X_CSRF_TOKEN']);

        if ($isJson) {
            http_response_code(419);
            header('Content-Type: application/json');
            return json_encode([
                'success' => false,
                'message' => 'Session expired, please refresh the page and try again.',
                'csrf'    => false,
            ]);
        }

        Session::flash('error', 'Session expired, please try again.');
        $old = $_POST;
        unset($old['_csrf'], $old['csrf_token'], $old['password'], $old['password_confirmation'], $old['current_password'], $old['new_password'], $old['confirm_password']);
        Session::flash('old', $old);

        \Core\Application::$app->response->back();
        exit();
    }
}
