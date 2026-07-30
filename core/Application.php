<?php

declare(strict_types=1);

namespace Core;

class Application
{
    public static $app;
    public Router $router;
    public Request $request;
    public Response $response;
    public Session $session;
    public Database $db;

    public function __construct()
    {
        self::$app = $this;

        $this->loadHelpers();
        $this->loadConfig();
        $this->initErrorHandling();

        $this->session  = new Session();
        $this->request  = new Request();
        $this->response = new Response();
        $this->db       = new Database();
        $this->router   = new Router($this->request, $this->response);

        $this->loadRoutes();
    }

    private function loadHelpers(): void
    {
        $files = [
            CORE_PATH . '/helpers.php',
            CORE_PATH . '/Router.php',
            CORE_PATH . '/Request.php',
            CORE_PATH . '/Response.php',
            CORE_PATH . '/Database.php',
            CORE_PATH . '/Session.php',
            CORE_PATH . '/View.php',
            CORE_PATH . '/Controller.php',
            CORE_PATH . '/Model.php',
            CORE_PATH . '/JWT.php',
            CORE_PATH . '/Validator.php',
            CORE_PATH . '/RateLimiter.php',
        ];

        foreach ($files as $file) {
            require_once $file;
        }

        // Auto-load App classes
        spl_autoload_register(function (string $class): void {
            $class = str_replace('\\', '/', $class);
            $class = str_replace('App/', '', $class);
            $file  = APP_PATH . '/' . $class . '.php';
            if (file_exists($file)) {
                require_once $file;
            }
        });
    }

    private function loadConfig(): void
    {
        $configs = ['app', 'database', 'auth'];
        foreach ($configs as $config) {
            $file = CONFIG_PATH . '/' . $config . '.php';
            if (file_exists($file)) {
                $key            = $config;
                $GLOBALS['config'][$key] = require $file;
            }
        }

        // Set system default timezone
        $timezone = config('app.timezone', 'UTC');
        date_default_timezone_set($timezone);
    }

    private function initErrorHandling(): void
    {
        $debug = config('app.debug', false);

        error_reporting($debug ? E_ALL : E_ERROR | E_PARSE);
        ini_set('display_errors', $debug ? '1' : '0');
        ini_set('log_errors', '1');
        ini_set('error_log', \STORAGE_PATH . '/logs/error.log');

        set_exception_handler(function (\Throwable $e): void {
            if (config('app.debug', false)) {
                error_log("Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString());
                echo '<pre style="background:#1e1e2e;color:#cdd6f4;padding:20px;font-family:monospace;">';
                echo '<b style="color:#f38ba8">Exception:</b> ' . htmlspecialchars($e->getMessage()) . "\n";
                echo '<b style="color:#a6e3a1">File:</b> ' . $e->getFile() . ':' . $e->getLine() . "\n\n";
                echo '<b style="color:#89b4fa">Trace:</b>' . "\n" . htmlspecialchars($e->getTraceAsString());
                echo '</pre>';
            } else {
                http_response_code(500);
                echo '500 Internal Server Error';
            }
        });
    }

    private function loadRoutes(): void
    {
        $router = $this->router;
        require ROOT_PATH . '/routes/web.php';
        require ROOT_PATH . '/routes/api.php';
    }

    public function run(): void
    {
        echo $this->router->resolve();
    }
}
