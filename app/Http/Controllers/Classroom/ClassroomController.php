<?php
namespace App\Http\Controllers\Classroom;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Section;
use App\Models\AcademicYear;
use App\Models\Teacher;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

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
        $teachers = Teacher::orderBy('full_name')->get();
        $branches = Branch::orderBy('name')->get();
        return view('admin.Classroom.create', compact('academicYears','teachers','branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'academic_year_id' => 'required|exists:academic_years,id',
            'branch_id' => 'required|exists:branches,id',
            'numeric_name' => 'nullable|string|max:255',
            'teacher_id' => 'nullable|exists:teachers,id',
            'sections.*.name' => 'nullable|string|max:255',
            'sections.*.max_students' => 'nullable|integer|min:1',
            'sections.*.teacher_id' => 'nullable|exists:teachers,id',
        ]);

        try {
            $class = Classroom::create($request->only('name','academic_year_id','branch_id','numeric_name','teacher_id'));
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
            // Auto-calculate capacity from sections
            $class->recalculateCapacity();
            return redirect()->route('admin.classrooms.index')->with('success','Class created with sections');
        } catch (QueryException $e) {
            if (str_contains($e->getMessage(), 'foreign key constraint')) {
                return back()->withInput()->withErrors(['teacher_id' => 'The selected teacher no longer exists in the database. This can happen if a teacher was recently deleted. Please select a different teacher or leave it blank.']);
            }
            throw $e;
        }
    }

    public function edit($id)
    {
        $data = Classroom::with(['sections.teacher','academicYear','teacher','branch'])->findOrFail($id);
        $academicYears = AcademicYear::orderBy('name')->get();
        $teachers = Teacher::orderBy('full_name')->get();
        $branches = Branch::orderBy('name')->get();

        // If the class or any section has a teacher_id that no longer exists in
        // the teachers table (e.g., the teacher was deleted but the FK has
        // ON DELETE SET NULL which may not have fired yet), clear it to prevent
        // foreign key errors on save.
        if ($data->teacher_id && !Teacher::where('id', $data->teacher_id)->exists()) {
            $data->teacher_id = null;
            $data->saveQuietly();
        }
        foreach ($data->sections as $sec) {
            if ($sec->teacher_id && !Teacher::where('id', $sec->teacher_id)->exists()) {
                $sec->teacher_id = null;
                $sec->saveQuietly();
            }
        }

        return view('admin.Classroom.edit', compact('data','academicYears','teachers','branches'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'academic_year_id' => 'required|exists:academic_years,id',
            'branch_id' => 'required|exists:branches,id',
            'numeric_name' => 'nullable|string|max:255',
            'teacher_id' => 'nullable|exists:teachers,id',
            'sections.*.name' => 'nullable|string|max:255',
            'sections.*.max_students' => 'nullable|integer|min:1',
            'sections.*.teacher_id' => 'nullable|exists:teachers,id',
        ]);

        try {
            $class = Classroom::findOrFail($id);

            // Build update data — convert empty string teacher_id to null
            $updateData = $request->only('name','academic_year_id','branch_id','numeric_name');
            $updateData['teacher_id'] = $request->filled('teacher_id') ? $request->teacher_id : null;
            $class->update($updateData);

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
            // Auto-calculate capacity from sections
            $class->recalculateCapacity();
            return redirect()->route('admin.classrooms.index')->with('success','Class updated with sections');
        } catch (QueryException $e) {
            if (str_contains($e->getMessage(), 'foreign key constraint')) {
                return back()->withInput()->withErrors(['teacher_id' => 'The selected teacher no longer exists in the database. This can happen if a teacher was recently deleted. Please select a different teacher or leave it blank.']);
            }
            throw $e;
        }
    }

    public function destroy($id)
    {
        $class = Classroom::findOrFail($id);
        Section::where('class_id', $class->id)->delete();
        $class->delete();
        return redirect()->route('admin.classrooms.index')->with('success','Deleted');
    }
}
