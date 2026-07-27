# PSNF Secure Digital Resource Management System

Local-first Core PHP MVC project for XAMPP with premium SaaS-style dashboard UI.

## Stack
- Core PHP (MVC)
- MySQL
- Bootstrap 5
- jQuery + AJAX
- JavaScript
- PDF.js
- Video.js

## Quick Start (XAMPP)
1. Create DB and tables by importing `database/schema.sql`.
2. Update DB credentials in `config/database.php`.
3. Place project in `htdocs/files/version 1`.
4. Open: `http://localhost/files/version%201/public/admin/login`
5. Staff portal: `http://localhost/files/version%201/public/staff-login`

## Default Credentials
- Admin: `admin@psnf.local` / `Admin@123`
- Staff: `staff@psnf.local` / `Admin@123`

## Security Features Included
- Role-based isolation (admin vs staff portal)
- Staff sees only assigned folders/resources
- Hidden real file URLs (stream via controller)
- Download/right-click/shortcut restrictions (UI-level)
- Blur on focus loss
- Watermark overlay
- Global access-time lock for all staff
- Mobile blocking toggle
- Session timeout
- Activity logs + failed login logs

## Important Notes
- Files are stored under `storage/private` and never directly exposed.
- Local/VPS mode toggle is available in Admin Settings for future deployment mode behavior.
- Debug mode toggle is persisted in settings table and prepared for environment-level behavior.

## Main Routes
- `/admin/login`
- `/admin/dashboard`
- `/admin/settings`
- `/staff-login`
- `/staff/dashboard`
- `/staff/resource/view?id={id}`

## Next Enhancements
- Staff registration/reset flows UI
- Chart widgets with live analytics
- Tokenized streaming with expiring signatures
- Server-level anti-download headers per file type
- Queue-based large bulk upload processing
