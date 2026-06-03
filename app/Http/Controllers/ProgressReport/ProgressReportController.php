<?php
namespace App\Http\Controllers\ProgressReport;
use App\Http\Controllers\Controller;
use App\Models\ProgressReport;
use App\Models\ProgressReportSubject;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\Classroom;
use Illuminate\Http\Request;

class ProgressReportController extends Controller
{
    public function index(Request $r)
    {
        $q = ProgressReport::with(["student","academicYear","term","classroom"]);
        if ($r->filled("search")) {
            $s = $r->search;
            $q->whereHas("student", function($x) use ($s) {
                $x->where("full_name", "LIKE", "%$s%");
            });
        }
        if ($r->filled("academic_year_id")) $q->where("academic_year_id", $r->academic_year_id);
        if ($r->filled("status")) $q->where("status", $r->status);
        $reports = $q->latest()->paginate(15);
        $totalReports = ProgressReport::count();
        return view("admin.ProgressReport.index", compact("reports", "totalReports"));
    }

    public function create()
    {
        $s = Student::where("status", "active")->orderBy("full_name")->get();
        $ay = AcademicYear::orderBy("name")->get();
        $t = Term::orderBy("name")->get();
        $c = Classroom::orderBy("numeric_name")->orderBy("name")->get();
        return view("admin.ProgressReport.create", compact("s", "ay", "t", "c"));
    }

    public function store(Request $r)
    {
        $v = $r->validate([
            "student_id" => "required|exists:students,id",
            "academic_year_id" => "required|exists:academic_years,id",
            "term_id" => "required|exists:terms,id",
            "class_id" => "required|exists:classes,id",
            "overall_grade" => "nullable|string|max:10",
            "total_marks" => "nullable|numeric",
            "max_marks" => "nullable|numeric",
            "class_rank" => "nullable|integer",
            "remarks" => "nullable|string",
            "teacher_comment" => "nullable|string",
            "status" => "nullable|in:draft,published",
            "subject_names" => "nullable|array",
            "subject_marks" => "nullable|array",
            "subject_max" => "nullable|array",
            "subject_grades" => "nullable|array",
        ]);

        $rep = ProgressReport::create([
            "student_id" => $v["student_id"],
            "academic_year_id" => $v["academic_year_id"],
            "term_id" => $v["term_id"],
            "class_id" => $v["class_id"],
            "overall_grade" => $v["overall_grade"] ?? null,
            "total_marks" => $v["total_marks"] ?? 0,
            "max_marks" => $v["max_marks"] ?? 100,
            "class_rank" => $v["class_rank"] ?? null,
            "remarks" => $v["remarks"] ?? null,
            "teacher_comment" => $v["teacher_comment"] ?? null,
            "status" => $v["status"] ?? "draft",
        ]);

        if (!empty($v["subject_names"])) {
            foreach ($v["subject_names"] as $i => $n) {
                if (empty($n)) continue;
                ProgressReportSubject::create([
                    "progress_report_id" => $rep->id,
                    "subject_name" => $n,
                    "marks_obtained" => $v["subject_marks"][$i] ?? 0,
                    "max_marks" => $v["subject_max"][$i] ?? 100,
                    "grade" => $v["subject_grades"][$i] ?? null,
                ]);
            }
        }

        return redirect()->route("admin.progress-reports.index")->with("success", "Report created successfully");
    }

    public function show(ProgressReport $progress_report)
    {
        $progress_report->load(['student','academicYear','term','classroom','subjects']);
        return view("admin.ProgressReport.show", compact("progress_report"));
    }

    public function edit(ProgressReport $progress_report)
    {
        $progress_report->load("subjects");
        $s = Student::where("status", "active")->orderBy("full_name")->get();
        $ay = AcademicYear::orderBy("name")->get();
        $t = Term::orderBy("name")->get();
        $c = Classroom::orderBy("numeric_name")->orderBy("name")->get();
        return view("admin.ProgressReport.edit", compact("progress_report", "s", "ay", "t", "c"));
    }

    public function update(Request $r, ProgressReport $progress_report)
    {
        $v = $r->validate([
            "student_id" => "required|exists:students,id",
            "academic_year_id" => "required|exists:academic_years,id",
            "term_id" => "required|exists:terms,id",
            "class_id" => "required|exists:classes,id",
            "overall_grade" => "nullable|string|max:10",
            "total_marks" => "nullable|numeric",
            "max_marks" => "nullable|numeric",
            "class_rank" => "nullable|integer",
            "remarks" => "nullable|string",
            "teacher_comment" => "nullable|string",
            "status" => "nullable|in:draft,published,archived",
            "subject_names" => "nullable|array",
            "subject_marks" => "nullable|array",
            "subject_max" => "nullable|array",
            "subject_grades" => "nullable|array",
        ]);

        $progress_report->update([
            "student_id" => $v["student_id"],
            "academic_year_id" => $v["academic_year_id"],
            "term_id" => $v["term_id"],
            "class_id" => $v["class_id"],
            "overall_grade" => $v["overall_grade"] ?? null,
            "total_marks" => $v["total_marks"] ?? 0,
            "max_marks" => $v["max_marks"] ?? 100,
            "class_rank" => $v["class_rank"] ?? null,
            "remarks" => $v["remarks"] ?? null,
            "teacher_comment" => $v["teacher_comment"] ?? null,
            "status" => $v["status"] ?? "draft",
        ]);

        $progress_report->subjects()->delete();
        if (!empty($v["subject_names"])) {
            foreach ($v["subject_names"] as $i => $n) {
                if (empty($n)) continue;
                ProgressReportSubject::create([
                    "progress_report_id" => $progress_report->id,
                    "subject_name" => $n,
                    "marks_obtained" => $v["subject_marks"][$i] ?? 0,
                    "max_marks" => $v["subject_max"][$i] ?? 100,
                    "grade" => $v["subject_grades"][$i] ?? null,
                ]);
            }
        }

        return redirect()->route("admin.progress-reports.index")->with("success", "Report updated successfully");
    }

    public function destroy(ProgressReport $progress_report)
    {
        $progress_report->subjects()->delete();
        $progress_report->delete();
        return redirect()->route("admin.progress-reports.index")->with("success", "Report deleted successfully");
    }
}
