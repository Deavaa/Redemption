<?php
namespace App\Http\Controllers\IncomeExpense;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IncomeExpense;

class IncomeExpenseController extends Controller
{
    public function index() { $data = IncomeExpense::latest()->paginate(20); return view('admin.IncomeExpense.index', compact('data')); }
    public function create() { return view('admin.IncomeExpense.create'); }
    public function store(Request $r) { IncomeExpense::create($r->all()); return redirect()->route("admin.income-expenses.index")->with('success','Created successfully'); }
    public function show(IncomeExpense $item) { return view('admin.IncomeExpense.show', compact('item')); }
    public function edit(IncomeExpense $item) { return view('admin.IncomeExpense.edit', compact('item')); }
    public function update(Request $r, IncomeExpense $item) { $item->update($r->all()); return redirect()->route("admin.income-expenses.index")->with('success','Updated successfully'); }
    public function destroy(IncomeExpense $item) { $item->delete(); return back()->with('success','Deleted successfully'); }
}