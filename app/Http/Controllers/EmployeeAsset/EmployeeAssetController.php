<?php
namespace App\Http\Controllers\EmployeeAsset;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmployeeAsset;
use App\Models\User;

class EmployeeAssetController extends Controller
{
    public function index(Request $r)
    {
        $q = EmployeeAsset::with('employee');
        if ($r->filled('search')) {
            $s = $r->search;
            $q->where('name', 'LIKE', "%$s%")->orWhereHas('employee', function($x) use ($s) {
                $x->where('name', 'LIKE', "%$s%");
            });
        }
        if ($r->filled('condition')) $q->where('condition', $r->condition);
        $data = $q->latest()->paginate(20);
        $totalAssets = EmployeeAsset::count();
        $employees = User::whereIn('role', ['admin','teacher','staff'])->orderBy('name')->get();
        return view('admin.EmployeeAsset.index', compact('data', 'totalAssets', 'employees'));
    }

    public function create()
    {
        $employees = User::whereIn('role', ['admin','teacher','staff'])->orderBy('name')->get();
        return view('admin.EmployeeAsset.create', compact('employees'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'employee_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'condition' => 'required|in:new,good,fair,poor,damaged',
            'issue_date' => 'required|date',
            'return_date' => 'nullable|date|after_or_equal:issue_date',
            'description' => 'nullable|string|max:500',
        ]);
        EmployeeAsset::create($r->only(['employee_id','name','quantity','condition','issue_date','return_date','description']));
        return redirect()->route("admin.employee-assets.index")->with('success','Asset assigned successfully');
    }

    public function show(EmployeeAsset $employee_asset)
    {
        $employee_asset->load('employee');
        return view('admin.EmployeeAsset.show', ['item' => $employee_asset]);
    }

    public function edit(EmployeeAsset $employee_asset)
    {
        $employees = User::whereIn('role', ['admin','teacher','staff'])->orderBy('name')->get();
        return view('admin.EmployeeAsset.edit', ['item' => $employee_asset, 'employees' => $employees]);
    }

    public function update(Request $r, EmployeeAsset $employee_asset)
    {
        $r->validate([
            'employee_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'condition' => 'required|in:new,good,fair,poor,damaged',
            'issue_date' => 'required|date',
            'return_date' => 'nullable|date|after_or_equal:issue_date',
            'description' => 'nullable|string|max:500',
        ]);
        $employee_asset->update($r->only(['employee_id','name','quantity','condition','issue_date','return_date','description']));
        return redirect()->route("admin.employee-assets.index")->with('success','Asset updated successfully');
    }

    public function destroy(EmployeeAsset $employee_asset)
    {
        $employee_asset->delete();
        return back()->with('success','Asset record deleted successfully');
    }
}
