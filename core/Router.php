<?php

declare(strict_types=1);

namespace Core;

class Router
{
    private array $routes = [];
    private Request $request;
    private Response $response;
    private array $middlewareAliases = [];

    public function __construct(Request $request, Response $response)
    {
        $this->request  = $request;
        $this->response = $response;

        $this->middlewareAliases = [
            'auth'        => \App\Middleware\AuthMiddleware::class,
            'guest'       => \App\Middleware\GuestMiddleware::class,
            'role'        => \App\Middleware\RoleMiddleware::class,
            'permission'  => \App\Middleware\PermissionMiddleware::class,
            'tenant'      => \App\Middleware\TenantMiddleware::class,
            'rate.limit'  => \App\Middleware\RateLimitMiddleware::class,
            'session.timeout' => \App\Middleware\SessionTimeoutMiddleware::class,
        ];
    }

    public function get(string $path, array|callable $callback, array $middleware = []): self
    {
        return $this->addRoute('GET', $path, $callback, $middleware);
    }

    public function post(string $path, array|callable $callback, array $middleware = []): self
    {
        return $this->addRoute('POST', $path, $callback, $middleware);
    }

    public function put(string $path, array|callable $callback, array $middleware = []): self
    {
        return $this->addRoute('PUT', $path, $callback, $middleware);
    }

    public function patch(string $path, array|callable $callback, array $middleware = []): self
    {
        return $this->addRoute('PATCH', $path, $callback, $middleware);
    }

    public function delete(string $path, array|callable $callback, array $middleware = []): self
    {
        return $this->addRoute('DELETE', $path, $callback, $middleware);
    }

    private function addRoute(string $method, string $path, array|callable $callback, array $middleware = []): self
    {
        $this->routes[] = [
            'method'     => $method,
            'path'       => $path,
            'callback'   => $callback,
            'middleware' => $middleware,
        ];
        return $this;
    }

    public function resolve(): string
    {
        $method = $this->request->getMethod();
        $path   = $this->request->getPath();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;

            $params = $this->matchRoute($route['path'], $path);
            if ($params === false) continue;

            // Run middleware stack
            $middlewareResponse = $this->runMiddleware($route['middleware'], $params);
            if ($middlewareResponse !== null) {
                return $middlewareResponse;
            }

            // Execute callback
            $callback = $route['callback'];

            if (is_callable($callback)) {
                return (string) call_user_func_array($callback, $params);
            }

            if (is_array($callback)) {
                [$controllerClass, $action] = $callback;
                $fullClass = str_starts_with($controllerClass, 'App\\')
                    ? $controllerClass
                    : 'App\\Controllers\\' . $controllerClass;

                if (!class_exists($fullClass)) {
                    $file = APP_PATH . '/Controllers/' . str_replace('App\\Controllers\\', '', $fullClass) . '.php';
                    if (file_exists($file)) require_once $file;
                }

                $controller = new $fullClass();
                return (string) $controller->$action(...$params);
            }
        }

        // 404
        return $this->handleNotFound();
    }

    private function matchRoute(string $routePath, string $requestPath): array|false
    {
        // Convert route params like {id} to regex
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $requestPath, $matches)) {
            array_shift($matches);
            return $matches;
        }

        return false;
    }

    private function runMiddleware(array $middleware, array $params): ?string
    {
        foreach ($middleware as $mw) {
            $args = [];

            // Support "role:admin" syntax
            if (str_contains($mw, ':')) {
                [$mwName, $mwArg] = explode(':', $mw, 2);
                $args = explode(',', $mwArg);
            } else {
                $mwName = $mw;
            }

            $mwClass = $this->middlewareAliases[$mwName] ?? $mwName;

            if (!class_exists($mwClass)) {
                $file = APP_PATH . '/Middleware/' . basename(str_replace('\\', '/', $mwClass)) . '.php';
                if (file_exists($file)) require_once $file;
            }

            if (!class_exists($mwClass)) continue;

            $instance = new $mwClass(...$args);
            $result   = $instance->handle($this->request);

            if ($result !== null) {
                return $result;
            }
        }

        return null;
    }

    private function handleNotFound(): string
    {
        $this->response->setStatusCode(404);
        if (file_exists(VIEWS_PATH . '/errors/404.php')) {
            return \Core\View::render('errors/404');
        }
        return '<h1 style="font-family:sans-serif;color:#f38ba8;text-align:center;padding:60px;">404 — Page Not Found</h1>';
    }
}
