<?php
declare(strict_types=1);

header('Content-Type: application/json');

// Check if FastAPI is already running on port 8000
$fp = @fsockopen("127.0.0.1", 8000, $errno, $errstr, 2);
if ($fp) {
    fclose($fp);
    echo json_encode([
        'success' => true,
        'running' => true,
        'message' => 'FastAPI service is already running on port 8000.'
    ]);
    exit();
}

// Attempt background launch on Windows
$backendDir = __DIR__ . '/../../backend';
$cmd = 'start /B python -m uvicorn app:app --host 0.0.0.0 --port 8000 --reload';

$descriptorspec = [
    0 => ["pipe", "r"],
    1 => ["pipe", "w"],
    2 => ["pipe", "w"]
];

$process = @proc_open("cmd.exe /c \"cd /d \"{$backendDir}\" && {$cmd}\"", $descriptorspec, $pipes);

if (is_resource($process)) {
    // Wait 2 seconds and verify
    sleep(2);
    $fpCheck = @fsockopen("127.0.0.1", 8000, $errno, $errstr, 2);
    if ($fpCheck) {
        fclose($fpCheck);
        echo json_encode([
            'success' => true,
            'started' => true,
            'message' => 'FastAPI backend service launched successfully on port 8000!'
        ]);
        exit();
    }
}

echo json_encode([
    'success' => false,
    'message' => 'Could not auto-start backend from PHP. Please run start_backend.bat or run: cd backend && uvicorn app:app --port 8000 in your terminal.',
    'manual_command' => 'cd c:\\xampp\\htdocs\\psnf\\backend && python -m uvicorn app:app --host 0.0.0.0 --port 8000 --reload'
]);
