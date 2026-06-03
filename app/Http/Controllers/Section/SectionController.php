<?php
namespace App\Http\Controllers\Section;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Section;
use App\Models\Classroom;
use App\Models\Teacher;

class SectionController extends Controller {
    public function index(Request $r)
    {
        $q = Section::with(['classRoom','teacher']);
        if ($r->filled('search')) {
            $s = $r->search;
            $q->where('name', 'LIKE', "%$s%")->orWhereHas('classRoom', function($x) use ($s) {
                $x->where('name', 'LIKE', "%$s%");
            });
        }
        if ($r->filled('class_id')) $q->where('class_id', $r->class_id);
        $data = $q->latest()->paginate(20);
        $totalSections = Section::count();
        $classes = Classroom::orderBy('numeric_name')->orderBy('name')->get();
        return view('admin.Section.index', compact('data', 'totalSections', 'classes'));
    }

    public function create()
    {
        $classes = Classroom::orderBy('numeric_name')->orderBy('name')->get();
        $teachers = Teacher::orderBy('full_name')->get();
        return view('admin.Section.create', compact('classes','teachers'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'class_id' => 'required|exists:classes,id',
            'name' => 'required|string|max:255',
            'max_students' => 'nullable|integer|min:1',
            'teacher_id' => 'nullable|exists:teachers,id',
        ]);
        $section = Section::create($r->only(['class_id','name','max_students','teacher_id']));
        // Recalculate parent classroom capacity
        $class = Classroom::find($r->class_id);
        if ($class) $class->recalculateCapacity();
        return redirect()->route('admin.sections.index')->with('success','Section created successfully');
    }

    public function show(Section $section)
    {
        $section->load(['classRoom','teacher']);
        return view('admin.Section.show', compact('section'));
    }

    public function edit(Section $section)
    {
        $classes = Classroom::orderBy('numeric_name')->orderBy('name')->get();
        $teachers = Teacher::orderBy('full_name')->get();
        return view('admin.Section.edit', compact('section','classes','teachers'));
    }

    public function update(Request $r, Section $section)
    {
        $r->validate([
            'class_id' => 'required|exists:classes,id',
            'name' => 'required|string|max:255',
            'max_students' => 'nullable|integer|min:1',
            'teacher_id' => 'nullable|exists:teachers,id',
        ]);
        $oldClassId = $section->class_id;
        $section->update($r->only(['class_id','name','max_students','teacher_id']));
        // Recalculate capacity for old and new classroom
        if ($oldClassId != $r->class_id) {
            $oldClass = Classroom::find($oldClassId);
            if ($oldClass) $oldClass->recalculateCapacity();
        }
        $newClass = Classroom::find($r->class_id);
        if ($newClass) $newClass->recalculateCapacity();
        return redirect()->route('admin.sections.index')->with('success','Section updated successfully');
    }

    public function destroy(Section $section) {
        $class = Classroom::find($section->class_id);
        $section->delete();
        if ($class) $class->recalculateCapacity();
        return back()->with('success','Section deleted successfully');
    }
}
