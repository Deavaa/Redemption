<?php
namespace App\Http\Controllers\Payroll;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payroll;
use App\Models\User;

class PayrollController extends Controller
{
    public function index(Request $r)
    {
        $q = Payroll::with('employee');
        if ($r->filled('search')) {
            $s = $r->search;
            $q->whereHas('employee', function($x) use ($s) {
                $x->where('name', 'LIKE', "%$s%");
            })->orWhere('pay_period', 'LIKE', "%$s%");
        }
        if ($r->filled('status')) $q->where('status', $r->status);
        $data = $q->latest()->paginate(20);
        $totalPayrolls = Payroll::count();
        $totalNetSalary = Payroll::sum('net_salary');
        $employees = User::whereIn('role', ['admin','teacher','staff'])->orderBy('name')->get();
        return view('admin.Payroll.index', compact('data', 'totalPayrolls', 'totalNetSalary', 'employees'));
    }

    public function create()
    {
        $employees = User::whereIn('role', ['admin','teacher','staff'])->orderBy('name')->get();
        return view('admin.Payroll.create', compact('employees'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'employee_id' => 'required|exists:users,id',
            'basic_salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'net_salary' => 'required|numeric',
            'pay_period' => 'required|string|max:255',
            'payment_date' => 'required|date',
            'status' => 'required|in:pending,paid,cancelled',
        ]);
        Payroll::create($r->only(['employee_id','basic_salary','allowances','deductions','tax','net_salary','pay_period','payment_date','status']));
        return redirect()->route("admin.payrolls.index")->with('success','Payroll created successfully');
    }

    public function show(Payroll $item)
    {
        $item->load('employee');
        return view('admin.Payroll.show', compact('item'));
    }

    public function edit(Payroll $item)
    {
        $employees = User::whereIn('role', ['admin','teacher','staff'])->orderBy('name')->get();
        return view('admin.Payroll.edit', compact('item', 'employees'));
    }

    public function update(Request $r, Payroll $item)
    {
        $r->validate([
            'employee_id' => 'required|exists:users,id',
            'basic_salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'net_salary' => 'required|numeric',
            'pay_period' => 'required|string|max:255',
            'payment_date' => 'required|date',
            'status' => 'required|in:pending,paid,cancelled',
        ]);
        $item->update($r->only(['employee_id','basic_salary','allowances','deductions','tax','net_salary','pay_period','payment_date','status']));
        return redirect()->route("admin.payrolls.index")->with('success','Payroll updated successfully');
    }

    public function destroy(Payroll $item) { $item->delete(); return back()->with('success','Payroll deleted successfully'); }
}
