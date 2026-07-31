<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\View;
use Core\Session;
use Core\Application;
use Core\Database;

class AnnouncementController extends Controller
{
    private function db()
    {
        return Application::$app->db;
    }

    public function index(): string
    {
        $tenantId = Database::getTenantId();
        
        $announcements = $this->db()->select("
            SELECT a.*, u.name as created_by_name 
            FROM announcements a
            LEFT JOIN users u ON a.created_by = u.id
            WHERE a.tenant_id = ?
            ORDER BY a.published_at DESC
        ", [$tenantId]);

        return View::render('academics/announcements/index', [
            'announcements' => $announcements,
            'title' => 'Manage Announcements',
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error'),
        ]);
    }

    public function store(): string
    {
        $tenantId = Database::getTenantId();
        $schoolId = Database::getSchoolId();
        $branchId = Database::getBranchId();

        $body = $this->request->getBody();
        $title = trim($body['title'] ?? '');
        $content = trim($body['content'] ?? '');
        $targetAudience = $body['target_audience'] ?? 'parents';

        if (empty($title) || empty($content)) {
            Session::flash('error', 'Title and content are required.');
            Application::$app->response->redirect('/academics/announcements');
            return '';
        }

        try {
            $this->db()->insert('announcements', [
                'tenant_id' => $tenantId,
                'school_id' => $schoolId,
                'branch_id' => $branchId,
                'title' => $title,
                'content' => $content,
                'target_audience' => $targetAudience,
                'created_by' => auth_id(),
                'published_at' => date('Y-m-d H:i:s'),
            ]);

            Session::flash('success', 'Announcement sent successfully.');
        } catch (\Exception $e) {
            Session::flash('error', 'Failed to send announcement: ' . $e->getMessage());
        }

        Application::$app->response->redirect('/academics/announcements');
        return '';
    }

    public function destroy(string $id): string
    {
        $tenantId = Database::getTenantId();
        
        $announcement = $this->db()->selectOne("SELECT id FROM announcements WHERE id = ? AND tenant_id = ?", [$id, $tenantId]);
        if (!$announcement) {
            Session::flash('error', 'Announcement not found.');
            Application::$app->response->redirect('/academics/announcements');
            return '';
        }

        try {
            $this->db()->query("DELETE FROM announcements WHERE id = ?", [$id]);
            Session::flash('success', 'Announcement deleted successfully.');
        } catch (\Exception $e) {
            Session::flash('error', 'Failed to delete announcement.');
        }

        Application::$app->response->redirect('/academics/announcements');
        return '';
    }
}
