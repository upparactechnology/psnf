<?php
declare(strict_types=1);

require_once __DIR__ . '/config/config.php';

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $relativePath = str_replace('\\', '/', $relative);

    $segments = explode('/', $relativePath);
    if (!empty($segments)) {
        $segments[0] = strtolower($segments[0]);
    }

    $file = APP_ROOT . '/app/' . implode('/', $segments) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

require_once __DIR__ . '/lib/helpers.php';

$dbConfig = require __DIR__ . '/config/database.php';
$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    $dbConfig['host'],
    $dbConfig['port'],
    $dbConfig['dbname'],
    $dbConfig['charset']
);

try {
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $exception) {
    http_response_code(500);
    echo 'Database connection failed: ' . htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8');
    exit;
}

$GLOBALS['app_pdo'] = $pdo;
$GLOBALS['app_settings_cache'] = [];

// Auto-upgrade legacy field_mappings schema for long template content and style metadata.
try {
    $columnsStmt = $pdo->prepare(
        "SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = 'field_mappings'"
    );
    $columnsStmt->execute();

    $columns = [];
    foreach ($columnsStmt->fetchAll() as $columnRow) {
        $columnName = (string) ($columnRow['COLUMN_NAME'] ?? '');
        if ($columnName !== '') {
            $columns[$columnName] = $columnRow;
        }
    }

    if (isset($columns['label'])) {
        $labelColumn = $columns['label'];
        $dataType = strtolower((string) ($labelColumn['DATA_TYPE'] ?? ''));
        $maxLength = (int) ($labelColumn['CHARACTER_MAXIMUM_LENGTH'] ?? 0);

        if (($dataType === 'varchar' || $dataType === 'char') && $maxLength > 0 && $maxLength < 2000) {
            $pdo->exec('ALTER TABLE field_mappings MODIFY label TEXT NOT NULL');
        }
    }

    if ($columns !== [] && !isset($columns['options_json'])) {
        $pdo->exec('ALTER TABLE field_mappings ADD COLUMN options_json TEXT NULL AFTER label');
    }
} catch (Throwable $exception) {
    if (APP_ENV === 'development') {
        error_log('Schema auto-migration skipped: ' . $exception->getMessage());
    }
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('PSNF_SESSION');
    session_start();
}

initialize_storage_paths();
