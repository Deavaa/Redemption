<?php
namespace App\Http\Controllers\Classroom;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Section;
use App\Models\AcademicYear;
use App\Models\Teacher;
use App\Models\Branch;
use Illuminate\Http\Request;
class ClassroomController extends Controller
{
    public function index()
    {
        $classes = Classroom::with(["academicYear", "sections"])->orderBy("name")->paginate(10);
        $totalClasses = Classroom::count();
        $totalSections = \App\Models\Section::count();
        $activeAcademicYear = \App\Models\AcademicYear::latest()->first();

        return view("admin.Classroom.index", compact("classes", "totalClasses", "totalSections", "activeAcademicYear"));
    }
    public function create()
    {
        $academicYears = AcademicYear::orderBy('name')->get();
        $teachers = Teacher::orderBy('first_name')->get();
        $branches = Branch::orderBy('name')->get();
        return view('admin.Classroom.create', compact('academicYears','teachers','branches'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'academic_year_id' => 'required|exists:academic_years,id',
            'branch_id' => 'required|exists:branches,id',
            'capacity' => 'nullable|integer|min:1',
            'teacher_id' => 'nullable|exists:teachers,id',
        ]);
        $class = Classroom::create($request->only('name','academic_year_id','branch_id','capacity','teacher_id'));
        if ($request->has('sections')) {
            foreach ($request->sections as $sec) {
                if (!empty($sec['name'])) {
                    Section::create([
                        'class_id' => $class->id,
                        'name' => $sec['name'],
                        'max_students' => !empty($sec['max_students']) ? $sec['max_students'] : null,
                        'teacher_id' => !empty($sec['teacher_id']) ? $sec['teacher_id'] : null,
                    ]);
                }
            }
        }
        return redirect()->route('admin.classrooms.index')->with('success','Class created with sections');
    }
    public function edit($id)
    {
        $data = Classroom::with(['sections.teacher','academicYear','teacher','branch'])->findOrFail($id);
        $academicYears = AcademicYear::orderBy('name')->get();
        $teachers = Teacher::orderBy('first_name')->get();
        $branches = Branch::orderBy('name')->get();
        return view('admin.Classroom.edit', compact('data','academicYears','teachers','branches'));
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'academic_year_id' => 'required|exists:academic_years,id',
            'branch_id' => 'required|exists:branches,id',
            'capacity' => 'nullable|integer|min:1',
            'teacher_id' => 'nullable|exists:teachers,id',
        ]);
        $class = Classroom::findOrFail($id);
        $class->update($request->only('name','academic_year_id','branch_id','capacity','teacher_id'));
        if ($request->has('sections')) {
            $existingIds = [];
            foreach ($request->sections as $sec) {
                if (!empty($sec['name'])) {
                    $secData = [
                        'class_id' => $class->id,
                        'name' => $sec['name'],
                        'max_students' => !empty($sec['max_students']) ? $sec['max_students'] : null,
                        'teacher_id' => !empty($sec['teacher_id']) ? $sec['teacher_id'] : null,
                    ];
                    if (!empty($sec['id'])) {
                        Section::where('id', $sec['id'])->update($secData);
                        $existingIds[] = $sec['id'];
                    } else {
                        $newSec = Section::create($secData);
                        $existingIds[] = $newSec->id;
                    }
                }
            }
            Section::where('class_id', $class->id)->whereNotIn('id', $existingIds)->delete();
        } else {
            Section::where('class_id', $class->id)->delete();
        }
        return redirect()->route('admin.classrooms.index')->with('success','Class updated with sections');
    }
    public function destroy($id)
    {
        $class = Classroom::findOrFail($id);
        Section::where('class_id', $class->id)->delete();
        $class->delete();
        return redirect()->route('admin.classrooms.index')->with('success','Deleted');
    }
}
