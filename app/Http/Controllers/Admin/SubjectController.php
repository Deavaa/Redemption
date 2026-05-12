<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
class SubjectController extends Controller
{
    public function index() { $subjects=Subject::orderBy('name','asc')->get(); return view('admin.subjects.index',compact('subjects')); }
    public function create() { return view('admin.subjects.create'); }
    public function store(Request $request) {
        $request->validate(['name'=>'required|string|max:255','code'=>'nullable|string|max:50|unique:subjects,code','type'=>'nullable|string|max:100','description'=>'nullable|string']);
        Subject::create($request->all());
        return redirect()->route('admin.subjects.index')->with('success','Subject created.');
    }
    public function edit(Subject $subject) { return view('admin.subjects.edit',compact('subject')); }
    public function update(Request $request, Subject $subject) {
        $request->validate(['name'=>'required|string|max:255','code'=>'nullable|string|max:50|unique:subjects,code,'.$subject->id,'type'=>'nullable|string|max:100','description'=>'nullable|string']);
        $subject->update($request->all());
        return redirect()->route('admin.subjects.index')->with('success','Subject updated.');
    }
    public function destroy(Subject $subject) { $subject->delete(); return redirect()->route('admin.subjects.index')->with('success','Subject deleted.'); }
}