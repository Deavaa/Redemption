<?php
namespace App\Http\Controllers\IncomeExpense;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IncomeExpense;
use App\Models\AcademicYear;
use App\Models\Branch;

class IncomeExpenseController extends Controller
{
    public function index(Request $r)
    {
        $q = IncomeExpense::with(['academicYear','branch']);
        if ($r->filled('search')) {
            $s = $r->search;
            $q->where('category', 'LIKE', "%$s%")->orWhere('description', 'LIKE', "%$s%")->orWhere('reference', 'LIKE', "%$s%");
        }
        if ($r->filled('type')) $q->where('type', $r->type);
        if ($r->filled('academic_year_id')) $q->where('academic_year_id', $r->academic_year_id);
        $data = $q->latest()->paginate(20);
        $totalRecords = IncomeExpense::count();
        $totalIncome = IncomeExpense::where('type', 'income')->sum('amount');
        $totalExpense = IncomeExpense::where('type', 'expense')->sum('amount');
        $academicYears = AcademicYear::orderBy('name')->get();
        return view('admin.IncomeExpense.index', compact('data', 'totalRecords', 'totalIncome', 'totalExpense', 'academicYears'));
    }

    public function create()
    {
        $academicYears = AcademicYear::orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();
        return view('admin.IncomeExpense.create', compact('academicYears', 'branches'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'type' => 'required|in:income,expense',
            'category' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'reference' => 'nullable|string|max:255',
            'branch_id' => 'nullable|exists:branches,id',
        ]);
        IncomeExpense::create($r->only(['academic_year_id','type','category','amount','date','description','reference','branch_id']));
        return redirect()->route("admin.income-expenses.index")->with('success','Record created successfully');
    }

    public function show(IncomeExpense $income_expense)
    {
        $income_expense->load(['academicYear','branch']);
        return view('admin.IncomeExpense.show', ['item' => $income_expense]);
    }

    public function edit(IncomeExpense $income_expense)
    {
        $academicYears = AcademicYear::orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();
        return view('admin.IncomeExpense.edit', ['item' => $income_expense, 'academicYears' => $academicYears, 'branches' => $branches]);
    }

    public function update(Request $r, IncomeExpense $income_expense)
    {
        $r->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'type' => 'required|in:income,expense',
            'category' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'reference' => 'nullable|string|max:255',
            'branch_id' => 'nullable|exists:branches,id',
        ]);
        $income_expense->update($r->only(['academic_year_id','type','category','amount','date','description','reference','branch_id']));
        return redirect()->route("admin.income-expenses.index")->with('success','Record updated successfully');
    }

    public function destroy(IncomeExpense $income_expense) { $income_expense->delete(); return back()->with('success','Record deleted successfully'); }
}
