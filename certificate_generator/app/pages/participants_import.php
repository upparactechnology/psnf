<?php
declare(strict_types=1);

use App\Services\ImportService;

$selectedConferenceId = 1;
$conferenceStmt = db()->prepare('SELECT * FROM conferences WHERE id = 1 LIMIT 1');
$conferenceStmt->execute();
$conference = $conferenceStmt->fetch();
if ($conference === false) {
    flash('error', 'Default conference not found.');
    redirect(url('dashboard'));
}

$certificateTypesStmt = db()->prepare('SELECT id, name FROM certificate_types WHERE conference_id = :conference_id ORDER BY name ASC');
$certificateTypesStmt->execute(['conference_id' => $selectedConferenceId]);
$certificateTypes = $certificateTypesStmt->fetchAll();

$errors = [];
$previewHeaders = [];
$previewSheets = [];
$selectedSheet = null;
$headerRow = 1;
$tempUploadName = '';
$originalUploadName = '';

if (is_post_request()) {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'preview_upload') {
        // Two flows: 1) new file upload, 2) selecting a sheet from previously uploaded temp file.
        if (isset($_FILES['import_file']) && (int) $_FILES['import_file']['error'] === UPLOAD_ERR_OK) {
            $tmpPath = (string) $_FILES['import_file']['tmp_name'];
            $originalName = safe_filename((string) $_FILES['import_file']['name']);
            $headerRow = max(1, (int) ($_POST['header_row'] ?? 1));
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            if (!in_array($extension, ['csv', 'xls', 'xlsx', 'ods'], true)) {
                flash('error', 'Only CSV and spreadsheet files are allowed (csv, xls, xlsx, ods).');
                redirect(url('participants-import', ['conference_id' => $selectedConferenceId]));
            }

            $storedName = date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '_' . $originalName;
            $storedPath = CSV_UPLOAD_ROOT . '/' . $storedName;
            if (!move_uploaded_file($tmpPath, $storedPath)) {
                flash('error', 'Unable to save uploaded file on server.');
                redirect(url('participants-import', ['conference_id' => $selectedConferenceId]));
            }

            $service = new ImportService(db());
            try {
                // If spreadsheet library is present, get sheet names to allow panel selection
                $sheetNames = [];
                try {
                    $sheetNames = $service->getSheetNames($storedPath, $originalName);
                } catch (\Throwable $inner) {
                    // ignore
                }

                if ($sheetNames !== []) {
                    $previewSheets = $sheetNames;
                    $tempUploadName = $storedPath;
                    $originalUploadName = $originalName;
                } else {
                    $headers = $service->getHeaders($storedPath, $originalName, null, $headerRow);
                    $previewHeaders = $headers;
                    $tempUploadName = $storedPath;
                    $originalUploadName = $originalName;
                }
            } catch (\Throwable $ex) {
                unlink($storedPath);
                flash('error', 'Unable to read uploaded file: ' . $ex->getMessage());
                redirect(url('participants-import', ['conference_id' => $selectedConferenceId]));
            }

        } elseif (!empty($_POST['temp_path']) && is_file((string) $_POST['temp_path'])) {
            // user selected a sheet from the sheet-picker form
            $temp = (string) $_POST['temp_path'];
            $originalName = (string) ($_POST['original_name'] ?? '');
            $selectedSheet = trim((string) ($_POST['sheet_name'] ?? '')) ?: null;
            $headerRow = max(1, (int) ($_POST['header_row'] ?? 1));

            $service = new ImportService(db());
            try {
                $headers = $service->getHeaders($temp, $originalName, $selectedSheet, $headerRow);
                $previewHeaders = $headers;
                $tempUploadName = $temp;
                $originalUploadName = $originalName;
            } catch (\Throwable $ex) {
                @unlink($temp);
                flash('error', 'Unable to read uploaded file: ' . $ex->getMessage());
                redirect(url('participants-import', ['conference_id' => $selectedConferenceId]));
            }

        } else {
            flash('error', 'File upload failed.');
            redirect(url('participants-import', ['conference_id' => $selectedConferenceId]));
        }
    }

    if ($action === 'execute_import') {
        $tempUploadName = (string) ($_POST['temp_path'] ?? '');
        $originalUploadName = (string) ($_POST['original_name'] ?? '');
        $mapping = $_POST['mapping'] ?? [];
        $defaultType = (int) ($_POST['default_certificate_type_id'] ?? 0);

        if ($tempUploadName === '' || !is_file($tempUploadName)) {
            flash('error', 'Temporary uploaded file not found. Start import again.');
            redirect(url('participants-import', ['conference_id' => $selectedConferenceId]));
        }

        $service = new ImportService(db());
        try {
            $selectedSheet = trim((string) ($_POST['sheet_name'] ?? '')) ?: null;
            $headerRow = max(1, (int) ($_POST['header_row'] ?? 1));
            $headers = $service->getHeaders($tempUploadName, $originalUploadName, $selectedSheet, $headerRow);
        } catch (\Throwable $ex) {
            unlink($tempUploadName);
            flash('error', 'Unable to read uploaded file: ' . $ex->getMessage());
            redirect(url('participants-import', ['conference_id' => $selectedConferenceId]));
        }

        $inserted = 0;
        $failed = 0;
        $rowErrors = [];

        foreach ($service->iterateRows($tempUploadName, $headers, $originalUploadName, $selectedSheet, $headerRow) as $rowNumber => $row) {
            // map row per mapping
            $name = '';
            $email = '';
            $institute = '';
            $title = '';
            $category = '';
            $dateRaw = '';
            $extra = [];

            foreach ($headers as $hIndex => $h) {
                $orig = $h;
                $value = (string) ($row[$orig] ?? '');
                $map = (string) ($mapping[$orig] ?? '');
                if ($map === '') {
                    continue;
                }

                if ($map === 'name') {
                    $name = $value;
                } elseif ($map === 'email') {
                    $email = $value;
                } elseif ($map === 'institute') {
                    $institute = $value;
                } elseif ($map === 'title') {
                    $title = $value;
                } elseif ($map === 'category') {
                    $category = $value;
                } elseif ($map === 'date') {
                    $dateRaw = $value;
                } elseif ($map === 'authors') {
                    // Column contains co-author(s) or group names; split into array
                    $candidates = preg_split('/,|;|\||\r\n|\n/', (string) $value) ?: [];
                    $parts = array_values(array_filter(array_map(static fn($v) => trim((string) $v), $candidates), static fn($v) => $v !== ''));
                    if ($parts !== []) {
                        // append to any existing authors collected from other columns
                        if (!isset($extra['authors'])) {
                            $extra['authors'] = [];
                        }
                        foreach ($parts as $p) {
                            if (!in_array($p, $extra['authors'], true) && strtolower($p) !== strtolower($name)) {
                                $extra['authors'][] = $p;
                            }
                        }
                    }
                } elseif (str_starts_with($map, 'extra:')) {
                    $key = substr($map, 6) ?: $orig;
                    $extra[$key] = $value;
                }
            }

            $name = trim($name);
            if ($name === '') {
                $failed++;
                $rowErrors[] = "Row missing name";
                continue;
            }

            $certificateTypeId = $defaultType ?: null;
            if ($category !== '') {
                $resolved = (new \App\Services\CsvService(db()))->resolveCertificateTypeId($selectedConferenceId, $category);
                if ($resolved === null) {
                    $failed++;
                    $rowErrors[] = "Category '{$category}' not found for conference.";
                    continue;
                }
                $certificateTypeId = $resolved;
            }

            if ($certificateTypeId === null) {
                $failed++;
                $rowErrors[] = 'No certificate type selected or resolved.';
                continue;
            }

            $issuedDate = $dateRaw !== '' ? date('Y-m-d', strtotime($dateRaw)) : date('Y-m-d');

            try {
                $stmt = db()->prepare(
                    'INSERT INTO participants
                     (conference_id, certificate_type_id, name, email, institute, title, issued_date, extra_json, verify_code, status, created_at)
                     VALUES (:conference_id, :certificate_type_id, :name, :email, :institute, :title, :issued_date, :extra_json, :verify_code, :status, NOW())'
                );

                $stmt->execute([
                    'conference_id' => $selectedConferenceId,
                    'certificate_type_id' => $certificateTypeId,
                    'name' => $name,
                    'email' => trim($email),
                    'institute' => trim($institute),
                    'title' => trim($title),
                    'issued_date' => $issuedDate,
                    'extra_json' => json_encode($extra, JSON_UNESCAPED_UNICODE),
                    'verify_code' => bin2hex(random_bytes(8)),
                    'status' => 'pending',
                ]);

                $inserted++;
            } catch (\Throwable $ex) {
                $failed++;
                $rowErrors[] = 'DB error: ' . $ex->getMessage();
            }
        }

        // cleanup
        @unlink($tempUploadName);

        $message = 'Import finished. Inserted: ' . $inserted . ', Failed: ' . $failed . '.';
        flash($failed > 0 ? 'warning' : 'success', $message);
        if ($rowErrors !== []) {
            $preview = array_slice($rowErrors, 0, 6);
            flash('info', 'Notes: ' . implode(' | ', $preview));
        }

        redirect(url('participants', ['conference_id' => $selectedConferenceId]));
    }
}

render_view('participants_import.php', [
    'pageTitle' => 'Import Participants',
    'conference' => $conference,
    'conference_id' => $selectedConferenceId,
    'certificateTypes' => $certificateTypes,
    'errors' => $errors,
    'previewHeaders' => $previewHeaders,
    'previewSheets' => $previewSheets,
    'selectedSheet' => $selectedSheet,
    'headerRow' => $headerRow,
    'tempUploadName' => $tempUploadName,
    'originalUploadName' => $originalUploadName,
]);
