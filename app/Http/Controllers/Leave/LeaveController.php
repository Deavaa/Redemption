<?php
namespace App\Http\Controllers\Leave;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Leave;

class LeaveController extends Controller
{
    public function index() { $data = Leave::latest()->paginate(20); return view('admin.Leave.index', compact('data')); }
    public function create() { return view('admin.Leave.create'); }
    public function store(Request $r) { Leave::create($r->all()); return redirect()->route("admin.leaves.index")->with('success','Created successfully'); }
    public function show(Leave $item) { return view('admin.Leave.show', compact('item')); }
    public function edit(Leave $item) { return view('admin.Leave.edit', compact('item')); }
    public function update(Request $r, Leave $item) { $item->update($r->all()); return redirect()->route("admin.leaves.index")->with('success','Updated successfully'); }
    public function destroy(Leave $item) { $item->delete(); return back()->with('success','Deleted successfully'); }
}