<?php
namespace App\Http\Controllers\TeacherAssignment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TeacherAssignment;

class TeacherAssignmentController extends Controller
{
    public function index() { $data = TeacherAssignment::latest()->paginate(20); return view('admin.TeacherAssignment.index', compact('data')); }
    public function create() { return view('admin.TeacherAssignment.create'); }
    public function store(Request $r) { TeacherAssignment::create($r->all()); return redirect()->route("admin.teacher-assignments.index")->with('success','Created successfully'); }
    public function show(TeacherAssignment $item) { return view('admin.TeacherAssignment.show', compact('item')); }
    public function edit(TeacherAssignment $item) { return view('admin.TeacherAssignment.edit', compact('item')); }
    public function update(Request $r, TeacherAssignment $item) { $item->update($r->all()); return redirect()->route("admin.teacher-assignments.index")->with('success','Updated successfully'); }
    public function destroy(TeacherAssignment $item) { $item->delete(); return back()->with('success','Deleted successfully'); }
}