<?php
 $file = 'app/Http/Controllers/Teacher/TeacherController.php';
 $c = <<<'PHP'
<?php
namespace App\Http\Controllers\Teacher;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Teacher;
class TeacherController extends Controller {
public function index(){ $data=Teacher::latest()->paginate(20); return view('admin.Teacher.index',compact('data')); }
public function create(){ return view('admin.Teacher.create'); }
public function store(Request $r){
  try{
    $t=Teacher::create($r->all());
    if($r->ajax()||$r->wantsJson()){
      return response()->json(['id'=>$t->id,'first_name'=>$t->first_name,'last_name'=>$t->last_name,'email'=>$t->email,'department'=>$t->department]);
    }
    return redirect()->route('admin.teachers.index')->with('success','Created successfully');
  }catch(\Exception $e){
    if($r->ajax()||$r->wantsJson()){
      return response()->json(['error'=>$e->getMessage()],422);
    }
    return back()->withErrors($e->getMessage())->withInput();
  }
}
public function show(Teacher $item){ return view('admin.Teacher.show',compact('item')); }
public function edit(Teacher $item){ return view('admin.Teacher.edit',compact('item')); }
public function update(Request $r,Teacher $item){ $item->update($r->all()); return redirect()->route('admin.teachers.index')->with('success','Updated'); }
public function destroy(Teacher $item){ $item->delete(); return back()->with('success','Deleted'); }
}
PHP;
file_put_contents($file,$c);
echo "DONE: TeacherController updated with AJAX support\n";
