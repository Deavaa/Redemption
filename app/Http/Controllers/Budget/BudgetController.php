<?php
namespace App\Http\Controllers\Budget;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Budget;

class BudgetController extends Controller
{
    public function index() { $data = Budget::latest()->paginate(20); return view('admin.Budget.index', compact('data')); }
    public function create() { return view('admin.Budget.create'); }
    public function store(Request $r) { Budget::create($r->all()); return redirect()->route("admin.budgets.index")->with('success','Created successfully'); }
    public function show(Budget $item) { return view('admin.Budget.show', compact('item')); }
    public function edit(Budget $item) { return view('admin.Budget.edit', compact('item')); }
    public function update(Request $r, Budget $item) { $item->update($r->all()); return redirect()->route("admin.budgets.index")->with('success','Updated successfully'); }
    public function destroy(Budget $item) { $item->delete(); return back()->with('success','Deleted successfully'); }
}