<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\GradeScale;
use App\Models\MarkEntry;
use App\Models\PromotionResult;
use App\Models\PromotionSetting;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PromotionService
{
    // ── Grading thresholds (matching MarkEntry::calcTotals & GradeScale seeds) ───

    protected const GRADE_MAP = [
        ['min' => 80, 'grade' => 'A',  'point' => 4.00],
        ['min' => 60, 'grade' => 'B',  'point' => 3.00],
        ['min' => 50, 'grade' => 'C',  'point' => 2.00],
        ['min' => 40, 'grade' => 'D',  'point' => 1.00],
        ['min' => 0.01,'grade' => 'F',  'point' => 0.00],
        ['min' => 0,  'grade' => 'I',  'point' => 0.00],
    ];

    // ── Public API ──────────────────────────────────────────────────────────────

    /**
     * Calculate a student's full performance profile from MarkEntry records.
     *
     * Returns an array with keys:
     *   average_score, overall_percentage, overall_grade, grade_point_average,
     *   total_subjects, subjects_passed, subjects_failed,
     *   attendance_percentage, subject_details (per-subject breakdown),
     *   failure_reasons (array), conduct_average
     *
     * @param  int  $studentId
     * @param  int  $academicYearId
     * @param  int  $termId
     * @return array
     */
    public function calculateStudentPerformance(int $studentId, int $academicYearId, int $termId): array
    {
        $markEntries = MarkEntry::where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->where('term_id', $termId)
            ->get();

        // No marks recorded – return zeroes
        if ($markEntries->isEmpty()) {
            return $this->emptyPerformance();
        }

        $totalSubjects    = $markEntries->count();
        $totalScore       = 0;
        $totalGradePoints = 0;
        $subjectsPassed   = 0;
        $subjectsFailed   = 0;
        $conductSum       = 0;
        $conductCount     = 0;
        $subjectDetails   = [];
        $failureReasons   = [];

        foreach ($markEntries as $entry) {
            $score       = (float) ($entry->grand_total ?? 0);
            $gradeInfo   = $this->resolveGrade($score);
            $isPassing   = $this->isPassingScore($score);

            $totalScore       += $score;
            $totalGradePoints += $gradeInfo['point'];

            if ($isPassing) {
                $subjectsPassed++;
            } else {
                $subjectsFailed++;
            }

            // Collect conduct scores where available
            if ($entry->conduct !== null && $entry->conduct !== '') {
                $conductSum   += (float) $entry->conduct;
                $conductCount++;
            }

            $subjectDetails[] = [
                'subject_id'   => $entry->subject_id,
                'subject_name' => $entry->subject?->name ?? 'Unknown',
                'grand_total'  => $score,
                'ca_total'     => (float) ($entry->ca_total ?? 0),
                'exam_total'   => (float) ($entry->exam_total ?? 0),
                'grade'        => $gradeInfo['grade'],
                'grade_point'  => $gradeInfo['point'],
                'is_passing'   => $isPassing,
                'conduct'      => $entry->conduct,
            ];

            if (! $isPassing) {
                $failureReasons[] = [
                    'subject_id'   => $entry->subject_id,
                    'subject_name' => $entry->subject?->name ?? 'Unknown',
                    'score'        => $score,
                    'grade'        => $gradeInfo['grade'],
                    'reason'       => "Score below pass mark ({$score} – {$gradeInfo['grade']})",
                ];
            }
        }

        $averageScore      = round($totalScore / $totalSubjects, 2);
        $gradePointAverage = round($totalGradePoints / $totalSubjects, 2);
        $overallGrade      = $this->resolveGrade($averageScore)['grade'];
        $conductAverage    = $conductCount > 0
            ? round($conductSum / $conductCount, 2)
            : null;

        $attendancePercentage = $this->getAttendancePercentage($studentId, $termId);

        return [
            'average_score'         => $averageScore,
            'overall_percentage'    => $averageScore,          // same scale (out of 100)
            'overall_grade'         => $overallGrade,
            'grade_point_average'   => $gradePointAverage,
            'total_subjects'        => $totalSubjects,
            'subjects_passed'       => $subjectsPassed,
            'subjects_failed'       => $subjectsFailed,
            'attendance_percentage' => $attendancePercentage,
            'conduct_average'       => $conductAverage,
            'subject_details'       => $subjectDetails,
            'failure_reasons'       => $failureReasons,
        ];
    }

    /**
     * Bulk-process promotion for every active student in a class.
     *
     * @param  int  $classId
     * @param  int  $academicYearId
     * @param  int  $termId
     * @param  int  $processedByUserId
     * @return array{processed: int, promoted: int, detained: int, conditional: int, errors: array}
     */
    public function processClassPromotion(int $classId, int $academicYearId, int $termId, int $processedByUserId): array
    {
        $students = Student::where('class_id', $classId)
            ->where('status', 'active')
            ->get();

        $results = [
            'processed'   => 0,
            'promoted'    => 0,
            'detained'    => 0,
            'conditional' => 0,
            'errors'      => [],
        ];

        $nextClass = $this->getNextClass($classId);
        $toClassId = $nextClass?->id ?? $classId; // stay in same class if no next

        foreach ($students as $student) {
            try {
                $promotionResult = $this->processStudentPromotion(
                    $student->id,
                    $academicYearId,
                    $termId,
                    $toClassId,
                    $processedByUserId,
                );

                $results['processed']++;

                if ($promotionResult->status === 'promoted') {
                    $results['promoted']++;
                } elseif ($promotionResult->status === 'detained') {
                    $results['detained']++;
                } elseif ($promotionResult->status === 'conditional') {
                    $results['conditional']++;
                }
            } catch (\Throwable $e) {
                $results['errors'][] = [
                    'student_id' => $student->id,
                    'student'    => $student->full_name,
                    'error'      => $e->getMessage(),
                ];
                Log::error('Promotion processing failed for student', [
                    'student_id' => $student->id,
                    'class_id'   => $classId,
                    'error'      => $e->getMessage(),
                ]);
            }
        }

        // Compute class ranks for all processed results in this class/term
        $this->computeClassRanks($classId, $academicYearId, $termId);

        return $results;
    }

    /**
     * Process promotion for a single student, with optional manual override.
     *
     * @param  int       $studentId
     * @param  int       $academicYearId
     * @param  int       $termId
     * @param  int|null  $toClassId      Target class (null = auto-detect via getNextClass)
     * @param  int       $processedByUserId
     * @param  string|null $remarks
     * @param  string|null $overrideStatus  If provided, forces the status (promoted/detained/conditional)
     * @return PromotionResult
     */
    public function processStudentPromotion(
        int $studentId,
        int $academicYearId,
        int $termId,
        ?int $toClassId = null,
        int $processedByUserId = 0,
        ?string $remarks = null,
        ?string $overrideStatus = null,
    ): PromotionResult {
        $student = Student::findOrFail($studentId);
        $setting = PromotionSetting::getActive();

        // Capture original class BEFORE any move
        $fromClassId = $student->class_id;
        $fromClass = ClassRoom::find($fromClassId);
        $currentGrade = (int) ($fromClass?->numeric_name ?? 0);

        // Calculate performance (needed for both graduation and promotion paths)
        $performance = $this->calculateStudentPerformance($studentId, $academicYearId, $termId);

        // ── Grade 12 students → GRADUATE instead of promoting ──
        if ($currentGrade >= 12) {
            $student->status = 'graduated';
            $student->save();

            // ── Auto-generate transcript for grades 9-12 ──
            $this->generateGraduationTranscript($student, $academicYearId, $termId);

            $promotionResult = PromotionResult::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'from_class_id' => $fromClassId,
                    'academic_year_id' => $academicYearId,
                    'term_id' => $termId,
                ],
                [
                    'status' => 'promoted',
                    'to_class_id' => $fromClassId,
                    'average_score' => $performance['average'] ?? null,
                    'subjects_passed' => $performance['subjects_passed'] ?? 0,
                    'subjects_failed' => $performance['subjects_failed'] ?? 0,
                    'failure_reasons' => json_encode([['reason' => 'Graduated (Grade 12 completed)']]),
                    'is_final' => true,
                    'processed_at' => now(),
                    'processed_by' => auth()->id(),
                ]
            );

            return $promotionResult;
        }

        // Resolve target class
        if ($toClassId === null) {
            $nextClass = $this->getNextClass($fromClassId);
            $toClassId = $nextClass?->id ?? $fromClassId;
        }

        // Determine promotion status
        if ($overrideStatus !== null && in_array($overrideStatus, ['promoted', 'detained', 'conditional'])) {
            $status = $overrideStatus;
            $allReasons = $performance['failure_reasons'];
            if ($overrideStatus === 'promoted') {
                array_unshift($allReasons, ['reason' => 'Manual override: forced promotion']);
            } elseif ($overrideStatus === 'detained') {
                array_unshift($allReasons, ['reason' => 'Manual override: forced detention']);
            } elseif ($overrideStatus === 'conditional') {
                array_unshift($allReasons, ['reason' => 'Manual override: forced conditional promotion']);
            }
        } else {
            [$status, $allReasons] = $this->determinePromotionStatus($performance, $setting);
        }

        // If detained, the student stays in the same class
        $effectiveToClassId = $status === 'detained' ? $fromClassId : $toClassId;

        // If promoted, actually move the student
        if ($status === 'promoted' || $status === 'conditional') {
            $this->moveStudentToClass($student, $effectiveToClassId);
        }

        // Build final failure-reasons list, appending any non-academic reasons
        $finalReasons = $this->buildFinalReasons($performance, $setting, $status);

        // Upsert the promotion result
        $promotionResult = PromotionResult::updateOrCreate(
            [
                'student_id'       => $studentId,
                'from_class_id'    => $fromClassId,
                'academic_year_id' => $academicYearId,
                'term_id'          => $termId,
            ],
            [
                'to_class_id'           => $effectiveToClassId,
                'status'                => $status,
                'average_score'         => $performance['average_score'],
                'overall_percentage'    => $performance['overall_percentage'],
                'overall_grade'         => $performance['overall_grade'],
                'grade_point_average'   => $performance['grade_point_average'],
                'total_subjects'        => $performance['total_subjects'],
                'subjects_passed'       => $performance['subjects_passed'],
                'subjects_failed'       => $performance['subjects_failed'],
                'attendance_percentage' => $performance['attendance_percentage'],
                'failure_reasons'       => $finalReasons,
                'processed_by'          => $processedByUserId,
                'processed_at'          => now(),
                'is_final'              => true,
                'remarks'               => $remarks,
            ],
        );

        return $promotionResult;
    }

    /**
     * Readmit a student who previously left the school.
     *
     * @param  int       $studentId
     * @param  int       $newClassId
     * @param  int       $newSectionId
     * @param  int       $academicYearId
     * @param  int       $processedByUserId
     * @param  string|null $remarks
     * @return Student
     * @throws \Exception
     */
    public function readmitStudent(
        int $studentId,
        int $newClassId,
        int $newSectionId,
        int $academicYearId,
        int $processedByUserId = 0,
        ?string $remarks = null,
    ): Student {
        $student = Student::findOrFail($studentId);

        if (! $student->canBeReadmitted()) {
            throw new \Exception("Student ID {$studentId} cannot be readmitted – current status is '{$student->status}'.");
        }

        return DB::transaction(function () use ($student, $newClassId, $newSectionId, $academicYearId, $processedByUserId, $remarks) {
            // Store previous placement for history
            $student->previous_class_id   = $student->class_id;
            $student->previous_section_id = $student->section_id;

            // Move to new class/section
            $student->class_id          = $newClassId;
            $student->section_id        = $newSectionId;
            $student->academic_year_id  = $academicYearId;
            $student->status            = 'active';
            $student->is_readmitted     = true;
            $student->readmission_count = ($student->readmission_count ?? 0) + 1;
            $student->leave_date        = null;
            $student->leave_reason      = null;
            $student->save();

            // Create a promotion result record documenting the readmission
            PromotionResult::create([
                'student_id'            => $student->id,
                'from_class_id'         => $student->previous_class_id,
                'to_class_id'           => $newClassId,
                'academic_year_id'      => $academicYearId,
                'term_id'               => null,
                'status'                => 'promoted',
                'average_score'         => null,
                'overall_percentage'    => null,
                'overall_grade'         => null,
                'grade_point_average'   => null,
                'total_subjects'        => null,
                'subjects_passed'       => null,
                'subjects_failed'       => null,
                'class_rank'            => null,
                'total_students'        => null,
                'attendance_percentage' => null,
                'failure_reasons'       => [['reason' => 'Readmission to school']],
                'processed_by'          => $processedByUserId,
                'processed_at'          => now(),
                'is_final'              => true,
                'remarks'               => $remarks ?? 'Student readmitted',
            ]);

            Log::info('Student readmitted', [
                'student_id'     => $student->id,
                'new_class_id'   => $newClassId,
                'new_section_id' => $newSectionId,
                'processed_by'   => $processedByUserId,
            ]);

            return $student->fresh();
        });
    }

    /**
     * Calculate attendance percentage for a student in a given term.
     *
     * Counts present, late, and excused as "attended". Absent is not attended.
     *
     * @param  int  $studentId
     * @param  int  $termId
     * @return float  Percentage rounded to 2 decimal places (0.0 – 100.0)
     */
    public function getAttendancePercentage(int $studentId, int $termId): float
    {
        $totalRecords = Attendance::where('student_id', $studentId)
            ->where('term_id', $termId)
            ->count();

        if ($totalRecords === 0) {
            return 100.0; // No attendance records = assume full attendance
        }

        $attendedRecords = Attendance::where('student_id', $studentId)
            ->where('term_id', $termId)
            ->whereIn('status', ['present', 'late', 'excused'])
            ->count();

        return round(($attendedRecords / $totalRecords) * 100, 2);
    }

    /**
     * Get the next class for promotion based on numeric_name ordering.
     *
     * @param  int  $classId  Current class ID
     * @return ClassRoom|null
     */
    public function getNextClass(int $classId): ?ClassRoom
    {
        $currentClass = ClassRoom::find($classId);

        if (! $currentClass) {
            return null;
        }

        $currentGrade = (int) ($currentClass->numeric_name ?? 0);

        // ── Grade 12 → no next class (student should be graduated) ──
        if ($currentGrade >= 12) {
            return null;
        }

        // Find the class with the next higher numeric_name using INTEGER comparison
        // (string comparison breaks: "9" > "10" as strings)
        return ClassRoom::where('branch_id', $currentClass->branch_id)
            ->whereRaw('CAST(numeric_name AS UNSIGNED) > ?', [$currentGrade])
            ->orderByRaw('CAST(numeric_name AS UNSIGNED) ASC')
            ->first();
    }

    /**
     * Get detailed failure reasons for a student's subjects.
     *
     * @param  int  $studentId
     * @param  int  $academicYearId
     * @param  int  $termId
     * @return array  List of failed subjects with scores and grades
     */
    public function getSubjectFailureReasons(int $studentId, int $academicYearId, int $termId): array
    {
        $setting = PromotionSetting::getActive();
        $passMark = $setting?->minimum_subject_pass_mark ?? 50;

        $markEntries = MarkEntry::where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->where('term_id', $termId)
            ->get();

        $failures = [];

        foreach ($markEntries as $entry) {
            $score = (float) ($entry->grand_total ?? 0);
            $gradeInfo = $this->resolveGrade($score);

            if ($score < $passMark) {
                $failures[] = [
                    'subject_id'     => $entry->subject_id,
                    'subject_name'   => $entry->subject?->name ?? 'Unknown',
                    'score'          => $score,
                    'grade'          => $gradeInfo['grade'],
                    'pass_mark'      => $passMark,
                    'shortfall'      => round($passMark - $score, 2),
                    'ca_total'       => (float) ($entry->ca_total ?? 0),
                    'exam_total'     => (float) ($entry->exam_total ?? 0),
                    'conduct'        => $entry->conduct,
                    'reason'         => "Score {$score} is below the pass mark of {$passMark} (grade: {$gradeInfo['grade']})",
                ];
            }
        }

        return $failures;
    }

    /**
     * Bulk-process promotion for a class with flexible modes.
     *
     * Modes:
     *   - 'all':              Process all students; apply the calculated result (current behaviour).
     *                          If $forcePromote is true, override all students to 'promoted'.
     *   - 'specific_result':  Only process students whose CALCULATED result matches $specificResult.
     *                          Students whose calculated result doesn't match are left unchanged.
     *   - 'satisfy_criteria': Process all students, but only those who satisfy the promotion
     *                          criteria (as defined in PromotionSetting) get promoted; others keep
     *                          their calculated status (detained / conditional).
     *
     * @param  int       $classId
     * @param  int       $academicYearId
     * @param  int       $termId
     * @param  int       $processedByUserId
     * @param  string    $promotionMode    One of: 'all', 'specific_result', 'satisfy_criteria'
     * @param  string|null $specificResult  When mode is 'specific_result': 'promoted'|'detained'|'conditional'
     * @param  bool      $forcePromote     When true with 'all' mode, override all to promoted
     * @return array{processed: int, promoted: int, detained: int, conditional: int, skipped: int, errors: array}
     */
    public function processBulkPromotion(
        int $classId,
        int $academicYearId,
        int $termId,
        int $processedByUserId,
        string $promotionMode = 'all',
        ?string $specificResult = null,
        bool $forcePromote = false,
    ): array {
        $students = Student::where('class_id', $classId)
            ->where('status', 'active')
            ->get();

        $results = [
            'processed'   => 0,
            'promoted'    => 0,
            'detained'    => 0,
            'conditional' => 0,
            'skipped'     => 0,
            'errors'      => [],
        ];

        $nextClass = $this->getNextClass($classId);
        $toClassId = $nextClass?->id ?? $classId;
        $setting   = PromotionSetting::getActive();

        foreach ($students as $student) {
            try {
                // Always calculate performance first
                $performance = $this->calculateStudentPerformance(
                    $student->id,
                    $academicYearId,
                    $termId,
                );

                // Determine the calculated status (without override)
                [$calculatedStatus, $calculatedReasons] = $this->determinePromotionStatus($performance, $setting);

                switch ($promotionMode) {
                    case 'all':
                        // If force_promote, override to promoted; otherwise use calculated status
                        $effectiveStatus = $forcePromote ? 'promoted' : $calculatedStatus;
                        $overrideStatus  = $forcePromote ? 'promoted' : null;
                        break;

                    case 'specific_result':
                        // Only process students whose calculated result matches the specific_result
                        if ($calculatedStatus !== $specificResult) {
                            $results['skipped']++;
                            continue 2; // skip to next student in the foreach
                        }
                        $effectiveStatus = $calculatedStatus;
                        $overrideStatus  = null;
                        break;

                    case 'satisfy_criteria':
                        // Process all students, but only those who satisfy criteria get promoted;
                        // others keep their calculated status (detained/conditional).
                        // "Satisfy criteria" means the calculated status IS promoted.
                        $effectiveStatus = $calculatedStatus;
                        $overrideStatus  = null;
                        break;

                    default:
                        $effectiveStatus = $calculatedStatus;
                        $overrideStatus  = null;
                }

                // Process the student with the determined status
                $promotionResult = $this->processStudentPromotion(
                    $student->id,
                    $academicYearId,
                    $termId,
                    $toClassId,
                    $processedByUserId,
                    null, // remarks
                    $overrideStatus,
                );

                $results['processed']++;

                if ($promotionResult->status === 'promoted') {
                    $results['promoted']++;
                } elseif ($promotionResult->status === 'detained') {
                    $results['detained']++;
                } elseif ($promotionResult->status === 'conditional') {
                    $results['conditional']++;
                }
            } catch (\Throwable $e) {
                $results['errors'][] = [
                    'student_id' => $student->id,
                    'student'    => $student->full_name,
                    'error'      => $e->getMessage(),
                ];
                Log::error('Bulk promotion processing failed for student', [
                    'student_id'    => $student->id,
                    'class_id'      => $classId,
                    'promotion_mode' => $promotionMode,
                    'error'         => $e->getMessage(),
                ]);
            }
        }

        // Compute class ranks for all processed results in this class/term
        $this->computeClassRanks($classId, $academicYearId, $termId);

        return $results;
    }

    // ── Protected helpers ───────────────────────────────────────────────────────

    /**
     * Determine the promotion status based on performance and settings.
     *
     * @param  array               $performance  From calculateStudentPerformance()
     * @param  PromotionSetting|null $setting
     * @return array [string $status, array $reasons]
     */
    protected function determinePromotionStatus(array $performance, ?PromotionSetting $setting): array
    {
        $reasons   = [];
        $isDetained = false;

        // Defaults when no active setting
        $minAverage          = $setting?->minimum_average_for_promotion ?? 50;
        $maxFailures         = $setting?->maximum_subjects_to_fail ?? 0;
        $minPassMark         = $setting?->minimum_subject_pass_mark ?? 50;
        $considerAttendance  = $setting?->consider_attendance ?? false;
        $minAttendance       = $setting?->minimum_attendance_percentage ?? 75;
        $considerBehavior    = $setting?->consider_behavior ?? false;
        $considerConduct     = $setting?->consider_conduct ?? false;
        $minConduct          = $setting?->minimum_conduct_score ?? 3;
        $autoPromoteAllPass  = $setting?->auto_promote_if_pass_all ?? true;
        $allowConditional    = $setting?->allow_conditional_promotion ?? true;
        $condMinAverage      = $setting?->conditional_promotion_min_average ?? 40;
        $condMaxFailures     = $setting?->conditional_promotion_max_failures ?? 2;

        $average       = $performance['average_score'];
        $failedCount   = $performance['subjects_failed'];
        $attendance    = $performance['attendance_percentage'];
        $conductAvg    = $performance['conduct_average'];

        // ── Auto-promote if passing all subjects ────────────────────────
        if ($autoPromoteAllPass && $failedCount === 0 && $average >= $minAverage) {
            // Still check attendance / conduct requirements
            if ($considerAttendance && $attendance < $minAttendance) {
                $reasons[] = [
                    'type'   => 'attendance',
                    'reason' => "Attendance {$attendance}% is below minimum required {$minAttendance}%",
                ];
                $isDetained = true;
            }

            if ($considerConduct && $conductAvg !== null && $conductAvg < $minConduct) {
                $reasons[] = [
                    'type'   => 'conduct',
                    'reason' => "Conduct average {$conductAvg} is below minimum required {$minConduct}",
                ];
                $isDetained = true;
            }

            if (! $isDetained) {
                return ['promoted', $performance['failure_reasons']];
            }
        }

        // ── Check average score ─────────────────────────────────────────
        if ($average < $minAverage) {
            $reasons[] = [
                'type'   => 'average',
                'reason' => "Average score {$average} is below minimum required {$minAverage}",
            ];

            if ($failedCount > 0) {
                $reasons[] = [
                    'type'   => 'failures',
                    'reason' => "Failed {$failedCount} subject(s)",
                ];
            }

            // Check conditional promotion eligibility
            if ($allowConditional
                && $average >= $condMinAverage
                && $failedCount <= $condMaxFailures
            ) {
                // Check attendance/conduct for conditional too
                $conditionalBlocked = false;

                if ($considerAttendance && $attendance < $minAttendance) {
                    $reasons[] = [
                        'type'   => 'attendance',
                        'reason' => "Attendance {$attendance}% is below minimum required {$minAttendance}%",
                    ];
                    $conditionalBlocked = true;
                }

                if ($considerConduct && $conductAvg !== null && $conductAvg < $minConduct) {
                    $reasons[] = [
                        'type'   => 'conduct',
                        'reason' => "Conduct average {$conductAvg} is below minimum required {$minConduct}",
                    ];
                    $conditionalBlocked = true;
                }

                if (! $conditionalBlocked) {
                    $reasons[] = [
                        'type'   => 'conditional',
                        'reason' => 'Eligible for conditional promotion',
                    ];
                    return ['conditional', array_merge($performance['failure_reasons'], $reasons)];
                }
            }

            return ['detained', array_merge($performance['failure_reasons'], $reasons)];
        }

        // ── Average is OK but check failure count ───────────────────────
        if ($failedCount > $maxFailures) {
            $reasons[] = [
                'type'   => 'failures',
                'reason' => "Failed {$failedCount} subject(s) – maximum allowed is {$maxFailures}",
            ];

            // Conditional if allowed
            if ($allowConditional && $failedCount <= $condMaxFailures) {
                $reasons[] = [
                    'type'   => 'conditional',
                    'reason' => 'Eligible for conditional promotion due to acceptable failure count',
                ];
                return ['conditional', array_merge($performance['failure_reasons'], $reasons)];
            }

            return ['detained', array_merge($performance['failure_reasons'], $reasons)];
        }

        // ── Check attendance ────────────────────────────────────────────
        if ($considerAttendance && $attendance < $minAttendance) {
            $reasons[] = [
                'type'   => 'attendance',
                'reason' => "Attendance {$attendance}% is below minimum required {$minAttendance}%",
            ];
            $isDetained = true;
        }

        // ── Check conduct ───────────────────────────────────────────────
        if ($considerConduct && $conductAvg !== null && $conductAvg < $minConduct) {
            $reasons[] = [
                'type'   => 'conduct',
                'reason' => "Conduct average {$conductAvg} is below minimum required {$minConduct}",
            ];
            $isDetained = true;
        }

        if ($isDetained) {
            return ['detained', array_merge($performance['failure_reasons'], $reasons)];
        }

        // ── All checks passed ───────────────────────────────────────────
        return ['promoted', $performance['failure_reasons']];
    }

    /**
     * Build the final failure_reasons JSON array, combining academic and
     * non-academic (attendance, conduct, behavior) reasons.
     */
    protected function buildFinalReasons(array $performance, ?PromotionSetting $setting, string $status): array
    {
        $reasons = $performance['failure_reasons'];

        $considerAttendance = $setting?->consider_attendance ?? false;
        $minAttendance      = $setting?->minimum_attendance_percentage ?? 75;
        $considerConduct    = $setting?->consider_conduct ?? false;
        $minConduct         = $setting?->minimum_conduct_score ?? 3;
        $considerBehavior   = $setting?->consider_behavior ?? false;

        // Attendance reason
        if ($considerAttendance && $performance['attendance_percentage'] < $minAttendance) {
            $reasons[] = [
                'type'   => 'attendance',
                'reason' => "Attendance {$performance['attendance_percentage']}% is below required {$minAttendance}%",
            ];
        }

        // Conduct reason
        if ($considerConduct && $performance['conduct_average'] !== null && $performance['conduct_average'] < $minConduct) {
            $reasons[] = [
                'type'   => 'conduct',
                'reason' => "Conduct average {$performance['conduct_average']} is below required {$minConduct}",
            ];
        }

        // Behavior note (informational)
        if ($considerBehavior && $status === 'detained') {
            $reasons[] = [
                'type'   => 'behavior',
                'reason' => 'Behavior assessment considered in detention decision',
            ];
        }

        return $reasons;
    }

    /**
     * Actually move a student to a new class (and optionally section).
     */
    protected function moveStudentToClass(Student $student, int $toClassId, ?int $toSectionId = null): void
    {
        $student->class_id = $toClassId;

        if ($toSectionId !== null) {
            $student->section_id = $toSectionId;
        } else {
            // Auto-assign to the first section of the target class
            $firstSection = Section::where('class_id', $toClassId)->orderBy('name')->first();
            if ($firstSection) {
                $student->section_id = $firstSection->id;
            }
        }

        $student->save();
    }

    /**
     * Compute and update class ranks for all promotion results
     * in a given class, academic year, and term.
     */
    protected function computeClassRanks(int $classId, int $academicYearId, int $termId): void
    {
        $results = PromotionResult::where('from_class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->where('term_id', $termId)
            ->whereNotNull('average_score')
            ->orderByDesc('average_score')
            ->get();

        $totalStudents = $results->count();
        $rank          = 0;
        $prevScore     = null;
        $skip          = 1;

        foreach ($results as $result) {
            $rank++;

            // Tie-handling: same score = same rank
            if ($prevScore !== null && $result->average_score == $prevScore) {
                $result->class_rank = $rank - $skip;
                $skip++;
            } else {
                $result->class_rank = $rank;
                $skip = 1;
            }

            $result->total_students = $totalStudents;
            $result->saveQuietly();

            $prevScore = $result->average_score;
        }
    }

    /**
     * Resolve a numeric score into grade letter and grade point.
     *
     * Tries the GradeScale model first; falls back to the static map.
     */
    protected function resolveGrade(float $score): array
    {
        // Try database GradeScale first
        $scale = GradeScale::getGrade($score);
        if ($scale) {
            return [
                'grade' => $scale->grade,
                'point' => (float) $scale->grade_point,
            ];
        }

        // Fallback to static map
        foreach (self::GRADE_MAP as $tier) {
            if ($score >= $tier['min']) {
                return [
                    'grade' => $tier['grade'],
                    'point' => $tier['point'],
                ];
            }
        }

        return ['grade' => 'F', 'point' => 0.00];
    }

    /**
     * Determine whether a score counts as "passing".
     *
     * Uses the active PromotionSetting's minimum_subject_pass_mark if available,
     * otherwise consults the GradeScale, and finally defaults to 50.
     */
    protected function isPassingScore(float $score): bool
    {
        $setting   = PromotionSetting::getActive();
        $passMark  = $setting?->minimum_subject_pass_mark;

        if ($passMark !== null) {
            return $score >= $passMark;
        }

        // Check GradeScale
        $scale = GradeScale::getGrade($score);
        if ($scale) {
            return $scale->is_passing;
        }

        // Default: C and above pass (>= 50)
        return $score >= 50;
    }

    /**
     * Return an empty performance array when no marks exist.
     */
    protected function emptyPerformance(): array
    {
        return [
            'average_score'         => 0,
            'overall_percentage'    => 0,
            'overall_grade'         => 'F',
            'grade_point_average'   => 0,
            'total_subjects'        => 0,
            'subjects_passed'       => 0,
            'subjects_failed'       => 0,
            'attendance_percentage' => 0,
            'conduct_average'       => null,
            'subject_details'       => [],
            'failure_reasons'       => [['reason' => 'No mark entries found for the given period']],
        ];
    }

    /**
     * Auto-generate a transcript certificate for a graduating student.
     * Covers grades 9-12 (all marks available in the system).
     */
    protected function generateGraduationTranscript(Student $student, int $academicYearId, int $termId): void
    {
        try {
            // Check if a transcript already exists for this student
            $existing = \App\Models\Certificate::where('student_id', $student->id)
                ->where('type', 'transcript')
                ->exists();

            if ($existing) {
                return; // Don't create duplicate transcripts
            }

            // Generate certificate number
            $prefix = 'TRA';
            $year = date('Y');
            $lastCert = \App\Models\Certificate::where('certificate_number', 'LIKE', "{$prefix}-{$year}-%")
                ->orderByDesc('id')
                ->first();
            $nextNum = 1;
            if ($lastCert) {
                $parts = explode('-', $lastCert->certificate_number);
                $lastNum = (int) end($parts);
                $nextNum = $lastNum + 1;
            }
            $certificateNumber = $prefix . '-' . $year . '-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

            while (\App\Models\Certificate::where('certificate_number', $certificateNumber)->exists()) {
                $nextNum++;
                $certificateNumber = $prefix . '-' . $year . '-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
            }

            // Check if the student has any marks in grades 9-12
            $hasMarks = MarkEntry::where('student_id', $student->id)
                ->whereHas('classRoom', function ($q) {
                    $q->whereRaw('CAST(numeric_name AS UNSIGNED) >= 9')
                      ->whereRaw('CAST(numeric_name AS UNSIGNED) <= 12');
                })
                ->exists();

            if (!$hasMarks) {
                \Log::info('Graduation transcript skipped — no marks found for grades 9-12', [
                    'student_id' => $student->id,
                    'student_name' => $student->full_name,
                ]);
                return;
            }

            \App\Models\Certificate::create([
                'student_id' => $student->id,
                'type' => 'transcript',
                'certificate_number' => $certificateNumber,
                'issue_date' => now()->format('Y-m-d'),
                'content' => 'Auto-generated graduation transcript (Grades 9-12) for ' . $student->full_name,
                'template' => 'transcript',
            ]);

            \Log::info('Graduation transcript auto-generated', [
                'student_id' => $student->id,
                'student_name' => $student->full_name,
                'certificate_number' => $certificateNumber,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to auto-generate graduation transcript: ' . $e->getMessage(), [
                'student_id' => $student->id,
            ]);
        }
    }
}
