<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Central upload validation. Every upload handler must call UploadGuard::check()
 * before move_uploaded_file() and use the extension it returns — never the
 * client-supplied one — so executable files (.php, .phtml, .phar, .htaccess …)
 * can never be stored in a web-reachable folder.
 */
class UploadGuard
{
    public const IMAGES    = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    public const DOCUMENTS = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt'];

    /** Real MIME types accepted for each extension (checked with finfo on the file contents). */
    private const MIME = [
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png'  => ['image/png'],
        'gif'  => ['image/gif'],
        'webp' => ['image/webp'],
        'pdf'  => ['application/pdf'],
        'doc'  => ['application/msword', 'application/octet-stream', 'application/x-ole-storage', 'application/CDFV2'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/octet-stream'],
        'xls'  => ['application/vnd.ms-excel', 'application/octet-stream', 'application/x-ole-storage', 'application/CDFV2'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip', 'application/octet-stream'],
        'csv'  => ['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'],
        'txt'  => ['text/plain'],
    ];

    /**
     * Validate an entry from $_FILES.
     *
     * @param array    $file     one $_FILES entry
     * @param string[] $allowed  allowed lowercase extensions (use the class constants)
     * @param int      $maxBytes size limit (default 10 MB)
     * @return string  the safe, lowercase extension to use for the stored file name
     * @throws \RuntimeException with a user-facing message when the file is rejected
     */
    public static function check(array $file, array $allowed, int $maxBytes = 10485760): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new \RuntimeException('File upload failed.');
        }
        if ((int) ($file['size'] ?? 0) <= 0 || (int) $file['size'] > $maxBytes) {
            throw new \RuntimeException('File is empty or larger than ' . round($maxBytes / 1048576) . ' MB.');
        }

        $ext = strtolower((string) pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        if ($ext === '' || !in_array($ext, $allowed, true) || !isset(self::MIME[$ext])) {
            throw new \RuntimeException('File type not allowed. Allowed: ' . implode(', ', $allowed) . '.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = (string) $finfo->file($file['tmp_name']);
        if (!in_array($mime, self::MIME[$ext], true)) {
            throw new \RuntimeException('File content does not match its type.');
        }

        // Images must really decode as images (blocks GIF/PHP polyglots passing a MIME sniff)
        if (in_array($ext, self::IMAGES, true) && @getimagesize($file['tmp_name']) === false) {
            throw new \RuntimeException('Invalid image file.');
        }

        return $ext === 'jpeg' ? 'jpg' : $ext;
    }

    /** Random, safe file name with a validated extension. */
    public static function storedName(string $ext, string $prefix = ''): string
    {
        return $prefix . bin2hex(random_bytes(16)) . '.' . $ext;
    }
}
