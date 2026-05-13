<?php
namespace App\Http\Controllers\TeacherAssignment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TeacherAssignment;
use App\Models\Teacher;
use App\Models\Classroom;
use App\Models\Section;
use App\Models\Subject;
use App\Models\AcademicYear;

class TeacherAssignmentController extends Controller
{
    public function index(Request $r)
    {
        $q = TeacherAssignment::with(['teacher','classRoom','section','subject','academicYear']);
        if ($r->filled('academic_year_id')) $q->where('academic_year_id', $r->academic_year_id);
        if ($r->filled('class_id')) $q->where('class_id', $r->class_id);
        $data = $q->latest()->paginate(20);
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $classes = Classroom::orderBy('name')->get();
        return view('admin.TeacherAssignment.index', compact('data', 'academicYears', 'classes'));
    }

    public function create()
    {
        $teachers = Teacher::orderBy('first_name')->get();
        $classes = Classroom::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        return view('admin.TeacherAssignment.create', compact('teachers', 'classes', 'subjects', 'academicYears'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);
        TeacherAssignment::create($r->only(['teacher_id','class_id','section_id','subject_id','academic_year_id']));
        return redirect()->route("admin.teacher-assignments.index")->with('success','Assignment created successfully');
    }

    public function show(TeacherAssignment $teacher_assignment)
    {
        $teacher_assignment->load(['teacher','classRoom','section','subject','academicYear']);
        return view('admin.TeacherAssignment.show', ['item' => $teacher_assignment]);
    }

    public function edit(TeacherAssignment $teacher_assignment)
    {
        $teachers = Teacher::orderBy('first_name')->get();
        $classes = Classroom::orderBy('name')->get();
        $sections = Section::where('class_id', $teacher_assignment->class_id)->orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        return view('admin.TeacherAssignment.edit', ['item' => $teacher_assignment, 'teachers' => $teachers, 'classes' => $classes, 'sections' => $sections, 'subjects' => $subjects, 'academicYears' => $academicYears]);
    }

    public function update(Request $r, TeacherAssignment $teacher_assignment)
    {
        $r->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);
        $teacher_assignment->update($r->only(['teacher_id','class_id','section_id','subject_id','academic_year_id']));
        return redirect()->route("admin.teacher-assignments.index")->with('success','Assignment updated successfully');
    }

    public function destroy(TeacherAssignment $teacher_assignment) { $teacher_assignment->delete(); return back()->with('success','Assignment deleted successfully'); }
}
