# Web-Based Certificate Generation System (Core PHP + MySQL)

A complete multi-conference certificate management system for XAMPP.

## Stack

- PHP 8.x (Core PHP, no framework)
- MySQL (MariaDB via XAMPP)
- HTML, CSS, JavaScript
- PHP GD for dynamic certificate rendering
- ZIP via ZipArchive
- PHPMailer integration (with native mail fallback)

## Features

- Role-based authentication (Super Admin, Admin, Sub Admin)
- Multi-conference management by year
- Certificate categories per conference (default + custom)
- JPG template upload per category
- Drag-and-drop field mapping editor (X/Y, font size, align, color, width, line height, underline)
- Bulk CSV participant import
- Dynamic certificate generation to JPG
- JPG to PDF conversion (A4 landscape)
- Individual and ZIP bulk downloads
- Bulk email sending with logs
- Public certificate verification by unique code
- Optional QR generation for verification URL

## Required Folder Structure

- templates/{year}/{category}/template.jpg
- generated/{year}/{category}/
- uploads/csv/

These folders are created automatically by the app if missing.

## Setup on XAMPP

1. Copy project to htdocs:
   - c:/xampp/htdocs/certificate generate/version 1
2. Start Apache and MySQL from XAMPP control panel.
3. Create database and tables:
   - Open phpMyAdmin
   - Import database/schema.sql
4. Open app in browser:
   - http://localhost/certificate%20generate/version%201/index.php

## Default Login

- Email: superadmin@example.com
- Password: password

Change this password immediately from User Management.

## SMTP / PHPMailer

EmailService automatically tries the following in order:

1. vendor/autoload.php (Composer installation)
2. lib/PHPMailer/src/* (manual library drop-in)
3. native PHP mail() fallback

To use full PHPMailer SMTP flow without Composer:

- Download PHPMailer source
- Place files under:
  - lib/PHPMailer/src/PHPMailer.php
  - lib/PHPMailer/src/SMTP.php
  - lib/PHPMailer/src/Exception.php
- Configure SMTP values in System Settings page.

## CSV Format

Expected columns:

- name
- institute
- title
- category
- email
- date

If category is empty, selected category in UI is used.

## Notes

- Configure default TTF path in System Settings if needed.
- Default is Windows Arial:
  - C:/Windows/Fonts/arial.ttf
- For best output quality, upload high-resolution JPG templates.

## Main Routing Pages

- index.php?page=login
- index.php?page=dashboard
- index.php?page=conferences
- index.php?page=certificate-types
- index.php?page=field-editor
- index.php?page=participants
- index.php?page=generate
- index.php?page=downloads
- index.php?page=emails
- index.php?page=users
- index.php?page=settings
- index.php?page=verify
