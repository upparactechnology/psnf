<?php
declare(strict_types=1);

namespace App\Services;

class DownloadService
{
    public function createZip(array $relativePaths, string $zipAbsolutePath): string
    {
        if ($relativePaths === []) {
            throw new \RuntimeException('No files selected for ZIP download.');
        }

        $directory = dirname($zipAbsolutePath);
        if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new \RuntimeException('Unable to create ZIP directory: ' . $directory);
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipAbsolutePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Unable to create ZIP archive.');
        }

        $added = 0;
        foreach ($relativePaths as $relativePath) {
            $cleanRelative = str_replace('\\', '/', (string) $relativePath);
            $absolutePath = APP_ROOT . '/' . ltrim($cleanRelative, '/');
            if (!is_file($absolutePath)) {
                continue;
            }

            $zip->addFile($absolutePath, basename($absolutePath));
            $added++;
        }

        $zip->close();

        if ($added === 0) {
            if (is_file($zipAbsolutePath)) {
                unlink($zipAbsolutePath);
            }
            throw new \RuntimeException('No certificate files were available to include in ZIP.');
        }

        return $zipAbsolutePath;
    }
}
