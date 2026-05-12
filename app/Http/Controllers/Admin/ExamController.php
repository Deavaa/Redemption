<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\Exam;
use Illuminate\Http\Request;
class ExamController extends Controller
{
    public function index() { $exams=Exam::with(['academicYear','term'])->orderBy('id','desc')->get(); return view('admin.exams.index',compact('exams')); }
    public function create() { $academicYears=AcademicYear::orderBy('id','desc')->get(); $allTerms=Term::orderBy('id','asc')->get(); return view('admin.exams.create',compact('academicYears','allTerms')); }
    public function store(Request $request) {
        $request->validate(['academic_year_id'=>'required|exists:academic_years,id','term_id'=>'required|exists:terms,id','name'=>'required|string|max:255','type'=>'nullable|string|max:100','start_date'=>'required|date','end_date'=>'required|date|after_or_equal:start_date','start_time'=>'nullable','end_time'=>'nullable','total_marks'=>'nullable|integer|min:0','passing_marks'=>'nullable|integer|min:0','description'=>'nullable|string']);
        Exam::create($request->all());
        return redirect()->route('admin.exams.index')->with('success','Exam created successfully.');
    }
    public function edit(Exam $exam) { $academicYears=AcademicYear::orderBy('id','desc')->get(); $allTerms=Term::orderBy('id','asc')->get(); return view('admin.exams.edit',compact('exam','academicYears','allTerms')); }
    public function update(Request $request, Exam $exam) {
        $request->validate(['academic_year_id'=>'required|exists:academic_years,id','term_id'=>'required|exists:terms,id','name'=>'required|string|max:255','type'=>'nullable|string|max:100','start_date'=>'required|date','end_date'=>'required|date|after_or_equal:start_date','start_time'=>'nullable','end_time'=>'nullable','total_marks'=>'nullable|integer|min:0','passing_marks'=>'nullable|integer|min:0','description'=>'nullable|string']);
        $exam->update($request->all());
        return redirect()->route('admin.exams.index')->with('success','Exam updated successfully.');
    }
    public function destroy(Exam $exam) { $exam->delete(); return redirect()->route('admin.exams.index')->with('success','Exam deleted.'); }
}