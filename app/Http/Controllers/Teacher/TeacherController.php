<?php
namespace App\Http\Controllers\Teacher;
use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Http\Request;
class TeacherController extends Controller
{
    public function index()
    {
        $data = Teacher::orderBy('first_name')->paginate(10);
        return view('admin.Teacher.index', compact('data'));
    }
    public function create()
    {
        return view('admin.Teacher.create');
    }
    public function store(Request $request)
    {
        try {
            $t = Teacher::create($request->all());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['id'=>$t->id,'first_name'=>$t->first_name,'last_name'=>$t->last_name,'email'=>$t->email ?? '','department'=>$t->department ?? '']);
            }
            return redirect()->route('admin.teachers.index')->with('success','Teacher created');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message'=>$e->getMessage()],422);
            }
            return redirect()->back()->with('error',$e->getMessage());
        }
    }
    public function edit($id)
    {
        $data = Teacher::findOrFail($id);
        return view('admin.Teacher.edit', compact('data'));
    }
    public function update(Request $request, $id)
    {
        $item = Teacher::findOrFail($id);
        $item->update($request->all());
        return redirect()->route('admin.teachers.index')->with('success','Teacher updated');
    }
    public function destroy($id)
    {
        Teacher::destroy($id);
        return redirect()->route('admin.teachers.index')->with('success','Deleted');
    }
}
