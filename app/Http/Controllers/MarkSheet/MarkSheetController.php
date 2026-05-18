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
use App\Models\Teacher;
use Illuminate\Http\Request;

class MarkSheetController extends Controller
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

    public function index(Request $r)
    {
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $terms = Term::orderBy('id', 'desc')->get();
        $exams = Exam::orderBy('id', 'desc')->get();

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

            // Only show classes where teacher is homeroom teacher
            $classes = $teacher->classRooms()->orderBy('name')->get();
        } else {
            $classes = ClassRoom::orderBy('name')->get();
        }

        return view('admin.mark-sheet.index', compact('academicYears', 'terms', 'classes', 'exams', 'isTeacher'));
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

        // ── Authorization check for teachers ──
        $teacher = $this->getTeacherForUser();
        if ($teacher) {
            $isHomeroom = $teacher->classRooms()->where('id', $r->class_id)->exists();
            if (!$isHomeroom) {
                abort(403, 'You are not authorized to generate mark sheets for this class. Only homeroom teachers can access this feature.');
            }
        }

        $query = MarkEntry::with(['student', 'subject', 'exam', 'term', 'academicYear', 'classRoom', 'section'])
            ->where('academic_year_id', $r->academic_year_id)
            ->where('class_id', $r->class_id);

        if ($r->filled('term_id')) $query->where('term_id', $r->term_id);
        if ($r->filled('section_id')) $query->where('section_id', $r->section_id);
        if ($r->filled('exam_id')) $query->where('exam_id', $r->exam_id);
        if ($r->filled('student_id')) $query->where('student_id', $r->student_id);

        $marks = $query->orderBy('student_id')->orderByRaw('(SELECT priority FROM subjects WHERE subjects.id = mark_entries.subject_id) ASC')->orderBy('subject_id')->get();
        $students = $marks->groupBy('student_id')->sortBy(function($studentMarks) {
            $s = $studentMarks->first()?->student;
            return $s ? (intval($s->roll_number) * 10000 + ord(strtoupper($s->full_name[0] ?? 'A'))) : 0;
        });
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
        $teacher = $this->getTeacherForUser();

        $query = Section::where('class_id', $r->class_id);

        if ($teacher) {
            // Only return sections where teacher is homeroom
            $query->where('teacher_id', $teacher->id);
        }

        $sections = $query->orderBy('name')->get(['id', 'name']);
        return response()->json($sections);
    }

    public function getStudents(Request $r)
    {
        $teacher = $this->getTeacherForUser();

        if ($teacher) {
            // Only allow access to students in homeroom classes
            $isHomeroom = $teacher->classRooms()->where('id', $r->class_id)->exists();
            if (!$isHomeroom) {
                return response()->json([]);
            }
        }

        $query = Student::where('class_id', $r->class_id);
        if ($r->filled('section_id')) $query->where('section_id', $r->section_id);
        $students = $query->selectRaw("*, CAST(roll_number AS UNSIGNED) as rn_sort")->orderByRaw('rn_sort ASC')->orderBy('full_name')->get(['id', 'full_name', 'roll_number']);
        return response()->json($students);
    }
}
