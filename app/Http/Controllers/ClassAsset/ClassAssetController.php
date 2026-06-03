<?php
namespace App\Http\Controllers\ClassAsset;
use App\Http\Controllers\Controller;
use App\Models\ClassAsset;
use App\Models\ClassRoom;
use App\Models\Section;
use Illuminate\Http\Request;

class ClassAssetController extends Controller
{
    public function index(Request $r)
    {
        $q = ClassAsset::with(['classroom', 'section']);
        if ($r->filled('search')) {
            $s = $r->search;
            $q->where('name', 'LIKE', "%$s%")->orWhere('condition', 'LIKE', "%$s%");
        }
        if ($r->filled('class_id')) $q->where('class_id', $r->class_id);
        if ($r->filled('section_id')) $q->where('section_id', $r->section_id);
        if ($r->filled('condition')) $q->where('condition', $r->condition);

        $data = $q->orderBy('name')->paginate(20);
        $classes = ClassRoom::orderBy('numeric_name')->orderBy('name')->get();

        $totalAssets = ClassAsset::count();
        $totalValue = ClassAsset::sum('purchase_price');
        $goodCondition = ClassAsset::where('condition', 'Good')->count();
        $needsRepair = ClassAsset::where('condition', 'Needs Repair')->count();

        return view('admin.ClassAsset.index', compact('data', 'classes', 'totalAssets', 'totalValue', 'goodCondition', 'needsRepair'));
    }

    public function create()
    {
        $classes = ClassRoom::orderBy('numeric_name')->orderBy('name')->get();
        $sections = collect();
        return view('admin.ClassAsset.create', compact('classes', 'sections'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'condition' => 'required|in:Good,Fair,Needs Repair,Damaged',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        ClassAsset::create($r->only(['class_id', 'section_id', 'name', 'quantity', 'condition', 'purchase_date', 'purchase_price', 'description']));
        return redirect()->route('admin.class-assets.index')->with('success', 'Asset added successfully.');
    }

    public function show(ClassAsset $class_asset)
    {
        $item = $class_asset->load(['classroom', 'section']);
        return view('admin.ClassAsset.show', ['item' => $item]);
    }

    public function edit(ClassAsset $class_asset)
    {
        $classes = ClassRoom::orderBy('numeric_name')->orderBy('name')->get();
        $sections = Section::where('class_id', $class_asset->class_id)->orderBy('name')->get();
        return view('admin.ClassAsset.edit', compact('class_asset', 'classes', 'sections'));
    }

    public function update(Request $r, ClassAsset $class_asset)
    {
        $r->validate([
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'condition' => 'required|in:Good,Fair,Needs Repair,Damaged',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $class_asset->update($r->only(['class_id', 'section_id', 'name', 'quantity', 'condition', 'purchase_date', 'purchase_price', 'description']));
        return redirect()->route('admin.class-assets.index')->with('success', 'Asset updated successfully.');
    }

    public function destroy(ClassAsset $class_asset)
    {
        $class_asset->delete();
        return redirect()->route('admin.class-assets.index')->with('success', 'Asset deleted successfully.');
    }

    public function apiSections(Request $r)
    {
        return response()->json(
            Section::where('class_id', $r->class_id)->orderBy('name')->get(['id', 'name'])
        );
    }
}
