<?php
declare(strict_types=1);

namespace App\Services;

use PDO;

class CsvService
{
    public function __construct(private PDO $pdo)
    {
    }

    public function importParticipants(string $csvPath, int $conferenceId, ?int $defaultCertificateTypeId = null): array
    {
        $handle = fopen($csvPath, 'rb');
        if ($handle === false) {
            throw new \RuntimeException('Unable to open CSV file.');
        }

        $headers = fgetcsv($handle);
        if ($headers === false) {
            fclose($handle);
            throw new \RuntimeException('CSV file is empty.');
        }

        $headers = array_map([$this, 'normalizeHeader'], $headers);
        $lineNumber = 1;
        $inserted = 0;
        $failed = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            $lineNumber++;
            if ($this->isEmptyRow($row)) {
                continue;
            }

            $data = $this->combineRow($headers, $row);
            $name = trim((string) ($data['name'] ?? ''));

            $groupNamesFromCsv = $this->collectGroupNames($data, '');
            if ($name === '' && $groupNamesFromCsv !== []) {
                $name = array_shift($groupNamesFromCsv);
            }

            if ($name === '') {
                $failed++;
                $errors[] = "Line {$lineNumber}: name is required.";
                continue;
            }

            $groupNames = $this->deduplicateNames($groupNamesFromCsv, $name);

            $certificateTypeId = $defaultCertificateTypeId;
            $category = trim((string) ($data['category'] ?? ''));
            if ($category !== '') {
                $resolved = $this->resolveCertificateTypeId($conferenceId, $category);
                if ($resolved === null) {
                    $failed++;
                    $errors[] = "Line {$lineNumber}: category '{$category}' not found for selected conference.";
                    continue;
                }
                $certificateTypeId = $resolved;
            }

            if ($certificateTypeId === null) {
                $failed++;
                $errors[] = "Line {$lineNumber}: no certificate type selected or resolved from category.";
                continue;
            }

            $dateRaw = trim((string) ($data['date'] ?? ''));
            $issuedDate = $dateRaw !== '' ? date('Y-m-d', strtotime($dateRaw)) : date('Y-m-d');

            $extra = $data;
            unset($extra['name'], $extra['institute'], $extra['title'], $extra['category'], $extra['email'], $extra['date']);

            foreach (array_keys($extra) as $extraKey) {
                if ($this->isGroupNameHeader((string) $extraKey)) {
                    unset($extra[$extraKey]);
                }
            }

            if ($groupNames !== []) {
                $extra['authors'] = $groupNames;
            }

            $stmt = $this->pdo->prepare(
                'INSERT INTO participants
                 (conference_id, certificate_type_id, name, email, institute, title, issued_date, extra_json, verify_code, status, created_at)
                 VALUES (:conference_id, :certificate_type_id, :name, :email, :institute, :title, :issued_date, :extra_json, :verify_code, :status, NOW())'
            );

            $stmt->execute([
                'conference_id' => $conferenceId,
                'certificate_type_id' => $certificateTypeId,
                'name' => $name,
                'email' => trim((string) ($data['email'] ?? '')),
                'institute' => trim((string) ($data['institute'] ?? '')),
                'title' => trim((string) ($data['title'] ?? '')),
                'issued_date' => $issuedDate,
                'extra_json' => json_encode($extra, JSON_UNESCAPED_UNICODE),
                'verify_code' => bin2hex(random_bytes(8)),
                'status' => 'pending',
            ]);

            $inserted++;
        }

        fclose($handle);

        return [
            'inserted' => $inserted,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    private function normalizeHeader(string $value): string
    {
        $value = strtolower(trim($value));
        $value = str_replace([' ', '-'], '_', $value);
        return preg_replace('/[^a-z0-9_]/', '', $value) ?? '';
    }

    private function combineRow(array $headers, array $row): array
    {
        $data = [];
        foreach ($headers as $index => $header) {
            if ($header === '') {
                continue;
            }
            $data[$header] = $row[$index] ?? null;
        }
        return $data;
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

    public function resolveCertificateTypeId(int $conferenceId, string $category): ?int
    {
        $slug = \slugify($category);
        $stmt = $this->pdo->prepare(
            'SELECT id FROM certificate_types
             WHERE conference_id = :conference_id
               AND (slug = :slug OR LOWER(name) = LOWER(:name))
             LIMIT 1'
        );
        $stmt->execute([
            'conference_id' => $conferenceId,
            'slug' => $slug,
            'name' => $category,
        ]);

        $id = $stmt->fetchColumn();
        return $id === false ? null : (int) $id;
    }

    private function collectGroupNames(array $data, string $primaryName): array
    {
        $candidates = [];

        foreach ($data as $key => $value) {
            $normalizedKey = strtolower(trim((string) $key));
            if (!$this->isGroupNameHeader($normalizedKey)) {
                continue;
            }

            $candidates = array_merge($candidates, $this->splitNameCandidates((string) $value));
        }

        return $this->deduplicateNames($candidates, $primaryName);
    }

    private function isGroupNameHeader(string $header): bool
    {
        return preg_match(
            '/^(authors?|co_?authors?|participants?|participant_names?|group_names?|names?|author\d+|authors\d+|co_?author\d+|co_?authors\d+|participant\d+|participants\d+)$/',
            $header
        ) === 1;
    }

    private function splitNameCandidates(string $raw): array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $chunks = [];
            foreach ($decoded as $value) {
                if (is_scalar($value)) {
                    $chunks[] = trim((string) $value);
                }
            }

            return array_values(array_filter($chunks, static fn ($value): bool => $value !== ''));
        }

        $parts = preg_split('/,|;|\||\r\n|\n/', $raw) ?: [];
        return array_values(array_filter(array_map(static fn ($value): string => trim((string) $value), $parts), static fn ($value): bool => $value !== ''));
    }

    private function deduplicateNames(array $names, string $primaryName = ''): array
    {
        $result = [];
        $seen = [];

        $normalizedPrimary = trim($primaryName) !== '' ? strtolower(trim($primaryName)) : '';
        if ($normalizedPrimary !== '') {
            $seen[$normalizedPrimary] = true;
        }

        foreach ($names as $name) {
            $candidate = trim((string) $name);
            if ($candidate === '') {
                continue;
            }

            $normalized = strtolower($candidate);
            if (isset($seen[$normalized])) {
                continue;
            }

            $seen[$normalized] = true;
            $result[] = $candidate;
        }

        return $result;
    }
}
