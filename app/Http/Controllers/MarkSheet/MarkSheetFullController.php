<?php

namespace App\Http\Controllers\MarkSheet;

use App\Http\Controllers\Controller;
use App\Models\MarkEntry;
use App\Models\ClassRoom;
use App\Models\Section;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;

class MarkSheetFullController extends Controller
{
    /**
     * Resolve the logged-in user's Teacher record.
     * Tries user_id FK first, then falls back to email match.
     * Returns null for non-teacher users or if no Teacher record found.
     */
    private function getTeacherForUser()
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'teacher') return null;

        // Try user_id FK first
        $teacher = Teacher::where('user_id', $user->id)->first();
        if (!$teacher) {
            // Fall back to email match (legacy)
            $teacher = Teacher::where('email', $user->email)->first();
        }
        return $teacher;
    }

    /**
     * Show the filter form for the full mark sheet (Term1 + Term2 + Annual).
     */
    public function index()
    {
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();

        $isTeacher = false;
        $teacher = $this->getTeacherForUser();

        if ($teacher) {
            $isTeacher = true;

            // Lock AY to active one for teachers
            $activeAy = AcademicYear::where('is_current', true)->first();
            if ($activeAy) {
                $academicYears = collect([$activeAy]); // Only show active AY for teachers
            }

            // Only show classes where teacher is homeroom teacher
            $classes = $teacher->classRooms()->orderBy('name')->get();
        } else {
            $classes = ClassRoom::orderBy('name')->get();
        }

        return view('admin.mark-sheet.full', compact('academicYears', 'classes', 'isTeacher'));
    }

    /**
     * Generate the full mark sheet: students × subjects with Term1, Term2, Annual columns.
     * Subjects are displayed as 90°-rotated column headers.
     * Includes: average row, term-specific ranks, annual = avg(T1+T2).
     */
    public function generate(Request $r)
    {
        $r->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_id'         => 'required|exists:classes,id',
            'section_id'       => 'nullable|exists:sections,id',
        ]);

        // ── Authorization check for teachers ──
        $teacher = $this->getTeacherForUser();
        if ($teacher) {
            $isHomeroom = $teacher->classRooms()->where('id', $r->class_id)->exists();
            if (!$isHomeroom) {
                abort(403, 'You are not authorized to generate full mark sheets for this class. Only homeroom teachers can access this feature.');
            }
        }

        $academicYearId = $r->academic_year_id;
        $classId        = $r->class_id;
        $sectionId      = $r->filled('section_id') ? $r->section_id : null;

        // Get all terms for the academic year
        $terms = Term::where('academic_year_id', $academicYearId)
            ->orderBy('term_number')
            ->get();

        $term1 = $terms->first();
        $term2 = $terms->count() >= 2 ? $terms->skip(1)->first() : null;

        // Query all students in the class/section
        $studentQuery = Student::where('class_id', $classId)
            ->where('status', 'active');
        if ($sectionId) {
            $studentQuery->where('section_id', $sectionId);
        }
        $students = $studentQuery
            ->selectRaw("*, CAST(roll_number AS UNSIGNED) as rn_sort")
            ->orderByRaw('rn_sort ASC')
            ->orderBy('first_name')
            ->get();

        // Query all mark entries for this academic year and class
        $marksQuery = MarkEntry::with(['subject', 'term'])
            ->where('academic_year_id', $academicYearId)
            ->where('class_id', $classId);
        if ($sectionId) {
            $marksQuery->where('section_id', $sectionId);
        }
        $allMarks = $marksQuery->get();

        // Get unique subjects from marks
        $subjects = $allMarks->pluck('subject')->filter()->unique('id')->sortBy(function($s) { return [$s->priority ?? 0, $s->name]; })->values();

        // If no marks yet, get subjects from teacher assignments
        if ($subjects->isEmpty()) {
            $subjects = \App\Models\Subject::whereHas('teacherAssignments', function ($q) use ($classId) {
                $q->where('class_id', $classId);
            })->orderBy('priority')->orderBy('name')->get();
        }

        // Build the data structure: [studentId][termId][subjectId] = mark data
        $markData = [];
        foreach ($allMarks as $entry) {
            $markData[$entry->student_id][$entry->term_id][$entry->subject_id] = [
                'grand_total' => $entry->grand_total,
                'grade'       => $entry->grade,
                'ca_total'    => $entry->ca_total,
                'exam_total'  => $entry->exam_total,
            ];
        }

        // Build roster rows with Term1, Term2, and Annual calculations
        $roster = [];
        foreach ($students as $student) {
            $row = [
                'student'      => $student,
                'term1'        => [],
                'term2'        => [],
                'annual'       => [],
                'term1_total'  => 0,
                'term2_total'  => 0,
                'annual_total' => 0,
                'term1_count'  => 0,
                'term2_count'  => 0,
                'annual_count' => 0,
                'term1_avg'    => 0,
                'term2_avg'    => 0,
                'annual_avg'   => 0,
            ];

            foreach ($subjects as $subj) {
                // Term 1
                $t1 = $markData[$student->id][$term1->id][$subj->id] ?? null;
                $row['term1'][$subj->id] = $t1;
                if ($t1 && $t1['grand_total'] !== null) {
                    $row['term1_total'] += floatval($t1['grand_total']);
                    $row['term1_count']++;
                }

                // Term 2
                $t2 = null;
                if ($term2) {
                    $t2 = $markData[$student->id][$term2->id][$subj->id] ?? null;
                }
                $row['term2'][$subj->id] = $t2;
                if ($t2 && $t2['grand_total'] !== null) {
                    $row['term2_total'] += floatval($t2['grand_total']);
                    $row['term2_count']++;
                }

                // Annual = average of Term1 and Term2 (if both exist)
                if ($t1 && $t2 && $t1['grand_total'] !== null && $t2['grand_total'] !== null) {
                    $annualMark = round((floatval($t1['grand_total']) + floatval($t2['grand_total'])) / 2, 1);
                    $row['annual'][$subj->id] = [
                        'grand_total' => $annualMark,
                        'grade'       => $this->calcGrade($annualMark),
                    ];
                    $row['annual_total'] += $annualMark;
                    $row['annual_count']++;
                } elseif ($t1 && $t1['grand_total'] !== null && (!$t2 || $t2['grand_total'] === null)) {
                    // Only Term1 exists, use it as annual
                    $row['annual'][$subj->id] = [
                        'grand_total' => $t1['grand_total'],
                        'grade'       => $t1['grade'],
                    ];
                    $row['annual_total'] += floatval($t1['grand_total']);
                    $row['annual_count']++;
                } elseif ($t2 && $t2['grand_total'] !== null && (!$t1 || $t1['grand_total'] === null)) {
                    // Only Term2 exists
                    $row['annual'][$subj->id] = [
                        'grand_total' => $t2['grand_total'],
                        'grade'       => $t2['grade'],
                    ];
                    $row['annual_total'] += floatval($t2['grand_total']);
                    $row['annual_count']++;
                } else {
                    $row['annual'][$subj->id] = null;
                }
            }

            // Calculate per-student averages
            $row['term1_avg'] = $row['term1_count'] > 0
                ? round($row['term1_total'] / $row['term1_count'], 1) : 0;
            $row['term2_avg'] = $row['term2_count'] > 0
                ? round($row['term2_total'] / $row['term2_count'], 1) : 0;
            $row['annual_avg'] = $row['annual_count'] > 0
                ? round($row['annual_total'] / $row['annual_count'], 1) : 0;

            $roster[] = $row;
        }

        // Calculate Term1 ranks
        $this->assignRanks($roster, 'term1_total', 'term1_rank');
        // Calculate Term2 ranks
        $this->assignRanks($roster, 'term2_total', 'term2_rank');
        // Calculate Annual ranks
        $this->assignRanks($roster, 'annual_total', 'annual_rank');

        // Calculate class averages for each subject per term
        $averages = [
            'term1'  => [],
            'term2'  => [],
            'annual' => [],
            'term1_total_avg'  => 0,
            'term2_total_avg'  => 0,
            'annual_total_avg' => 0,
        ];

        $studentCount = count($roster);
        if ($studentCount > 0) {
            foreach ($subjects as $subj) {
                // Term1 subject average
                $t1Sum = 0; $t1Cnt = 0;
                foreach ($roster as $row) {
                    $t1 = $row['term1'][$subj->id] ?? null;
                    if ($t1 && $t1['grand_total'] !== null) {
                        $t1Sum += floatval($t1['grand_total']);
                        $t1Cnt++;
                    }
                }
                $averages['term1'][$subj->id] = $t1Cnt > 0 ? round($t1Sum / $t1Cnt, 1) : null;

                // Term2 subject average
                $t2Sum = 0; $t2Cnt = 0;
                foreach ($roster as $row) {
                    $t2 = $row['term2'][$subj->id] ?? null;
                    if ($t2 && $t2['grand_total'] !== null) {
                        $t2Sum += floatval($t2['grand_total']);
                        $t2Cnt++;
                    }
                }
                $averages['term2'][$subj->id] = $t2Cnt > 0 ? round($t2Sum / $t2Cnt, 1) : null;

                // Annual subject average
                $aSum = 0; $aCnt = 0;
                foreach ($roster as $row) {
                    $ann = $row['annual'][$subj->id] ?? null;
                    if ($ann && $ann['grand_total'] !== null) {
                        $aSum += floatval($ann['grand_total']);
                        $aCnt++;
                    }
                }
                $averages['annual'][$subj->id] = $aCnt > 0 ? round($aSum / $aCnt, 1) : null;
            }

            // Overall total averages
            $t1TotalSum = array_sum(array_column($roster, 'term1_total'));
            $t2TotalSum = array_sum(array_column($roster, 'term2_total'));
            $aTotalSum  = array_sum(array_column($roster, 'annual_total'));

            $averages['term1_total_avg']  = round($t1TotalSum / $studentCount, 1);
            $averages['term2_total_avg']  = round($t2TotalSum / $studentCount, 1);
            $averages['annual_total_avg'] = round($aTotalSum / $studentCount, 1);
        }

        // Lookup reference models
        $class        = ClassRoom::find($classId);
        $section      = $sectionId ? Section::find($sectionId) : null;
        $academicYear = AcademicYear::find($academicYearId);

        $academicYears = AcademicYear::orderBy('id', 'desc')->get();

        // Re-apply teacher class filtering for the form dropdown
        if ($teacher) {
            // Lock AY to active one for teachers
            $activeAy = AcademicYear::where('is_current', true)->first();
            if ($activeAy) {
                $academicYears = collect([$activeAy]);
            }
            $classes = $teacher->classRooms()->orderBy('name')->get();
        } else {
            $classes = ClassRoom::orderBy('name')->get();
        }

        return view('admin.mark-sheet.full', compact(
            'roster',
            'subjects',
            'class',
            'section',
            'academicYear',
            'term1',
            'term2',
            'academicYears',
            'classes',
            'averages',
            'isTeacher'
        ));
    }

    /**
     * JSON API — return sections for a given class_id.
     */
    public function getSections(Request $r)
    {
        $teacher = $this->getTeacherForUser();

        $query = Section::where('class_id', $r->class_id);

        if ($teacher) {
            // Only return sections where teacher is homeroom
            $query->where('teacher_id', $teacher->id);
        }

        $sections = $query->orderBy('name')->get(['id', 'name']);

        return response()->json($sections);
    }

    /**
     * Assign ranks to roster based on a given total field.
     * Handles ties (same total = same rank, next rank skips).
     */
    private function assignRanks(array &$roster, string $totalField, string $rankField): void
    {
        $ranked = collect($roster)->sortByDesc($totalField)->values();
        $rank = 1;
        $rankMap = [];

        foreach ($ranked as $i => $r) {
            if ($i > 0 && $r[$totalField] < $ranked[$i - 1][$totalField]) {
                $rank = $i + 1;
            }
            $rankMap[$r['student']->id] = $rank;
        }

        foreach ($roster as &$row) {
            $row[$rankField] = $rankMap[$row['student']->id] ?? '-';
        }
        unset($row);
    }

    /**
     * Calculate grade from mark.
     */
    private function calcGrade(float $mark): string
    {
        if ($mark >= 90) return 'A+';
        if ($mark >= 80) return 'A';
        if ($mark >= 75) return 'A-';
        if ($mark >= 70) return 'B+';
        if ($mark >= 65) return 'B';
        if ($mark >= 60) return 'B-';
        if ($mark >= 55) return 'C+';
        if ($mark >= 50) return 'C';
        if ($mark >= 45) return 'C-';
        if ($mark >= 40) return 'D';
        return 'F';
    }
}
