<?php require 'vendor/autoload.php'; require 'core/Application.php'; $app = new \Core\Application(__DIR__); $ctrl = new \App\Controllers\TransportController(); echo $ctrl->apiTestCalc();
