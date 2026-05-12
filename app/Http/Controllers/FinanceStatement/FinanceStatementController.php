<?php
namespace App\Http\Controllers\FinanceStatement;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FinanceStatement;

class FinanceStatementController extends Controller
{
    public function index() { $data = FinanceStatement::latest()->paginate(20); return view('admin.FinanceStatement.index', compact('data')); }
    public function create() { return view('admin.FinanceStatement.create'); }
    public function store(Request $r) { FinanceStatement::create($r->all()); return redirect()->route("admin.finance-statements.index")->with('success','Created successfully'); }
    public function show(FinanceStatement $item) { return view('admin.FinanceStatement.show', compact('item')); }
    public function edit(FinanceStatement $item) { return view('admin.FinanceStatement.edit', compact('item')); }
    public function update(Request $r, FinanceStatement $item) { $item->update($r->all()); return redirect()->route("admin.finance-statements.index")->with('success','Updated successfully'); }
    public function destroy(FinanceStatement $item) { $item->delete(); return back()->with('success','Deleted successfully'); }
}