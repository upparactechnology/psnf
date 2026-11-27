<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Pearl School report card (the 11-page "PSNF – PEARL SCHOOL REPORT CARD" format).
 *
 * The layout is fixed and mirrors the printed card page by page; only the empty
 * cells are filled from the student's saved report card. Stored in
 * student_report_cards JSON columns:
 *
 *   routine_profile / learning_skills / cocurriculum_profile:
 *       { row_key: { "g": "A|B|C|NA|AB|NS", "p": ["VP","GP"] } }
 *   academic_profile:
 *       { english|maths|gk: { ut|th|op: { "v": "14"|"AB"|"NS"|"NA", "p": [...] } },
 *         homework: { "g": ..., "p": [...] } }
 *   attendance_profile:
 *       { present_days, working_days, punctuality, combine_day_presence }
 *   authorized_by:
 *       { feedback_date: "dd/mm/yyyy", format: "pearl_v1" }
 */
class PearlReportCard
{
    public const FORMAT = 'pearl_v1';
    public const TOTAL_PAGES = 11;

    /** Grades used in the Routine / Learning / Co-curriculum profiles. */
    public const RATINGS = ['A', 'B', 'C', 'NA', 'AB', 'NS'];

    /** Special entries allowed instead of marks in the Academic profile. */
    public const MARK_CODES = ['AB', 'NS', 'NA'];

    /** Support-provided (prompt) keys, printed as "[VP,GP]" under a value. */
    public const PROMPTS = [
        'OP'  => 'Occasional Prompt',
        'VP'  => 'Verbal Prompt',
        'GP'  => 'Gestural Prompt',
        'MP'  => 'Modeling Prompt',
        'PP'  => 'Physical Prompt',
        'PR'  => 'Paper Read by the Instructor',
        'PRW' => 'Paper Read and Written by the Instructor',
        'PTL' => 'Paper Translated by the Instructor',
        'SWL' => 'Student writes in Hindi or Gujarati',
    ];

    /** Academic columns: key => [label, max marks]. */
    public const ACADEMIC_COLUMNS = [
        'ut' => ['Unit Test [20]', 20],
        'th' => ['Theory [40]', 40],
        'op' => ['Oral / Practical/ Project [15]', 15],
    ];

    public const ACADEMIC_SUBJECTS = [
        'english' => 'English [Includes English-1 English-2 English-3]',
        'maths'   => 'Maths',
        'gk'      => 'General Knowledge / EVS',
    ];

    /**
     * Rating tables, in print order. Each row is [key, label] or
     * ['group', label] for an underlined sub-heading without a value cell.
     *
     * @return array<string, array{column: string, store: string, title: string, rows: array}>
     */
    public static function tables(): array
    {
        return [
            'routine_1' => [
                'store' => 'routine_profile', 'column' => 'Routines', 'title' => 'Routine Profile',
                'rows' => [
                    ['behaviour', 'Behaviour'],
                    ['class_work', 'Class Work'],
                    ['book_care', 'Book Care'],
                    ['uniform', 'Uniform'],
                    ['neatness', 'Neatness'],
                    ['appearance', 'Appearance'],
                    ['knowing_belongings', 'Knowing Belongings'],
                    ['taking_care_belonging', 'Taking Care Of Belonging'],
                    ['following_instructions', 'Following Instructions'],
                ],
            ],
            'routine_2' => [
                'store' => 'routine_profile', 'column' => 'Routines', 'title' => 'Routine Profile Continued',
                'rows' => [
                    ['personal_hygiene', 'Personal Hygiene'],
                    ['group', 'Self Help Skills'],
                    ['eating_skills', 'Eating Skills'],
                    ['drinking_skills', 'Drinking Skills'],
                    ['toileting_skills', 'Toileting Skills'],
                    ['dressing_skills', 'Dressing Skills'],
                    ['effort', 'Effort'],
                    ['free_play', 'Participates in Free Play Time'],
                    ['peer_socialization', 'Peer Socialization'],
                    ['communication_teachers', 'Communication with Teachers'],
                    ['attitude_teachers', 'Attitudes towards Teachers'],
                    ['communication_classmates', 'Communication with Classmates'],
                    ['attitude_schoolmates', 'Attitude towards Schoolmates'],
                ],
            ],
            'learning_1' => [
                'store' => 'learning_skills', 'column' => 'Skills', 'title' => 'Learning Skills Profile',
                'rows' => [
                    ['reading_words', 'Reading words'],
                    ['writing', 'Writing [Homebase]'],
                    ['speaking', 'Speaking'],
                    ['listening', 'Listening'],
                    ['pointing', 'Pointing'],
                    ['counting', 'Counting'],
                    ['identify', 'Identify'],
                    ['matching', 'Matching'],
                    ['pairing', 'Pairing'],
                    ['telling_time', 'Telling Time'],
                    ['differentiate', 'Differentiate'],
                    ['sequencing', 'Sequencing'],
                    ['thinking', 'Thinking'],
                ],
            ],
            'learning_2' => [
                'store' => 'learning_skills', 'column' => 'Skills', 'title' => 'Learning Skills Profile Continued',
                'rows' => [
                    ['memorize', 'Memorize'],
                    ['avaz_tablet', 'Avaz Tablet'],
                    ['avaz_sheet', 'Avaz Sheet'],
                    ['non_curriculum', 'Participation in non- curriculum activities'],
                    ['active_participation', 'Active participation in class'],
                ],
            ],
            'cocurricular_1' => [
                'store' => 'cocurriculum_profile', 'column' => 'Subjects',
                'title' => 'Co-Curriculum Profile and Towards Independence of Life Skills',
                'rows' => [
                    ['computer', 'Computer'],
                    ['pe', 'P.E.'],
                    ['music', 'Music'],
                    ['art_craft', 'Art and Craft'],
                    ['story', 'Story'],
                    ['rhymes', 'Rhymes'],
                    ['structured_games', 'Structured Games'],
                    ['adl_skills', 'Activities for Daily Living [ADL] Skills'],
                    ['money_skills', 'Money Skills'],
                    ['motor_skills', 'Motor Skills'],
                    ['pre_academic', 'Pre Academic Mental Skills'],
                    ['community_survival', 'Community Survival Skills'],
                    ['performance_readiness', 'Performance Readiness Skills'],
                    ['cognitive_development', 'Cognitive Development'],
                    ['pecs', 'PECS'],
                ],
            ],
            'cocurricular_2' => [
                'store' => 'cocurriculum_profile', 'column' => null,
                'title' => 'Co-Curriculum Profile and Towards Independence of Life Skills  Continued',
                'rows' => [
                    ['socio_emotional_1', 'Socio- Emotional Skills-1'],
                    ['socio_emotional_2', 'Socio- Emotional Skills-2'],
                    ['gtbt', 'GTBT'],
                    ['diy_kits', 'DIY Kits'],
                    ['dance', 'Dance'],
                    ['yoga', 'Yoga'],
                    ['month_of_year', 'Month Of the Year'],
                    ['days_of_week', 'Days of the Week'],
                    ['nature_club', 'Nature Club'],
                    ['group', 'Assembly'],
                    ['assembly_calendar', 'Calendar'],
                    ['assembly_my_needs', 'My Needs'],
                    ['assembly_feelings', 'Feelings'],
                    ['assembly_class_rules', 'Class Rules'],
                    ['assembly_weekend', 'How was your weekend?'],
                ],
            ],
        ];
    }

    /** Store column => list of valid row keys. */
    public static function rowKeys(): array
    {
        $keys = [];
        foreach (self::tables() as $t) {
            foreach ($t['rows'] as [$key]) {
                if ($key !== 'group') {
                    $keys[$t['store']][] = $key;
                }
            }
        }
        return $keys;
    }

    // ─── Input normalisation ──────────────────────────────────────────────

    /** "vp, gp" | ["VP","GP"] => ["VP","GP"] (known codes only, de-duplicated). */
    public static function normalizePrompts($raw): array
    {
        if (is_array($raw)) {
            $raw = implode(',', $raw);
        }
        $out = [];
        foreach (preg_split('/[\s,;\/]+/', strtoupper((string) $raw)) as $code) {
            $code = trim($code, "[] ");
            if ($code !== '' && isset(self::PROMPTS[$code]) && !in_array($code, $out, true)) {
                $out[] = $code;
            }
        }
        return $out;
    }

    private static function normalizeRating($raw): array
    {
        $g = strtoupper(trim((string) ($raw['g'] ?? '')));
        return [
            'g' => in_array($g, self::RATINGS, true) ? $g : '',
            'p' => self::normalizePrompts($raw['p'] ?? ''),
        ];
    }

    /** Marks cell: a number within 0..max, or AB / NS / NA. */
    private static function normalizeMark($raw, int $max): array
    {
        $v = strtoupper(trim((string) ($raw['v'] ?? '')));
        if ($v !== '' && !in_array($v, self::MARK_CODES, true)) {
            $v = is_numeric($v) ? self::formatNumber(max(0.0, min((float) $v, (float) $max))) : '';
        }
        return ['v' => $v, 'p' => self::normalizePrompts($raw['p'] ?? '')];
    }

    /**
     * Turn posted form data into the stored JSON structures.
     *
     * @return array{routine_profile: array, learning_skills: array, cocurriculum_profile: array,
     *               academic_profile: array, attendance_profile: array}
     */
    public static function normalizeInput(array $post): array
    {
        $out = [];
        foreach (self::rowKeys() as $store => $keys) {
            $out[$store] = [];
            foreach ($keys as $key) {
                $cell = self::normalizeRating($post[$store][$key] ?? []);
                if ($cell['g'] !== '' || $cell['p']) {
                    $out[$store][$key] = $cell;
                }
            }
        }

        $academic = [];
        foreach (self::ACADEMIC_SUBJECTS as $sub => $_) {
            foreach (self::ACADEMIC_COLUMNS as $col => [, $max]) {
                $cell = self::normalizeMark($post['academic_profile'][$sub][$col] ?? [], $max);
                if ($cell['v'] !== '' || $cell['p']) {
                    $academic[$sub][$col] = $cell;
                }
            }
        }
        $hw = self::normalizeRating($post['academic_profile']['homework'] ?? []);
        if ($hw['g'] !== '' || $hw['p']) {
            $academic['homework'] = $hw;
        }
        $out['academic_profile'] = $academic;

        $att = $post['attendance_profile'] ?? [];
        $int = static fn($v) => ($v === '' || $v === null || !is_numeric($v)) ? '' : (string) max(0, (int) $v);
        $txt = static fn($v) => mb_substr(trim(strip_tags((string) $v)), 0, 40);
        $out['attendance_profile'] = [
            'present_days'         => $int($att['present_days'] ?? ''),
            'working_days'         => $int($att['working_days'] ?? ''),
            'punctuality'          => $txt($att['punctuality'] ?? ''),
            'combine_day_presence' => $txt($att['combine_day_presence'] ?? ''),
        ];

        return $out;
    }

    // ─── Calculations ─────────────────────────────────────────────────────

    /** % and grade for a subject; only when every component has numeric marks. */
    public static function subjectResult(array $subject): array
    {
        $obtained = 0.0;
        $max = 0;
        foreach (self::ACADEMIC_COLUMNS as $col => [, $colMax]) {
            $v = $subject[$col]['v'] ?? '';
            if (!is_numeric($v)) {
                return ['pct' => '', 'grade' => ''];
            }
            $obtained += (float) $v;
            $max += $colMax;
        }
        $pct = $max > 0 ? round($obtained / $max * 100, 2) : 0.0;
        return ['pct' => number_format($pct, 2), 'grade' => self::gradeFor($pct)];
    }

    /** Grade scale printed on page 10. */
    public static function gradeFor(float $pct): string
    {
        foreach ([[91, 'A+'], [81, 'A'], [71, 'B+'], [61, 'B'], [51, 'C+'], [41, 'C'], [33, 'D'], [21, 'E']] as [$min, $g]) {
            if ($pct >= $min) {
                return $g;
            }
        }
        return 'F';
    }

    public static function formatNumber(float $n): string
    {
        return rtrim(rtrim(number_format($n, 2, '.', ''), '0'), '.');
    }

    /** "2026-2027" => "2026-27" (the card always prints the short form). */
    public static function displayYear(string $year): string
    {
        return preg_match('/^(\d{4})\s*[-–\/]\s*(\d{2})(\d{2})$/', trim($year), $m) ? "$m[1]-$m[3]" : trim($year);
    }

    /** "semester 1" => "Semester 1". */
    public static function displaySemester(string $semester): string
    {
        return ucwords(strtolower(trim($semester)));
    }

    public static function promptText(array $p): string
    {
        return $p ? '[' . implode(',', $p) . ']' : '';
    }

    // ─── Student / assets ─────────────────────────────────────────────────

    /** "PRIMARY ADVANCE-C" style class line for the cover. */
    public static function standardFor(array $student): string
    {
        $name = '';
        $section = trim((string) ($student['section'] ?? ''));
        if (!empty($student['class_id'])) {
            $class = \Core\Application::$app->db->selectOne("SELECT name, section FROM classes WHERE id = ? LIMIT 1", [(int) $student['class_id']]);
            if ($class) {
                $name = trim((string) $class['name']);
                $section = $section !== '' ? $section : trim((string) ($class['section'] ?? ''));
            }
        }
        if ($name === '') {
            $name = trim((string) ($student['class'] ?? ''));
        }
        if ($section !== '' && !preg_match('/[-\s]' . preg_quote($section, '/') . '$/i', $name)) {
            $name .= '-' . $section;
        }
        return mb_strtoupper($name);
    }

    /** Bundled asset (resources/report-card) as a data: URI, so HTML and PDF need no web access. */
    public static function asset(string $file): string
    {
        $path = ROOT_PATH . '/resources/report-card/' . basename($file);
        return is_file($path) ? self::dataUri($path) : '';
    }

    /**
     * Signature for a trustee: the uploaded one from Report Card Settings when it
     * resolves to a local file, else the bundled default matched by name.
     */
    public static function trusteeSignature(array $trustee): string
    {
        $uploaded = self::uploadedImage($trustee['sig'] ?? '');
        if ($uploaded !== '') {
            return $uploaded;
        }
        $name = strtolower((string) ($trustee['name'] ?? ''));
        foreach (['griva' => 'sig_griva_shah.jpg', 'bijal' => 'sig_bijal_fadia.jpg', 'sonia' => 'sig_sonia_parikh.jpg'] as $needle => $file) {
            if (str_contains($name, $needle)) {
                return self::asset($file);
            }
        }
        return '';
    }

    /** Local image (e.g. an uploaded stamp under public/uploads) as a data: URI. */
    public static function uploadedImage(?string $url): string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return '';
        }
        if (str_starts_with($url, 'data:image/')) {
            return $url;
        }
        $path = parse_url($url, PHP_URL_PATH) ?: $url;
        $pos = strpos($path, '/uploads/');
        if ($pos === false) {
            return '';
        }
        $local = ROOT_PATH . '/public' . substr($path, $pos);
        return is_file($local) ? self::dataUri($local) : '';
    }

    private static function dataUri(string $path): string
    {
        $real = realpath($path);
        if ($real === false || !str_starts_with($real, realpath(ROOT_PATH))) {
            return '';
        }
        $mime = mime_content_type($real) ?: 'image/jpeg';
        if (!str_starts_with($mime, 'image/')) {
            return '';
        }
        return 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($real));
    }

    // ─── Rendering ────────────────────────────────────────────────────────

    /** Trustees for the "Authorised By" block (settings, else the three defaults). */
    public static function trustees(?array $settings): array
    {
        $list = $settings['trustees_config'] ?? [];
        if (is_string($list)) {
            $list = json_decode($list, true) ?: [];
        }
        if (!$list) {
            $list = [
                ['name' => 'Dr Griva Shah', 'title' => 'MT-PSNF', 'sig' => ''],
                ['name' => 'Bijal Fadia', 'title' => 'Trustee-PSNF', 'sig' => ''],
                ['name' => 'Sonia Parikh', 'title' => 'Trustee-PSNF', 'sig' => ''],
            ];
        }
        return array_slice($list, 0, 3);
    }

    /** Full printable document (11 A4 pages) as HTML; used for the preview and for Dompdf. */
    public static function renderHtml(array $student, array $reportCard, string $semester, string $academicYear, ?array $settings, bool $forPdf, array $toolbar = []): string
    {
        $data = [
            'student'      => $student,
            'card'         => $reportCard,
            'semester'     => $semester,
            'academicYear' => $academicYear,
            'settings'     => $settings ?: [],
            'forPdf'       => $forPdf,
            'toolbar'      => $toolbar,
        ];
        extract($data);
        ob_start();
        include VIEWS_PATH . '/report-cards/pearl_document.php';
        return (string) ob_get_clean();
    }

    /** Render with Dompdf and return the PDF bytes. */
    public static function renderPdf(string $html): string
    {
        require_once ROOT_PATH . '/public/certificate_generator/vendor/autoload.php';
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false); // every image is an inline data: URI
        $options->set('defaultFont', 'Helvetica');
        $options->set('dpi', 96);
        // Font metric cache in storage (vendor/ may not be writable on the server)
        $cache = ROOT_PATH . '/storage/cache/dompdf';
        if (is_dir($cache) || @mkdir($cache, 0775, true)) {
            $options->set('fontCache', $cache);
        }
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return (string) $dompdf->output();
    }

    public static function fileName(array $student, string $semester, string $academicYear): string
    {
        $name = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
        $base = 'Report Card ' . $name . ' ' . $semester . ' ' . $academicYear;
        return preg_replace('/[^A-Za-z0-9._ -]+/', '', $base) . '.pdf';
    }
}
