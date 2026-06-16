<?php
spl_autoload_register(function ($class) {
  $prefix = 'App\\';
  $baseDir = __DIR__ . '/../app/';
  if (strncmp($prefix, $class, strlen($prefix)) !== 0) return;
  $relativeClass = substr($class, strlen($prefix));
  $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
  if (file_exists($file)) require $file;
});

// Debug-friendly error handling: log to file and show errors on page.
error_reporting(E_ALL);
ini_set('display_errors', '1');

$logFile = __DIR__ . '/../storage/logs/app.log';
$logError = function (string $message) use ($logFile): void {
  $timestamp = date('Y-m-d H:i:s');
  $entry = '[' . $timestamp . '] ' . $message . "\n";
  @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
};

set_error_handler(function (int $severity, string $message, string $file, int $line) use ($logError): bool {
  if (!(error_reporting() & $severity)) return false;
  $logError('PHP ERROR ' . $severity . ': ' . $message . ' in ' . $file . ':' . $line);
  return false; // Let PHP display the error too.
});

set_exception_handler(function (Throwable $e) use ($logError): void {
  $logError('UNCAUGHT ' . get_class($e) . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
  http_response_code(500);
  echo 'Uncaught ' . get_class($e) . ': ' . htmlspecialchars($e->getMessage()) . ' in ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine();
});

register_shutdown_function(function () use ($logError): void {
  $error = error_get_last();
  if (!$error) return;
  $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];
  if (!in_array($error['type'], $fatalTypes, true)) return;
  $logError('FATAL ' . $error['type'] . ': ' . $error['message'] . ' in ' . $error['file'] . ':' . $error['line']);
  http_response_code(500);
  echo 'Fatal error: ' . htmlspecialchars($error['message']) . ' in ' . htmlspecialchars($error['file']) . ':' . $error['line'];
});

$appConfig = require __DIR__ . '/../config/app.php';
date_default_timezone_set($appConfig['timezone']);

session_name('PSNF_SESSION');
session_start();

if (!empty($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > $appConfig['session_timeout']) {
  session_unset();
  session_destroy();
  session_start();
}
$_SESSION['last_activity'] = time();

$router = new App\Core\Router();
require __DIR__ . '/../routes/web.php';
$router->dispatch($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
