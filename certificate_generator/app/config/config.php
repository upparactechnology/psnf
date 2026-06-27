<?php
declare(strict_types=1);

define('APP_NAME', 'Web Certificate Generation System');
define('APP_ROOT', dirname(__DIR__, 2));
define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('TIMEZONE', 'Asia/Kolkata');
date_default_timezone_set(TIMEZONE);

$https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$basePath = str_replace('\\', '/', dirname($scriptName));
if ($basePath === '/' || $basePath === '\\' || $basePath === '.') {
    $basePath = '';
}
define('BASE_URL', ($https ? 'https' : 'http') . '://' . $host . $basePath);

// Prefer a shipped/ uploaded TTF within the project if available to avoid
// environment font path issues on some Windows/XAMPP setups.
define('DEFAULT_FONT_PATH', APP_ROOT . '/uploads/fonts/baijamjuree-regular-20260410205944-3f1e77.ttf');

define('TEMPLATES_ROOT', APP_ROOT . '/templates');
define('GENERATED_ROOT', APP_ROOT . '/generated');
define('UPLOADS_ROOT', APP_ROOT . '/uploads');
define('CSV_UPLOAD_ROOT', UPLOADS_ROOT . '/csv');
define('FONTS_ROOT', UPLOADS_ROOT . '/fonts');

define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/jpg']);

define('DEFAULT_FILE_NAME_FORMAT', '{conference}_{year}_{category}_{name}_{id}');
define('DEFAULT_DOWNLOAD_FORMAT', 'pdf');
