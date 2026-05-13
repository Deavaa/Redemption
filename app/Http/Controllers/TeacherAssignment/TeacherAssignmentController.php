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

    public function show(TeacherAssignment $item)
    {
        $item->load(['teacher','classRoom','section','subject','academicYear']);
        return view('admin.TeacherAssignment.show', compact('item'));
    }

    public function edit(TeacherAssignment $item)
    {
        $teachers = Teacher::orderBy('first_name')->get();
        $classes = Classroom::orderBy('name')->get();
        $sections = Section::where('class_id', $item->class_id)->orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        return view('admin.TeacherAssignment.edit', compact('item', 'teachers', 'classes', 'sections', 'subjects', 'academicYears'));
    }

    public function update(Request $r, TeacherAssignment $item)
    {
        $r->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);
        $item->update($r->only(['teacher_id','class_id','section_id','subject_id','academic_year_id']));
        return redirect()->route("admin.teacher-assignments.index")->with('success','Assignment updated successfully');
    }

    public function destroy(TeacherAssignment $item) { $item->delete(); return back()->with('success','Assignment deleted successfully'); }
}
