<?php

use App\Controllers\AdminAuthController;
use App\Controllers\AdminController;
use App\Controllers\StaffAuthController;
use App\Controllers\StaffController;
use App\Controllers\ResourceController;

$router->get('/', [AdminAuthController::class, 'loginView']);
$router->get('/admin/login', [AdminAuthController::class, 'loginView']);
$router->post('/admin/login', [AdminAuthController::class, 'login']);
$router->get('/admin/logout', [AdminAuthController::class, 'logout']);

$router->get('/admin/dashboard', [AdminController::class, 'dashboard']);
$router->get('/admin/analytics', [AdminController::class, 'analytics']);
$router->get('/admin/audit', [AdminController::class, 'audit']);
$router->get('/admin/permissions', [AdminController::class, 'permissions']);
$router->get('/admin/storage', [AdminController::class, 'storage']);
$router->get('/admin/folder-insights', [AdminController::class, 'folderInsights']);
$router->get('/admin/files', [AdminController::class, 'files']);
$router->get('/admin/folders', [AdminController::class, 'folders']);
$router->get('/admin/folders/view', [AdminController::class, 'folderView']);
$router->get('/admin/users', [AdminController::class, 'staff']);
$router->get('/admin/profile', [AdminController::class, 'profile']);
$router->post('/admin/profile/save', [AdminController::class, 'saveProfile']);

$router->post('/admin/resources/upload', [AdminController::class, 'upload']);
$router->post('/admin/resources/update', [AdminController::class, 'updateResource']);
$router->post('/admin/resources/delete', [AdminController::class, 'deleteResource']);
$router->post('/admin/resources/copy', [AdminController::class, 'copyResource']);
$router->post('/admin/resources/move', [AdminController::class, 'moveResource']);
$router->post('/admin/resources/reorder', [AdminController::class, 'reorderResources']);
$router->get('/admin/resources/view', [AdminController::class, 'viewResource']);
$router->get('/admin/resources/stream', [AdminController::class, 'streamResource']);
$router->post('/admin/folders/create', [AdminController::class, 'createFolder']);
$router->post('/admin/folders/update', [AdminController::class, 'updateFolder']);
$router->post('/admin/folders/delete', [AdminController::class, 'deleteFolder']);
$router->post('/admin/assignments/save', [AdminController::class, 'saveAssignment']);
$router->get('/admin/settings', [AdminController::class, 'settings']);
$router->post('/admin/settings/save', [AdminController::class, 'saveSettings']);
$router->get('/admin/staff', [AdminController::class, 'staff']);
$router->post('/admin/staff/create', [AdminController::class, 'createStaff']);
$router->post('/admin/staff/update', [AdminController::class, 'updateStaff']);
$router->post('/admin/staff/toggle', [AdminController::class, 'toggleStaff']);
$router->post('/admin/staff/delete', [AdminController::class, 'deleteStaff']);
$router->post('/admin/staff/reset-password', [AdminController::class, 'resetStaffPassword']);

$router->get('/staff-login', [StaffAuthController::class, 'loginView']);
$router->post('/staff-login', [StaffAuthController::class, 'login']);
$router->get('/staff/logout', [StaffAuthController::class, 'logout']);

$router->get('/staff/dashboard', [StaffController::class, 'dashboard']);
$router->get('/staff/folder', [StaffController::class, 'folder']);
$router->get('/staff/profile', [StaffController::class, 'profile']);
$router->post('/staff/profile/save', [StaffController::class, 'saveProfile']);
$router->post('/staff/favorites/toggle', [StaffController::class, 'toggleFavorite']);
$router->get('/staff/resource/view', [ResourceController::class, 'viewer']);
$router->get('/staff/resource/stream', [ResourceController::class, 'stream']);
