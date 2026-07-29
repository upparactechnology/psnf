<?php
declare(strict_types=1);
header('Content-Type: application/json');

/**
 * InsightFace Backend Launcher
 * Checks if the Python FastAPI service is running on port 8000.
 * If not, attempts to launch it via uvicorn.
 */

$backendDir = realpath(__DIR__ . '/../../backend');
$port = 8000;

// 1. Check if already running
$fp = @fsockopen('127.0.0.1', $port, $errno, $errstr, 2);
if ($fp) {
    fclose($fp);
    echo json_encode([
        'success'  => true,
        'running'  => true,
        'engine'   => 'InsightFace (buffalo_l)',
        'message'  => 'InsightFace backend is already running on port ' . $port . '.',
    ]);
    exit();
}

// 2. Attempt auto-launch on Windows via cmd.exe
if (!$backendDir || !is_dir($backendDir)) {
    echo json_encode([
        'success' => false,
        'message' => 'Backend directory not found: ' . ($backendDir ?: 'unknown'),
    ]);
    exit();
}

$logFile = $backendDir . '\\backend_startup.log';
$cmd = "start /B python -m uvicorn app:app --host 0.0.0.0 --port {$port} --reload > \"{$logFile}\" 2>&1";

$descriptorspec = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
];

$process = @proc_open(
    "cmd.exe /c \"cd /d \"{$backendDir}\" && {$cmd}\"",
    $descriptorspec,
    $pipes
);

if (is_resource($process)) {
    proc_close($process);

    // Wait up to 5 seconds for the port to open
    $started = false;
    for ($i = 0; $i < 10; $i++) {
        sleep(1);
        $check = @fsockopen('127.0.0.1', $port, $e, $s, 1);
        if ($check) {
            fclose($check);
            $started = true;
            break;
        }
    }

    if ($started) {
        echo json_encode([
            'success' => true,
            'started' => true,
            'engine'  => 'InsightFace (buffalo_l)',
            'message' => 'InsightFace backend launched successfully on port ' . $port . '!',
        ]);
    } else {
        echo json_encode([
            'success'        => false,
            'message'        => 'Backend process started but port ' . $port . ' did not open in time. Check startup log.',
            'log'            => $logFile,
            'manual_command' => "cd \"{$backendDir}\" && python -m uvicorn app:app --host 0.0.0.0 --port {$port} --reload",
        ]);
    }
    exit();
}

// 3. Auto-launch failed — return manual instructions
echo json_encode([
    'success'        => false,
    'engine'         => 'InsightFace (buffalo_l) — REQUIRED',
    'message'        => 'Could not auto-start the InsightFace backend. Please start it manually.',
    'manual_command' => "cd \"{$backendDir}\" && python -m uvicorn app:app --host 0.0.0.0 --port {$port} --reload",
    'note'           => 'Make sure Python is in your PATH and all requirements are installed: pip install -r requirements.txt',
]);
