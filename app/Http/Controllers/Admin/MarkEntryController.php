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
use Illuminate\Support\Facades\Log;
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

        // Sort alphabetically by first_name then last_name
        $students = $query->orderBy('first_name', 'asc')->orderBy('last_name', 'asc')->get();

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
            ->orderBy('students.first_name','asc')->orderBy('students.last_name','asc')
            ->select('students.id as student_id',DB::raw("CONCAT(students.first_name, ' ', students.last_name) as student_name"),'students.roll_number','students.gender')->get();
        $existingMarks = MarkEntry::where('academic_year_id',$ayId)->where('term_id',$termId)
            ->where('class_id',$classId)->where('section_id',$sectionId)->where('subject_id',$subjectId)->get()->keyBy('student_id');
        $markFields = MarkEntry::getMarkFields();
        $caFields = ['ca1','ca2','ca3','ca4','ca5','ca6','ca7','ca8','ca9','ca10','conduct','handwriting','creativity'];
        $examFields = ['test1','test2','mid_term','final_exam'];
        $rows = [];
        foreach ($students as $s) {
            $mark = $existingMarks->get($s->student_id);
            $row = ['student_id'=>$s->student_id,'student_name'=>$s->student_name??'Unknown','roll_number'=>$s->roll_number,'gender'=>$s->gender];
            foreach ($markFields as $field) { $col=$field['col']; $row[$col]=$mark?floatval($mark->$col):null; }
            // Recalculate totals from raw fields (don't trust stored values)
            if ($mark) {
                $caRaw = 0;
                foreach ($caFields as $f) { $caRaw += floatval($mark->$f ?? 0); }
                $examRaw = 0;
                foreach ($examFields as $f) { $examRaw += floatval($mark->$f ?? 0); }
                $row['ca_total'] = round(($caRaw / 70) * 30, 2);
                $row['exam_total'] = min($examRaw, 70);
                $row['grand_total'] = round($row['ca_total'] + $row['exam_total'], 2);
            } else {
                $row['ca_total'] = 0;
                $row['exam_total'] = 0;
                $row['grand_total'] = 0;
            }
            $rows[]=$row;
        }
        $subject=Subject::find($subjectId); $term=Term::find($termId); $class=ClassRoom::find($classId); $section=Section::find($sectionId);
        return response()->json(['students'=>$rows,'markFields'=>$markFields,
            'subject'=>$subject?$subject->name:'','term'=>$term?$term->name:'',
            'class'=>$class?$class->name:'','section'=>$section?$section->name:'']);
    }

    public function apiSave(Request $request) {
        // Relaxed validation — auto-save sends minimal data
        $request->validate([
            'student_id' => 'required',
            'subject_id' => 'required',
            'term_id' => 'required',
        ]);

        $studentId = $request->input('student_id');
        $subjectId = $request->input('subject_id');
        $ayId = $request->input('academic_year_id') ?: null;
        $termId = $request->input('term_id');

        // Collect only the mark fields that were actually sent
        $markFieldNames = ['ca1','ca2','ca3','ca4','ca5','ca6','ca7','ca8','ca9','ca10',
            'conduct','handwriting','creativity','test1','test2','mid_term','final_exam'];
        $data = [];
        $data['student_id'] = $studentId;
        $data['subject_id'] = $subjectId;
        $data['academic_year_id'] = $ayId;
        $data['term_id'] = $termId;

        // Copy explicit fields from request
        foreach (['class_id','section_id','class_grade','section','exam_id','grade','remarks'] as $f) {
            if ($request->has($f)) {
                $data[$f] = $request->input($f) ?: null;
            }
        }

        // Resolve class_id and section_id from class_grade/section if not provided
        if (empty($data['class_id']) && !empty($data['class_grade'])) {
            $classRoom = ClassRoom::where('name', $data['class_grade'])->first();
            if ($classRoom) {
                $data['class_id'] = $classRoom->id;
            }
        }
        if (empty($data['section_id']) && !empty($data['section']) && !empty($data['class_id'])) {
            $sectionModel = Section::where('class_id', $data['class_id'])
                ->where('name', $data['section'])->first();
            if ($sectionModel) {
                $data['section_id'] = $sectionModel->id;
            }
        }

        // Set teacher_id — ONLY use teacher profile ID (avoid FK violation with users table)
        $teacherId = null;
        if (auth()->check()) {
            $teacher = \App\Models\Teacher::where('email', auth()->user()->email)->first();
            if ($teacher) {
                $teacherId = $teacher->id;
            }
        }
        $data['teacher_id'] = $teacherId;

        // ── Load existing record FIRST so calcTotals has complete data ──
        $existingQuery = MarkEntry::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->where('term_id', $termId);
        if (!empty($ayId)) {
            $existingQuery->where('academic_year_id', $ayId);
        } else {
            $existingQuery->whereNull('academic_year_id');
        }
        $existing = $existingQuery->first();

        // Seed $data with existing values so calcTotals works with ALL fields
        if ($existing) {
            foreach ($markFieldNames as $f) {
                if (!isset($data[$f])) {
                    $data[$f] = $existing->$f;
                }
            }
        }

        // Handle single-field auto-save (mark_key + mark_value) — override the one field
        if ($request->filled('mark_key') && $request->has('mark_value')) {
            $markKey = $request->mark_key;
            $markValue = $request->mark_value;
            $data[$markKey] = ($markValue === '' || $markValue === null) ? null : $markValue;
        } else {
            // Full save — copy all mark fields from request
            foreach ($markFieldNames as $f) {
                if ($request->has($f)) {
                    $val = $request->input($f);
                    $data[$f] = ($val === '' || $val === null) ? null : $val;
                }
            }
        }

        // Calculate totals — now $data has ALL mark fields, not just the changed one
        $data = MarkEntry::calcTotals($data);

        // Set marks_obtained = grand_total for backward compatibility (column is NOT NULL in original migration)
        $data['marks_obtained'] = $data['grand_total'] ?? 0;

        try {
            // $existing was already loaded above (before calcTotals)
            if ($existing) {
                // Only update the fields we actually have data for
                $updateData = array_filter($data, function($value, $key) use ($markFieldNames) {
                    // Always update these core fields
                    if (in_array($key, ['student_id','subject_id','academic_year_id','term_id',
                        'class_id','section_id','teacher_id','marks_obtained',
                        'ca_total','exam_total','grand_total'])) return true;
                    // Update mark fields only if they were explicitly set
                    if (in_array($key, $markFieldNames)) return true;
                    // Update other fields like class_grade, section, etc.
                    if (in_array($key, ['class_grade','section','exam_id','grade','remarks'])) return true;
                    return false;
                }, ARRAY_FILTER_USE_BOTH);

                $existing->update($updateData);
                $entry = $existing->fresh();
            } else {
                // Set defaults for required fields on create
                if (!isset($data['marks_obtained'])) $data['marks_obtained'] = 0;
                $entry = MarkEntry::create($data);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('MarkEntry save error: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage(),
            ], 500);
        } catch (\Throwable $e) {
            Log::error('MarkEntry save exception: ' . $e->getMessage(), [
                'class' => get_class($e),
                'data' => $data
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'entry' => $entry,
            'ca_total' => $entry->ca_total,
            'exam_total' => $entry->exam_total,
            'grand_total' => $entry->grand_total,
        ]);
    }
}
