<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;

class FaceRecognitionController extends Controller
{
    private function db()
    {
        return Application::$app->db;
    }

    public function index(): string
    {
        return $this->redirect(url('public/attendance/index.php'));
    }

    public function registerView(): string
    {
        return $this->redirect(url('public/attendance/register.php'));
    }

    public function verifyView(): string
    {
        return $this->redirect(url('public/attendance/verify.php'));
    }

    public function historyView(): string
    {
        return $this->redirect(url('public/attendance/history.php'));
    }
}
