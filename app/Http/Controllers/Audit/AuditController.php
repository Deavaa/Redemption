<?php
namespace App\Http\Controllers\Audit;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Audit;

class AuditController extends Controller
{
    public function index() { $data = Audit::latest()->paginate(20); return view('admin.Audit.index', compact('data')); }
    public function create() { return view('admin.Audit.create'); }
    public function store(Request $r) { Audit::create($r->all()); return redirect()->route("admin.audits.index")->with('success','Created successfully'); }
    public function show(Audit $item) { return view('admin.Audit.show', compact('item')); }
    public function edit(Audit $item) { return view('admin.Audit.edit', compact('item')); }
    public function update(Request $r, Audit $item) { $item->update($r->all()); return redirect()->route("admin.audits.index")->with('success','Updated successfully'); }
    public function destroy(Audit $item) { $item->delete(); return back()->with('success','Deleted successfully'); }
}