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
use App\Models\TeacherAssignment;
use App\Models\Branch;
use App\Models\Setting;
use Illuminate\Http\Request;

class MarkRosterController extends Controller
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
     * Show the filter form for generating a mark roster.
     */
    public function index(Request $request)
    {
        $branchScope = $request->attributes->get('branch_scope');
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $terms         = Term::orderBy('id', 'desc')->get();

        $isTeacher = false;
        $teacher = $this->getTeacherForUser();

        if ($teacher) {
            $isTeacher = true;

            // Lock AY and Term to active ones for teachers
            $activeAy = AcademicYear::where('is_current', true)->first();
            if ($activeAy) {
                $academicYears = collect([$activeAy]); // Only show active AY for teachers
                $activeTerm = Term::where('academic_year_id', $activeAy->id)->where('is_active', true)->first();
                if ($activeTerm) {
                    $terms = collect([$activeTerm]); // Only show active term for teachers
                } else {
                    $terms = Term::where('academic_year_id', $activeAy->id)->orderBy('id', 'asc')->get();
                }
            }

            // Show classes from their assignments AND homeroom classes
            $assignmentClassIds = $teacher->assignments()->pluck('class_id')->unique();
            $homeroomClassIds = $teacher->classRooms()->pluck('id');
            $classIds = $assignmentClassIds->merge($homeroomClassIds)->unique();

            $classes = ClassRoom::whereIn('id', $classIds)->orderBy('numeric_name')->orderBy('name')->get();
        } else {
            $classes = ClassRoom::when($branchScope, fn($q) => $q->where('branch_id', $branchScope))->orderBy('numeric_name')->orderBy('name')->get();
        }

        return view('admin.mark-roster.index', compact('academicYears', 'terms', 'classes', 'isTeacher'));
    }

    /**
     * Generate the mark roster — a SEPARATE TABLE per subject showing
     * every CA and Exam field as a column, grouped under
     * "Continuous Assessment" and "Exam" header rows.
     */
    public function generate(Request $r)
    {
        $branchScope = $r->attributes->get('branch_scope');
        // If GET request with no filters, redirect to index
        if ($r->isMethod('GET') && !$r->filled('academic_year_id')) {
            return redirect()->route('admin.mark-roster.index');
        }

        $r->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id'          => 'required|exists:terms,id',
            'class_id'         => 'required|exists:classes,id',
            'section_id'       => 'nullable|exists:sections,id',
        ]);

        // ── Authorization and subject scoping for teachers ──
        $teacher = $this->getTeacherForUser();
        $isTeacher = (bool) $teacher;
        $isHomeroomForClass = false;
        $teacherSubjectIds = null; // null = show all subjects

        if ($teacher) {
            $isHomeroomForClass = $teacher->classRooms()->where('id', $r->class_id)->exists();

            // Check if teacher has any assignments in this class at all
            $hasAssignmentsInClass = TeacherAssignment::where('teacher_id', $teacher->id)
                ->where('class_id', $r->class_id)
                ->exists();

            // Also check if they're homeroom for a section in this class
            $isHomeroomForSection = false;
            if ($r->filled('section_id')) {
                $isHomeroomForSection = Section::where('id', $r->section_id)
                    ->where('class_id', $r->class_id)
                    ->where('teacher_id', $teacher->id)
                    ->exists();
            } else {
                // No section selected — check if homeroom for any section in this class
                $isHomeroomForSection = Section::where('class_id', $r->class_id)
                    ->where('teacher_id', $teacher->id)
                    ->exists();
            }

            if (!$isHomeroomForClass && !$hasAssignmentsInClass && !$isHomeroomForSection) {
                return redirect()->route('admin.mark-roster.index')
                    ->with('error', 'You are not authorized to view the mark roster for this class. Only teachers with assignments or homeroom duties in this class can access it.');
            }

            // If NOT homeroom (class or section), only show their assigned subjects
            if (!$isHomeroomForClass && !$isHomeroomForSection) {
                $teacherSubjectIds = TeacherAssignment::where('teacher_id', $teacher->id)
                    ->where('class_id', $r->class_id)
                    ->pluck('subject_id')
                    ->unique()
                    ->values()
                    ->toArray();
            }
            // If homeroom, $teacherSubjectIds stays null = show all subjects
        }

        // ── Build the query ──────────────────────────────────────────
        $query = MarkEntry::with(['student', 'subject'])
            ->where('academic_year_id', $r->academic_year_id)
            ->where('term_id', $r->term_id)
            ->where('class_id', $r->class_id);

        if ($r->filled('section_id')) {
            $query->where('section_id', $r->section_id);
        }

        $marks = $query->orderBy('student_id')->orderByRaw('(SELECT priority FROM subjects WHERE subjects.id = mark_entries.subject_id) ASC')->orderBy('subject_id')->get();

        // ── Unique subjects (preserve the order they appear in) ──────
        $subjects = $marks->pluck('subject')->filter()->unique('id')->sortBy(function($s) { return [$s->priority ?? 0, $s->name]; })->values();

        // If no marks yet, get subjects from teacher assignments
        if ($subjects->isEmpty()) {
            $subjects = \App\Models\Subject::whereHas('teacherAssignments', function ($q) use ($r) {
                $q->where('class_id', $r->class_id);
            })->orderBy('priority')->orderBy('name')->get();
        }

        // ── Filter subjects for non-homeroom teachers ──
        if ($teacherSubjectIds !== null) {
            $subjects = $subjects->filter(function($s) use ($teacherSubjectIds) {
                return in_array($s->id, $teacherSubjectIds);
            })->values();
        }

        // ── Get all students in the class/section ────────────────────
        $studentQuery = Student::where('class_id', $r->class_id)
            ->where('status', 'active');
        if ($r->filled('section_id')) {
            $studentQuery->where('section_id', $r->section_id);
        }
        $students = $studentQuery
            ->selectRaw("*, CAST(roll_number AS UNSIGNED) as rn_sort")
            ->orderByRaw('rn_sort ASC')
            ->orderBy('full_name')
            ->get();

        // ── Build detailed mark data per subject ─────────────────────
        // Structure: [subjectId][studentId] = full mark entry row
        $markData = [];
        foreach ($marks as $entry) {
            $markData[$entry->subject_id][$entry->student_id] = $entry;
        }

        // ── Build subject rosters with all CA/Exam detail ────────────
        $caFields = ['ca1','ca2','ca3','ca4','ca5','ca6','ca7','ca8','ca9','ca10','conduct','handwriting','creativity'];
        $examFields = ['test1','test2','mid_term','final_exam'];
        $subjectRosters = [];
        foreach ($subjects as $subj) {
            $rows = [];
            foreach ($students as $i => $student) {
                $entry = $markData[$subj->id][$student->id] ?? null;
                $row = [
                    'serial'   => $i + 1,
                    'student'  => $student,
                    'ca1'        => $entry->ca1 ?? null,
                    'ca2'        => $entry->ca2 ?? null,
                    'ca3'        => $entry->ca3 ?? null,
                    'ca4'        => $entry->ca4 ?? null,
                    'ca5'        => $entry->ca5 ?? null,
                    'ca6'        => $entry->ca6 ?? null,
                    'ca7'        => $entry->ca7 ?? null,
                    'ca8'        => $entry->ca8 ?? null,
                    'ca9'        => $entry->ca9 ?? null,
                    'ca10'       => $entry->ca10 ?? null,
                    'conduct'    => $entry->conduct ?? null,
                    'handwriting'=> $entry->handwriting ?? null,
                    'creativity' => $entry->creativity ?? null,
                    'test1'      => $entry->test1 ?? null,
                    'test2'      => $entry->test2 ?? null,
                    'mid_term'   => $entry->mid_term ?? null,
                    'final_exam' => $entry->final_exam ?? null,
                ];
                // Recalculate totals from raw fields (don't trust stored values)
                if ($entry) {
                    $caRaw = 0;
                    foreach ($caFields as $f) { $caRaw += floatval($entry->$f ?? 0); }
                    $examRaw = 0;
                    foreach ($examFields as $f) { $examRaw += floatval($entry->$f ?? 0); }
                    $row['ca_total'] = round(($caRaw / 70) * 30, 2);
                    $row['exam_total'] = min($examRaw, 70);
                    $row['grand_total'] = round($row['ca_total'] + $row['exam_total'], 2);
                    // Calculate grade from recalculated grand_total
                    $gt = $row['grand_total'];
                    if ($gt <= 0) $row['grade'] = 'I';
                    elseif ($gt >= 80) $row['grade'] = 'A';
                    elseif ($gt >= 60) $row['grade'] = 'B';
                    elseif ($gt >= 50) $row['grade'] = 'C';
                    elseif ($gt >= 40) $row['grade'] = 'D';
                    else $row['grade'] = 'F';
                } else {
                    $row['ca_total'] = null;
                    $row['exam_total'] = null;
                    $row['grand_total'] = null;
                    $row['grade'] = null;
                }
                $rows[] = $row;
            }

            // Compute column averages
            $colSums = [];
            $colCounts = [];
            $avgFields = ['ca1','ca2','ca3','ca4','ca5','ca6','ca7','ca8','ca9','ca10',
                          'conduct','handwriting','creativity','ca_total',
                          'test1','test2','mid_term','final_exam','exam_total','grand_total'];
            foreach ($avgFields as $f) { $colSums[$f] = 0; $colCounts[$f] = 0; }
            foreach ($rows as $row) {
                foreach ($avgFields as $f) {
                    if ($row[$f] !== null) {
                        $colSums[$f] += floatval($row[$f]);
                        $colCounts[$f]++;
                    }
                }
            }
            $averages = [];
            $calcFields = ['ca_total', 'exam_total', 'grand_total'];
            foreach ($avgFields as $f) {
                if ($colCounts[$f] > 0) {
                    // Calculated fields get 2 decimal places, raw fields get 1
                    $decimals = in_array($f, $calcFields) ? 2 : 1;
                    $averages[$f] = round($colSums[$f] / $colCounts[$f], $decimals);
                } else {
                    $averages[$f] = null;
                }
            }

            $subjectRosters[] = [
                'subject'  => $subj,
                'rows'     => $rows,
                'averages' => $averages,
            ];
        }

        // ── Lookup reference models ──────────────────────────────────
        $class        = ClassRoom::find($r->class_id);
        $section      = $r->filled('section_id') ? Section::find($r->section_id) : null;
        $academicYear = AcademicYear::find($r->academic_year_id);
        $term         = Term::find($r->term_id);

        // Re-fetch filter dropdown data for the form
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $terms         = Term::orderBy('id', 'desc')->get();

        // Re-apply teacher class filtering for the form dropdown
        if ($teacher) {
            // Lock AY and Term to active ones for teachers
            $activeAy = AcademicYear::where('is_current', true)->first();
            if ($activeAy) {
                $academicYears = collect([$activeAy]);
                $activeTerm = Term::where('academic_year_id', $activeAy->id)->where('is_active', true)->first();
                if ($activeTerm) {
                    $terms = collect([$activeTerm]);
                } else {
                    $terms = Term::where('academic_year_id', $activeAy->id)->orderBy('id', 'asc')->get();
                }
            }

            $assignmentClassIds = $teacher->assignments()->pluck('class_id')->unique();
            $homeroomClassIds = $teacher->classRooms()->pluck('id');
            $classIds = $assignmentClassIds->merge($homeroomClassIds)->unique();
            $classes = ClassRoom::whereIn('id', $classIds)->orderBy('numeric_name')->orderBy('name')->get();
        } else {
            $classes = ClassRoom::when($branchScope, fn($q) => $q->where('branch_id', $branchScope))->orderBy('numeric_name')->orderBy('name')->get();
        }

        // Get school name, logo, and branch for the report header
        $schoolName = Setting::getLocalizedName();
        $logoUrl = Setting::getLogoUrl();
        $branch = null;
        if ($class && $class->branch_id) {
            $branch = Branch::find($class->branch_id);
        }
        if (!$branch && $branchScope) {
            $branch = Branch::find($branchScope);
        }

        return view('admin.mark-roster.index', compact(
            'subjectRosters',
            'subjects',
            'students',
            'class',
            'section',
            'academicYear',
            'term',
            'academicYears',
            'terms',
            'classes',
            'isTeacher',
            'schoolName',
            'logoUrl',
            'branch'
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
            // Only return sections they're assigned to or homeroom for
            $assignmentSectionIds = $teacher->assignments()
                ->where('class_id', $r->class_id)
                ->pluck('section_id')
                ->filter()
                ->unique();
            $homeroomSectionIds = $teacher->sections()
                ->where('class_id', $r->class_id)
                ->pluck('id');

            $allowedSectionIds = $assignmentSectionIds->merge($homeroomSectionIds)->unique();

            // If teacher has assignments with null section_id for this class, they can see all sections
            $hasNullSectionAssignment = $teacher->assignments()
                ->where('class_id', $r->class_id)
                ->whereNull('section_id')
                ->exists();

            // Also check if they are homeroom for the class itself
            $isHomeroomClass = $teacher->classRooms()->where('id', $r->class_id)->exists();

            if (!$hasNullSectionAssignment && !$isHomeroomClass) {
                $query->whereIn('id', $allowedSectionIds);
            }
        }

        $sections = $query->orderBy('name')->get(['id', 'name']);

        return response()->json($sections);
    }

    /**
     * Generate summary mark list — 3 rows per student (Term1, Term2, Annual).
     * Subjects as columns, Total/Average/Rank at the end.
     */
    public function generateSummary(Request $r)
    {
        $branchScope = $r->attributes->get('branch_scope');
        if ($r->isMethod('GET') && !$r->filled('academic_year_id')) {
            return redirect()->route('admin.mark-roster.index');
        }

        $r->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_id'         => 'required|exists:classes,id',
            'section_id'       => 'nullable|exists:sections,id',
        ]);

        $academicYearId = $r->academic_year_id;
        $classId = $r->class_id;
        $sectionId = $r->filled('section_id') ? $r->section_id : null;

        // Load terms for this academic year
        $terms = Term::where('academic_year_id', $academicYearId)->orderBy('id', 'asc')->get();
        $term1 = $terms->first();
        $term2 = $terms->count() > 1 ? $terms[1] : null;

        // Load students
        $enrolledIds = \App\Models\StudentEnrollment::where('academic_year_id', $academicYearId)
            ->where('class_id', $classId)
            ->when($sectionId, fn($q) => $q->where('section_id', $sectionId))
            ->where('status', 'enrolled')
            ->pluck('student_id');

        if ($enrolledIds->isNotEmpty()) {
            $students = Student::whereIn('id', $enrolledIds)->where('status', 'active')->orderBy('full_name')->get();
        } else {
            $students = Student::where('class_id', $classId)->when($sectionId, fn($q) => $q->where('section_id', $sectionId))->where('status', 'active')->orderBy('full_name')->get();
        }

        // Load ONLY subjects assigned to this class/section via teacher assignments
        $subjectIds = TeacherAssignment::where('class_id', $classId)
            ->when($sectionId, function($q) use ($sectionId) {
                $q->where(function($q2) use ($sectionId) {
                    $q2->where('section_id', $sectionId)->orWhereNull('section_id');
                });
            })
            ->pluck('subject_id')
            ->unique();

        if ($subjectIds->isNotEmpty()) {
            $subjects = \App\Models\Subject::whereIn('id', $subjectIds)
                ->orderBy('priority')->orderBy('name')->get(['id', 'name']);
        } else {
            // Fallback: get subjects from existing mark entries for this class
            $markSubjectIds = MarkEntry::where('academic_year_id', $academicYearId)
                ->where('class_id', $classId)
                ->pluck('subject_id')->unique();
            if ($markSubjectIds->isNotEmpty()) {
                $subjects = \App\Models\Subject::whereIn('id', $markSubjectIds)
                    ->orderBy('priority')->orderBy('name')->get(['id', 'name']);
            } else {
                $subjects = collect();
            }
        }

        // Load all marks for these students
        $allMarks = MarkEntry::where('academic_year_id', $academicYearId)
            ->where('class_id', $classId)
            ->when($sectionId, fn($q) => $q->where('section_id', $sectionId))
            ->whereIn('student_id', $students->pluck('id'))
            ->get();

        // Load first-term overrides for mid-year entrants
        $overrideMap = \App\Models\FirstTermOverride::where('academic_year_id', $academicYearId)
            ->where('class_id', $classId)
            ->when($sectionId, fn($q) => $q->where('section_id', $sectionId))
            ->get()
            ->keyBy(fn($o) => (string)$o->student_id . '_' . (string)$o->subject_id);

        // Build roster: for each student, 3 rows (T1, T2, Annual)
        $roster = [];
        foreach ($students as $student) {
            $isMidYear = (int)($student->joined_term ?? 1) === 2;
            $studentRows = [];

            foreach (['term1' => $term1, 'term2' => $term2, 'annual' => null] as $key => $term) {
                $row = [
                    'student' => $student,
                    'term_label' => $key === 'term1' ? 'Term 1' : ($key === 'term2' ? 'Term 2' : 'Annual'),
                    'subjects' => [],
                    'total' => 0,
                    'count' => 0,
                    'average' => 0,
                    'rank' => '-',
                ];

                foreach ($subjects as $subj) {
                    $mark = null;
                    if ($key === 'annual') {
                        // Annual = average of T1 and T2
                        $t1Mark = null;
                        $t2Mark = null;
                        if ($isMidYear) {
                            $override = $overrideMap[(string)$student->id . '_' . (string)$subj->id] ?? null;
                            $t1Mark = $override?->grand_total !== null ? floatval($override->grand_total) : null;
                        } else {
                            $t1Entry = $allMarks->first(fn($m) => $m->student_id == $student->id && $m->subject_id == $subj->id && $term1 && $m->term_id == $term1->id);
                            $t1Mark = $t1Entry?->grand_total !== null ? floatval($t1Entry->grand_total) : null;
                        }
                        if ($term2) {
                            $t2Entry = $allMarks->first(fn($m) => $m->student_id == $student->id && $m->subject_id == $subj->id && $m->term_id == $term2->id);
                            $t2Mark = $t2Entry?->grand_total !== null ? floatval($t2Entry->grand_total) : null;
                        }
                        if ($isMidYear) {
                            // Mid-year: annual = T2 only
                            $mark = $t2Mark;
                        } elseif ($t1Mark !== null && $t2Mark !== null) {
                            $mark = round(($t1Mark + $t2Mark) / 2, 1);
                        } elseif ($t1Mark !== null) {
                            $mark = $t1Mark;
                        } else {
                            $mark = $t2Mark;
                        }
                    } else {
                        // Term 1 or Term 2
                        if ($key === 'term1' && $isMidYear) {
                            $override = $overrideMap[(string)$student->id . '_' . (string)$subj->id] ?? null;
                            $mark = $override?->grand_total !== null ? floatval($override->grand_total) : null;
                        } elseif ($term) {
                            $entry = $allMarks->first(fn($m) => $m->student_id == $student->id && $m->subject_id == $subj->id && $m->term_id == $term->id);
                            $mark = $entry?->grand_total !== null ? floatval($entry->grand_total) : null;
                        }
                    }

                    $row['subjects'][$subj->id] = $mark;
                    if ($mark !== null) {
                        $row['total'] += $mark;
                        $row['count']++;
                    }
                }
                $row['average'] = $row['count'] > 0 ? round($row['total'] / $row['count'], 1) : 0;
                $studentRows[$key] = $row;
            }

            // Calculate ranks for each term across all students (done after all students are processed)
            $roster[] = $studentRows;
        }

        // Calculate ranks
        $this->assignSummaryRanks($roster, 'term1');
        $this->assignSummaryRanks($roster, 'term2');
        $this->assignSummaryRanks($roster, 'annual');

        $academicYear = AcademicYear::find($academicYearId);
        $class = ClassRoom::find($classId);
        $section = $sectionId ? Section::find($sectionId) : null;
        $schoolName = Setting::getLocalizedName();
        $logoUrl = Setting::getLogoUrl();
        $branch = null;
        if ($class && $class->branch_id) $branch = Branch::find($class->branch_id);

        return view('admin.mark-roster.summary', compact(
            'roster', 'subjects', 'students', 'academicYear', 'class', 'section',
            'schoolName', 'logoUrl', 'branch', 'term1', 'term2'
        ));
    }

    private function assignSummaryRanks(array &$roster, string $termKey): void
    {
        // Exclude special needs students from ranking
        $ranked = collect($roster)->filter(function($s) use ($termKey) {
            return !($s[$termKey]['student']->special_needs ?? false);
        })->sortByDesc(fn($s) => $s[$termKey]['total'])->values();
        $rank = 1;
        $rankMap = [];
        foreach ($ranked as $i => $studentRows) {
            if ($i > 0 && $studentRows[$termKey]['total'] < $ranked[$i - 1][$termKey]['total']) {
                $rank = $i + 1;
            }
            $rankMap[$studentRows[$termKey]['student']->id] = $rank;
        }
        foreach ($roster as &$studentRows) {
            if ($studentRows[$termKey]['student']->special_needs ?? false) {
                $studentRows[$termKey]['rank'] = 'SN';
            } else {
                $studentRows[$termKey]['rank'] = $rankMap[$studentRows[$termKey]['student']->id] ?? '-';
            }
        }
        unset($studentRows);
    }
}
