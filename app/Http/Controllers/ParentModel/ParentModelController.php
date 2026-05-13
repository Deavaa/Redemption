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
    public function show(ParentModel $item){ return view("admin.ParentModel.show", compact("item")); }
    public function edit(ParentModel $item){ return view("admin.ParentModel.edit", compact("item")); }
    public function update(Request $r, ParentModel $item){
        $r->validate(["father_name"=>"required","father_phone"=>"required"]);
        $item->update($r->all());
        return redirect()->route("admin.parents.index")->with("success","Updated");
    }
    public function destroy(ParentModel $item){ $item->delete(); return back()->with("success","Deleted"); }
}