<?php
namespace App\Http\Controllers\FinanceStatement;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FinanceStatement;
use App\Models\AcademicYear;
use App\Models\Branch;

class FinanceStatementController extends Controller
{
    public function index(Request $r)
    {
        $q = FinanceStatement::with(['academicYear','branch']);
        if ($r->filled('search')) {
            $s = $r->search;
            $q->where('statement_type', 'LIKE', "%$s%")->orWhere('description', 'LIKE', "%$s%");
        }
        if ($r->filled('academic_year_id')) $q->where('academic_year_id', $r->academic_year_id);
        $data = $q->latest()->paginate(20);
        $totalStatements = FinanceStatement::count();
        $totalIncome = FinanceStatement::sum('total_income');
        $totalExpense = FinanceStatement::sum('total_expense');
        $academicYears = AcademicYear::orderBy('name')->get();
        return view('admin.FinanceStatement.index', compact('data', 'totalStatements', 'totalIncome', 'totalExpense', 'academicYears'));
    }

    public function create()
    {
        $academicYears = AcademicYear::orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();
        return view('admin.FinanceStatement.create', compact('academicYears', 'branches'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'branch_id' => 'required|exists:branches,id',
            'statement_type' => 'required|in:income,expense,summary,trial_balance',
            'period_from' => 'required|date',
            'period_to' => 'required|date|after_or_equal:period_from',
            'total_income' => 'nullable|numeric|min:0',
            'total_expense' => 'nullable|numeric|min:0',
            'net_balance' => 'nullable|numeric',
            'description' => 'nullable|string',
        ]);
        FinanceStatement::create($r->only(['academic_year_id','branch_id','statement_type','period_from','period_to','total_income','total_expense','net_balance','description']));
        return redirect()->route("admin.finance-statements.index")->with('success','Statement created successfully');
    }

    public function show(FinanceStatement $finance_statement)
    {
        $finance_statement->load(['academicYear','branch']);
        return view('admin.FinanceStatement.show', ['item' => $finance_statement]);
    }

    public function edit(FinanceStatement $finance_statement)
    {
        $academicYears = AcademicYear::orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();
        return view('admin.FinanceStatement.edit', ['item' => $finance_statement, 'academicYears' => $academicYears, 'branches' => $branches]);
    }

    public function update(Request $r, FinanceStatement $finance_statement)
    {
        $r->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'branch_id' => 'required|exists:branches,id',
            'statement_type' => 'required|in:income,expense,summary,trial_balance',
            'period_from' => 'required|date',
            'period_to' => 'required|date|after_or_equal:period_from',
            'total_income' => 'nullable|numeric|min:0',
            'total_expense' => 'nullable|numeric|min:0',
            'net_balance' => 'nullable|numeric',
            'description' => 'nullable|string',
        ]);
        $finance_statement->update($r->only(['academic_year_id','branch_id','statement_type','period_from','period_to','total_income','total_expense','net_balance','description']));
        return redirect()->route("admin.finance-statements.index")->with('success','Statement updated successfully');
    }

    public function destroy(FinanceStatement $finance_statement)
    {
        $finance_statement->delete();
        return back()->with('success','Statement deleted successfully');
    }
}
