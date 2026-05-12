<?php
namespace App\Http\Controllers\Branch;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Branch;

class BranchController extends Controller
{
    public function index() { $data = Branch::latest()->paginate(20); return view('admin.Branch.index', compact('data')); }
    public function create() { return view('admin.Branch.create'); }
    public function store(Request $r) { Branch::create($r->all()); return redirect()->route("admin.branches.index")->with('success','Created successfully'); }
    public function show(Branch $item) { return view('admin.Branch.show', compact('item')); }
    public function edit(Branch $item) { return view('admin.Branch.edit', compact('item')); }
    public function update(Request $r, Branch $item) { $item->update($r->all()); return redirect()->route("admin.branches.index")->with('success','Updated successfully'); }
    public function destroy(Branch $item) { $item->delete(); return back()->with('success','Deleted successfully'); }
}