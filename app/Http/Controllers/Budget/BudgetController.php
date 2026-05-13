<?php
namespace App\Http\Controllers\Budget;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Budget;
use App\Models\AcademicYear;

class BudgetController extends Controller
{
    public function index(Request $r)
    {
        $q = Budget::with('academicYear');
        if ($r->filled('search')) {
            $s = $r->search;
            $q->where('category', 'LIKE', "%$s%")->orWhere('description', 'LIKE', "%$s%");
        }
        if ($r->filled('status')) $q->where('status', $r->status);
        if ($r->filled('academic_year_id')) $q->where('academic_year_id', $r->academic_year_id);
        $data = $q->latest()->paginate(20);
        $totalBudgets = Budget::count();
        $totalAllocated = Budget::sum('allocated_amount');
        $totalSpent = Budget::sum('spent_amount');
        $academicYears = AcademicYear::orderBy('name')->get();
        return view('admin.Budget.index', compact('data', 'totalBudgets', 'totalAllocated', 'totalSpent', 'academicYears'));
    }

    public function create()
    {
        $academicYears = AcademicYear::orderBy('name')->get();
        return view('admin.Budget.create', compact('academicYears'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'category' => 'required|string|max:255',
            'allocated_amount' => 'required|numeric|min:0',
            'spent_amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:planned,approved,active,completed,cancelled',
        ]);
        Budget::create($r->only(['academic_year_id','category','allocated_amount','spent_amount','description','status']));
        return redirect()->route("admin.budgets.index")->with('success','Budget created successfully');
    }

    public function show(Budget $budget)
    {
        $budget->load('academicYear');
        return view('admin.Budget.show', ['item' => $budget]);
    }

    public function edit(Budget $budget)
    {
        $academicYears = AcademicYear::orderBy('name')->get();
        return view('admin.Budget.edit', ['item' => $budget, 'academicYears' => $academicYears]);
    }

    public function update(Request $r, Budget $budget)
    {
        $r->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'category' => 'required|string|max:255',
            'allocated_amount' => 'required|numeric|min:0',
            'spent_amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:planned,approved,active,completed,cancelled',
        ]);
        $budget->update($r->only(['academic_year_id','category','allocated_amount','spent_amount','description','status']));
        return redirect()->route("admin.budgets.index")->with('success','Budget updated successfully');
    }

    public function destroy(Budget $budget)
    {
        $budget->delete();
        return back()->with('success','Budget deleted successfully');
    }
}
