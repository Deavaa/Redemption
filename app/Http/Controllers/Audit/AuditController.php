<?php
namespace App\Http\Controllers\Audit;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Audit;
use App\Models\AcademicYear;
use App\Models\Branch;

class AuditController extends Controller
{
    public function index(Request $r)
    {
        $q = Audit::with(['academicYear','branch']);
        if ($r->filled('search')) {
            $s = $r->search;
            $q->where('auditor_name', 'LIKE', "%$s%")->orWhere('findings', 'LIKE', "%$s%");
        }
        if ($r->filled('status')) $q->where('status', $r->status);
        if ($r->filled('academic_year_id')) $q->where('academic_year_id', $r->academic_year_id);
        $data = $q->latest()->paginate(20);
        $totalAudits = Audit::count();
        $academicYears = AcademicYear::orderBy('name')->get();
        return view('admin.Audit.index', compact('data', 'totalAudits', 'academicYears'));
    }

    public function create()
    {
        $academicYears = AcademicYear::orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();
        return view('admin.Audit.create', compact('academicYears', 'branches'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'branch_id' => 'required|exists:branches,id',
            'auditor_name' => 'required|string|max:255',
            'audit_date' => 'required|date',
            'findings' => 'required|string',
            'recommendations' => 'nullable|string',
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
        ]);
        Audit::create($r->only(['academic_year_id','branch_id','auditor_name','audit_date','findings','recommendations','status']));
        return redirect()->route("admin.audits.index")->with('success','Audit created successfully');
    }

    public function show(Audit $item)
    {
        $item->load(['academicYear','branch']);
        return view('admin.Audit.show', compact('item'));
    }

    public function edit(Audit $item)
    {
        $academicYears = AcademicYear::orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();
        return view('admin.Audit.edit', compact('item', 'academicYears', 'branches'));
    }

    public function update(Request $r, Audit $item)
    {
        $r->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'branch_id' => 'required|exists:branches,id',
            'auditor_name' => 'required|string|max:255',
            'audit_date' => 'required|date',
            'findings' => 'required|string',
            'recommendations' => 'nullable|string',
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
        ]);
        $item->update($r->only(['academic_year_id','branch_id','auditor_name','audit_date','findings','recommendations','status']));
        return redirect()->route("admin.audits.index")->with('success','Audit updated successfully');
    }

    public function destroy(Audit $item)
    {
        $item->delete();
        return back()->with('success','Audit deleted successfully');
    }
}
