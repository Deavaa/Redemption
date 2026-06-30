<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FirstTermOverride;
use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\TeacherAssignment;
use Illuminate\Http\Request;

class FirstTermOverrideController extends Controller
{
    /**
     * Show the form for entering per-subject first-term marks for mid-year entrants.
     */
    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $currentAy = $academicYears->first();

        $classes = ClassRoom::orderBy('numeric_name')->orderBy('name')->get(['id', 'name']);

        $selectedAy = $request->query('academic_year_id', $currentAy?->id);
        $selectedClass = $request->query('class_id');
        $selectedSection = $request->query('section_id');

        $students = collect();
        $subjects = collect();
        $overrides = collect();

        if ($selectedAy && $selectedClass && $selectedSection) {
            // Load students enrolled in this class/section/year
            $enrolledIds = StudentEnrollment::where('academic_year_id', $selectedAy)
                ->where('class_id', $selectedClass)
                ->where('section_id', $selectedSection)
                ->where('status', 'enrolled')
                ->pluck('student_id');

            if ($enrolledIds->isNotEmpty()) {
                $students = Student::whereIn('id', $enrolledIds)
                    ->where('status', 'active')
                    ->orderBy('full_name')
                    ->get();
            } else {
                $students = Student::where('class_id', $selectedClass)
                    ->where('section_id', $selectedSection)
                    ->where('status', 'active')
                    ->orderBy('full_name')
                    ->get();
            }

            // Load ONLY subjects assigned to this class/section via teacher assignments
            $subjectIds = TeacherAssignment::where('class_id', $selectedClass)
                ->where(function($q) use ($selectedSection) {
                    $q->where('section_id', $selectedSection)->orWhereNull('section_id');
                })
                ->pluck('subject_id')
                ->unique();

            if ($subjectIds->isNotEmpty()) {
                $subjects = Subject::whereIn('id', $subjectIds)
                    ->orderBy('priority')->orderBy('name')
                    ->get(['id', 'name']);
            } else {
                // Fallback: if no teacher assignments, show all subjects
                $subjects = Subject::orderBy('priority')->orderBy('name')->get(['id', 'name']);
            }

            // Load existing overrides
            $overrides = FirstTermOverride::where('academic_year_id', $selectedAy)
                ->where('class_id', $selectedClass)
                ->where('section_id', $selectedSection)
                ->get()
                ->keyBy(function($o) { return $o->student_id . '_' . $o->subject_id; });
        }

        $sections = collect();
        if ($selectedClass) {
            $sections = Section::where('class_id', $selectedClass)->orderBy('name')->get(['id', 'name']);
        }

        return view('admin.first-term-overrides.index', compact(
            'academicYears', 'classes', 'sections',
            'selectedAy', 'selectedClass', 'selectedSection',
            'students', 'subjects', 'overrides'
        ));
    }

    /**
     * Save per-subject first-term marks for mid-year entrants.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'marks' => 'required|array',
            'marks.*.student_id' => 'required|exists:students,id',
            'marks.*.subject_id' => 'required|exists:subjects,id',
            'marks.*.grand_total' => 'nullable|numeric',
            'marks.*.rank_override' => 'nullable|integer',
        ]);

        $saved = 0;
        foreach ($validated['marks'] as $mark) {
            // Skip empty entries
            if (empty($mark['grand_total']) && empty($mark['rank_override'])) {
                continue;
            }

            $grandTotal = !empty($mark['grand_total']) ? floatval($mark['grand_total']) : null;
            $grade = $grandTotal !== null ? $this->calcGrade($grandTotal) : null;
            $rankOverride = !empty($mark['rank_override']) ? (int)$mark['rank_override'] : null;

            FirstTermOverride::updateOrCreate(
                [
                    'student_id' => $mark['student_id'],
                    'subject_id' => $mark['subject_id'],
                    'academic_year_id' => $validated['academic_year_id'],
                ],
                [
                    'class_id' => $validated['class_id'],
                    'section_id' => $validated['section_id'],
                    'grand_total' => $grandTotal,
                    'grade' => $grade,
                    'rank_override' => $rankOverride,
                ]
            );
            $saved++;
        }

        return redirect()->back()->with('success', "Saved {$saved} first-term override mark(s) successfully.");
    }

    private function calcGrade(float $mark): string
    {
        if ($mark <= 0) return 'I';
        if ($mark >= 80) return 'A';
        if ($mark >= 60) return 'B';
        if ($mark >= 50) return 'C';
        if ($mark >= 40) return 'D';
        return 'F';
    }
}
