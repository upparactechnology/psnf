<?php
declare(strict_types=1);

namespace App\Services;

use PDO;

class CertificateGenerator
{
    public function __construct(
        private PDO $pdo,
        private PdfService $pdfService
    ) {
    }

    public function generateBulk(int $conferenceId, ?int $certificateTypeId = null): array
    {
        $where = ['conference_id = :conference_id'];
        $params = ['conference_id' => $conferenceId];

        if ($certificateTypeId !== null && $certificateTypeId > 0) {
            $where[] = 'certificate_type_id = :certificate_type_id';
            $params['certificate_type_id'] = $certificateTypeId;
        }

        $stmt = $this->pdo->prepare(
            'SELECT id FROM participants WHERE ' . implode(' AND ', $where) . ' ORDER BY id ASC'
        );
        $stmt->execute($params);

        $participantIds = array_map(static fn (array $row): int => (int) $row['id'], $stmt->fetchAll());

        $generated = 0;
        $failed = 0;
        $errors = [];

        foreach ($participantIds as $participantId) {
            try {
                $this->generateForParticipant($participantId);
                $generated++;
            } catch (\Throwable $exception) {
                $failed++;
                $errors[] = 'Participant #' . $participantId . ': ' . $exception->getMessage();
            }
        }

        return [
            'total' => count($participantIds),
            'generated' => $generated,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    public function generateForParticipant(int $participantId): array
    {
        $participant = $this->findParticipantPayload($participantId);
        if ($participant === null) {
            throw new \RuntimeException('Participant not found.');
        }

        if (empty($participant['template_path'])) {
            throw new \RuntimeException('Template not uploaded for this certificate type.');
        }

        $templateAbsolute = APP_ROOT . '/' . str_replace('\\', '/', (string) $participant['template_path']);
        if (!is_file($templateAbsolute)) {
            throw new \RuntimeException('Template file not found at: ' . $participant['template_path']);
        }

        $image = imagecreatefromjpeg($templateAbsolute);
        if ($image === false) {
            throw new \RuntimeException('Unable to read template image.');
        }

        $fontPath = $this->resolveBaseFontPath();
        if ($fontPath === null) {
            imagedestroy($image);
            throw new \RuntimeException('No usable TTF font found. Check Settings > Default Font Path.');
        }

        $mappings = $this->findMappings((int) $participant['certificate_type_id']);
        if ($mappings === []) {
            imagedestroy($image);
            throw new \RuntimeException('No field mappings found for this certificate type.');
        }

        $extras = [];
        if (!empty($participant['extra_json'])) {
            $decoded = json_decode((string) $participant['extra_json'], true);
            if (is_array($decoded)) {
                $extras = $decoded;
            }
        }

        foreach ($mappings as $map) {
            $fontSize = max(8, (int) $map['font_size']);
            $maxWidth = max(100, (int) $map['max_width']);
            $lineHeight = max($fontSize + 6, (int) $map['line_height']);
            $x = (int) $map['x_pos'];
            $y = (int) $map['y_pos'];
            $align = (string) $map['align'];
            $color = $this->allocateColor($image, (string) $map['color_hex']);
            $options = $this->mappingOptions($map, $align);
            $renderFontPath = $this->resolveFontPathByOptions($fontPath, $options);

            if ($this->isTemplateMapping($map)) {
                $this->renderTemplateMapping(
                    $image,
                    $map,
                    $participant,
                    $extras,
                    $renderFontPath,
                    $fontSize,
                    $maxWidth,
                    $lineHeight,
                    $x,
                    $y,
                    $align,
                    $color,
                    $options
                );
                continue;
            }

            $fieldValue = $this->resolveFieldValue((string) $map['field_key'], $participant, $extras);
            if ($fieldValue === '') {
                continue;
            }

            $lines = $this->wrapText($fieldValue, $renderFontPath, $fontSize, $maxWidth);

            foreach ($lines as $line) {
                $lineWidth = $this->textWidth($line, $renderFontPath, $fontSize);
                if ($align === 'center') {
                    $drawX = (int) round($x - ($lineWidth / 2));
                } elseif ($align === 'right') {
                    $drawX = $x - $lineWidth;
                } else {
                    $drawX = $x;
                }

                imagettftext(
                    $image,
                    $fontSize,
                    0,
                    $drawX,
                    $y,
                    $color,
                    $renderFontPath,
                    $line
                );

                if ((int) $map['underline'] === 1) {
                    imageline($image, $drawX, $y + 4, $drawX + $lineWidth, $y + 4, $color);
                }

                $y += $lineHeight;
            }
        }

        $outputDir = GENERATED_ROOT . '/' . $participant['year'] . '/' . $participant['type_slug'];
        if (!is_dir($outputDir) && !mkdir($outputDir, 0777, true) && !is_dir($outputDir)) {
            imagedestroy($image);
            throw new \RuntimeException('Unable to create output directory: ' . $outputDir);
        }

        $nameFormat = setting('file_name_format', DEFAULT_FILE_NAME_FORMAT) ?? DEFAULT_FILE_NAME_FORMAT;
        $fileBase = $this->buildFileName($nameFormat, $participant);

        $jpgAbsolute = $outputDir . '/' . $fileBase . '.jpg';
        $pdfAbsolute = $outputDir . '/' . $fileBase . '.pdf';

        imagejpeg($image, $jpgAbsolute, 95);
        imagedestroy($image);

        $this->pdfService->jpgToPdf($jpgAbsolute, $pdfAbsolute);

        $jpgRelative = $this->toRelativePath($jpgAbsolute);
        $pdfRelative = $this->toRelativePath($pdfAbsolute);

        $saveStmt = $this->pdo->prepare(
            'INSERT INTO generated_certificates (participant_id, jpg_path, pdf_path, generated_at)
             VALUES (:participant_id, :jpg_path, :pdf_path, NOW())
             ON DUPLICATE KEY UPDATE jpg_path = VALUES(jpg_path), pdf_path = VALUES(pdf_path), generated_at = NOW()'
        );

        $saveStmt->execute([
            'participant_id' => $participantId,
            'jpg_path' => $jpgRelative,
            'pdf_path' => $pdfRelative,
        ]);

        $statusStmt = $this->pdo->prepare('UPDATE participants SET status = :status WHERE id = :id');
        $statusStmt->execute([
            'status' => 'generated',
            'id' => $participantId,
        ]);

        // Sync to student_documents if participant has a linked student_id
        $studentId = (int) ($participant['student_id'] ?? 0);
        if ($studentId > 0) {
            $certTitle = trim((string) ($participant['type_name'] ?? 'Certificate'));
            $this->syncToStudentDocument($studentId, $certTitle, $pdfRelative, $pdfAbsolute);
        }

        return [
            'jpg_path' => $jpgRelative,
            'pdf_path' => $pdfRelative,
        ];
    }

    private function findParticipantPayload(int $participantId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.*, c.name AS conference_name, c.year, ct.name AS type_name, ct.slug AS type_slug, ct.template_path
             FROM participants p
             INNER JOIN conferences c ON c.id = p.conference_id
             INNER JOIN certificate_types ct ON ct.id = p.certificate_type_id
             WHERE p.id = :id
             LIMIT 1'
        );

        $stmt->execute(['id' => $participantId]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    private function isTemplateMapping(array $map): bool
    {
        $fieldKey = trim((string) ($map['field_key'] ?? ''));
        $label = (string) ($map['label'] ?? '');

        if ($fieldKey !== '' && str_starts_with($fieldKey, 'tpl_')) {
            return true;
        }

        return preg_match('/\{[a-zA-Z0-9_]+\}/', $label) === 1;
    }

    private function findMappings(int $certificateTypeId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM field_mappings WHERE certificate_type_id = :certificate_type_id ORDER BY sort_order ASC, id ASC'
        );
        $stmt->execute(['certificate_type_id' => $certificateTypeId]);
        return $stmt->fetchAll();
    }

    private function resolveFieldValue(string $fieldKey, array $participant, array $extras): string
    {
        $key = strtolower(trim($fieldKey));

        $groupedNames = $this->collectGroupedParticipantNames($participant, $extras);
        $secondaryNames = $this->collectSecondaryParticipantNames($participant, $extras);

        if ($this->isCoauthorPlaceholder($key) && $secondaryNames === []) {
            return '';
        }

        // Prefer the primary participant name; only fall back to grouped names if missing.
        if ($key === 'name') {
            $primaryName = trim((string) ($participant['name'] ?? ''));
            if ($primaryName !== '') {
                return $primaryName;
            }

            return implode(', ', $groupedNames);
        }

        // Support numbered and plural author/participant placeholders from one grouped source.
        if (preg_match('/^(author|authors|participant|participants)(\d*)$/i', $key, $match) === 1) {
            $resolvedNames = $secondaryNames;
            $index = ($match[2] ?? '') !== '' ? (int) $match[2] : 0;
            if ($index > 0) {
                return trim((string) ($resolvedNames[$index - 1] ?? ''));
            }

            return implode(', ', $resolvedNames);
        }

        // Co-author placeholders exclude the primary participant name.
        if (preg_match('/^(coauthor|coauthors|co_author|co_authors)(\d*)$/i', $key, $match) === 1) {
            $coAuthors = $secondaryNames !== []
                ? $secondaryNames
                : (count($groupedNames) > 1 ? array_slice($groupedNames, 1) : []);
            $index = ($match[2] ?? '') !== '' ? (int) $match[2] : 0;
            if ($index > 0) {
                return trim((string) ($coAuthors[$index - 1] ?? ''));
            }

            return implode(', ', $coAuthors);
        }

        // Direct participant fields first
        if (isset($participant[$key]) && !is_array($participant[$key]) && trim((string) $participant[$key]) !== '') {
            return trim((string) $participant[$key]);
        }

        // Extras override / provide arrays
        if (isset($extras[$key])) {
            // If extras contains a JSON-encoded array, normalize it
            if (is_array($extras[$key])) {
                $list = $this->extractNameList($extras[$key]);
                return implode(', ', $list);
            }

            // If string contains JSON array, try decode
            if (is_string($extras[$key])) {
                $maybe = json_decode((string) $extras[$key], true);
                if (is_array($maybe)) {
                    return implode(', ', $this->extractNameList($maybe));
                }

                return trim((string) $extras[$key]);
            }
        }

        // Specific fallbacks
        if ($key === 'title') {
            // Use participant title if present; otherwise use certificate type name (category)
            $title = trim((string) ($participant['title'] ?? ''));
            if ($title !== '') {
                return $title;
            }

            return trim((string) ($participant['type_name'] ?? ''));
        }

        if ($key === 'date') {
            return trim((string) ($participant['issued_date'] ?? date('Y-m-d')));
        }

        if ($key === 'conference') {
            return trim((string) ($participant['conference_name'] ?? ''));
        }

        if ($key === 'year') {
            return trim((string) ($participant['year'] ?? ''));
        }

        if ($key === 'category') {
            return trim((string) ($participant['type_name'] ?? ''));
        }

        if ($key === 'verify_code') {
            return trim((string) ($participant['verify_code'] ?? ''));
        }

        // Default: extras or empty
        if (isset($extras[$key])) {
            return trim((string) $extras[$key]);
        }

        return trim((string) ($extras[$fieldKey] ?? ''));
    }

    private function collectGroupedParticipantNames(array $participant, array $extras): array
    {
        $names = [];

        $this->appendUniqueName($names, (string) ($participant['name'] ?? ''));

        $this->appendNamesFromKnownGroupKeys($names, $participant);
        $this->appendNamesFromKnownGroupKeys($names, $extras);

        return $names;
    }

    private function collectSecondaryParticipantNames(array $participant, array $extras): array
    {
        $names = [];
        $primaryName = trim((string) ($participant['name'] ?? ''));

        $this->appendNamesFromKnownGroupKeys($names, $participant, $primaryName);
        $this->appendNamesFromKnownGroupKeys($names, $extras, $primaryName);

        return $names;
    }

    private function appendNamesFromKnownGroupKeys(array &$names, array $source, string $excludeName = ''): void
    {
        $priorityKeys = ['authors', 'participants', 'coauthors', 'co_authors', 'group_names', 'participant_names', 'names'];
        foreach ($priorityKeys as $groupKey) {
            if (!array_key_exists($groupKey, $source)) {
                continue;
            }

            foreach ($this->extractNameList($source[$groupKey]) as $value) {
                $this->appendUniqueName($names, $value, $excludeName);
            }
        }

        $indexed = [];
        foreach ($source as $sourceKey => $sourceValue) {
            if (!is_string($sourceKey)) {
                continue;
            }

            $normalizedKey = strtolower(trim($sourceKey));
            if (preg_match('/^(author|authors|participant|participants|coauthor|coauthors|co_author|co_authors)(\d+)$/', $normalizedKey, $match) !== 1) {
                continue;
            }

            $indexed[] = [
                'index' => (int) $match[2],
                'value' => $sourceValue,
            ];
        }

        usort($indexed, static fn (array $left, array $right): int => $left['index'] <=> $right['index']);

        foreach ($indexed as $row) {
            foreach ($this->extractNameList($row['value']) as $value) {
                $this->appendUniqueName($names, $value, $excludeName);
            }
        }
    }

    private function extractNameList($value): array
    {
        if (is_array($value)) {
            $result = [];
            foreach ($value as $item) {
                if (is_scalar($item)) {
                    $candidate = trim((string) $item);
                    if ($candidate !== '') {
                        $result[] = $candidate;
                    }
                }
            }

            return $result;
        }

        if (!is_scalar($value)) {
            return [];
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return $this->extractNameList($decoded);
        }

        $parts = preg_split('/,|;|\||\r\n|\n/', $raw) ?: [];
        $result = [];
        foreach ($parts as $part) {
            $candidate = trim((string) $part);
            if ($candidate !== '') {
                $result[] = $candidate;
            }
        }

        return $result;
    }

    private function appendUniqueName(array &$names, string $candidate, string $excludeName = ''): void
    {
        $candidate = trim($candidate);
        if ($candidate === '') {
            return;
        }

        $excludeName = trim($excludeName);
        if ($excludeName !== '' && strtolower($excludeName) === strtolower($candidate)) {
            return;
        }

        $normalizedCandidate = strtolower($candidate);
        foreach ($names as $existing) {
            if (strtolower((string) $existing) === $normalizedCandidate) {
                return;
            }
        }

        $names[] = $candidate;
    }

    private function mappingOptions(array $map, string $fallbackAlign): array
    {
        $fallbackTextAlign = strtolower(trim($fallbackAlign));
        if (!in_array($fallbackTextAlign, ['left', 'center', 'right', 'justify'], true)) {
            $fallbackTextAlign = 'left';
        }

        $defaults = [
            'text_align' => $fallbackTextAlign,
            'font_style' => 'regular',
            'font_family' => 'default',
            'line_spacing' => 1.25,
            'word_spacing' => 0,
        ];

        $raw = $map['options_json'] ?? null;
        $decoded = [];

        if (is_array($raw)) {
            $decoded = $raw;
        } elseif (is_string($raw) && trim($raw) !== '') {
            $json = json_decode($raw, true);
            if (is_array($json)) {
                $decoded = $json;
            }
        }

        $align = strtolower((string) ($decoded['text_align'] ?? $defaults['text_align']));
        if (!in_array($align, ['left', 'center', 'right', 'justify'], true)) {
            $align = $defaults['text_align'];
        }

        $fontStyle = strtolower((string) ($decoded['font_style'] ?? $defaults['font_style']));
        if (!in_array($fontStyle, ['regular', 'bold', 'italic', 'bold_italic'], true)) {
            $fontStyle = 'regular';
        }

        $fontFamily = $this->normalizeFontFamilyValue((string) ($decoded['font_family'] ?? $defaults['font_family']));

        $lineSpacing = (float) ($decoded['line_spacing'] ?? $defaults['line_spacing']);
        if ($lineSpacing < 1.0 || $lineSpacing > 3.0) {
            $lineSpacing = $defaults['line_spacing'];
        }

        $wordSpacing = (int) round((float) ($decoded['word_spacing'] ?? $defaults['word_spacing']));
        $wordSpacing = max(0, min(30, $wordSpacing));

        return [
            'text_align' => $align,
            'font_style' => $fontStyle,
            'font_family' => $fontFamily,
            'line_spacing' => $lineSpacing,
            'word_spacing' => $wordSpacing,
        ];
    }

    private function resolveFontPathByOptions(string $defaultFontPath, array $options): string
    {
        $family = $this->normalizeFontFamilyValue((string) ($options['font_family'] ?? 'default'));
        $style = $this->normalizeInlineFontStyle((string) ($options['font_style'] ?? 'regular'));

        $customPath = $this->resolveCustomFontPath($family, $style);
        if ($customPath !== null) {
            return $customPath;
        }

        $settingCandidates = [];
        if (!str_starts_with($family, 'custom:')) {
            $settingCandidates[] = 'font_' . $family . '_' . $style . '_path';
            if ($style === 'regular') {
                $settingCandidates[] = 'font_' . $family . '_path';
            }
        }

        if ($family === 'default') {
            $settingCandidates[] = 'default_font_' . $style . '_path';
            $settingCandidates[] = 'default_font_path';
        }

        foreach ($settingCandidates as $settingKey) {
            $settingPath = setting($settingKey, '');
            if (!is_string($settingPath) || trim($settingPath) === '') {
                continue;
            }

            $resolvedSettingPath = $this->resolveFontDiskPath($settingPath);
            if ($resolvedSettingPath !== null) {
                return $resolvedSettingPath;
            }
        }

        $windowsFontMap = [
            'default' => [
                'regular' => 'arial.ttf',
                'bold' => 'arialbd.ttf',
                'italic' => 'ariali.ttf',
                'bold_italic' => 'arialbi.ttf',
            ],
            'arial' => [
                'regular' => 'arial.ttf',
                'bold' => 'arialbd.ttf',
                'italic' => 'ariali.ttf',
                'bold_italic' => 'arialbi.ttf',
            ],
            'times' => [
                'regular' => 'times.ttf',
                'bold' => 'timesbd.ttf',
                'italic' => 'timesi.ttf',
                'bold_italic' => 'timesbi.ttf',
            ],
            'georgia' => [
                'regular' => 'georgia.ttf',
                'bold' => 'georgiab.ttf',
                'italic' => 'georgiai.ttf',
                'bold_italic' => 'georgiaz.ttf',
            ],
            'calibri' => [
                'regular' => 'calibri.ttf',
                'bold' => 'calibrib.ttf',
                'italic' => 'calibrii.ttf',
                'bold_italic' => 'calibriz.ttf',
            ],
        ];

        $familyMap = $windowsFontMap[$family] ?? $windowsFontMap['default'];
        $fileName = $familyMap[$style] ?? $familyMap['regular'];
        $windowsPath = 'C:/Windows/Fonts/' . $fileName;
        $resolvedWindowsPath = $this->resolveFontDiskPath($windowsPath);
        if ($resolvedWindowsPath !== null) {
            return $resolvedWindowsPath;
        }

        $resolvedDefaultPath = $this->resolveFontDiskPath($defaultFontPath);
        if ($resolvedDefaultPath !== null) {
            return $resolvedDefaultPath;
        }

        $fallbackBaseFont = $this->resolveBaseFontPath();
        if ($fallbackBaseFont !== null) {
            return $fallbackBaseFont;
        }

        return $defaultFontPath;
    }

    private function resolveBaseFontPath(): ?string
    {
        $configuredRegular = setting('default_font_regular_path', null);
        $configuredDefault = setting('default_font_path', null);

        $candidates = [
            is_string($configuredRegular) ? trim($configuredRegular) : '',
            is_string($configuredDefault) ? trim($configuredDefault) : '',
            DEFAULT_FONT_PATH,
            'C:/Windows/Fonts/arial.ttf',
            'C:/Windows/Fonts/segoeui.ttf',
            'C:/Windows/Fonts/tahoma.ttf',
        ];

        foreach ($candidates as $candidate) {
            $resolved = $this->resolveFontDiskPath((string) $candidate);
            if ($resolved !== null) {
                return $resolved;
            }
        }

        return null;
    }

    private function resolveCustomFontPath(string $family, string $style): ?string
    {
        if (!str_starts_with($family, 'custom:')) {
            return null;
        }

        $slug = substr($family, 7);
        if ($slug === '' || preg_match('/^[a-z0-9_-]+$/', $slug) !== 1) {
            return null;
        }

        $raw = setting('custom_fonts_json', '[]') ?? '[]';
        $decoded = json_decode((string) $raw, true);
        if (!is_array($decoded)) {
            return null;
        }

        foreach ($decoded as $font) {
            if (!is_array($font)) {
                continue;
            }

            $fontSlug = strtolower(trim((string) ($font['slug'] ?? '')));
            if ($fontSlug !== $slug) {
                continue;
            }

            $files = is_array($font['files'] ?? null) ? $font['files'] : [];
            $candidate = (string) ($files[$style] ?? $files['regular'] ?? '');
            return $this->resolveFontDiskPath($candidate);
        }

        return null;
    }

    private function resolveFontDiskPath(string $path): ?string
    {
        $normalized = str_replace('\\', '/', trim($path));
        if ($normalized === '') {
            return null;
        }

        if (is_file($normalized)) {
            return $normalized;
        }

        $absolute = APP_ROOT . '/' . ltrim($normalized, '/');
        if (is_file($absolute)) {
            return str_replace('\\', '/', $absolute);
        }

        return null;
    }

    private function normalizeFontFamilyValue(string $fontFamily): string
    {
        $fontFamily = strtolower(trim($fontFamily));
        if ($fontFamily === '') {
            return 'default';
        }

        if (in_array($fontFamily, ['default', 'arial', 'times', 'georgia', 'calibri'], true)) {
            return $fontFamily;
        }

        if (preg_match('/^custom:[a-z0-9_-]+$/', $fontFamily) === 1) {
            return $fontFamily;
        }

        if (preg_match('/^[a-z0-9_-]+$/', $fontFamily) === 1) {
            return 'custom:' . $fontFamily;
        }

        return 'default';
    }

    private function renderTemplateMapping(
        $image,
        array $map,
        array $participant,
        array $extras,
        string $fontPath,
        int $fontSize,
        int $maxWidth,
        int $lineHeight,
        int $x,
        int $y,
        string $align,
        int $color,
        array $options
    ): void {
        $template = (string) ($map['label'] ?? '');
        if (trim($template) === '') {
            return;
        }

        $textAlign = in_array((string) ($options['text_align'] ?? 'left'), ['left', 'center', 'right', 'justify'], true)
            ? (string) $options['text_align']
            : (in_array($align, ['center', 'right', 'justify'], true) ? $align : 'left');
        $lineSpacingFactor = max(1.0, min(3.0, (float) ($options['line_spacing'] ?? 1.25)));
        $wordSpacing = max(0, min(30, (int) round((float) ($options['word_spacing'] ?? 0))));

        $segments = $this->parseTemplateStyledSegments(
            $template,
            $participant,
            $extras,
            (string) ($options['font_style'] ?? 'regular'),
            (string) ($options['font_family'] ?? 'default'),
            $textAlign
        );
        $boxWidth = max(100, $maxWidth);
        $boxHeight = max($fontSize + 8, $lineHeight);

        $fit = $this->fitTemplateLayoutToBox(
            $segments,
            $fontPath,
            $fontSize,
            $boxWidth,
            $boxHeight,
            $lineSpacingFactor,
            $wordSpacing,
            $options
        );

        if (!empty($fit['truncated'])) {
            $fit = $this->fitTemplateLayoutToBox(
                $segments,
                $fontPath,
                $fontSize,
                $boxWidth,
                PHP_INT_MAX,
                $lineSpacingFactor,
                $wordSpacing,
                $options
            );

            $fitLineCount = is_array($fit['lines'] ?? null) ? count($fit['lines']) : 0;
            if ($fitLineCount > 0) {
                $fitFontSize = (int) ($fit['font_size'] ?? $fontSize);
                $fitLineSpacing = (int) ($fit['line_spacing'] ?? max($fitFontSize + 4, (int) round($fitFontSize * $lineSpacingFactor)));
                $contentHeight = $fitFontSize + (($fitLineCount - 1) * $fitLineSpacing) + 4;
                if ($contentHeight > $boxHeight) {
                    $boxHeight = $contentHeight;
                }
            }
        }

        $drawFontSize = (int) $fit['font_size'];
        $drawLineSpacing = (int) $fit['line_spacing'];
        $lines = is_array($fit['lines']) ? $fit['lines'] : [];

        $topY = $y;
        $baselineY = $topY + $drawFontSize;
        $maxBaselineY = $topY + $boxHeight - 2;

        $lineCount = count($lines);
        foreach ($lines as $lineIndex => $line) {
            $lineWidth = (int) ($line['width'] ?? 0);
            $lineAlign = strtolower((string) ($line['text_align'] ?? $textAlign));
            if (!in_array($lineAlign, ['left', 'center', 'right', 'justify'], true)) {
                $lineAlign = $textAlign;
            }

            if ($lineAlign === 'center') {
                $drawX = $x + (int) round(($boxWidth - $lineWidth) / 2);
                $drawX = max($x, $drawX);
            } elseif ($lineAlign === 'right') {
                $drawX = $x + max(0, ($boxWidth - $lineWidth));
            } else {
                $drawX = $x;
            }

            if ($baselineY > $maxBaselineY) {
                break;
            }

            $cursorX = $drawX;
            $spaceCount = $this->spaceCountInTemplateLine($line['pieces'] ?? []);
            $lineEndsWithBreak = !empty($line['ends_with_break']);
            $isLastLine = $lineIndex >= ($lineCount - 1);
            $justifyExtraPerSpace = 0.0;
            if ($lineAlign === 'justify' && !$isLastLine && !$lineEndsWithBreak && $spaceCount > 0 && $lineWidth < $boxWidth) {
                $justifyExtraPerSpace = ($boxWidth - $lineWidth) / $spaceCount;
            }

            $dynamicRunStart = null;
            $dynamicRunEnd = null;

            $pieces = is_array($line['pieces'] ?? null) ? $line['pieces'] : [];
            $lastDynamicIndex = -1;
            foreach ($pieces as $pieceIndex => $piece) {
                if (!empty($piece['dynamic'])) {
                    $lastDynamicIndex = $pieceIndex;
                }
            }

            foreach ($pieces as $pieceIndex => $piece) {
                $text = (string) ($piece['text'] ?? '');
                if ($text === '') {
                    continue;
                }

                $pieceFontPath = (string) ($piece['font_path'] ?? $fontPath);
                $pieceWidth = (int) ($piece['width'] ?? $this->pieceWidth($text, $pieceFontPath, $drawFontSize, $wordSpacing));

                imagettftext(
                    $image,
                    $drawFontSize,
                    0,
                    $cursorX,
                    $baselineY,
                    $color,
                    $pieceFontPath,
                    $text
                );

                $advance = (float) $pieceWidth;
                if ($justifyExtraPerSpace > 0.0 && trim($text) === '') {
                    $advance += substr_count($text, ' ') * $justifyExtraPerSpace;
                }

                $nextCursorX = $cursorX + (int) round($advance);

                $hasDynamicAfter = $pieceIndex < $lastDynamicIndex;

                if (!empty($piece['dynamic'])) {
                    if ($dynamicRunStart === null) {
                        $dynamicRunStart = $cursorX;
                    }
                    $dynamicRunEnd = $nextCursorX;
                } elseif ($dynamicRunStart !== null && $dynamicRunEnd !== null) {
                    if ($hasDynamicAfter && $this->isUnderlineBridgeText($text)) {
                        $dynamicRunEnd = $nextCursorX;
                    } else {
                        $this->drawDottedUnderline($image, $dynamicRunStart, $dynamicRunEnd, $baselineY + 4, $color);
                        $dynamicRunStart = null;
                        $dynamicRunEnd = null;
                    }
                }

                $cursorX = $nextCursorX;
            }

            if ($dynamicRunStart !== null && $dynamicRunEnd !== null) {
                $this->drawDottedUnderline($image, $dynamicRunStart, $dynamicRunEnd, $baselineY + 4, $color);
            }

            $baselineY += $drawLineSpacing;
        }
    }

    private function fitTemplateLayoutToBox(
        array $segments,
        string $fontPath,
        int $startFontSize,
        int $maxWidth,
        int $boxHeight,
        float $lineSpacingFactor,
        int $wordSpacing,
        array $options
    ): array {
        $minFontSize = 8;

        for ($candidateFont = $startFontSize; $candidateFont >= $minFontSize; $candidateFont--) {
            $lineSpacing = max($candidateFont + 4, (int) round($candidateFont * $lineSpacingFactor));

            $pieces = $this->buildTemplatePieces($segments, $fontPath, $candidateFont, $maxWidth, $wordSpacing, $options);
            $lines = $this->layoutTemplateLines($pieces, $fontPath, $candidateFont, $maxWidth, $wordSpacing);
            $lines = $this->trimTrailingEmptyTemplateLines($lines);

            if ($this->templateContentFitsHeight(count($lines), $candidateFont, $lineSpacing, $boxHeight)) {
                return [
                    'font_size' => $candidateFont,
                    'line_spacing' => $lineSpacing,
                    'lines' => $lines,
                    'truncated' => false,
                ];
            }
        }

        $lineSpacing = max($minFontSize + 4, (int) round($minFontSize * $lineSpacingFactor));
        $maxLines = 1;
        for ($candidateLines = 1; $candidateLines <= 200; $candidateLines++) {
            if (!$this->templateContentFitsHeight($candidateLines, $minFontSize, $lineSpacing, $boxHeight)) {
                break;
            }
            $maxLines = $candidateLines;
        }

        $pieces = $this->buildTemplatePieces($segments, $fontPath, $minFontSize, $maxWidth, $wordSpacing, $options);
        $lines = $this->layoutTemplateLines($pieces, $fontPath, $minFontSize, $maxWidth, $wordSpacing);
        $lines = $this->trimTrailingEmptyTemplateLines($lines);

        $truncated = false;
        if (count($lines) > $maxLines) {
            $lines = array_slice($lines, 0, $maxLines);
            $this->appendEllipsisToLastTemplateLine($lines, $fontPath, $minFontSize, $maxWidth);
            $truncated = true;
        }

        return [
            'font_size' => $minFontSize,
            'line_spacing' => $lineSpacing,
            'lines' => $lines,
            'truncated' => $truncated,
        ];
    }

    private function templateContentFitsHeight(int $lineCount, int $fontSize, int $lineSpacing, int $boxHeight): bool
    {
        if ($lineCount <= 0) {
            return true;
        }

        $contentHeight = $fontSize + (($lineCount - 1) * $lineSpacing) + 4;
        return $contentHeight <= $boxHeight;
    }

    private function parseTemplateStyledSegments(
        string $template,
        array $participant,
        array $extras,
        string $baseFontStyle,
        string $baseFontFamily,
        string $baseTextAlign = 'left'
    ): array
    {
        $normalizedBaseStyle = $this->normalizeInlineFontStyle($baseFontStyle);
        $normalizedBaseFamily = $this->normalizeFontFamilyValue($baseFontFamily);
        $normalizedBaseTextAlign = strtolower(trim($baseTextAlign));
        if (!in_array($normalizedBaseTextAlign, ['left', 'center', 'right', 'justify'], true)) {
            $normalizedBaseTextAlign = 'left';
        }

        $styledSegments = $this->parseInlineStyleSegments($template, $normalizedBaseStyle, $normalizedBaseFamily, $normalizedBaseTextAlign);
        $resolvedSegments = [];

        foreach ($styledSegments as $styled) {
            $styledText = (string) ($styled['text'] ?? '');
            $fontStyle = $this->normalizeInlineFontStyle((string) ($styled['font_style'] ?? $normalizedBaseStyle));
            $fontFamily = $this->normalizeFontFamilyValue((string) ($styled['font_family'] ?? $normalizedBaseFamily));
            $textAlign = strtolower((string) ($styled['text_align'] ?? $normalizedBaseTextAlign));
            if (!in_array($textAlign, ['left', 'center', 'right', 'justify'], true)) {
                $textAlign = $normalizedBaseTextAlign;
            }
            $matched = preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $styledText, $matches, PREG_OFFSET_CAPTURE);

            if ($matched === false || $matched < 1 || empty($matches[0])) {
                if ($styledText !== '') {
                    $resolvedSegments[] = [
                        'text' => $styledText,
                        'dynamic' => false,
                        'font_style' => $fontStyle,
                        'font_family' => $fontFamily,
                        'text_align' => $textAlign,
                    ];
                }
                continue;
            }

            $cursor = 0;
            foreach ($matches[0] as $index => $fullMatch) {
                $placeholder = (string) $fullMatch[0];
                $offset = (int) $fullMatch[1];
                $fieldKey = strtolower((string) ($matches[1][$index][0] ?? ''));

                if ($offset > $cursor) {
                    $resolvedSegments[] = [
                        'text' => substr($styledText, $cursor, $offset - $cursor),
                        'dynamic' => false,
                        'font_style' => $fontStyle,
                        'font_family' => $fontFamily,
                        'text_align' => $textAlign,
                    ];
                }

                $value = $this->resolveFieldValue($fieldKey, $participant, $extras);
                if ($value === '' && !$this->isStructuredParticipantPlaceholder($fieldKey) && isset($extras[$fieldKey])) {
                    $value = $extras[$fieldKey];
                }
                $value = $this->normalizeTemplateDynamicValue($value);

                if ($value === '') {
                    if ($this->isStructuredParticipantPlaceholder($fieldKey)) {
                        $this->trimTrailingTemplateSeparator($resolvedSegments);
                    }

                    $cursor = $offset + strlen($placeholder);
                    continue;
                }

                $resolvedSegments[] = [
                    'text' => $value,
                    'dynamic' => true,
                    'placeholder' => $placeholder,
                    'font_style' => $fontStyle,
                    'font_family' => $fontFamily,
                    'text_align' => $textAlign,
                ];

                $cursor = $offset + strlen($placeholder);
            }

            if ($cursor < strlen($styledText)) {
                $resolvedSegments[] = [
                    'text' => substr($styledText, $cursor),
                    'dynamic' => false,
                    'font_style' => $fontStyle,
                    'font_family' => $fontFamily,
                    'text_align' => $textAlign,
                ];
            }
        }

        return $resolvedSegments === [] ? [[
            'text' => $template,
            'dynamic' => false,
            'font_style' => $normalizedBaseStyle,
            'font_family' => $normalizedBaseFamily,
            'text_align' => $normalizedBaseTextAlign,
        ]] : $resolvedSegments;
    }

    private function isStructuredParticipantPlaceholder(string $fieldKey): bool
    {
        return preg_match('/^(name|author|authors|participant|participants|coauthor|coauthors|co_author|co_authors)(\d*)$/i', trim($fieldKey)) === 1;
    }

    private function isCoauthorPlaceholder(string $fieldKey): bool
    {
        return preg_match('/^(coauthor|coauthors|co_author|co_authors)(\d*)$/i', trim($fieldKey)) === 1;
    }

    private function trimTrailingTemplateSeparator(array &$resolvedSegments): void
    {
        for ($index = count($resolvedSegments) - 1; $index >= 0; $index--) {
            if (!isset($resolvedSegments[$index]) || !is_array($resolvedSegments[$index])) {
                continue;
            }

            if (!empty($resolvedSegments[$index]['dynamic'])) {
                continue;
            }

            $text = (string) ($resolvedSegments[$index]['text'] ?? '');
            $trimmed = rtrim($text, " \t\n\r\0\x0B,.;:-–—");
            if ($trimmed === $text) {
                return;
            }

            if ($trimmed === '') {
                array_splice($resolvedSegments, $index, 1);
            } else {
                $resolvedSegments[$index]['text'] = $trimmed;
            }

            return;
        }
    }

    private function parseInlineStyleSegments(string $template, string $baseFontStyle, string $baseFontFamily, string $baseTextAlign = 'left'): array
    {
        $baseFontStyle = $this->normalizeInlineFontStyle($baseFontStyle);
        $baseFontFamily = $this->normalizeFontFamilyValue($baseFontFamily);
        $baseTextAlign = strtolower(trim($baseTextAlign));
        if (!in_array($baseTextAlign, ['left', 'center', 'right', 'justify'], true)) {
            $baseTextAlign = 'left';
        }

        $pattern = '/\[(\/?)(b|i|bi|font|left|center|right|justify)(?:=([^\]]+))?\]/i';
        $matched = preg_match_all($pattern, $template, $matches, PREG_OFFSET_CAPTURE);
        if ($matched === false || $matched < 1) {
            return [[
                'text' => $template,
                'font_style' => $baseFontStyle,
                'font_family' => $baseFontFamily,
                'text_align' => $baseTextAlign,
            ]];
        }

        $styleStack = [[
            'tag' => 'root',
            'font_style' => $baseFontStyle,
            'font_family' => $baseFontFamily,
            'text_align' => $baseTextAlign,
        ]];
        $segments = [];

        $cursor = 0;
        foreach ($matches[0] as $index => $fullMatch) {
            $token = (string) $fullMatch[0];
            $offset = (int) $fullMatch[1];

            if ($offset > $cursor) {
                $textChunk = substr($template, $cursor, $offset - $cursor);
                if ($textChunk !== '') {
                    $active = $styleStack[count($styleStack) - 1] ?? [
                        'font_style' => $baseFontStyle,
                        'font_family' => $baseFontFamily,
                        'text_align' => $baseTextAlign,
                    ];

                    $fontStyle = $this->normalizeInlineFontStyle((string) ($active['font_style'] ?? $baseFontStyle));
                    $fontFamily = $this->normalizeFontFamilyValue((string) ($active['font_family'] ?? $baseFontFamily));
                    $textAlign = strtolower((string) ($active['text_align'] ?? $baseTextAlign));
                    if (!in_array($textAlign, ['left', 'center', 'right', 'justify'], true)) {
                        $textAlign = $baseTextAlign;
                    }

                    $lastIndex = count($segments) - 1;
                    if (
                        $lastIndex >= 0
                        && ($segments[$lastIndex]['font_style'] ?? '') === $fontStyle
                        && ($segments[$lastIndex]['font_family'] ?? '') === $fontFamily
                        && ($segments[$lastIndex]['text_align'] ?? '') === $textAlign
                    ) {
                        $segments[$lastIndex]['text'] .= $textChunk;
                    } else {
                        $segments[] = [
                            'text' => $textChunk,
                            'font_style' => $fontStyle,
                            'font_family' => $fontFamily,
                            'text_align' => $textAlign,
                        ];
                    }
                }
            }

            $isClosing = (string) ($matches[1][$index][0] ?? '') === '/';
            $tag = strtolower((string) ($matches[2][$index][0] ?? ''));
            $tagValue = strtolower(trim((string) ($matches[3][$index][0] ?? '')));

            if ($isClosing) {
                for ($idx = count($styleStack) - 1; $idx >= 1; $idx--) {
                    if (($styleStack[$idx]['tag'] ?? '') === $tag) {
                        array_splice($styleStack, $idx, 1);
                        break;
                    }
                }

                $cursor = $offset + strlen($token);
                continue;
            }

            $active = $styleStack[count($styleStack) - 1] ?? [
                'font_style' => $baseFontStyle,
                'font_family' => $baseFontFamily,
                'text_align' => $baseTextAlign,
            ];
            $nextStyle = $this->normalizeInlineFontStyle((string) ($active['font_style'] ?? $baseFontStyle));
            $nextFamily = $this->normalizeFontFamilyValue((string) ($active['font_family'] ?? $baseFontFamily));
            $nextAlign = strtolower((string) ($active['text_align'] ?? $baseTextAlign));
            if (!in_array($nextAlign, ['left', 'center', 'right', 'justify'], true)) {
                $nextAlign = $baseTextAlign;
            }

            if ($tag === 'font') {
                $nextFamily = $this->normalizeFontFamilyValue($tagValue);
            } elseif (in_array($tag, ['left', 'center', 'right', 'justify'], true)) {
                $nextAlign = $tag;
            } else {
                $nextStyle = $this->fontStyleFromInlineTag($tag);
            }

            $styleStack[] = [
                'tag' => $tag,
                'font_style' => $nextStyle,
                'font_family' => $nextFamily,
                'text_align' => $nextAlign,
            ];

            $cursor = $offset + strlen($token);
        }

        if ($cursor < strlen($template)) {
            $tail = substr($template, $cursor);
            if ($tail !== '') {
                $active = $styleStack[count($styleStack) - 1] ?? [
                    'font_style' => $baseFontStyle,
                    'font_family' => $baseFontFamily,
                    'text_align' => $baseTextAlign,
                ];

                $fontStyle = $this->normalizeInlineFontStyle((string) ($active['font_style'] ?? $baseFontStyle));
                $fontFamily = $this->normalizeFontFamilyValue((string) ($active['font_family'] ?? $baseFontFamily));
                $textAlign = strtolower((string) ($active['text_align'] ?? $baseTextAlign));
                if (!in_array($textAlign, ['left', 'center', 'right', 'justify'], true)) {
                    $textAlign = $baseTextAlign;
                }

                $lastIndex = count($segments) - 1;
                if (
                    $lastIndex >= 0
                    && ($segments[$lastIndex]['font_style'] ?? '') === $fontStyle
                    && ($segments[$lastIndex]['font_family'] ?? '') === $fontFamily
                    && ($segments[$lastIndex]['text_align'] ?? '') === $textAlign
                ) {
                    $segments[$lastIndex]['text'] .= $tail;
                } else {
                    $segments[] = [
                        'text' => $tail,
                        'font_style' => $fontStyle,
                        'font_family' => $fontFamily,
                        'text_align' => $textAlign,
                    ];
                }
            }
        }

        return $segments === [] ? [[
            'text' => $template,
            'font_style' => $baseFontStyle,
            'font_family' => $baseFontFamily,
            'text_align' => $baseTextAlign,
        ]] : $segments;
    }

    private function fontStyleFromInlineTag(string $tag): string
    {
        return match (strtolower($tag)) {
            'b' => 'bold',
            'i' => 'italic',
            'bi' => 'bold_italic',
            default => 'regular',
        };
    }

    private function normalizeInlineFontStyle(string $fontStyle): string
    {
        $fontStyle = strtolower(trim($fontStyle));
        return in_array($fontStyle, ['regular', 'bold', 'italic', 'bold_italic'], true)
            ? $fontStyle
            : 'regular';
    }

    private function parseTemplateSegments(string $template, array $participant, array $extras): array
    {
        return $this->parseTemplateStyledSegments($template, $participant, $extras, 'regular', 'default');
    }

    private function normalizeTemplateDynamicValue($value): string
    {
        if (is_array($value)) {
            $value = implode(', ', $this->extractNameList($value));
        } elseif (!is_scalar($value) && $value !== null) {
            return '';
        }

        $text = trim((string) $value);
        if ($text === '') {
            return '';
        }

        $normalized = preg_replace('/\s+/u', ' ', $text);
        if ($normalized === null) {
            return $text;
        }

        return trim($normalized);
    }

    private function buildTemplatePieces(
        array $segments,
        string $fontPath,
        int $fontSize,
        int $maxWidth,
        int $wordSpacing,
        array $options
    ): array
    {
        $pieces = [];

        foreach ($segments as $segment) {
            $text = (string) ($segment['text'] ?? '');
            $dynamic = (bool) ($segment['dynamic'] ?? false);
            $segmentFontStyle = $this->normalizeInlineFontStyle((string) ($segment['font_style'] ?? ($options['font_style'] ?? 'regular')));
            $segmentFontFamily = $this->normalizeFontFamilyValue((string) ($segment['font_family'] ?? ($options['font_family'] ?? 'default')));
            $segmentTextAlign = strtolower((string) ($segment['text_align'] ?? ($options['text_align'] ?? 'left')));
            if (!in_array($segmentTextAlign, ['left', 'center', 'right', 'justify'], true)) {
                $segmentTextAlign = in_array((string) ($options['text_align'] ?? 'left'), ['left', 'center', 'right', 'justify'], true)
                    ? (string) $options['text_align']
                    : 'left';
            }
            $segmentOptions = $options;
            $segmentOptions['font_style'] = $segmentFontStyle;
            $segmentOptions['font_family'] = $segmentFontFamily;
            $segmentFontPath = $this->resolveFontPathByOptions($fontPath, $segmentOptions);

            $lineParts = preg_split('/(\n)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
            if ($lineParts === false) {
                $lineParts = [$text];
            }

            foreach ($lineParts as $linePart) {
                if ($linePart === '') {
                    continue;
                }

                if ($linePart === "\n") {
                    $pieces[] = ['break' => true];
                    continue;
                }

                $chunks = preg_split('/(\s+)/u', $linePart, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
                if ($chunks === false || $chunks === []) {
                    continue;
                }

                foreach ($chunks as $chunk) {
                    if (trim($chunk) === '') {
                        $spaceWidth = $this->pieceWidth($chunk, $segmentFontPath, $fontSize, $wordSpacing);
                        $pieces[] = [
                            'text' => $chunk,
                            'dynamic' => $dynamic,
                            'font_style' => $segmentFontStyle,
                            'font_family' => $segmentFontFamily,
                            'text_align' => $segmentTextAlign,
                            'font_path' => $segmentFontPath,
                            'width' => $spaceWidth,
                        ];
                        continue;
                    }

                    $chunkWidth = $this->pieceWidth($chunk, $segmentFontPath, $fontSize, $wordSpacing);
                    if ($chunkWidth <= $maxWidth) {
                        $pieces[] = [
                            'text' => $chunk,
                            'dynamic' => $dynamic,
                            'font_style' => $segmentFontStyle,
                            'font_family' => $segmentFontFamily,
                            'text_align' => $segmentTextAlign,
                            'font_path' => $segmentFontPath,
                            'width' => $chunkWidth,
                        ];
                        continue;
                    }

                    $wordParts = $this->splitLongWord($chunk, $segmentFontPath, $fontSize, $maxWidth);
                    foreach ($wordParts as $wordPart) {
                        $wordPartWidth = $this->pieceWidth($wordPart, $segmentFontPath, $fontSize, $wordSpacing);
                        $pieces[] = [
                            'text' => $wordPart,
                            'dynamic' => $dynamic,
                            'font_style' => $segmentFontStyle,
                            'font_family' => $segmentFontFamily,
                            'text_align' => $segmentTextAlign,
                            'font_path' => $segmentFontPath,
                            'width' => $wordPartWidth,
                        ];
                    }
                }
            }
        }

        return $pieces;
    }

    private function layoutTemplateLines(array $pieces, string $fontPath, int $fontSize, int $maxWidth, int $wordSpacing): array
    {
        $lines = [['pieces' => [], 'width' => 0, 'ends_with_break' => false, 'text_align' => null]];

        foreach ($pieces as $piece) {
            if (!empty($piece['break'])) {
                $currentIndex = count($lines) - 1;
                $lines[$currentIndex]['ends_with_break'] = true;
                $lines[] = ['pieces' => [], 'width' => 0, 'ends_with_break' => false, 'text_align' => null];
                continue;
            }

            $text = (string) ($piece['text'] ?? '');
            if ($text === '') {
                continue;
            }

            $pieceFontPath = (string) ($piece['font_path'] ?? $fontPath);
            $pieceWidth = (int) ($piece['width'] ?? $this->pieceWidth($text, $pieceFontPath, $fontSize, $wordSpacing));
            $isWhitespace = trim($text) === '';
            $pieceTextAlign = strtolower((string) ($piece['text_align'] ?? 'left'));
            if (!in_array($pieceTextAlign, ['left', 'center', 'right', 'justify'], true)) {
                $pieceTextAlign = 'left';
            }

            $currentIndex = count($lines) - 1;
            $currentLine = $lines[$currentIndex];

            if (($currentLine['pieces'] ?? []) !== [] && !$isWhitespace && ($currentLine['text_align'] ?? 'left') !== $pieceTextAlign) {
                $lines[] = ['pieces' => [], 'width' => 0, 'ends_with_break' => false, 'text_align' => null];
                $currentIndex = count($lines) - 1;
                $currentLine = $lines[$currentIndex];
            }

            if ($isWhitespace && $currentLine['width'] === 0) {
                continue;
            }

            if (($currentLine['width'] + $pieceWidth) > $maxWidth && $currentLine['width'] > 0) {
                $lines[] = ['pieces' => [], 'width' => 0, 'ends_with_break' => false, 'text_align' => null];
                $currentIndex = count($lines) - 1;
                $currentLine = $lines[$currentIndex];

                if ($isWhitespace) {
                    continue;
                }
            }

            $lines[$currentIndex]['pieces'][] = [
                'text' => $text,
                'dynamic' => (bool) ($piece['dynamic'] ?? false),
                'font_path' => $pieceFontPath,
                'font_style' => (string) ($piece['font_style'] ?? 'regular'),
                'text_align' => $pieceTextAlign,
                'width' => $pieceWidth,
            ];
            $lines[$currentIndex]['width'] += $pieceWidth;

            if ($lines[$currentIndex]['text_align'] === null) {
                $lines[$currentIndex]['text_align'] = $pieceTextAlign;
            }
        }

        if ($lines === []) {
            return [['pieces' => [], 'width' => 0, 'ends_with_break' => false, 'text_align' => null]];
        }

        return $lines;
    }

    private function pieceWidth(string $text, string $fontPath, int $fontSize, int $wordSpacing): int
    {
        $width = $this->textWidth($text, $fontPath, $fontSize);
        if (trim($text) === '' && $wordSpacing > 0) {
            $width += substr_count($text, ' ') * $wordSpacing;
        }

        return $width;
    }

    private function spaceCountInTemplateLine(array $pieces): int
    {
        $count = 0;
        foreach ($pieces as $piece) {
            $count += substr_count((string) ($piece['text'] ?? ''), ' ');
        }

        return $count;
    }

    private function trimTrailingEmptyTemplateLines(array $lines): array
    {
        while (count($lines) > 1) {
            $last = $lines[count($lines) - 1] ?? null;
            if (!is_array($last)) {
                break;
            }

            $pieces = $last['pieces'] ?? [];
            $width = (int) ($last['width'] ?? 0);
            if ($width !== 0 || $pieces !== []) {
                break;
            }

            array_pop($lines);
        }

        return $lines;
    }

    private function appendEllipsisToLastTemplateLine(array &$lines, string $fontPath, int $fontSize, int $maxWidth): void
    {
        if ($lines === []) {
            return;
        }

        $ellipsis = '...';
        $ellipsisWidth = $this->textWidth($ellipsis, $fontPath, $fontSize);
        $lastIndex = count($lines) - 1;

        if (!isset($lines[$lastIndex]) || !is_array($lines[$lastIndex])) {
            return;
        }

        if (!isset($lines[$lastIndex]['pieces']) || !is_array($lines[$lastIndex]['pieces'])) {
            $lines[$lastIndex]['pieces'] = [];
        }

        if (!isset($lines[$lastIndex]['width'])) {
            $lines[$lastIndex]['width'] = 0;
        }

        while ($lines[$lastIndex]['pieces'] !== [] && ((int) $lines[$lastIndex]['width'] + $ellipsisWidth) > $maxWidth) {
            $removed = array_pop($lines[$lastIndex]['pieces']);
            $removedText = (string) ($removed['text'] ?? '');
            $removedWidth = (int) ($removed['width'] ?? $this->textWidth($removedText, $fontPath, $fontSize));
            $lines[$lastIndex]['width'] = max(0, (int) $lines[$lastIndex]['width'] - $removedWidth);
        }

        if (((int) $lines[$lastIndex]['width'] + $ellipsisWidth) > $maxWidth) {
            return;
        }

        $lines[$lastIndex]['pieces'][] = [
            'text' => $ellipsis,
            'dynamic' => false,
            'width' => $ellipsisWidth,
        ];
        $lines[$lastIndex]['width'] = (int) $lines[$lastIndex]['width'] + $ellipsisWidth;
    }

    private function drawDottedUnderline($image, int $startX, int $endX, int $y, int $color): void
    {
        if ($endX <= $startX) {
            return;
        }

        $dotLength = 3;
        $gapLength = 2;
        $thickness = 2;

        for ($x = $startX; $x <= $endX; $x += ($dotLength + $gapLength)) {
            $dotEnd = min($x + $dotLength - 1, $endX);
            for ($offset = 0; $offset < $thickness; $offset++) {
                imageline($image, $x, $y + $offset, $dotEnd, $y + $offset, $color);
            }
        }
    }

    private function isUnderlineBridgeText(string $text): bool
    {
        return preg_match('/^[^\p{L}\p{N}]+$/u', $text) === 1;
    }

    private function allocateColor($image, string $hexColor): int
    {
        $hexColor = \normalize_hex_color($hexColor);
        $red = hexdec(substr($hexColor, 1, 2));
        $green = hexdec(substr($hexColor, 3, 2));
        $blue = hexdec(substr($hexColor, 5, 2));

        return imagecolorallocate($image, $red, $green, $blue);
    }

    private function wrapText(string $text, string $fontPath, int $fontSize, int $maxWidth): array
    {
        $text = trim(preg_replace('/\s+/', ' ', $text) ?? $text);
        if ($text === '') {
            return [''];
        }

        $wrappedLines = [];
        $paragraphs = preg_split('/\n+/', $text) ?: [$text];

        foreach ($paragraphs as $paragraph) {
            $words = preg_split('/\s+/', trim($paragraph)) ?: [];
            if ($words === []) {
                $wrappedLines[] = '';
                continue;
            }

            $line = '';
            foreach ($words as $word) {
                $candidate = $line === '' ? $word : $line . ' ' . $word;
                if ($this->textWidth($candidate, $fontPath, $fontSize) <= $maxWidth) {
                    $line = $candidate;
                    continue;
                }

                if ($line !== '') {
                    $wrappedLines[] = $line;
                    $line = '';
                }

                if ($this->textWidth($word, $fontPath, $fontSize) <= $maxWidth) {
                    $line = $word;
                } else {
                    $chunks = $this->splitLongWord($word, $fontPath, $fontSize, $maxWidth);
                    foreach ($chunks as $index => $chunk) {
                        if ($index === count($chunks) - 1) {
                            $line = $chunk;
                        } else {
                            $wrappedLines[] = $chunk;
                        }
                    }
                }
            }

            if ($line !== '') {
                $wrappedLines[] = $line;
            }
        }

        return $wrappedLines === [] ? [''] : $wrappedLines;
    }

    private function splitLongWord(string $word, string $fontPath, int $fontSize, int $maxWidth): array
    {
        $chars = preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY) ?: str_split($word);
        $chunks = [];
        $current = '';

        foreach ($chars as $char) {
            $candidate = $current . $char;
            if ($current !== '' && $this->textWidth($candidate, $fontPath, $fontSize) > $maxWidth) {
                $chunks[] = $current;
                $current = $char;
            } else {
                $current = $candidate;
            }
        }

        if ($current !== '') {
            $chunks[] = $current;
        }

        return $chunks === [] ? [$word] : $chunks;
    }

    private function textWidth(string $text, string $fontPath, int $fontSize): int
    {
        $bbox = imagettfbbox($fontSize, 0, $fontPath, $text);
        if ($bbox === false) {
            return strlen($text) * $fontSize;
        }

        return (int) abs($bbox[2] - $bbox[0]);
    }

    private function buildFileName(string $format, array $participant): string
    {
        $format = trim($format);
        if ($format === '') {
            $format = DEFAULT_FILE_NAME_FORMAT;
        }

        $replace = [
            '{conference}' => \safe_filename((string) ($participant['conference_name'] ?? 'conference')),
            '{year}' => \safe_filename((string) ($participant['year'] ?? date('Y'))),
            '{category}' => \safe_filename((string) ($participant['type_slug'] ?? 'certificate')),
            '{name}' => \safe_filename((string) ($participant['name'] ?? 'participant')),
            '{id}' => (string) ($participant['id'] ?? '0'),
        ];

        $value = strtr($format, $replace);
        if (strpos($format, '{id}') === false) {
            $value .= '_' . $replace['{id}'];
        }

        $value = \safe_filename($value);
        if ($value === '') {
            return 'certificate_' . $replace['{id}'];
        }

        return $value;
    }

    private function toRelativePath(string $absolutePath): string
    {
        $normalizedAbsolute = str_replace('\\', '/', $absolutePath);
        $normalizedRoot = str_replace('\\', '/', APP_ROOT);

        if (str_starts_with($normalizedAbsolute, $normalizedRoot . '/')) {
            return substr($normalizedAbsolute, strlen($normalizedRoot) + 1);
        }

        return $normalizedAbsolute;
    }

    private function syncToStudentDocument(int $studentId, string $certTitle, string $pdfRelative, string $pdfAbsolute): void
    {
        $fileSize = is_file($pdfAbsolute) ? filesize($pdfAbsolute) : 0;
        $fileName = basename($pdfRelative);

        $check = $this->pdo->prepare(
            "SELECT id FROM student_documents WHERE student_id = :student_id AND type = 'certificate' AND title = :title AND deleted_at IS NULL LIMIT 1"
        );
        $check->execute(['student_id' => $studentId, 'title' => $certTitle]);

        if ($check->fetch()) {
            $update = $this->pdo->prepare(
                "UPDATE student_documents SET file_name = :file_name, stored_name = :stored_name, file_size = :file_size, updated_at = NOW() WHERE student_id = :student_id AND type = 'certificate' AND title = :title AND deleted_at IS NULL"
            );
            $update->execute([
                'file_name'   => $fileName,
                'stored_name' => $pdfRelative,
                'file_size'   => $fileSize,
                'student_id'  => $studentId,
                'title'       => $certTitle,
            ]);
        } else {
            $insert = $this->pdo->prepare(
                "INSERT INTO student_documents (student_id, type, title, file_name, stored_name, mime_type, file_size, status, created_at) VALUES (:student_id, 'certificate', :title, :file_name, :stored_name, 'application/pdf', :file_size, 'verified', NOW())"
            );
            $insert->execute([
                'student_id'  => $studentId,
                'title'       => $certTitle,
                'file_name'   => $fileName,
                'stored_name' => $pdfRelative,
                'file_size'   => $fileSize,
            ]);
        }
    }
}
