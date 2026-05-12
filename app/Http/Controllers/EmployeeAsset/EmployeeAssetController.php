<?php
namespace App\Http\Controllers\EmployeeAsset;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmployeeAsset;

class EmployeeAssetController extends Controller
{
    public function index() { $data = EmployeeAsset::latest()->paginate(20); return view('admin.EmployeeAsset.index', compact('data')); }
    public function create() { return view('admin.EmployeeAsset.create'); }
    public function store(Request $r) { EmployeeAsset::create($r->all()); return redirect()->route("admin.employee-assets.index")->with('success','Created successfully'); }
    public function show(EmployeeAsset $item) { return view('admin.EmployeeAsset.show', compact('item')); }
    public function edit(EmployeeAsset $item) { return view('admin.EmployeeAsset.edit', compact('item')); }
    public function update(Request $r, EmployeeAsset $item) { $item->update($r->all()); return redirect()->route("admin.employee-assets.index")->with('success','Updated successfully'); }
    public function destroy(EmployeeAsset $item) { $item->delete(); return back()->with('success','Deleted successfully'); }
}