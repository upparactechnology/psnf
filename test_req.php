<?php

require __DIR__ . '/core/Request.php';

function test($uri, $scriptName, $docRoot = '', $scriptFilename = '') {
    $_SERVER['REQUEST_URI'] = $uri;
    $_SERVER['SCRIPT_NAME'] = $scriptName;
    $_SERVER['DOCUMENT_ROOT'] = $docRoot;
    $_SERVER['SCRIPT_FILENAME'] = $scriptFilename;

    $req = new \Core\Request();
    $base = $req->detectBasePath();
    $path = $req->getPath();

    echo "URI: " . str_pad($uri, 30) . " | SCRIPT: " . str_pad($scriptName, 25) . " => Base: " . str_pad(var_export($base, true), 20) . " => Path: " . var_export($path, true) . "\n";
}

echo "--- CURRENT BEHAVIOR ---\n";
test('/erpv2/public/', '/index.php');
test('/erpv2/public', '/index.php');
test('/erpv2/public/login', '/index.php');
test('/erpv2/public/students', '/index.php');
test('/erpv2/public/', '/erpv2/public/index.php');
test('/erpv2/public/login', '/erpv2/public/index.php');
test('/psnf/public/', '/psnf/public/index.php');
test('/psnf/public/login', '/psnf/public/index.php');
test('/', '/index.php');
test('/login', '/index.php');