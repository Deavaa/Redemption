<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\ClassRoom;
use App\Models\Section;
use App\Models\Subject;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
class SubjectAssignmentController extends Controller
{
    public function index(Request $request) {
        $branchScope = $request->attributes->get('branch_scope');
        $query=TeacherAssignment::with(['subject','classRoom','section','teacher','academicYear']);
        if ($request->filled('academic_year_id')) $query->where('academic_year_id',$request->academic_year_id);
        if ($request->filled('class_id')) $query->where('class_id',$request->class_id);
        $assignments=$query->orderBy('class_id')->orderBy('section_id')->orderBy('subject_id')->get();
        $coreAssignments=$assignments->whereNull('section_id');
        $electiveAssignments=$assignments->whereNotNull('section_id');
        $academicYears=AcademicYear::orderBy('id','desc')->get();
        $classes=ClassRoom::when($branchScope,fn($q)=>$q->where('branch_id',$branchScope))->orderBy('numeric_name','asc')->orderBy('name','asc')->get();
        return view('admin.subject-assignments.index',compact('assignments','coreAssignments','electiveAssignments','academicYears','classes'));
    }
    public function create(Request $request) {
        $branchScope = $request->attributes->get('branch_scope');
        $academicYears=AcademicYear::orderBy('id','desc')->get();
        $classes=ClassRoom::with(['branch','sections'])->when($branchScope,fn($q)=>$q->where('branch_id',$branchScope))->orderBy('numeric_name','asc')->orderBy('name','asc')->get();
        $subjects=Subject::orderBy('name','asc')->get();
        $teachers=Teacher::when($branchScope,fn($q)=>$q->where('branch_id',$branchScope))->orderBy('full_name')->select('id','full_name')->get();
        return view('admin.subject-assignments.create',compact('academicYears','classes','subjects','teachers'));
    }
    public function store(Request $request) {
        $request->validate([
            'academic_year_id'=>'required|exists:academic_years,id',
            'class_id'=>'required|exists:classes,id',
            'subject_ids'=>'required|array|min:1',
            'subject_ids.*'=>'exists:subjects,id',
            'teacher_id'=>'nullable|exists:teachers,id',
            'assignment_type'=>'required|in:core,elective',
        ]);
        if ($request->assignment_type==='elective') $request->validate(['section_ids'=>'required|array|min:1','section_ids.*'=>'exists:sections,id']);
        $ayId=$request->academic_year_id;
        $classId=$request->class_id;
        $teacherId=$request->teacher_id;
        $type=$request->assignment_type;
        $created=0;
        $skipped=0;
        $sectionIds=$type==='elective'?$request->section_ids:[null];
        foreach ($request->subject_ids as $subjectId) {
            foreach ($sectionIds as $sectionId) {
                $exists=TeacherAssignment::where('academic_year_id',$ayId)
                    ->where('subject_id',$subjectId)
                    ->where('class_id',$classId)
                    ->where(function($q)use($sectionId){
                        if($sectionId===null)$q->whereNull('section_id');
                        else$q->where('section_id',$sectionId);
                    })->exists();
                if($exists){$skipped++;continue;}
                TeacherAssignment::create([
                    'academic_year_id'=>$ayId,
                    'subject_id'=>$subjectId,
                    'class_id'=>$classId,
                    'section_id'=>$sectionId,
                    'teacher_id'=>$teacherId,
                ]);
                $created++;
            }
        }
        if($created>0){
            $msg="Created $created assignment(s) for class (type: $type).";
            if($skipped>0)$msg.=" Skipped $skipped duplicate(s).";
        }else{
            $msg="No new assignments created (all $skipped were duplicates).";
        }
        return redirect()->route('admin.subject-assignments.index')->with('success',$msg);
    }
    public function edit(Request $request, TeacherAssignment $subject_assignment) {
        $branchScope = $request->attributes->get('branch_scope');
        $academicYears=AcademicYear::orderBy('id','desc')->get(); $classes=ClassRoom::with('branch')->when($branchScope,fn($q)=>$q->where('branch_id',$branchScope))->orderBy('numeric_name','asc')->orderBy('name','asc')->get();
        $subjects=Subject::orderBy('name','asc')->get();
        $sections=Section::where('class_id',$subject_assignment->class_id)->orderBy('name','asc')->get();
        $teachers=Teacher::when($branchScope,fn($q)=>$q->where('branch_id',$branchScope))->orderBy('full_name')->select('id','full_name')->get();
        $assignment=$subject_assignment;
        return view('admin.subject-assignments.edit',compact('academicYears','classes','subjects','sections','teachers','assignment'));
    }
    public function update(Request $request, TeacherAssignment $subject_assignment) {
        $request->validate(['academic_year_id'=>'required|exists:academic_years,id','subject_id'=>'required|exists:subjects,id','class_id'=>'required|exists:classes,id','section_id'=>'nullable|exists:sections,id','teacher_id'=>'nullable|exists:teachers,id']);
        $data = $request->only(['academic_year_id','subject_id','class_id','section_id']);
        if ($request->filled('teacher_id')) {
            $data['teacher_id'] = $request->teacher_id;
        }
        $subject_assignment->update($data);
        return redirect()->route('admin.subject-assignments.index')->with('success','Assignment updated.');
    }
    public function destroy(TeacherAssignment $subject_assignment) { $subject_assignment->delete(); return redirect()->route('admin.subject-assignments.index')->with('success','Assignment removed.'); }
    public function bulkDelete(Request $request) {
        $ids=$request->input('ids',[]);
        if (!empty($ids)) { TeacherAssignment::whereIn('id',$ids)->delete(); return redirect()->route('admin.subject-assignments.index')->with('success',count($ids).' deleted.'); }
        return redirect()->route('admin.subject-assignments.index');
    }
    public function apiClasses() { return response()->json(ClassRoom::orderBy('numeric_name','asc')->orderBy('name','asc')->get(['id','name'])); }
    public function apiSections(Request $request) {
        $classId=$request->query('class_id'); if (!$classId) return response()->json([]);
        return response()->json(Section::where('class_id',$classId)->orderBy('name','asc')->get(['id','name']));
    }
    public function apiExisting(Request $request) {
        $classId=$request->query('class_id');
        $ayId=$request->query('academic_year_id');
        if(!$classId||!$ayId) return response()->json(['existing_subject_ids'=>[]]);
        $ids=TeacherAssignment::where('class_id',$classId)->where('academic_year_id',$ayId)->pluck('subject_id')->unique()->values()->all();
        return response()->json(['existing_subject_ids'=>$ids]);
    }
}