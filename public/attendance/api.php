<?php
declare(strict_types=1);

/**
 * PSNF Face Recognition API Proxy
 * ─────────────────────────────────────────────────────────────────────────────
 * Strictly proxies ALL requests to the Python InsightFace backend running on
 * http://127.0.0.1:8000 (FastAPI + uvicorn).
 *
 * ⚠ There is NO PHP fallback. Python/InsightFace is MANDATORY.
 *    If the backend is offline this API returns a clear 503 error.
 *
 * To start the backend:
 *   cd C:\xampp\htdocs\psnf\backend
 *   python -m uvicorn app:app --host 0.0.0.0 --port 8000 --reload
 * ─────────────────────────────────────────────────────────────────────────────
 */

date_default_timezone_set('Asia/Kolkata');
error_reporting(0);
ini_set('display_errors', '0');
ob_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE, PUT, PATCH');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Pre-flight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    http_response_code(200);
    exit();
}

// ─── Resolve endpoint ─────────────────────────────────────────────────────────
$fastApiBase = 'http://127.0.0.1:8000';
$endpoint    = $_GET['endpoint'] ?? $_SERVER['PATH_INFO'] ?? '/health';

if (strpos($endpoint, '/api') !== 0 && strpos($endpoint, '/health') !== 0) {
    $endpoint = '/api/' . ltrim($endpoint, '/');
}

// Preserve query string params (exclude 'endpoint' itself)
$queryParams = $_GET;
unset($queryParams['endpoint']);
$qs = http_build_query($queryParams);

$targetUrl   = $fastApiBase . $endpoint . ($qs ? '?' . $qs : '');
$requestBody = file_get_contents('php://input');
$method      = $_SERVER['REQUEST_METHOD'];

// ─── Check Python backend is reachable ───────────────────────────────────────
$fp = @fsockopen('127.0.0.1', 8000, $errno, $errstr, 1.5);
if (!$fp) {
    ob_end_clean();
    http_response_code(503);
    echo json_encode([
        'success' => false,
        'engine'  => 'InsightFace/Python (REQUIRED)',
        'message' => '⚠ InsightFace backend is offline. Please start the Python service.',
        'fix'     => 'Run: cd C:\\xampp\\htdocs\\psnf\\backend && python -m uvicorn app:app --host 0.0.0.0 --port 8000 --reload',
        'port'    => 8000,
        'error'   => $errstr ?: 'Connection refused'
    ]);
    exit();
}
fclose($fp);

// ─── Proxy request to InsightFace backend ────────────────────────────────────
$ch = curl_init($targetUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 30,        // allow up to 30s for model inference
    CURLOPT_CONNECTTIMEOUT => 3,
    CURLOPT_CUSTOMREQUEST  => $method,
    CURLOPT_FOLLOWLOCATION => true,
]);

// Forward body for POST/PUT/PATCH/DELETE
if (!empty($requestBody) && in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
    curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($requestBody),
        'Accept: application/json',
    ]);
} else {
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
    ]);
}

$response  = curl_exec($ch);
$httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

ob_end_clean();

// ─── Handle cURL failure ──────────────────────────────────────────────────────
if ($response === false || $httpCode === 0) {
    http_response_code(503);
    echo json_encode([
        'success' => false,
        'engine'  => 'InsightFace/Python (REQUIRED)',
        'message' => 'Failed to reach the InsightFace backend. cURL error: ' . $curlError,
        'fix'     => 'Ensure uvicorn is running: python -m uvicorn app:app --port 8000',
    ]);
    exit();
}

// ─── Return upstream response as-is ─────────────────────────────────────────
http_response_code($httpCode);
echo $response;
