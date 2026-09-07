<?php
declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';

$page = (string) ($_GET['page'] ?? (is_logged_in() ? 'dashboard' : 'login'));

$routes = [
    'login' => ['file' => APP_ROOT . '/app/pages/login.php', 'auth' => false],
    'logout' => ['file' => APP_ROOT . '/app/pages/logout.php', 'auth' => true],
    'dashboard' => ['file' => APP_ROOT . '/app/pages/dashboard.php', 'auth' => true],
    'users' => ['file' => APP_ROOT . '/app/pages/users.php', 'auth' => true, 'roles' => ['super_admin']],
    'conferences' => ['file' => APP_ROOT . '/app/pages/conferences.php', 'auth' => true, 'permission' => 'view_certificates'],
    'certificate-types' => ['file' => APP_ROOT . '/app/pages/certificate-types.php', 'auth' => true, 'permission' => 'edit_certificates'],
    'field-editor' => ['file' => APP_ROOT . '/app/pages/field-editor.php', 'auth' => true, 'permission' => 'edit_certificates'],
    'participants' => ['file' => APP_ROOT . '/app/pages/participants.php', 'auth' => true, 'permission' => 'view_certificates'],
    'participants-import' => ['file' => APP_ROOT . '/app/pages/participants_import.php', 'auth' => true, 'permission' => 'create_certificates'],
    'download-certificate' => ['file' => APP_ROOT . '/app/pages/download_certificate.php', 'auth' => false],
    'generate' => ['file' => APP_ROOT . '/app/pages/generate.php', 'auth' => true, 'permission' => 'create_certificates'],
    'emails' => ['file' => APP_ROOT . '/app/pages/emails.php', 'auth' => true, 'permission' => 'create_certificates'],
    'printer' => ['file' => APP_ROOT . '/app/pages/printer.php', 'auth' => true, 'permission' => 'create_certificates'],
    'settings' => ['file' => APP_ROOT . '/app/pages/settings.php', 'auth' => true, 'permission' => 'edit_certificates'],
    'verify' => ['file' => APP_ROOT . '/app/pages/verify.php', 'auth' => false],
];

if (!isset($routes[$page])) {
    http_response_code(404);
    render_view('404.php', ['pageTitle' => 'Page Not Found'], false);
    exit;
}

$route = $routes[$page];

if (($route['auth'] ?? true) === true) {
    require_login();
}

if (isset($route['roles'])) {
    require_roles($route['roles']);
} elseif (isset($route['permission'])) {
    require_permission($route['permission']);
}

$currentPage = $page;
require $route['file'];
