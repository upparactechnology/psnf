<?php

declare(strict_types=1);

namespace Core;

class Response
{
    private int $statusCode = 200;
    private array $headers  = [];

    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        http_response_code($code);
        return $this;
    }

    public function setHeader(string $key, string $value): self
    {
        $this->headers[$key] = $value;
        header("$key: $value");
        return $this;
    }

    public function json(mixed $data, int $statusCode = 200): string
    {
        $this->setStatusCode($statusCode);
        $this->setHeader('Content-Type', 'application/json');
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    public function redirect(string $url, int $statusCode = 302): string
    {
        // Strip header-injection characters
        $url = str_replace(["\r", "\n", "\0"], '', $url);

        if (preg_match('#^(https?:)?//#i', $url) || str_starts_with($url, '\\') || str_starts_with($url, '/\\')) {
            // Absolute / protocol-relative URL: only allow our own host (prevents open redirects)
            $targetHost = strtolower((string) parse_url(str_replace('\\', '/', $url), PHP_URL_HOST));
            $ownHost    = strtolower((string) parse_url('http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'), PHP_URL_HOST));
            if ($targetHost === '' || $targetHost !== $ownHost) {
                $url = function_exists('url') ? url('/') : '/';
            }
        } else {
            $url = function_exists('url') ? url($url) : $url;
        }
        // Flush session before redirect so flash messages persist
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        header("Location: $url", true, $statusCode);
        exit();
    }

    public function back(): string
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        return $this->redirect($referer);
    }

    public function withFlash(string $key, mixed $value): self
    {
        \Core\Session::flash($key, $value);
        return $this;
    }

    public function abort(int $code = 403, string $message = ''): void
    {
        $this->setStatusCode($code);
        if (!$message) {
            $messages = [403 => 'Forbidden', 401 => 'Unauthorized', 404 => 'Not Found', 500 => 'Server Error'];
            $message  = $messages[$code] ?? 'Error';
        }
        echo \Core\View::render('errors/' . $code, ['message' => $message]);
        exit();
    }
}
