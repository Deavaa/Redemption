<?php
namespace App\Http\Controllers\Subject;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subject;

class SubjectController extends Controller
{
    public function index(Request $r)
    {
        $q = Subject::query();
        if ($r->filled('search')) {
            $s = $r->search;
            $q->where('name', 'LIKE', "%$s%")->orWhere('code', 'LIKE', "%$s%");
        }
        if ($r->filled('type')) $q->where('type', $r->type);
        $data = $q->latest()->paginate(20);
        $totalSubjects = Subject::count();
        return view('admin.Subject.index', compact('data', 'totalSubjects'));
    }

    public function create() { return view('admin.Subject.create'); }

    public function store(Request $r)
    {
        $r->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:subjects,code',
            'type' => 'required|in:core,elective,optional',
            'description' => 'nullable|string|max:500',
        ]);
        Subject::create($r->only(['name','code','type','description']));
        return redirect()->route('admin.subjects.index')->with('success','Subject created successfully');
    }

    public function show(Subject $subject)
    {
        $subject->load('assignments');
        return view('admin.Subject.show', compact('subject'));
    }

    public function edit(Subject $subject) { return view('admin.Subject.edit', ['data' => $subject]); }

    public function update(Request $r, Subject $subject)
    {
        $r->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:subjects,code,' . $subject->id,
            'type' => 'required|in:core,elective,optional',
            'description' => 'nullable|string|max:500',
        ]);
        $subject->update($r->only(['name','code','type','description']));
        return redirect()->route('admin.subjects.index')->with('success','Subject updated successfully');
    }

    public function destroy(Subject $subject) { $subject->delete(); return back()->with('success','Subject deleted successfully'); }
}
