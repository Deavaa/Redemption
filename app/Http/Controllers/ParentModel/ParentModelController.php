<?php
namespace App\Http\Controllers\ParentModel;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ParentModel;
class ParentModelController extends Controller
{
    public function index(){
        $data = ParentModel::latest()->paginate(20);
        $totalParents = ParentModel::count();
        return view("admin.ParentModel.index", compact("data","totalParents"));
    }
    public function create(){ return view("admin.ParentModel.create"); }
    public function store(Request $r){
        $r->validate(["father_name"=>"required","father_phone"=>"required"]);
        ParentModel::create($r->all());
        return redirect()->route("admin.parents.index")->with("success","Parent added");
    }
    public function show(ParentModel $parent){ return view("admin.ParentModel.show", ["item" => $parent]); }
    public function edit(ParentModel $parent){ return view("admin.ParentModel.edit", ["item" => $parent]); }
    public function update(Request $r, ParentModel $parent){
        $r->validate(["father_name"=>"required","father_phone"=>"required"]);
        $parent->update($r->all());
        return redirect()->route("admin.parents.index")->with("success","Updated");
    }
    public function destroy(ParentModel $parent){ $parent->delete(); return back()->with("success","Deleted"); }
}