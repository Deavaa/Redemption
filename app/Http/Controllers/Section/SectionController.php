<?php
namespace App\Http\Controllers\Section;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Section;
use App\Models\ClassRoom;
use App\Models\Teacher;

class SectionController extends Controller {
    public function index() { $data = Section::with(['classroom','teacher'])->latest()->paginate(20); return view('admin.Section.index', compact('data')); }
    public function create() { $classes = ClassRoom::orderBy('name')->get(); $teachers = Teacher::orderBy('first_name')->get(); return view('admin.Section.create', compact('classes','teachers')); }
    public function store(Request $r) { Section::create($r->all()); return redirect()->route('admin.sections.index')->with('success','Created'); }
    public function edit(Section $section) { $classes = ClassRoom::orderBy('name')->get(); $teachers = Teacher::orderBy('first_name')->get(); return view('admin.Section.edit', compact('section','classes','teachers')); }
    public function update(Request $r, Section $section) { $section->update($r->all()); return redirect()->route('admin.sections.index')->with('success','Updated'); }
    public function destroy(Section $section) { $section->delete(); return back()->with('success','Deleted'); }
}
