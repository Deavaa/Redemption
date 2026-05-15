<?php

namespace App\Http\Controllers\MarkSheet;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\MarkEntry;
use App\Models\ClassRoom;
use App\Models\Section;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\Setting;
use Illuminate\Http\Request;

class ReportCardController extends Controller
{
    /**
     * Show filter form for generating report cards.
     */
    public function index()
    {
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $classes = ClassRoom::orderBy('name')->get();

        return view('admin.report-card.index', compact('academicYears', 'classes'));
    }

    /**
     * Generate year-based report cards for selected students.
     *
     * Layout: A4 Landscape with two equal columns (left & right).
     * Shows all terms for the academic year, plus annual average.
     * - Left Column: Student info + Subject marks table (all terms)
     * - Right Column: Performance summary + Grading scale + Comments + Signatures
     */
    public function generate(Request $r)
    {
        $r->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_id'         => 'required|exists:classes,id',
            'section_id'       => 'nullable|exists:sections,id',
            'student_id'       => 'nullable|exists:students,id',
        ]);

        $academicYearId = $r->academic_year_id;
        $classId        = $r->class_id;
        $sectionId      = $r->filled('section_id') ? $r->section_id : null;

        // Get all terms for the academic year
        $terms = Term::where('academic_year_id', $academicYearId)
            ->orderBy('term_number')
            ->get();

        $term1 = $terms->first();
        $term2 = $terms->count() >= 2 ? $terms->skip(1)->first() : null;

        // Get all mark entries for the academic year and class
        $query = MarkEntry::with(['student', 'subject', 'term', 'academicYear', 'classRoom', 'section'])
            ->where('academic_year_id', $academicYearId)
            ->where('class_id', $classId);

        if ($sectionId) {
            $query->where('section_id', $sectionId);
        }
        if ($r->filled('student_id')) {
            $query->where('student_id', $r->student_id);
        }

        $allMarks = $query->orderBy('student_id')
            ->orderByRaw('(SELECT priority FROM subjects WHERE subjects.id = mark_entries.subject_id) ASC')
            ->orderBy('subject_id')
            ->get();

        // Get unique subjects
        $subjects = $allMarks->pluck('subject')->filter()->unique('id')->sortBy(function($s) { return [$s->priority ?? 0, $s->name]; })->values();

        // Build mark data: [studentId][termId][subjectId] = entry
        $markData = [];
        foreach ($allMarks as $entry) {
            $markData[$entry->student_id][$entry->term_id][$entry->subject_id] = $entry;
        }

        // Get students
        $studentQuery = Student::where('class_id', $classId)->where('status', 'active');
        if ($sectionId) {
            $studentQuery->where('section_id', $sectionId);
        }
        if ($r->filled('student_id')) {
            $studentQuery->where('id', $r->student_id);
        }
        $studentsList = $studentQuery
            ->selectRaw("*, CAST(roll_number AS UNSIGNED) as rn_sort")
            ->orderByRaw('rn_sort ASC')
            ->orderBy('first_name')
            ->get();

        // Build student cards data
        $students = [];
        $allAnnualTotals = []; // for rank calculation

        foreach ($studentsList as $student) {
            $subjectRows = [];
            $annualGrandTotal = 0;
            $annualSubjectCount = 0;

            foreach ($subjects as $subj) {
                $t1Entry = $markData[$student->id][$term1->id][$subj->id] ?? null;
                $t2Entry = $term2 ? ($markData[$student->id][$term2->id][$subj->id] ?? null) : null;

                $t1Ca = $t1Entry ? $t1Entry->ca_total : null;
                $t1Exam = $t1Entry ? $t1Entry->exam_total : null;
                $t1Grand = $t1Entry ? $t1Entry->grand_total : null;
                $t1Grade = $t1Entry ? $t1Entry->grade : null;

                $t2Ca = $t2Entry ? $t2Entry->ca_total : null;
                $t2Exam = $t2Entry ? $t2Entry->exam_total : null;
                $t2Grand = $t2Entry ? $t2Entry->grand_total : null;
                $t2Grade = $t2Entry ? $t2Entry->grade : null;

                // Annual = average of T1+T2 if both exist
                $annGrand = null;
                $annGrade = null;
                if ($t1Grand !== null && $t2Grand !== null) {
                    $annGrand = round((floatval($t1Grand) + floatval($t2Grand)) / 2, 1);
                    $annGrade = $this->getGrade($annGrand);
                } elseif ($t1Grand !== null) {
                    $annGrand = floatval($t1Grand);
                    $annGrade = $t1Grade;
                } elseif ($t2Grand !== null) {
                    $annGrand = floatval($t2Grand);
                    $annGrade = $t2Grade;
                }

                if ($annGrand !== null) {
                    $annualGrandTotal += $annGrand;
                    $annualSubjectCount++;
                }

                $subjectRows[] = [
                    'name'    => $subj->name,
                    't1_ca'   => $t1Ca,
                    't1_exam' => $t1Exam,
                    't1_total'=> $t1Grand,
                    't1_grade'=> $t1Grade,
                    't2_ca'   => $t2Ca,
                    't2_exam' => $t2Exam,
                    't2_total'=> $t2Grand,
                    't2_grade'=> $t2Grade,
                    'ann_total'=> $annGrand,
                    'ann_grade'=> $annGrade,
                ];
            }

            $maxPossible = $subjects->count() * 100;
            $percentage = $maxPossible > 0 ? round(($annualGrandTotal / $maxPossible) * 100, 1) : 0;
            $overallGrade = $this->getGrade($percentage);

            $allAnnualTotals[$student->id] = $annualGrandTotal;

            $students[] = [
                'student'          => $student,
                'subjects'         => $subjectRows,
                'annualGrandTotal' => $annualGrandTotal,
                'maxPossible'      => $maxPossible,
                'percentage'       => $percentage,
                'grade'            => $overallGrade,
                'subjectCount'     => $annualSubjectCount,
            ];
        }

        // Calculate ranks based on annual totals
        arsort($allAnnualTotals);
        $rankMap = [];
        $rank = 1;
        foreach ($allAnnualTotals as $sid => $total) {
            $rankMap[$sid] = $rank++;
        }
        foreach ($students as &$s) {
            $s['rank'] = $rankMap[$s['student']->id] ?? '-';
        }
        unset($s);

        // Sort students by roll number then name
        usort($students, function ($a, $b) {
            $rollA = $a['student']->roll_number ?? '';
            $rollB = $b['student']->roll_number ?? '';
            if ($rollA !== $rollB) return strcmp($rollA, $rollB);
            return strcmp(
                ($a['student']->first_name ?? '') . ($a['student']->last_name ?? ''),
                ($b['student']->first_name ?? '') . ($b['student']->last_name ?? '')
            );
        });

        // Reference data
        $class = ClassRoom::find($classId);
        $section = $sectionId ? Section::find($sectionId) : null;
        $academicYear = AcademicYear::find($academicYearId);

        // School settings
        $schoolName = Setting::get('school_name', 'School of Redemption');
        $schoolMotto = Setting::get('school_motto', '');
        $schoolLogo = Setting::get('school_logo', '');
        $schoolAddress = Setting::get('address', '');
        $schoolPhone = Setting::get('phone', '');
        $schoolEmail = Setting::get('email', '');

        $logoUrl = null;
        if ($schoolLogo) {
            $logoUrl = filter_var($schoolLogo, FILTER_VALIDATE_URL)
                ? $schoolLogo
                : Setting::getLogoUrl();
        }

        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $classes = ClassRoom::orderBy('name')->get();

        return view('admin.report-card.card', compact(
            'students', 'class', 'section', 'academicYear',
            'term1', 'term2', 'subjects',
            'schoolName', 'schoolMotto', 'logoUrl', 'schoolAddress', 'schoolPhone', 'schoolEmail',
            'academicYears', 'classes'
        ));
    }

    /**
     * Get sections for a class (AJAX).
     */
    public function getSections(Request $r)
    {
        $sections = Section::where('class_id', $r->class_id)->orderBy('name')->get(['id', 'name']);
        return response()->json($sections);
    }

    /**
     * Get students for a class/section (AJAX).
     */
    public function getStudents(Request $r)
    {
        $query = Student::where('class_id', $r->class_id)->where('status', 'active');
        if ($r->filled('section_id')) $query->where('section_id', $r->section_id);
        $students = $query->selectRaw("*, CAST(roll_number AS UNSIGNED) as rn_sort")->orderByRaw('rn_sort ASC')->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'roll_number']);
        return response()->json($students);
    }

    /**
     * Convert mark/percentage to grade letter.
     */
    private function getGrade(float $mark): string
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
