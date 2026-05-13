<?php
namespace App\Http\Controllers\Leave;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Leave;
use App\Models\User;

class LeaveController extends Controller
{
    public function index(Request $r)
    {
        $q = Leave::with(['employee','approver']);
        if ($r->filled('search')) {
            $s = $r->search;
            $q->whereHas('employee', function($x) use ($s) {
                $x->where('name', 'LIKE', "%$s%");
            })->orWhere('reason', 'LIKE', "%$s%");
        }
        if ($r->filled('status')) $q->where('status', $r->status);
        if ($r->filled('leave_type')) $q->where('leave_type', $r->leave_type);
        $data = $q->latest()->paginate(20);
        $totalLeaves = Leave::count();
        $pendingCount = Leave::where('status', 'pending')->count();
        return view('admin.Leave.index', compact('data', 'totalLeaves', 'pendingCount'));
    }

    public function create()
    {
        $employees = User::whereIn('role', ['admin','teacher','staff'])->orderBy('name')->get();
        return view('admin.Leave.create', compact('employees'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'employee_id' => 'required|exists:users,id',
            'leave_type' => 'required|in:sick,casual,annual,maternity,paternity,unpaid,other',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'total_days' => 'required|numeric|min:0.5',
            'reason' => 'required|string',
            'status' => 'required|in:pending,approved,rejected',
        ]);
        Leave::create($r->only(['employee_id','leave_type','start_date','end_date','total_days','reason','status']));
        return redirect()->route("admin.leaves.index")->with('success','Leave request created successfully');
    }

    public function show(Leave $leave)
    {
        $leave->load(['employee','approver']);
        return view('admin.Leave.show', ['item' => $leave]);
    }

    public function edit(Leave $leave)
    {
        $employees = User::whereIn('role', ['admin','teacher','staff'])->orderBy('name')->get();
        return view('admin.Leave.edit', ['item' => $leave, 'employees' => $employees]);
    }

    public function update(Request $r, Leave $leave)
    {
        $r->validate([
            'employee_id' => 'required|exists:users,id',
            'leave_type' => 'required|in:sick,casual,annual,maternity,paternity,unpaid,other',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'total_days' => 'required|numeric|min:0.5',
            'reason' => 'required|string',
            'status' => 'required|in:pending,approved,rejected',
        ]);
        $data = $r->only(['employee_id','leave_type','start_date','end_date','total_days','reason','status']);
        if ($r->filled('status') && $r->status !== 'pending') {
            $data['approved_by'] = auth()->id();
        }
        $leave->update($data);
        return redirect()->route("admin.leaves.index")->with('success','Leave updated successfully');
    }

    public function destroy(Leave $leave) { $leave->delete(); return back()->with('success','Leave deleted successfully'); }
}
