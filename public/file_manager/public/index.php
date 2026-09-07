<?php
// ─── PSNF ERP Session Bridge: Auto-login from main ERP ─────────────────────
// The main ERP uses session name 'PSNF_SESSION'. We read it first, extract
// the logged-in user, then restore our own session for the file manager.
// Staff/teacher/driver roles are routed to the staff portal; admin roles
// go to the admin portal.

$erpUser = null;

// Always read the ERP session to determine correct portal type
$currentSessionId = session_id();
if (!empty($currentSessionId)) {
    session_write_close();
}

session_name('PSNF_SESSION');
session_start();
$erpUser = $_SESSION['user'] ?? null;
session_write_close();

// Restore file manager session
session_name('PSNF_FM_SESSION');
session_start();

if ($erpUser && !empty($erpUser['id'])) {
    $erpRoles = $erpUser['roles'] ?? [];
    $staffRoles = ['teacher', 'staff', 'driver'];
    $isStaff = !empty(array_intersect($erpRoles, $staffRoles));

    if ($isStaff) {
        // Staff user — ensure they have staff_id, not admin_id
        if (!empty($_SESSION['admin_id'])) {
            unset($_SESSION['admin_id'], $_SESSION['admin_name']);
        }
        // Look up this ERP user in the file manager's staff table by email
        $erpEmail = $erpUser['email'] ?? '';
        if ($erpEmail !== '' && empty($_SESSION['staff_id'])) {
            try {
                $fmDb = require __DIR__ . '/../config/database.php';
                $pdo = new PDO(
                    "mysql:host={$fmDb['host']};port={$fmDb['port']};dbname={$fmDb['database']};charset={$fmDb['charset']}",
                    $fmDb['username'],
                    $fmDb['password'],
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                $stmt = $pdo->prepare("SELECT id, name FROM staff WHERE email = ? AND is_active = 1 LIMIT 1");
                $stmt->execute([$erpEmail]);
                $staffRow = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$staffRow) {
                    // Auto-create staff record from ERP user data
                    $erpName = $erpUser['name'] ?? 'Staff';
                    $defaultPass = password_hash('changeme123', PASSWORD_BCRYPT);
                    $ins = $pdo->prepare("INSERT INTO staff (name, email, password, is_active) VALUES (?, ?, ?, 1)");
                    $ins->execute([$erpName, $erpEmail, $defaultPass]);
                    $staffId = (int) $pdo->lastInsertId();
                    if ($staffId > 0) {
                        $staffRow = ['id' => $staffId, 'name' => $erpName];
                    }
                }

                if ($staffRow) {
                    $_SESSION['staff_id']   = (int) $staffRow['id'];
                    $_SESSION['staff_name'] = (string) ($staffRow['name'] ?? $erpUser['name'] ?? 'Staff');
                }
            } catch (\Throwable $e) {
                // DB error — fall through
            }
        }
        $_SESSION['erp_bridged'] = true;
    } else {
        // Admin/manager roles — ensure they have admin_id, not staff_id
        if (!empty($_SESSION['staff_id'])) {
            unset($_SESSION['staff_id'], $_SESSION['staff_name']);
        }
        $_SESSION['admin_id']   = (int) $erpUser['id'];
        $_SESSION['admin_name'] = (string) ($erpUser['name'] ?? 'ERP User');
        $_SESSION['erp_bridged'] = true;
    }
}
// ────────────────────────────────────────────────────────────────────────────

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

if (session_status() !== PHP_SESSION_ACTIVE) {
  session_name('PSNF_FM_SESSION');
  session_start();
}

if (!empty($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > ($appConfig['session_timeout'] ?? 3600)) {
  // Only destroy if NOT bridged from ERP
  if (empty($_SESSION['erp_bridged'])) {
    session_unset();
    session_destroy();
    session_start();
  }
}
$_SESSION['last_activity'] = time();

$router = new App\Core\Router();
require __DIR__ . '/../routes/web.php';
$router->dispatch($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

