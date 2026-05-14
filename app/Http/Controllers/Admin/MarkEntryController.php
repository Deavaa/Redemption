<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\ClassRoom;
use App\Models\Section;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\Student;
use App\Models\MarkEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class MarkEntryController extends Controller
{
    public function index() {
        $academicYears = AcademicYear::orderBy('id','desc')->get();
        $currentAy = $academicYears->first();
        $terms = $currentAy ? Term::where('academic_year_id', $currentAy->id)->orderBy('id','asc')->get() : collect();
        $currentTerm = $terms->first();
        $sections = Section::with('classRoom')->orderBy('class_id','asc')->orderBy('name','asc')->get();
        return view('admin.mark-entries.index', compact('academicYears', 'terms', 'sections', 'currentAy', 'currentTerm'));
    }
    public function apiClasses() {
        return response()->json(ClassRoom::orderBy('name','asc')->get(['id','name']));
    }
    public function apiTerms(Request $request) {
        $ayId = $request->query('academic_year_id');
        if (!$ayId) return response()->json([]);
        return response()->json(Term::where('academic_year_id',$ayId)->orderBy('id','asc')->get(['id','name']));
    }
    public function apiSections(Request $request) {
        $classId = $request->query('class_id');
        $classGrade = $request->query('class_grade');

        if ($classId) {
            return response()->json(Section::where('class_id',$classId)->orderBy('name','asc')->get(['id','name']));
        }

        if ($classGrade) {
            $sections = Student::where('class_grade',$classGrade)
                ->whereNotNull('section')
                ->where('section','<>','')
                ->distinct()
                ->orderBy('section','asc')
                ->pluck('section');
            return response()->json($sections);
        }

        return response()->json([]);
    }
    public function apiSubjects(Request $request) {
        $classId = $request->query('class_id');
        $sectionId = $request->query('section_id');
        $ayId = $request->query('academic_year_id');
        if (!$classId || !$sectionId) return response()->json([]);
        $query = TeacherAssignment::with('subject')->where('class_id',$classId)
            ->where(function($q) use ($sectionId) { $q->where('section_id',$sectionId)->orWhereNull('section_id'); });
        if ($ayId) {
            $query->where(function($q) use ($ayId) {
                $q->whereNull('academic_year_id')->orWhere('academic_year_id',$ayId);
            });
        }
        $assignments = $query->get();
        $subjects = $assignments->map(function($a) {
            $subj = $a->subject;
            if (!$subj) return null;
            return ['id'=>$subj->id,'name'=>$subj->name,'code'=>$subj->code??'','type'=>strtolower($subj->type??''),'priority'=>$subj->priority??0,'is_core'=>is_null($a->section_id)];
        })->filter();
        return response()->json($subjects->sortBy('priority')->unique('id')->values());
    }

    public function apiStudents(Request $request) {
        $query = Student::where('status', 'active');

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        if ($request->filled('class_grade')) {
            $query->where('class_grade', $request->class_grade);
        }
        if ($request->filled('section')) {
            $query->where('section', $request->section);
        }

        $students = $query->orderBy('last_name')->orderBy('first_name')->get();

        $subjectId = $request->get('subject_id');
        $ayId = $request->get('academic_year_id');
        $termId = $request->get('term_id');
        $marks = [];

        if ($subjectId) {
            $mq = MarkEntry::where('subject_id', $subjectId);
            if ($ayId) $mq->where('academic_year_id', $ayId);
            if ($termId) $mq->where('term_id', $termId);
            foreach ($mq->get() as $m) {
                $marks[$m->student_id] = $m;
            }
        }

        $studentsData = [];
        $marksData = [];

        foreach ($students as $student) {
            $studentsData[] = [
                'id' => $student->id,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'admission_number' => $student->admission_number,
                'roll_number' => $student->roll_number,
                'class_grade' => $student->class_grade,
                'section' => $student->section,
            ];

            if (isset($marks[$student->id])) {
                $marksData[$student->id] = $marks[$student->id]->only([
                    'ca1','ca2','ca3','ca4','ca5','ca6','ca7','ca8','ca9','ca10',
                    'conduct','handwriting','creativity','test1','test2','mid_term','final_exam'
                ]);
            }
        }

        return response()->json(['students' => $studentsData, 'marks' => $marksData]);
    }

    public function apiLoadStudents(Request $request) {
        $ayId=$request->query('academic_year_id'); $termId=$request->query('term_id');
        $classId=$request->query('class_id'); $sectionId=$request->query('section_id'); $subjectId=$request->query('subject_id');
        if (!$ayId||!$termId||!$classId||!$sectionId||!$subjectId) return response()->json(['error'=>'All filters required'],400);
        $students = DB::table('students')
            ->where('students.class_id',$classId)->where('students.section_id',$sectionId)->where('students.academic_year_id',$ayId)
            ->orderBy('students.last_name','asc')->orderBy('students.first_name','asc')
            ->select('students.id as student_id',DB::raw("CONCAT(students.first_name, ' ', students.last_name) as student_name"),'students.roll_number','students.gender')->get();
        $existingMarks = MarkEntry::where('academic_year_id',$ayId)->where('term_id',$termId)
            ->where('class_id',$classId)->where('section_id',$sectionId)->where('subject_id',$subjectId)->get()->keyBy('student_id');
        $markFields = MarkEntry::getMarkFields();
        $rows = [];
        foreach ($students as $s) {
            $mark = $existingMarks->get($s->student_id);
            $row = ['student_id'=>$s->student_id,'student_name'=>$s->student_name??'Unknown','roll_number'=>$s->roll_number,'gender'=>$s->gender];
            foreach ($markFields as $field) { $col=$field['col']; $row[$col]=$mark?floatval($mark->$col):null; }
            $row['ca_total']=$mark?floatval($mark->ca_total):0;
            $row['exam_total']=$mark?floatval($mark->exam_total):0;
            $row['grand_total']=$mark?floatval($mark->grand_total):0;
            $rows[]=$row;
        }
        $subject=Subject::find($subjectId); $term=Term::find($termId); $class=ClassRoom::find($classId); $section=Section::find($sectionId);
        return response()->json(['students'=>$rows,'markFields'=>$markFields,
            'subject'=>$subject?$subject->name:'','term'=>$term?$term->name:'',
            'class'=>$class?$class->name:'','section'=>$section?$section->name:'']);
    }
    public function apiSave(Request $request) {
        $request->validate(['student_id'=>'required|numeric','subject_id'=>'required|numeric',
            'academic_year_id'=>'nullable|numeric','term_id'=>'required|numeric']);
        $data = $request->only('student_id','subject_id','academic_year_id','term_id','class_id','section_id','class_grade','section',
            'ca1','ca2','ca3','ca4','ca5','ca6','ca7','ca8','ca9','ca10','conduct','handwriting','creativity','test1','test2','mid_term','final_exam');
        if ($request->filled('mark_key') && $request->filled('mark_value')) {
            $data[$request->mark_key] = $request->mark_value;
        }
        if (auth()->check()) $data['teacher_id']=auth()->id();
        $data = MarkEntry::calcTotals($data);
        $entry = MarkEntry::updateOrCreate(['student_id'=>$data['student_id'],'subject_id'=>$data['subject_id'],
            'academic_year_id'=>$data['academic_year_id']??null,'term_id'=>$data['term_id']??null], $data);
        return response()->json(['success'=>true,'entry'=>$entry,'ca_total'=>$entry->ca_total,'exam_total'=>$entry->exam_total,'grand_total'=>$entry->grand_total]);
    }
}