<?php
namespace App\Http\Controllers\Subject;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subject;

class SubjectController extends Controller
{
    public function index() { $data = Subject::latest()->paginate(20); return view('admin.Subject.index', compact('data')); }
    public function create() { return view('admin.Subject.create'); }
    public function store(Request $r) { Subject::create($r->all()); return redirect()->route('admin.subjects.index')->with('success','Created successfully'); }
    public function edit(Subject $subject) { return view('admin.Subject.edit', ['data' => $subject]); }
    public function update(Request $r, Subject $subject) { $subject->update($r->all()); return redirect()->route('admin.subjects.index')->with('success','Updated successfully'); }
    public function destroy(Subject $subject) { $subject->delete(); return back()->with('success','Deleted successfully'); }
}