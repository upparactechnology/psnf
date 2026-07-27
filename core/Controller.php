<?php

declare(strict_types=1);

namespace Core;

abstract class Controller
{
    protected Request $request;
    protected Response $response;

    public function __construct()
    {
        $this->request  = \Core\Application::$app->request;
        $this->response = \Core\Application::$app->response;
    }

    protected function view(string $view, array $data = []): string
    {
        return View::render($view, $data);
    }

    protected function render(string $view, array $data = [], string $layout = 'app'): string
    {
        return View::render($view, $data);
    }

    protected function json(mixed $data, int $status = 200): string
    {
        return $this->response->json($data, $status);
    }

    protected function redirect(string $url): string
    {
        return $this->response->redirect($url);
    }

    protected function back(): string
    {
        return $this->response->back();
    }

    protected function flash(string $type, mixed $message): void
    {
        Session::flash($type, $message);
    }

    protected function validate(array $rules): array
    {
        $validator = new Validator($this->request->getBody(), $rules);
        if (!$validator->passes()) {
            Session::flash('errors', $validator->errors());
            Session::flash('old', $this->request->getBody());
            $this->response->back();
            exit();
        }
        return $validator->validated();
    }

    protected function auth(): ?array
    {
        return Session::get('user');
    }

    protected function authId(): ?int
    {
        return Session::get('user.id');
    }

    protected function hasPermission(string $permission): bool
    {
        $user = $this->auth();
        if (!$user) return false;

        // Super admin bypasses everything
        if (in_array('super_admin', $user['roles'] ?? [])) return true;

        return in_array($permission, $user['permissions'] ?? []);
    }

    protected function abort(int $code = 403): void
    {
        $this->response->abort($code);
    }

    protected function isHtmx(): bool
    {
        return $this->request->isHtmx();
    }

    protected function successResponse(string $message, mixed $data = null, int $status = 200): string
    {
        return $this->json(['success' => true, 'message' => $message, 'data' => $data], $status);
    }

    protected function errorResponse(string $message, mixed $errors = null, int $status = 400): string
    {
        return $this->json(['success' => false, 'message' => $message, 'errors' => $errors], $status);
    }
}
