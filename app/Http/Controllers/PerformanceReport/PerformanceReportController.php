<?php
namespace App\Http\Controllers\PerformanceReport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PerformanceReport;

class PerformanceReportController extends Controller
{
    public function index() { $data = PerformanceReport::latest()->paginate(20); return view('admin.PerformanceReport.index', compact('data')); }
    public function create() { return view('admin.PerformanceReport.create'); }
    public function store(Request $r) { PerformanceReport::create($r->all()); return redirect()->route("admin.performance-reports.index")->with('success','Created successfully'); }
    public function show(PerformanceReport $item) { return view('admin.PerformanceReport.show', compact('item')); }
    public function edit(PerformanceReport $item) { return view('admin.PerformanceReport.edit', compact('item')); }
    public function update(Request $r, PerformanceReport $item) { $item->update($r->all()); return redirect()->route("admin.performance-reports.index")->with('success','Updated successfully'); }
    public function destroy(PerformanceReport $item) { $item->delete(); return back()->with('success','Deleted successfully'); }
}