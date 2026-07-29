<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;
use Core\View;

class FaceRecognitionController extends Controller
{
    private function db()
    {
        return Application::$app->db;
    }

    public function index(): string
    {
        return $this->redirect('/attendance/face-kiosk');
    }

    /**
     * Face attendance kiosk - embedded in ERP layout, no auth required.
     */
    public function kioskView(): string
    {
        return View::render('attendance/face_kiosk', []);
    }

    /**
     * Face registration - embedded in ERP layout, requires admin auth overlay.
     * Handles AJAX login/logout POST via the view itself.
     */
    public function registerView(): string
    {
        return View::render('attendance/face_register', []);
    }

    public function historyView(): string
    {
        return $this->redirect('/attendance/face-kiosk');
    }

    public function verifyView(): string
    {
        return $this->redirect('/attendance/face-kiosk');
    }
}
