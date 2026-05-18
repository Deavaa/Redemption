<?php
namespace App\Http\Controllers\PerformanceReport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PerformanceReport;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\Term;

class PerformanceReportController extends Controller
{
    public function index(Request $r)
    {
        $q = PerformanceReport::with(['student','academicYear','term']);
        if ($r->filled('search')) {
            $s = $r->search;
            $q->whereHas('student', function($x) use ($s) {
                $x->where('full_name', 'LIKE', "%$s%");
            });
        }
        if ($r->filled('academic_year_id')) $q->where('academic_year_id', $r->academic_year_id);
        $data = $q->latest()->paginate(20);
        $totalReports = PerformanceReport::count();
        $academicYears = AcademicYear::orderBy('name')->get();
        return view('admin.PerformanceReport.index', compact('data', 'totalReports', 'academicYears'));
    }

    public function create()
    {
        $students = Student::where('status', 'active')->orderBy('full_name')->get();
        $academicYears = AcademicYear::orderBy('name')->get();
        $terms = Term::orderBy('name')->get();
        return view('admin.PerformanceReport.create', compact('students', 'academicYears', 'terms'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'student_id' => 'required|exists:students,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'nullable|exists:terms,id',
            'attendance_percentage' => 'nullable|numeric|min:0|max:100',
            'behavior_rating' => 'nullable|numeric|min:0|max:10',
            'sports_rating' => 'nullable|numeric|min:0|max:10',
            'extracurricular_rating' => 'nullable|numeric|min:0|max:10',
            'overall_rating' => 'nullable|numeric|min:0|max:10',
            'remarks' => 'nullable|string',
        ]);
        PerformanceReport::create($r->only(['student_id','academic_year_id','term_id','attendance_percentage','behavior_rating','sports_rating','extracurricular_rating','overall_rating','remarks']));
        return redirect()->route("admin.performance-reports.index")->with('success','Report created successfully');
    }

    public function show(PerformanceReport $performance_report)
    {
        $performance_report->load(['student','academicYear','term']);
        return view('admin.PerformanceReport.show', ['item' => $performance_report]);
    }

    public function edit(PerformanceReport $performance_report)
    {
        $students = Student::where('status', 'active')->orderBy('full_name')->get();
        $academicYears = AcademicYear::orderBy('name')->get();
        $terms = Term::orderBy('name')->get();
        return view('admin.PerformanceReport.edit', ['item' => $performance_report, 'students' => $students, 'academicYears' => $academicYears, 'terms' => $terms]);
    }

    public function update(Request $r, PerformanceReport $performance_report)
    {
        $r->validate([
            'student_id' => 'required|exists:students,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'nullable|exists:terms,id',
            'attendance_percentage' => 'nullable|numeric|min:0|max:100',
            'behavior_rating' => 'nullable|numeric|min:0|max:10',
            'sports_rating' => 'nullable|numeric|min:0|max:10',
            'extracurricular_rating' => 'nullable|numeric|min:0|max:10',
            'overall_rating' => 'nullable|numeric|min:0|max:10',
            'remarks' => 'nullable|string',
        ]);
        $performance_report->update($r->only(['student_id','academic_year_id','term_id','attendance_percentage','behavior_rating','sports_rating','extracurricular_rating','overall_rating','remarks']));
        return redirect()->route("admin.performance-reports.index")->with('success','Report updated successfully');
    }

    public function destroy(PerformanceReport $performance_report) { $performance_report->delete(); return back()->with('success','Report deleted successfully'); }
}
