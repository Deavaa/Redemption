<?php

namespace App\Http\Controllers\MarkSheet;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\MarkEntry;
use App\Models\ClassRoom;
use App\Models\Section;
use App\Models\Exam;
use App\Models\AcademicYear;
use App\Models\Term;
use Illuminate\Http\Request;

class MarkSheetController extends Controller
{
    public function index(Request $r)
    {
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $terms = Term::orderBy('id', 'desc')->get();
        $classes = ClassRoom::orderBy('name')->get();
        $exams = Exam::orderBy('id', 'desc')->get();

        return view('admin.mark-sheet.index', compact('academicYears', 'terms', 'classes', 'exams'));
    }

    public function generate(Request $r)
    {
        $r->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'nullable|exists:terms,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'student_id' => 'nullable|exists:students,id',
            'exam_id' => 'nullable|exists:exams,id',
        ]);

        $query = MarkEntry::with(['student', 'subject', 'exam', 'term', 'academicYear', 'classRoom', 'section'])
            ->where('academic_year_id', $r->academic_year_id)
            ->where('class_id', $r->class_id);

        if ($r->filled('term_id')) $query->where('term_id', $r->term_id);
        if ($r->filled('section_id')) $query->where('section_id', $r->section_id);
        if ($r->filled('exam_id')) $query->where('exam_id', $r->exam_id);
        if ($r->filled('student_id')) $query->where('student_id', $r->student_id);

        $marks = $query->orderBy('student_id')->orderByRaw('(SELECT priority FROM subjects WHERE subjects.id = mark_entries.subject_id) ASC')->orderBy('subject_id')->get();
        $students = $marks->groupBy('student_id');
        $class = ClassRoom::find($r->class_id);
        $academicYear = AcademicYear::find($r->academic_year_id);
        $term = $r->filled('term_id') ? Term::find($r->term_id) : null;

        if ($r->filled('student_id')) {
            $student = Student::find($r->student_id);
            return view('admin.mark-sheet.sheet', compact('marks', 'student', 'class', 'academicYear', 'term', 'students'));
        }

        return view('admin.mark-sheet.batch', compact('marks', 'students', 'class', 'academicYear', 'term'));
    }

    public function getSections(Request $r)
    {
        $sections = Section::where('class_id', $r->class_id)->orderBy('name')->get(['id', 'name']);
        return response()->json($sections);
    }

    public function getStudents(Request $r)
    {
        $query = Student::where('class_id', $r->class_id);
        if ($r->filled('section_id')) $query->where('section_id', $r->section_id);
        $students = $query->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'roll_number']);
        return response()->json($students);
    }
}
