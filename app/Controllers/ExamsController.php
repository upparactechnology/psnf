<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;
use App\Models\ActivityLog;

class ExamsController extends Controller
{
    private function db()
    {
        return Application::$app->db;
    }

    public function index(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        $years = $db->select("SELECT * FROM academic_years WHERE tenant_id = ? ORDER BY year_name DESC", [$tenantId]);

        $selectedYearId = (int) $this->request->get('academic_year_id');
        if (!$selectedYearId && !empty($years)) {
            foreach ($years as $y) {
                if ($y['status'] === 'current') {
                    $selectedYearId = (int) $y['id'];
                    break;
                }
            }
            if (!$selectedYearId) {
                $selectedYearId = (int) $years[0]['id'];
            }
        }

        $mainGroups = $db->select(
            "SELECT id, name FROM main_groups WHERE tenant_id = ? AND is_active = 1 ORDER BY name ASC",
            [$tenantId]
        );

        $selectedGroupId = (int) $this->request->get('main_group_id', $mainGroups[0]['id'] ?? 0);
        $selectedSemester = $this->request->get('semester', '');

        $query = "SELECT * FROM exams WHERE tenant_id = ?";
        $params = [$tenantId];

        if ($selectedYearId > 0) {
            $query .= " AND (academic_year_id = ? OR academic_year_id IS NULL)";
            $params[] = $selectedYearId;
        }

        if ($selectedGroupId > 0) {
            $query .= " AND (main_group_id = ? OR main_group_id IS NULL)";
            $params[] = $selectedGroupId;
        }

        if ($selectedSemester) {
            $query .= " AND semester = ?";
            $params[] = $selectedSemester;
        }

        $query .= " ORDER BY FIELD(semester, 'Semester 1', 'Semester 2'), id ASC";
        $exams = $db->select($query, $params);

        return $this->view('exams/index', compact(
            'mainGroups', 'selectedGroupId', 'selectedSemester', 'exams', 'years', 'selectedYearId'
        ));
    }

    public function getSubjectsByGroup(): void
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();
        $groupId = (int) $this->request->get('main_group_id', 0);

        header('Content-Type: application/json');

        if ($groupId <= 0) {
            $subjects = $db->select(
                "SELECT id, name, type as category FROM subjects WHERE tenant_id = ? ORDER BY name ASC",
                [$tenantId]
            );
            echo json_encode($subjects);
            return;
        }

        $templateRow = $db->selectOne("
            SELECT ct.id 
            FROM curriculum_templates ct
            WHERE ct.main_group_id = ? AND ct.tenant_id = ? AND ct.is_active = 1
            ORDER BY ct.id DESC LIMIT 1
        ", [$groupId, $tenantId]);

        $subjects = [];

        if ($templateRow) {
            $subjects = $db->select("
                SELECT DISTINCT s.id, s.name, sec.section_name as category
                FROM curriculum_sections sec
                JOIN curriculum_subjects cs ON cs.curriculum_section_id = sec.id
                JOIN subjects s ON cs.subject_id = s.id
                WHERE sec.curriculum_template_id = ?
                ORDER BY sec.sort_order ASC, cs.sequence ASC
            ", [$templateRow['id']]);
        }

        if (empty($subjects)) {
            $subjects = $db->select(
                "SELECT id, name, type as category FROM subjects WHERE tenant_id = ? ORDER BY name ASC",
                [$tenantId]
            );
        }

        echo json_encode($subjects);
        return;
    }

    public function store(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        $name      = trim($this->request->input('name', ''));
        $semester  = $this->request->input('semester', 'Semester 1');
        $maxMarks  = (float) $this->request->input('max_marks', 100);
        $groupId   = (int) $this->request->input('main_group_id', 0);
        $subjectIds = $this->request->input('subject_ids', []);
        $academicYearId = (int) $this->request->input('academic_year_id', 0);

        if (!$name || !$semester) {
            $this->flash('error', 'Exam name and semester are required.');
            return $this->redirect(url('academics/exams'));
        }

        $schoolId = 1;
        $branchId = 1;

        $subjectIdsJson = !empty($subjectIds) ? json_encode(array_map('intval', $subjectIds)) : null;

        try {
            $db->insert('exams', [
                'tenant_id'       => $tenantId,
                'school_id'       => $schoolId,
                'branch_id'       => $branchId,
                'name'            => $name,
                'semester'        => $semester,
                'max_marks'       => $maxMarks,
                'main_group_id'   => $groupId > 0 ? $groupId : null,
                'academic_year_id'=> $academicYearId > 0 ? $academicYearId : null,
                'subject_ids'     => $subjectIdsJson,
            ]);

            ActivityLog::log('exam_created', $this->authId(), [
                'name'     => $name,
                'semester' => $semester,
            ]);

            $this->flash('success', 'Exam created successfully.');
        } catch (\Throwable $e) {
            $this->flash('error', 'Failed to create exam: ' . $e->getMessage());
        }

        $redirect = '/academics/exams?main_group_id=' . $groupId;
        if ($academicYearId > 0) $redirect .= '&academic_year_id=' . $academicYearId;
        if ($semester) $redirect .= '&semester=' . urlencode($semester);
        return $this->redirect($redirect);
    }

    public function create(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        $mainGroups = $db->select(
            "SELECT id, name FROM main_groups WHERE tenant_id = ? AND is_active = 1 ORDER BY name ASC",
            [$tenantId]
        );

        $years = $db->select("SELECT * FROM academic_years WHERE tenant_id = ? ORDER BY year_name DESC", [$tenantId]);

        $selectedYearId = (int) $this->request->get('academic_year_id');
        if (!$selectedYearId && !empty($years)) {
            foreach ($years as $y) {
                if ($y['status'] === 'current') {
                    $selectedYearId = (int) $y['id'];
                    break;
                }
            }
            if (!$selectedYearId) {
                $selectedYearId = (int) $years[0]['id'];
            }
        }

        $selectedGroupId = (int) $this->request->get('main_group_id', $mainGroups[0]['id'] ?? 0);
        $selectedSemester = $this->request->get('semester', 'Semester 1');

        return $this->view('exams/create', compact('mainGroups', 'years', 'selectedYearId', 'selectedGroupId', 'selectedSemester'));
    }

    public function edit(string $id): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        $exam = $db->selectOne("SELECT * FROM exams WHERE id = ? AND tenant_id = ?", [(int)$id, $tenantId]);
        if (!$exam) {
            $this->flash('error', 'Exam not found.');
            return $this->redirect(url('academics/exams'));
        }

        $mainGroups = $db->select(
            "SELECT id, name FROM main_groups WHERE tenant_id = ? AND is_active = 1 ORDER BY name ASC",
            [$tenantId]
        );

        $years = $db->select("SELECT * FROM academic_years WHERE tenant_id = ? ORDER BY year_name DESC", [$tenantId]);

        return $this->view('exams/edit', compact('exam', 'mainGroups', 'years'));
    }

    public function update(string $id): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        $name     = trim($this->request->input('name', ''));
        $semester = $this->request->input('semester', 'Semester 1');
        $maxMarks = (float) $this->request->input('max_marks', 100);
        $groupId  = (int) $this->request->input('main_group_id', 0);
        $subjectIds = $this->request->input('subject_ids', []);
        $academicYearId = (int) $this->request->input('academic_year_id', 0);

        if (!$name || !$semester) {
            $this->flash('error', 'Exam name and semester are required.');
            return $this->redirect(url('academics/exams'));
        }

        $subjectIdsJson = !empty($subjectIds) ? json_encode(array_map('intval', $subjectIds)) : null;

        try {
            $db->update('exams', [
                'name'            => $name,
                'semester'        => $semester,
                'max_marks'       => $maxMarks,
                'main_group_id'   => $groupId > 0 ? $groupId : null,
                'academic_year_id'=> $academicYearId > 0 ? $academicYearId : null,
                'subject_ids'     => $subjectIdsJson,
            ], 'id = ? AND tenant_id = ?', [(int)$id, $tenantId]);

            $this->flash('success', 'Exam updated successfully.');
        } catch (\Throwable $e) {
            $this->flash('error', 'Failed to update exam: ' . $e->getMessage());
        }

        $redirect = '/academics/exams?main_group_id=' . $groupId;
        if ($academicYearId > 0) $redirect .= '&academic_year_id=' . $academicYearId;
        if ($semester) $redirect .= '&semester=' . urlencode($semester);
        return $this->redirect($redirect);
    }

    public function destroy(string $id): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();
        $groupId = (int) $this->request->input('main_group_id', 0);
        $academicYearId = (int) $this->request->input('academic_year_id', 0);
        $semester = $this->request->input('semester', '');

        $db->query("DELETE FROM exams WHERE id = ? AND tenant_id = ?", [(int)$id, $tenantId]);
        $this->flash('success', 'Exam deleted successfully.');

        $redirect = '/academics/exams?main_group_id=' . $groupId;
        if ($academicYearId > 0) $redirect .= '&academic_year_id=' . $academicYearId;
        if ($semester) $redirect .= '&semester=' . urlencode($semester);
        return $this->redirect($redirect);
    }

    private function resolveSubjectsForClass($db, $tenantId, $selectedClass, $selectedSection, $selectedExam = null): array
    {
        // If the exam has selected subjects, load them and resolve assessment_type from curriculum
        if ($selectedExam && !empty($selectedExam['subject_ids'])) {
            $allowedSubjectIds = json_decode($selectedExam['subject_ids'], true) ?: [];
            if (!empty($allowedSubjectIds)) {
                // Get the class's curriculum template
                $templateId = 0;
                if ($selectedClass) {
                    $classRow = $db->selectOne("
                        SELECT curriculum_template_id, academic_year_id, main_group_id 
                        FROM classes 
                        WHERE tenant_id = ? AND LOWER(name) = LOWER(?) AND COALESCE(section, '') = ? LIMIT 1
                    ", [$tenantId, $selectedClass, $selectedSection]);
                    if ($classRow) {
                        $templateId = $classRow['curriculum_template_id'] ?? 0;
                        if (!$templateId) {
                            $tempRow = $db->selectOne("
                                SELECT id FROM curriculum_templates 
                                WHERE academic_year_id = ? AND main_group_id = ? AND tenant_id = ? 
                                ORDER BY id DESC LIMIT 1
                            ", [$classRow['academic_year_id'], $classRow['main_group_id'], $tenantId]);
                            $templateId = $tempRow['id'] ?? 0;
                        }
                    }
                }

                // Try to get assessment_type from curriculum
                $assessmentMap = [];
                if ($templateId) {
                    $placeholders = implode(',', array_fill(0, count($allowedSubjectIds), '?'));
                    $curriculumRows = $db->select("
                        SELECT cs.subject_id, sec.assessment_type
                        FROM curriculum_subjects cs
                        JOIN curriculum_sections sec ON cs.curriculum_section_id = sec.id
                        WHERE sec.curriculum_template_id = ? AND cs.subject_id IN ($placeholders)
                    ", array_merge([$templateId], $allowedSubjectIds));
                    foreach ($curriculumRows as $cr) {
                        $assessmentMap[(int)$cr['subject_id']] = $cr['assessment_type'];
                    }
                }

                $placeholders = implode(',', array_fill(0, count($allowedSubjectIds), '?'));
                $subjects = $db->select(
                    "SELECT id, name, code, type as category FROM subjects WHERE tenant_id = ? AND id IN ($placeholders) ORDER BY name ASC",
                    array_merge([$tenantId], $allowedSubjectIds)
                );

                foreach ($subjects as &$s) {
                    $s['assessment_type'] = $assessmentMap[(int)$s['id']] ?? 'Grade';
                }
                unset($s);

                return $subjects;
            }
        }

        // Fallback: load subjects from curriculum template for the class
        $subjects = [];
        if ($selectedClass) {
            $classRow = $db->selectOne("
                SELECT id, curriculum_template_id, academic_year_id, main_group_id 
                FROM classes 
                WHERE tenant_id = ? AND LOWER(name) = LOWER(?) AND COALESCE(section, '') = ? LIMIT 1
            ", [$tenantId, $selectedClass, $selectedSection]);

            if ($classRow) {
                $templateId = $classRow['curriculum_template_id'];
                if (!$templateId) {
                    $tempRow = $db->selectOne("
                        SELECT id FROM curriculum_templates 
                        WHERE academic_year_id = ? AND main_group_id = ? AND tenant_id = ? 
                        ORDER BY id DESC LIMIT 1
                    ", [$classRow['academic_year_id'], $classRow['main_group_id'], $tenantId]);
                    $templateId = $tempRow['id'] ?? 0;
                }

                if ($templateId) {
                    $subjects = $db->select("
                        SELECT DISTINCT s.id, s.name, s.code, sec.section_name as category, sec.assessment_type
                        FROM curriculum_sections sec
                        JOIN curriculum_subjects cs ON cs.curriculum_section_id = sec.id
                        JOIN subjects s ON cs.subject_id = s.id
                        WHERE sec.curriculum_template_id = ?
                        ORDER BY sec.sort_order ASC, cs.sequence ASC
                    ", [$templateId]);
                }
            }
        }

        if (empty($subjects)) {
            $subjects = $db->select(
                "SELECT id, name, code, type as category, 'Grade' as assessment_type FROM subjects WHERE tenant_id = ? ORDER BY type ASC, name ASC",
                [$tenantId]
            );
        }

        return $subjects;
    }

    public function bulkEntry(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        $years = $db->select("SELECT * FROM academic_years WHERE tenant_id = ? ORDER BY year_name DESC", [$tenantId]);
        
        $selectedYearId = (int) $this->request->get('academic_year_id');
        if (!$selectedYearId && !empty($years)) {
            foreach ($years as $y) {
                if ($y['status'] === 'current') {
                    $selectedYearId = (int) $y['id'];
                    break;
                }
            }
            if (!$selectedYearId) {
                $selectedYearId = (int) $years[0]['id'];
            }
        }

        $mainGroups = $db->select(
            "SELECT id, name FROM main_groups WHERE tenant_id = ? AND is_active = 1 ORDER BY name ASC",
            [$tenantId]
        );

        $selectedMainGroupId = (int) $this->request->get('main_group_id');
        if (!$selectedMainGroupId && !empty($mainGroups)) {
            $selectedMainGroupId = (int) $mainGroups[0]['id'];
        }

        $classes = $db->select(
            "SELECT name as class, section FROM classes WHERE tenant_id = ? AND academic_year_id = ? AND main_group_id = ? ORDER BY name ASC, section ASC",
            [$tenantId, (int)$selectedYearId, $selectedMainGroupId]
        );

        $selectedClass   = $this->request->get('class');
        $selectedSection = $this->request->get('section');

        $classExists = false;
        if ($selectedClass !== null) {
            foreach ($classes as $c) {
                $cName = $c['class'] ?? $c['name'] ?? '';
                if ($cName === $selectedClass && ($c['section'] ?? '') === $selectedSection) {
                    $classExists = true;
                    break;
                }
            }
        }

        if (!$classExists && !empty($classes)) {
            $selectedClass = $classes[0]['class'] ?? $classes[0]['name'] ?? '';
            $selectedSection = $classes[0]['section'] ?? '';
        }

        $examsQuery = "SELECT * FROM exams WHERE tenant_id = ?";
        $examsParams = [$tenantId];
        if ($selectedYearId > 0) {
            $examsQuery .= " AND (academic_year_id = ? OR academic_year_id IS NULL)";
            $examsParams[] = $selectedYearId;
        }
        if ($selectedMainGroupId > 0) {
            $examsQuery .= " AND (main_group_id = ? OR main_group_id IS NULL)";
            $examsParams[] = $selectedMainGroupId;
        }
        $examsQuery .= " ORDER BY semester ASC, id ASC";
        $examsList = $db->select($examsQuery, $examsParams);

        $selectedExamName = $this->request->get('exam_name', $examsList[0]['name'] ?? 'Unit Test - 1');
        $selectedExam = null;
        foreach ($examsList as $ex) {
            if ($ex['name'] === $selectedExamName) {
                $selectedExam = $ex;
                break;
            }
        }
        if (!$selectedExam && !empty($examsList)) {
            $selectedExam = $examsList[0];
            $selectedExamName = $selectedExam['name'];
        }

        $resolvedSemester = $selectedExam ? $selectedExam['semester'] : 'Semester 1';
        $selectedTerm = ($resolvedSemester === 'Semester 2') ? 'Second Term Evaluation' : 'First Term Evaluation';

        $students = [];
        if ($selectedClass) {
            $students = $db->select(
                "SELECT id, first_name, last_name, admission_number FROM students 
                 WHERE tenant_id = ? AND LOWER(class) = LOWER(?) AND COALESCE(section, '') = ? AND deleted_at IS NULL AND admission_status = 'enrolled'
                 ORDER BY first_name ASC",
                [$tenantId, $selectedClass, $selectedSection]
            );
        }

        $subjects = $this->resolveSubjectsForClass($db, $tenantId, $selectedClass, $selectedSection, $selectedExam);

        $existingMarks = [];
        if (!empty($students) && !empty($subjects)) {
            $studentIds = array_column($students, 'id');
            $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
            
            $rows = $db->select(
                "SELECT student_id, subject, exam_name, marks_obtained, max_marks, grade, remarks 
                 FROM exam_results 
                 WHERE (exam_name = ? OR exam_name = ?) AND student_id IN ($placeholders)",
                array_merge([$selectedExamName, $selectedTerm], $studentIds)
            );
            foreach ($rows as $r) {
                $subKey = strtolower(trim($r['subject']));
                if ($r['exam_name'] === $selectedExamName) {
                    $existingMarks[$r['student_id']][$subKey]['marks_obtained'] = $r['marks_obtained'];
                    $existingMarks[$r['student_id']][$subKey]['remarks'] = $r['remarks'];
                }
                if ($r['exam_name'] === $selectedTerm) {
                    $existingMarks[$r['student_id']][$subKey]['grade'] = $r['grade'];
                    $existingMarks[$r['student_id']][$subKey]['term_remarks'] = $r['remarks'];
                }
            }
        }

        return $this->view('exams/bulk_entry', compact(
            'classes', 'selectedClass', 'selectedSection', 'examsList', 'selectedExam', 'selectedExamName', 'selectedTerm', 'students', 'subjects', 'existingMarks', 'years', 'selectedYearId', 'mainGroups', 'selectedMainGroupId'
        ));
    }

    public function marksheet(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        $years = $db->select("SELECT * FROM academic_years WHERE tenant_id = ? ORDER BY year_name DESC", [$tenantId]);
        
        $selectedYearId = (int) $this->request->get('academic_year_id');
        if (!$selectedYearId && !empty($years)) {
            foreach ($years as $y) {
                if ($y['status'] === 'current') {
                    $selectedYearId = (int) $y['id'];
                    break;
                }
            }
            if (!$selectedYearId) {
                $selectedYearId = (int) $years[0]['id'];
            }
        }

        $mainGroups = $db->select(
            "SELECT id, name FROM main_groups WHERE tenant_id = ? AND is_active = 1 ORDER BY name ASC",
            [$tenantId]
        );

        $selectedMainGroupId = (int) $this->request->get('main_group_id');
        if (!$selectedMainGroupId && !empty($mainGroups)) {
            $selectedMainGroupId = (int) $mainGroups[0]['id'];
        }

        $classes = $db->select(
            "SELECT name as class, section FROM classes WHERE tenant_id = ? AND academic_year_id = ? AND main_group_id = ? ORDER BY name ASC, section ASC",
            [$tenantId, (int)$selectedYearId, $selectedMainGroupId]
        );

        $selectedClass   = $this->request->get('class');
        $selectedSection = $this->request->get('section');

        $classExists = false;
        if ($selectedClass !== null) {
            foreach ($classes as $c) {
                $cName = $c['class'] ?? $c['name'] ?? '';
                if ($cName === $selectedClass && ($c['section'] ?? '') === $selectedSection) {
                    $classExists = true;
                    break;
                }
            }
        }

        if (!$classExists && !empty($classes)) {
            $selectedClass = $classes[0]['class'] ?? $classes[0]['name'] ?? '';
            $selectedSection = $classes[0]['section'] ?? '';
        }

        $students = [];
        if ($selectedClass) {
            $students = $db->select(
                "SELECT id, first_name, last_name, admission_number FROM students 
                 WHERE tenant_id = ? AND LOWER(class) = LOWER(?) AND COALESCE(section, '') = ? AND deleted_at IS NULL AND admission_status = 'enrolled'
                 ORDER BY first_name ASC",
                [$tenantId, $selectedClass, $selectedSection]
            );
        }

        $examsList = $db->select(
            "SELECT * FROM exams WHERE tenant_id = ? AND main_group_id = ? ORDER BY FIELD(semester, 'Semester 1', 'Semester 2'), id ASC",
            [$tenantId, $selectedMainGroupId]
        );

        $semester1Exams = array_values(array_filter($examsList, fn($e) => $e['semester'] === 'Semester 1'));
        $semester2Exams = array_values(array_filter($examsList, fn($e) => $e['semester'] === 'Semester 2'));

        $subjects = $this->resolveSubjectsForClass($db, $tenantId, $selectedClass, $selectedSection);

        $marksData = [];
        if (!empty($students) && !empty($subjects)) {
            $studentIds = array_column($students, 'id');
            $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
            
            $allExamNames = array_column($examsList, 'name');
            if (!empty($allExamNames)) {
                $examPlaceholders = implode(',', array_fill(0, count($allExamNames), '?'));

                $rows = $db->select(
                    "SELECT student_id, subject, exam_name, marks_obtained, max_marks, grade 
                     FROM exam_results 
                     WHERE exam_name IN ($examPlaceholders) AND student_id IN ($placeholders)",
                    array_merge($allExamNames, $studentIds)
                );

                foreach ($rows as $r) {
                    $studentId = (int)$r['student_id'];
                    $subName = strtolower(trim($r['subject']));
                    $examName = $r['exam_name'];
                    
                    if (!isset($marksData[$studentId][$subName])) {
                        $marksData[$studentId][$subName] = [];
                    }
                    $marksData[$studentId][$subName][$examName] = [
                        'marks' => $r['marks_obtained'],
                        'max'   => $r['max_marks'],
                        'grade' => $r['grade'],
                    ];
                }
            }
        }

        $result = [];
        foreach ($students as $stu) {
            $studentId = (int)$stu['id'];
            $subjectsResult = [];

            foreach ($subjects as $subj) {
                $subName = strtolower(trim($subj['name']));
                $subjectMarks = $marksData[$studentId][$subName] ?? [];

                $sem1Ut = null;
                $sem1UtMax = 0;
                foreach ($semester1Exams as $ex) {
                    if (stripos($ex['name'], 'unit test') !== false) {
                        $sem1Ut = $subjectMarks[$ex['name']]['marks'] ?? null;
                        $sem1UtMax = (float)$ex['max_marks'];
                        break;
                    }
                }

                $sem1Exam = null;
                $sem1ExamMax = 0;
                foreach ($semester1Exams as $ex) {
                    if (stripos($ex['name'], 'unit test') === false && stripos($ex['name'], 'project') === false && stripos($ex['name'], 'practical') === false) {
                        $sem1Exam = $subjectMarks[$ex['name']]['marks'] ?? null;
                        $sem1ExamMax = (float)$ex['max_marks'];
                        break;
                    }
                }

                $sem1Proj = null;
                $sem1ProjMax = 0;
                foreach ($semester1Exams as $ex) {
                    if (stripos($ex['name'], 'project') !== false || stripos($ex['name'], 'practical') !== false) {
                        $sem1Proj = $subjectMarks[$ex['name']]['marks'] ?? null;
                        $sem1ProjMax = (float)$ex['max_marks'];
                        break;
                    }
                }

                $sem2Ut = null;
                $sem2UtMax = 0;
                foreach ($semester2Exams as $ex) {
                    if (stripos($ex['name'], 'unit test') !== false) {
                        $sem2Ut = $subjectMarks[$ex['name']]['marks'] ?? null;
                        $sem2UtMax = (float)$ex['max_marks'];
                        break;
                    }
                }

                $sem2Exam = null;
                $sem2ExamMax = 0;
                foreach ($semester2Exams as $ex) {
                    if (stripos($ex['name'], 'unit test') === false && stripos($ex['name'], 'project') === false && stripos($ex['name'], 'practical') === false) {
                        $sem2Exam = $subjectMarks[$ex['name']]['marks'] ?? null;
                        $sem2ExamMax = (float)$ex['max_marks'];
                        break;
                    }
                }

                $sem2Proj = null;
                $sem2ProjMax = 0;
                foreach ($semester2Exams as $ex) {
                    if (stripos($ex['name'], 'project') !== false || stripos($ex['name'], 'practical') !== false) {
                        $sem2Proj = $subjectMarks[$ex['name']]['marks'] ?? null;
                        $sem2ProjMax = (float)$ex['max_marks'];
                        break;
                    }
                }

                $sem1Total = 0;
                $sem1Max = 0;
                if ($sem1Ut !== null && $sem1Ut !== '') { $sem1Total += (float)$sem1Ut; $sem1Max += $sem1UtMax; }
                if ($sem1Exam !== null && $sem1Exam !== '') { $sem1Total += (float)$sem1Exam; $sem1Max += $sem1ExamMax; }
                if ($sem1Proj !== null && $sem1Proj !== '') { $sem1Total += (float)$sem1Proj; $sem1Max += $sem1ProjMax; }

                $sem2Total = 0;
                $sem2Max = 0;
                if ($sem2Ut !== null && $sem2Ut !== '') { $sem2Total += (float)$sem2Ut; $sem2Max += $sem2UtMax; }
                if ($sem2Exam !== null && $sem2Exam !== '') { $sem2Total += (float)$sem2Exam; $sem2Max += $sem2ExamMax; }
                if ($sem2Proj !== null && $sem2Proj !== '') { $sem2Total += (float)$sem2Proj; $sem2Max += $sem2ProjMax; }

                $sem1Pct = $sem1Max > 0 ? round(($sem1Total / $sem1Max) * 100, 2) : 0;
                $sem2Pct = $sem2Max > 0 ? round(($sem2Total / $sem2Max) * 100, 2) : 0;

                $sem1Grade = $sem1Max > 0 ? $this->calculateGrade($sem1Pct) : null;
                $sem2Grade = $sem2Max > 0 ? $this->calculateGrade($sem2Pct) : null;

                $subjectsResult[] = [
                    'name'      => $subj['name'],
                    'sem1_ut'   => $sem1Ut,
                    'sem1_ut_max'=> $sem1UtMax,
                    'sem1_exam' => $sem1Exam,
                    'sem1_exam_max'=> $sem1ExamMax,
                    'sem1_proj' => $sem1Proj,
                    'sem1_proj_max'=> $sem1ProjMax,
                    'sem1_total'=> $sem1Max > 0 ? $sem1Total : null,
                    'sem1_max'  => $sem1Max > 0 ? $sem1Max : null,
                    'sem1_pct'  => $sem1Max > 0 ? $sem1Pct : null,
                    'sem1_grade'=> $sem1Grade,
                    'sem2_ut'   => $sem2Ut,
                    'sem2_ut_max'=> $sem2UtMax,
                    'sem2_exam' => $sem2Exam,
                    'sem2_exam_max'=> $sem2ExamMax,
                    'sem2_proj' => $sem2Proj,
                    'sem2_proj_max'=> $sem2ProjMax,
                    'sem2_total'=> $sem2Max > 0 ? $sem2Total : null,
                    'sem2_max'  => $sem2Max > 0 ? $sem2Max : null,
                    'sem2_pct'  => $sem2Max > 0 ? $sem2Pct : null,
                    'sem2_grade'=> $sem2Grade,
                ];
            }

            $result[] = [
                'student'  => $stu,
                'subjects' => $subjectsResult,
            ];
        }

        return $this->view('exams/marksheet', compact(
            'classes', 'selectedClass', 'selectedSection', 'years', 'selectedYearId', 'result', 'semester1Exams', 'semester2Exams', 'mainGroups', 'selectedMainGroupId'
        ));
    }

    private function calculateGrade(float $pct): string
    {
        if ($pct >= 90) return 'A+';
        if ($pct >= 80) return 'A';
        if ($pct >= 70) return 'B';
        if ($pct >= 60) return 'C+';
        if ($pct >= 41) return 'C';
        if ($pct >= 33) return 'D';
        return 'F';
    }

    public static function resolveSemesterFromTerm(string $term): string
    {
        $lower = strtolower($term);
        if (str_contains($lower, 'first') || str_contains($lower, '1') || str_contains($lower, 'sem 1') || str_contains($lower, 'semester 1')) {
            return 'Semester 1';
        }
        if (str_contains($lower, 'second') || str_contains($lower, '2') || str_contains($lower, 'sem 2') || str_contains($lower, 'semester 2')) {
            return 'Semester 2';
        }
        return $term;
    }

    public function bulkSave(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();
        $class   = $this->request->input('class', '');
        $section = $this->request->input('section', '');
        $selectedExamName = $this->request->input('exam_name', '');
        $marks   = $this->request->input('marks', []);
        $remarks = $this->request->input('remarks', []);

        $examRow = $db->selectOne(
            "SELECT * FROM exams WHERE name = ? AND tenant_id = ? LIMIT 1",
            [$selectedExamName, $tenantId]
        );
        $resolvedSemester = $examRow ? $examRow['semester'] : 'Semester 1';
        $selectedTerm = ($resolvedSemester === 'Semester 2') ? 'Second Term Evaluation' : 'First Term Evaluation';

        $activeYearRow = $db->selectOne("SELECT year_name FROM academic_years WHERE status = 'current' AND tenant_id = ? LIMIT 1", [$tenantId]);
        $academicYear = $activeYearRow['year_name'] ?? '2025-26';

        if (is_year_locked($academicYear)) {
            $this->flash('error', 'This academic year is locked. Marks entry is frozen.');
            return $this->redirect(url("academics/assessments?class=" . urlencode($class) . "&section=" . urlencode($section) . "&exam_name=" . urlencode($selectedExamName)));
        }

        $semExams = $db->select(
            "SELECT name, max_marks FROM exams WHERE tenant_id = ? AND semester = ? ORDER BY id ASC",
            [$tenantId, $resolvedSemester]
        );

        $savedCount = 0;
        foreach ($marks as $studentId => $subjMarks) {
            if (empty($subjMarks)) continue;

            $student = $db->selectOne(
                "SELECT tenant_id, school_id, branch_id FROM students WHERE id = ?",
                [(int)$studentId]
            );
            if (!$student) continue;

            $stuRemark = $remarks[$studentId] ?? '';

            $reportCardRow = $db->selectOne(
                "SELECT id, academic_profile FROM student_report_cards WHERE student_id = ? AND academic_year = ? AND semester = ?",
                [(int)$studentId, $academicYear, $resolvedSemester]
            );

            $academicProfile = [];
            if ($reportCardRow) {
                $academicProfile = json_decode($reportCardRow['academic_profile'], true) ?: [];
            }

            foreach ($subjMarks as $subjId => $val) {
                $subjRow = $db->selectOne("
                    SELECT s.name, sec.assessment_type 
                    FROM subjects s
                    JOIN curriculum_subjects cs ON cs.subject_id = s.id
                    JOIN curriculum_sections sec ON cs.curriculum_section_id = sec.id
                    WHERE s.id = ? AND s.tenant_id = ? LIMIT 1
                ", [(int)$subjId, $tenantId]);
                if (!$subjRow) continue;

                $subjName = $subjRow['name'];
                $assessmentType = $subjRow['assessment_type'] ?? 'Grade';

                if ($assessmentType === 'Marks') {
                    if ($val === '' || $val === null) continue;
                    $obt = (float)$val;
                    $max = $examRow ? (float)$examRow['max_marks'] : 100.0;

                    $exists = $db->selectOne(
                        "SELECT id FROM exam_results WHERE student_id = ? AND exam_name = ? AND subject = ?",
                        [(int)$studentId, $selectedExamName, $subjName]
                    );
                    if ($exists) {
                        $db->update('exam_results', [
                            'marks_obtained' => $obt,
                            'max_marks'      => $max,
                            'grade'          => '',
                            'remarks'        => $stuRemark,
                            'date_published' => date('Y-m-d'),
                        ], 'id = ?', [$exists['id']]);
                    } else {
                        $db->insert('exam_results', [
                            'tenant_id'      => $student['tenant_id'],
                            'school_id'      => $student['school_id'],
                            'branch_id'      => $student['branch_id'],
                            'student_id'     => (int)$studentId,
                            'exam_name'      => $selectedExamName,
                            'subject'        => $subjName,
                            'marks_obtained' => $obt,
                            'max_marks'      => $max,
                            'grade'          => '',
                            'remarks'        => $stuRemark,
                            'date_published' => date('Y-m-d'),
                        ]);
                    }

                    $totalObtained = 0.0;
                    $totalMax = 0.0;
                    $components = [];

                    foreach ($semExams as $se) {
                        $exName = $se['name'];
                        if ($exName === $selectedExamName) {
                            $obtVal = $val;
                        } else {
                            $scoreRow = $db->selectOne("
                                SELECT marks_obtained FROM exam_results 
                                WHERE student_id = ? AND exam_name = ? AND subject = ?
                            ", [(int)$studentId, $exName, $subjName]);
                            $obtVal = $scoreRow ? $scoreRow['marks_obtained'] : null;
                        }

                        if ($obtVal !== null && $obtVal !== '') {
                            $obtFloat = (float)$obtVal;
                            $components[$exName] = $obtFloat;
                            $totalObtained += $obtFloat;
                            $totalMax += (float)$se['max_marks'];
                        }
                    }

                    $pct = $totalMax > 0 ? ($totalObtained / $totalMax) * 100 : 0.0;
                    $grade = $this->calculateGrade($pct);

                    $academicProfile[$subjId] = [
                        'marks' => $components,
                        'total' => $totalObtained,
                        'pct'   => round($pct, 2),
                        'grade' => $grade
                    ];
                } else {
                    $grade = trim((string)$val);
                    if ($grade !== '') {
                        $exists = $db->selectOne(
                            "SELECT id FROM exam_results WHERE student_id = ? AND exam_name = ? AND subject = ?",
                            [(int)$studentId, $selectedTerm, $subjName]
                        );
                        if ($exists) {
                            $db->update('exam_results', [
                                'marks_obtained' => 0.0,
                                'max_marks'      => 0.0,
                                'grade'          => $grade,
                                'remarks'        => $stuRemark,
                                'date_published' => date('Y-m-d'),
                            ], 'id = ?', [$exists['id']]);
                        } else {
                            $db->insert('exam_results', [
                                'tenant_id'      => $student['tenant_id'],
                                'school_id'      => $student['school_id'],
                                'branch_id'      => $student['branch_id'],
                                'student_id'     => (int)$studentId,
                                'exam_name'      => $selectedTerm,
                                'subject'        => $subjName,
                                'marks_obtained' => 0.0,
                                'max_marks'      => 0.0,
                                'grade'          => $grade,
                                'remarks'        => $stuRemark,
                                'date_published' => date('Y-m-d'),
                            ]);
                        }
                        $academicProfile[$subjId] = $grade;
                    }
                }
            }

            $academicJson = json_encode($academicProfile, JSON_UNESCAPED_UNICODE);
            if ($reportCardRow) {
                $db->query(
                    "UPDATE student_report_cards SET academic_profile = ? WHERE id = ?",
                    [$academicJson, $reportCardRow['id']]
                );
            } else {
                $db->insert('student_report_cards', [
                    'tenant_id' => $student['tenant_id'],
                    'school_id' => $student['school_id'],
                    'branch_id' => $student['branch_id'],
                    'student_id' => (int)$studentId,
                    'academic_year' => $academicYear,
                    'semester' => $resolvedSemester,
                    'routine_profile' => '{}',
                    'learning_skills' => '{}',
                    'academic_profile' => $academicJson,
                    'cocurriculum_profile' => '{}',
                    'attendance_profile' => '{}',
                    'feedback_text' => $stuRemark,
                    'authorized_by' => '[]',
                ]);
            }
            $savedCount++;
        }

        ActivityLog::log('bulk_exam_marks_saved', $this->authId(), [
            'class' => $class,
            'term'  => $selectedTerm,
            'count' => $savedCount
        ]);

        $this->flash('success', "Successfully saved bulk evaluation marks and synchronized report cards.");
        return $this->redirect(url("academics/assessments?class=" . urlencode($class) . "&section=" . urlencode($section) . "&exam_name=" . urlencode($selectedExamName)));
    }
}
