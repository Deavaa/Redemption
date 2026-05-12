<?php
namespace App\Http\Controllers\ClassAsset;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClassAsset;
use App\Models\Classroom;
use App\Models\Section;
class ClassAssetController extends Controller
{
    public function index()
    {
        $data = ClassAsset::with("classroom", "section")->latest()->paginate(20);
        $totalAssets = ClassAsset::count();
        $totalValue = ClassAsset::sum("purchase_price");
        $classrooms = Classroom::orderBy("name")->get();
        return view("admin.ClassAsset.index", compact("data", "totalAssets", "totalValue", "classrooms"));
    }

    public function create()
    {
        $classrooms = Classroom::with("sections")->orderBy("name")->get();
        return view("admin.ClassAsset.create", compact("classrooms"));
    }

    public function store(Request $r)
    {
        $r->validate([
            "name" => "required|string|max:255",
            "class_id" => "required|exists:classes,id",
            "section_id" => "nullable|exists:sections,id",
            "quantity" => "required|integer|min:1",
            "condition" => "required|in:new,good,fair,poor,damaged",
            "purchase_date" => "nullable|date",
            "purchase_price" => "nullable|numeric|min:0",
            "description" => "nullable|string|max:500",
        ]);
        ClassAsset::create($r->all());
        return redirect()->route("admin.class-assets.index")->with("success", "Asset registered successfully");
    }

    public function show(ClassAsset $item) { return view("admin.ClassAsset.show", compact("item")); }

    public function edit(ClassAsset $item)
    {
        $classrooms = Classroom::with("sections")->orderBy("name")->get();
        return view("admin.ClassAsset.edit", compact("item", "classrooms"));
    }

    public function update(Request $r, ClassAsset $item)
    {
        $r->validate([
            "name" => "required|string|max:255",
            "class_id" => "required|exists:classes,id",
            "section_id" => "nullable|exists:sections,id",
            "quantity" => "required|integer|min:1",
            "condition" => "required|in:new,good,fair,poor,damaged",
            "purchase_date" => "nullable|date",
            "purchase_price" => "nullable|numeric|min:0",
            "description" => "nullable|string|max:500",
        ]);
        $item->update($r->all());
        return redirect()->route("admin.class-assets.index")->with("success", "Asset updated successfully");
    }

    public function destroy(ClassAsset $item)
    {
        $item->delete();
        return back()->with("success", "Asset deleted successfully");
    }

    public function getAssetsByClass($classId)
    {
        $assets = ClassAsset::with("section")->where("class_id", $classId)->get();
        return response()->json($assets->map(function($a) {
            return [
                "id" => $a->id,
                "name" => $a->name,
                "quantity" => $a->quantity,
                "condition" => $a->condition,
                "section_name" => $a->section ? $a->section->name : null,
            ];
        }));
    }

    public function getSectionsByClass($classId)
    {
        $sections = Section::where("class_id", $classId)->orderBy("name")->get();
        return response()->json($sections);
    }
}