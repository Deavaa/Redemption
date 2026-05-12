<?php
namespace App\Http\Controllers\Payroll;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payroll;

class PayrollController extends Controller
{
    public function index() { $data = Payroll::latest()->paginate(20); return view('admin.Payroll.index', compact('data')); }
    public function create() { return view('admin.Payroll.create'); }
    public function store(Request $r) { Payroll::create($r->all()); return redirect()->route("admin.payrolls.index")->with('success','Created successfully'); }
    public function show(Payroll $item) { return view('admin.Payroll.show', compact('item')); }
    public function edit(Payroll $item) { return view('admin.Payroll.edit', compact('item')); }
    public function update(Request $r, Payroll $item) { $item->update($r->all()); return redirect()->route("admin.payrolls.index")->with('success','Updated successfully'); }
    public function destroy(Payroll $item) { $item->delete(); return back()->with('success','Deleted successfully'); }
}