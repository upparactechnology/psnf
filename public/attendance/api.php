<?php
declare(strict_types=1);

/**
 * PSNF Face Recognition API Proxy
 * ─────────────────────────────────────────────────────────────────────────────
 * Proxies a fixed whitelist of endpoints to the Python InsightFace backend
 * running on http://127.0.0.1:8000 (FastAPI + uvicorn, loopback only).
 *
 * Access control:
 *   - kiosk endpoints (health, recognize-face, verify-face): a logged-in ERP
 *     session, or the per-session kiosk token (X-Kiosk-Token) issued by
 *     KioskAuthMiddleware when the face kiosk page is opened.
 *   - everything else (registration, lists, history, deletes, model control,
 *     face images): logged-in ERP user who is super_admin / school_admin or
 *     holds a face-register permission.
 * Every proxied request carries X-Internal-Key (env FACE_API_KEY or
 * storage/keys/face_api_key), which the FastAPI backend requires.
 * ─────────────────────────────────────────────────────────────────────────────
 */

date_default_timezone_set('Asia/Kolkata');
error_reporting(0);
ini_set('display_errors', '0');
ob_start();

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function face_api_fail(int $code, string $message): void
{
    if (ob_get_level()) ob_end_clean();
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => $message]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    face_api_fail(405, 'Method not allowed.');
}

$rootPath = dirname(__DIR__, 2);
require_once $rootPath . '/core/helpers.php'; // app_secret()

// ─── Endpoint whitelist ──────────────────────────────────────────────────────
// path => [allowed methods, access level, tenant param mode]
$routes = [
    '/health'                  => [['GET'],           'kiosk', null],
    '/api/recognize-face'      => [['POST'],          'kiosk', 'body'],
    '/api/verify-face'         => [['POST'],          'kiosk', 'body'],
    '/api/register-face'       => [['POST'],          'admin', 'body'],
    '/api/employees/list'      => [['GET'],           'admin', 'query'],
    '/api/employees/delete'    => [['DELETE', 'POST'],'admin', 'query'],
    '/api/users/list'          => [['GET'],           'admin', 'query'],
    '/api/attendance/history'  => [['GET'],           'admin', 'query'],
    '/api/attendance/delete'   => [['DELETE', 'POST'],'admin', 'query'],
    '/api/detect-frame'        => [['POST'],          'admin', null],
    '/api/diagnostics/config'  => [['GET'],           'admin', null],
    '/api/model/load'          => [['POST'],          'admin', null],
    '/api/model/unload'        => [['POST'],          'admin', null],
];

$endpoint = (string) ($_GET['endpoint'] ?? $_SERVER['PATH_INFO'] ?? '/health');
$endpoint = '/' . ltrim($endpoint, '/');
if (!str_starts_with($endpoint, '/api/') && !str_starts_with($endpoint, '/health') && !str_starts_with($endpoint, '/uploads/')) {
    $endpoint = '/api/' . ltrim($endpoint, '/');
}

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

if (preg_match('#^/uploads/(faces|attendance|failed)/([A-Za-z0-9_\-]+\.(?:jpg|jpeg|png|webp))$#i', $endpoint, $m)) {
    $routeCfg = [['GET'], 'admin', null];
} elseif (isset($routes[$endpoint])) {
    $routeCfg = $routes[$endpoint];
} else {
    face_api_fail(404, 'Unknown endpoint.');
}
[$allowedMethods, $access, $tenantMode] = $routeCfg;
if (!in_array($method, $allowedMethods, true)) {
    face_api_fail(405, 'Method not allowed.');
}

// ─── Authentication (ERP session) ────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_name('PSNF_SESSION');
    session_start(['read_and_close' => true]); // don't hold the session lock during inference
}
$sessionUser = $_SESSION['user'] ?? null;
$userId      = is_array($sessionUser) ? (int) ($sessionUser['id'] ?? 0) : 0;
$kioskToken  = (string) ($_SESSION['kiosk_token'] ?? '');

$dbUser = null;
$roles = [];
$perms = [];
if ($userId > 0) {
    try {
        $dbCfg = require $rootPath . '/config/database.php';
        $pdo = new PDO(
            sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $dbCfg['host'], (int) $dbCfg['port'], $dbCfg['dbname'], $dbCfg['charset'] ?? 'utf8mb4'),
            $dbCfg['username'],
            $dbCfg['password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
        $st = $pdo->prepare('SELECT id, tenant_id, is_active FROM users WHERE id = ? AND deleted_at IS NULL LIMIT 1');
        $st->execute([$userId]);
        $dbUser = $st->fetch() ?: null;
        if ($dbUser && (int) $dbUser['is_active'] === 1) {
            $st = $pdo->prepare('SELECT r.slug FROM roles r JOIN user_roles ur ON ur.role_id = r.id WHERE ur.user_id = ? AND r.deleted_at IS NULL');
            $st->execute([$userId]);
            $roles = array_column($st->fetchAll(), 'slug');
            $st = $pdo->prepare('SELECT DISTINCT p.slug FROM permissions p JOIN role_permissions rp ON rp.permission_id = p.id JOIN user_roles ur ON ur.role_id = rp.role_id WHERE ur.user_id = ?');
            $st->execute([$userId]);
            $perms = array_column($st->fetchAll(), 'slug');
        } else {
            $dbUser = null;
        }
    } catch (\Throwable $e) {
        face_api_fail(500, 'Authentication check failed.');
    }
}

$isAdmin = $dbUser !== null && (
    array_intersect($roles, ['super_admin', 'school_admin']) ||
    array_intersect($perms, ['view_face_register', 'create_face_register', 'edit_face_register', 'delete_face_register'])
);

if ($access === 'admin') {
    if ($dbUser === null) face_api_fail(401, 'Please log in to continue.');
    if (!$isAdmin) face_api_fail(403, 'You are not allowed to manage face attendance.');
} else { // kiosk
    $supplied = (string) ($_SERVER['HTTP_X_KIOSK_TOKEN'] ?? '');
    $kioskOk  = $kioskToken !== '' && $supplied !== '' && hash_equals($kioskToken, $supplied);
    if ($dbUser === null && !$kioskOk) {
        face_api_fail(401, 'Kiosk not authorized. Reload the face kiosk page.');
    }
}

$tenantId = $dbUser ? (int) $dbUser['tenant_id'] : null;

// ─── Build upstream request ──────────────────────────────────────────────────
$fastApiBase = 'http://127.0.0.1:8000';

$queryParams = $_GET;
unset($queryParams['endpoint']);
if ($tenantMode === 'query' && $tenantId) {
    $queryParams['tenant_id'] = $tenantId;
}
$qs = http_build_query($queryParams);
$targetUrl = $fastApiBase . $endpoint . ($qs ? '?' . $qs : '');

$requestBody = in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true) ? (string) file_get_contents('php://input') : '';
if ($tenantMode === 'body' && $tenantId && $requestBody !== '') {
    $decoded = json_decode($requestBody, true);
    if (is_array($decoded)) {
        $decoded['tenant_id'] = $tenantId;
        $requestBody = json_encode($decoded);
    }
}

try {
    $internalKey = app_secret('FACE_API_KEY', 'face_api_key');
} catch (\Throwable $e) {
    face_api_fail(500, 'Face service key is not configured.');
}

// ─── Check Python backend is reachable ───────────────────────────────────────
$fp = @fsockopen('127.0.0.1', 8000, $errno, $errstr, 1.5);
if (!$fp) {
    if (ob_get_level()) ob_end_clean();
    http_response_code(503);
    echo json_encode([
        'success' => false,
        'engine'  => 'InsightFace/Python (REQUIRED)',
        'message' => 'InsightFace backend is offline. Please start the Python service.',
        'fix'     => 'Start the face recognition service (start_backend.bat) on the server.',
    ]);
    exit();
}
fclose($fp);

// ─── Proxy request to InsightFace backend ────────────────────────────────────
$headers = ['Accept: application/json', 'X-Internal-Key: ' . $internalKey];
$ch = curl_init($targetUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 30,        // allow up to 30s for model inference
    CURLOPT_CONNECTTIMEOUT => 3,
    CURLOPT_CUSTOMREQUEST  => $method,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_PROTOCOLS      => CURLPROTO_HTTP,
]);

if ($requestBody !== '') {
    curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'Content-Length: ' . strlen($requestBody);
}
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response    = curl_exec($ch);
$httpCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

if (ob_get_level()) ob_end_clean();

// ─── Handle cURL failure ──────────────────────────────────────────────────────
if ($response === false || $httpCode === 0) {
    http_response_code(503);
    echo json_encode([
        'success' => false,
        'engine'  => 'InsightFace/Python (REQUIRED)',
        'message' => 'Failed to reach the InsightFace backend.',
    ]);
    exit();
}

// ─── Return upstream response ─────────────────────────────────────────────────
http_response_code($httpCode);
if ($contentType && preg_match('#^(application/json|image/(jpeg|png|webp))#i', (string) $contentType)) {
    header('Content-Type: ' . $contentType);
}
if (str_starts_with($endpoint, '/uploads/')) {
    header('Cache-Control: private, max-age=300');
}
echo $response;
