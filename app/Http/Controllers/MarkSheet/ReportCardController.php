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
     * Show filter form for generating foldable report cards.
     */
    public function index()
    {
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $terms = Term::orderBy('id', 'desc')->get();
        $classes = ClassRoom::orderBy('name')->get();

        return view('admin.report-card.index', compact('academicYears', 'terms', 'classes'));
    }

    /**
     * Generate foldable report cards for selected students.
     *
     * Layout: 4 faces like a postcard folded in two places
     * - Face 1 (Front Cover): School logo, name, "Report Card", student name/class
     * - Face 2 (Inside Left): Attendance summary, behavior/conduct
     * - Face 3 (Inside Right): Subject marks table with grades
     * - Face 4 (Back Cover): Teacher/principal comments, grading scale, signature
     */
    public function generate(Request $r)
    {
        $r->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id'          => 'required|exists:terms,id',
            'class_id'         => 'required|exists:classes,id',
            'section_id'       => 'nullable|exists:sections,id',
            'student_id'       => 'nullable|exists:students,id',
        ]);

        // Get all mark entries for the given filters
        $query = MarkEntry::with(['student', 'subject', 'term', 'academicYear', 'classRoom', 'section'])
            ->where('academic_year_id', $r->academic_year_id)
            ->where('term_id', $r->term_id)
            ->where('class_id', $r->class_id);

        if ($r->filled('section_id')) {
            $query->where('section_id', $r->section_id);
        }
        if ($r->filled('student_id')) {
            $query->where('student_id', $r->student_id);
        }

        $marks = $query->orderBy('student_id')
            ->orderByRaw('(SELECT priority FROM subjects WHERE subjects.id = mark_entries.subject_id) ASC')
            ->orderBy('subject_id')
            ->get();

        // Group marks by student
        $studentMarks = $marks->groupBy('student_id');

        // For each student, compute subject list, totals, rank
        $students = [];
        $allTotals = []; // for rank calculation

        foreach ($studentMarks as $studentId => $entries) {
            $student = $entries->first()->student;
            $subjects = [];
            $grandTotal = 0;
            $maxPossible = 0;

            foreach ($entries as $entry) {
                if ($entry->subject) {
                    $subjects[] = [
                        'name'         => $entry->subject->name,
                        'type'         => $entry->subject->type ?? 'compulsory',
                        'ca_total'     => $entry->ca_total,
                        'exam_total'   => $entry->exam_total,
                        'grand_total'  => $entry->grand_total,
                        'grade'        => $entry->grade,
                        'max'          => 100,
                    ];
                    $grandTotal += floatval($entry->grand_total ?? 0);
                    $maxPossible += 100;
                }
            }

            $percentage = $maxPossible > 0 ? round(($grandTotal / $maxPossible) * 100, 1) : 0;
            $overallGrade = $this->getGrade($percentage);

            $allTotals[$studentId] = $grandTotal;

            $students[] = [
                'student'     => $student,
                'subjects'    => $subjects,
                'grandTotal'  => $grandTotal,
                'maxPossible' => $maxPossible,
                'percentage'  => $percentage,
                'grade'       => $overallGrade,
            ];
        }

        // Calculate ranks
        arsort($allTotals);
        $rankMap = [];
        $rank = 1;
        foreach ($allTotals as $sid => $total) {
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
        $class = ClassRoom::find($r->class_id);
        $section = $r->filled('section_id') ? Section::find($r->section_id) : null;
        $academicYear = AcademicYear::find($r->academic_year_id);
        $term = Term::find($r->term_id);

        // School settings
        $schoolName = Setting::get('school_name', 'School of Redemption');
        $schoolMotto = Setting::get('school_motto', '');
        $schoolLogo = Setting::get('school_logo', '');
        $schoolAddress = Setting::get('address', '');
        $schoolPhone = Setting::get('phone', '');
        $schoolEmail = Setting::get('email', '');
        $currencySymbol = Setting::get('currency_symbol', 'Br');

        $logoUrl = null;
        if ($schoolLogo) {
            $logoUrl = filter_var($schoolLogo, FILTER_VALIDATE_URL)
                ? $schoolLogo
                : (\Illuminate\Support\Facades\Storage::disk('public')->url($schoolLogo));
        }

        return view('admin.report-card.card', compact(
            'students', 'class', 'section', 'academicYear', 'term',
            'schoolName', 'schoolMotto', 'logoUrl', 'schoolAddress', 'schoolPhone', 'schoolEmail',
            'currencySymbol'
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
        $students = $query->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'roll_number']);
        return response()->json($students);
    }

    /**
     * Convert percentage to grade letter.
     */
    private function getGrade(float $percentage): string
    {
        if ($percentage >= 90) return 'A+';
        if ($percentage >= 85) return 'A';
        if ($percentage >= 80) return 'A-';
        if ($percentage >= 75) return 'B+';
        if ($percentage >= 70) return 'B';
        if ($percentage >= 65) return 'B-';
        if ($percentage >= 60) return 'C+';
        if ($percentage >= 55) return 'C';
        if ($percentage >= 50) return 'C-';
        if ($percentage >= 45) return 'D';
        if ($percentage >= 40) return 'D-';
        return 'F';
    }
}
