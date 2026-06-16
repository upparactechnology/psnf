<?php

declare(strict_types=1);

namespace Core;

class View
{
    public static function render(string $view, array $data = []): string
    {
        $viewFile = VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View not found: $view ($viewFile)");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // Check if view extends a layout
        if (isset($layout)) {
            $layoutFile = VIEWS_PATH . '/layouts/' . $layout . '.php';
            if (file_exists($layoutFile)) {
                ob_start();
                require $layoutFile;
                return ob_get_clean();
            }
        }

        return $content;
    }

    public static function partial(string $partial, array $data = []): string
    {
        return self::render('partials/' . $partial, $data);
    }

    public static function escape(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    public static function old(string $key, mixed $default = ''): mixed
    {
        $old = Session::get('old') ?? [];
        return $old[$key] ?? $default;
    }

    public static function error(string $key): string
    {
        $errors = Session::get('errors') ?? [];
        return $errors[$key] ?? '';
    }

    public static function hasError(string $key): bool
    {
        $errors = Session::get('errors') ?? [];
        return isset($errors[$key]);
    }

    public static function csrf(): string
    {
        $token = Session::get('csrf_token');
        if (!$token) {
            $token = bin2hex(random_bytes(32));
            Session::set('csrf_token', $token);
        }
        return '<input type="hidden" name="_csrf" value="' . $token . '">';
    }

    public static function csrfToken(): string
    {
        $token = Session::get('csrf_token');
        if (!$token) {
            $token = bin2hex(random_bytes(32));
            Session::set('csrf_token', $token);
        }
        return $token;
    }

    public static function method(string $method): string
    {
        return '<input type="hidden" name="_method" value="' . strtoupper($method) . '">';
    }

    public static function asset(string $path): string
    {
        $base = config('app.base_url', '');
        return rtrim($base, '/') . '/assets/' . ltrim($path, '/');
    }

    public static function route(string $path): string
    {
        $base = config('app.base_url', '');
        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }
}
