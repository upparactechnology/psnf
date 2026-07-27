<?php
declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';

$page = (string) ($_GET['page'] ?? (is_logged_in() ? 'dashboard' : 'login'));

$routes = [
    'login' => ['file' => APP_ROOT . '/app/pages/login.php', 'auth' => false],
    'logout' => ['file' => APP_ROOT . '/app/pages/logout.php', 'auth' => true],
    'dashboard' => ['file' => APP_ROOT . '/app/pages/dashboard.php', 'auth' => true],
    'users' => ['file' => APP_ROOT . '/app/pages/users.php', 'auth' => true, 'roles' => ['super_admin']],
    'conferences' => ['file' => APP_ROOT . '/app/pages/conferences.php', 'auth' => true, 'roles' => ['super_admin', 'school_admin', 'manager']],
    'certificate-types' => ['file' => APP_ROOT . '/app/pages/certificate-types.php', 'auth' => true, 'roles' => ['super_admin', 'school_admin', 'manager']],
    'field-editor' => ['file' => APP_ROOT . '/app/pages/field-editor.php', 'auth' => true, 'roles' => ['super_admin', 'school_admin', 'manager']],
    'participants' => ['file' => APP_ROOT . '/app/pages/participants.php', 'auth' => true, 'roles' => ['super_admin', 'school_admin', 'manager']],
    'participants-import' => ['file' => APP_ROOT . '/app/pages/participants_import.php', 'auth' => true, 'roles' => ['super_admin', 'school_admin', 'manager']],
    'download-certificate' => ['file' => APP_ROOT . '/app/pages/download_certificate.php', 'auth' => false],
    'generate' => ['file' => APP_ROOT . '/app/pages/generate.php', 'auth' => true, 'roles' => ['super_admin', 'school_admin', 'manager']],
    'downloads' => ['file' => APP_ROOT . '/app/pages/downloads.php', 'auth' => true, 'roles' => ['super_admin', 'school_admin', 'manager']],
    'download-file' => ['file' => APP_ROOT . '/app/pages/download-file.php', 'auth' => false],
    'emails' => ['file' => APP_ROOT . '/app/pages/emails.php', 'auth' => true, 'roles' => ['super_admin', 'school_admin', 'manager']],
    'contact-messages' => ['file' => APP_ROOT . '/app/pages/contact_messages.php', 'auth' => true, 'roles' => ['super_admin', 'school_admin', 'manager']],
    'settings' => ['file' => APP_ROOT . '/app/pages/settings.php', 'auth' => true, 'roles' => ['super_admin']],
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
}

$currentPage = $page;
require $route['file'];
