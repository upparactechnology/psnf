<?php
declare(strict_types=1);

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReader;
use PDO;

class ImportService
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Return normalized headers from the uploaded file (CSV/XLSX/XLS).
     * If $sheetName is provided, read from that sheet.
     */
    public function getHeaders(string $filePath, string $originalName = '', ?string $sheetName = null, int $headerRow = 1): array
    {
        $extension = strtolower(pathinfo($originalName ?: $filePath, PATHINFO_EXTENSION));

        if (in_array($extension, ['xls', 'xlsx', 'ods'], true) && class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            $reader = IOFactory::createReaderForFile($filePath);
            $spreadsheet = $reader->load($filePath);
            if ($sheetName !== null) {
                $sheet = $spreadsheet->getSheetByName($sheetName);
                if ($sheet === null) {
                    throw new \RuntimeException('Sheet "' . $sheetName . '" not found.');
                }
            } else {
                $sheet = $spreadsheet->getActiveSheet();
            }
            $col = $sheet->getHighestColumn();
            $rowIndex = max(1, $headerRow);
            $row = $sheet->rangeToArray('A' . $rowIndex . ':' . $col . $rowIndex, null, true, false)[0] ?? [];
            return array_map([$this, 'normalizeHeader'], $row);
        }

        // Fallback to CSV parsing (first non-empty line)
        $handle = fopen($filePath, 'rb');
        if ($handle === false) {
            throw new \RuntimeException('Unable to open uploaded file.');
        }

        $headers = null;
        $current = 1;
        // skip lines until we reach headerRow - then read the next non-empty line as header
        while ($current < $headerRow && ($tmp = fgetcsv($handle)) !== false) {
            $current++;
        }
        while (($line = fgetcsv($handle)) !== false) {
            if ($this->isEmptyRow($line)) {
                $current++;
                continue;
            }
            $headers = $line;
            break;
        }
        fclose($handle);

        if ($headers === null) {
            throw new \RuntimeException('No header row detected in uploaded file.');
        }

        return array_map([$this, 'normalizeHeader'], $headers);
    }

    /**
     * Iterate rows as associative arrays (header => value).
     * Caller must supply mapping if re-keying is required.
     * Yields associative array per row.
     */
    public function iterateRows(string $filePath, array $headers, string $originalName = '', ?string $sheetName = null, int $headerRow = 1): iterable
    {
        $extension = strtolower(pathinfo($originalName ?: $filePath, PATHINFO_EXTENSION));

        if (in_array($extension, ['xls', 'xlsx', 'ods'], true) && class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            $reader = IOFactory::createReaderForFile($filePath);
            $spreadsheet = $reader->load($filePath);
            if ($sheetName !== null) {
                $sheet = $spreadsheet->getSheetByName($sheetName);
                if ($sheet === null) {
                    throw new \RuntimeException('Sheet "' . $sheetName . '" not found.');
                }
            } else {
                $sheet = $spreadsheet->getActiveSheet();
            }
            $highestRow = $sheet->getHighestRow();
            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestColumn());

            $startRow = max(1, $headerRow) + 1;
            for ($r = $startRow; $r <= $highestRow; $r++) {
                $row = [];
                for ($c = 1; $c <= $highestColumnIndex; $c++) {
                    $value = $sheet->getCellByColumnAndRow($c, $r)->getValue();
                    $header = $headers[$c - 1] ?? ('col_' . $c);
                    $row[$header] = $value;
                }

                if ($this->isEmptyAssocRow($row)) {
                    continue;
                }

                yield $row;
            }

            return;
        }

        // CSV fallback
        $handle = fopen($filePath, 'rb');
        if ($handle === false) {
            throw new \RuntimeException('Unable to open uploaded file.');
        }

        // skip to header row then read subsequent data lines
        $current = 1;
        while ($current <= $headerRow && ($tmp = fgetcsv($handle)) !== false) {
            $current++;
        }

        while (($row = fgetcsv($handle)) !== false) {
            if ($this->isEmptyRow($row)) {
                continue;
            }

            $assoc = [];
            foreach ($headers as $i => $h) {
                $assoc[$h] = $row[$i] ?? null;
            }

            if ($this->isEmptyAssocRow($assoc)) {
                continue;
            }

            yield $assoc;
        }

        fclose($handle);
    }

    private function normalizeHeader(string $value): string
    {
        $value = strtolower(trim((string) $value));
        $value = str_replace([' ', '-'], '_', $value);
        return preg_replace('/[^a-z0-9_]/', '', $value) ?? '';
    }

    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }
        return true;
    }

    private function isEmptyAssocRow(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }
        return true;
    }

    /**
     * Return available sheet names for spreadsheet files, empty array for CSV.
     */
    public function getSheetNames(string $filePath, string $originalName = ''): array
    {
        $extension = strtolower(pathinfo($originalName ?: $filePath, PATHINFO_EXTENSION));
        if (!in_array($extension, ['xls', 'xlsx', 'ods'], true) || !class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            return [];
        }

        $reader = IOFactory::createReaderForFile($filePath);
        $spreadsheet = $reader->load($filePath);
        return $spreadsheet->getSheetNames();
    }
}
