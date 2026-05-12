<?php
 $file = 'app/Http/Controllers/Section/SectionController.php';
 $c = <<<'PHP'
<?php
namespace App\Http\Controllers\Section;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Section;
class SectionController extends Controller {
public function index(){ $data=Section::latest()->paginate(20); return view('admin.Section.index',compact('data')); }
public function create(){ return view('admin.Section.create'); }
public function store(Request $r){ Section::create($r->all()); return redirect()->back()->with('section_success','Section created successfully'); }
public function show(Section $item){ return view('admin.Section.show',compact('item')); }
public function edit(Section $item){ return view('admin.Section.edit',compact('item')); }
public function update(Request $r,Section $item){
  $item->update($r->all());
  if($r->ajax()||$r->wantsJson()){return response()->json(['success'=>true,'name'=>$item->name,'capacity'=>$item->capacity]);}
  return redirect()->back()->with('section_success','Section updated');
}
public function destroy(Section $item){ $item->delete(); return back()->with('section_success','Section deleted'); }
}
PHP;
file_put_contents($file,$c);
echo "DONE: SectionController updated\n";
