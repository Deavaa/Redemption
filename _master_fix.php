<?php
/**
 * School of Redemption - Master Fix Script
 * Fixes: routes, layout, controllers, models, ALL views
 * Run: php _master_fix.php
 */
 $base = __DIR__;
echo "=== School of Redemption Master Fix ===\n\n";

// Helper function to write files
function wf($path, $content) {
    global $base;
    $full = $base . '/' . $path;
    $dir = dirname($full);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    file_put_contents($full, $content);
    echo "  OK: $path\n";
}

echo "[1/4] Writing Models...\n";
wf('app/Models/ClassRoom.php', <<<'MODEL'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class ClassRoom extends Model
{
    use HasFactory;
    protected $table = 'classes';
    protected $fillable = ['branch_id','academic_year_id','name','numeric_name','teacher_id'];
    public function sections() { return $this->hasMany(Section::class, 'class_id'); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
}
MODEL
);

wf('app/Models/Section.php', <<<'MODEL'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Section extends Model
{
    use HasFactory;
    protected $fillable = ['class_id','name','max_students','teacher_id','capacity'];
    public function classRoom() { return $this->belongsTo(ClassRoom::class, 'class_id'); }
}
MODEL
);

wf('app/Models/Subject.php', <<<'MODEL'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Subject extends Model
{
    use HasFactory;
    protected $fillable = ['name','code','type','description'];
}
MODEL
);

wf('app/Models/Term.php', <<<'MODEL'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Term extends Model
{
    use HasFactory;
    protected $fillable = ['academic_year_id','name','start_date','end_date','term_number','is_active'];
    protected $casts = ['start_date'=>'date','end_date'=>'date','is_active'=>'boolean'];
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
}
MODEL
);

wf('app/Models/Exam.php', <<<'MODEL'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Exam extends Model
{
    use HasFactory;
    protected $fillable = ['academic_year_id','term_id','name','type','start_date','end_date','start_time','end_time','total_marks','passing_marks','description','class_id','subject_id','exam_date'];
    protected $casts = ['start_date'=>'date','end_date'=>'date','exam_date'=>'date'];
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function term() { return $this->belongsTo(Term::class); }
}
MODEL
);

wf('app/Models/TeacherAssignment.php', <<<'MODEL'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class TeacherAssignment extends Model
{
    use HasFactory;
    protected $fillable = ['teacher_id','class_id','section_id','subject_id','academic_year_id'];
    public function subject() { return $this->belongsTo(Subject::class); }
    public function classRoom() { return $this->belongsTo(ClassRoom::class, 'class_id'); }
    public function section() { return $this->belongsTo(Section::class); }
    public function teacher() { return $this->belongsTo(\App\Models\User::class, 'teacher_id'); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
}
MODEL
);

wf('app/Models/MarkEntry.php', <<<'MODEL'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class MarkEntry extends Model
{
    use HasFactory;
    protected $table = 'mark_entries';
    protected $fillable = [
        'student_id','subject_id','academic_year_id','term_id','class_id','section_id','teacher_id',
        'ca1','ca2','ca3','ca4','ca5','ca6','ca7','ca8','ca9','ca10',
        'conduct','handwriting','creativity',
        'test1','test2','mid_term','final_exam',
        'ca_total','exam_total','grand_total',
    ];
    public static function getMarkFields() {
        return [
            ['col'=>'ca1','max'=>5],['col'=>'ca2','max'=>5],['col'=>'ca3','max'=>5],['col'=>'ca4','max'=>5],['col'=>'ca5','max'=>5],
            ['col'=>'ca6','max'=>5],['col'=>'ca7','max'=>5],['col'=>'ca8','max'=>5],['col'=>'ca9','max'=>5],['col'=>'ca10','max'=>5],
            ['col'=>'conduct','max'=>5],['col'=>'handwriting','max'=>5],['col'=>'creativity','max'=>10],
            ['col'=>'test1','max'=>10],['col'=>'test2','max'=>10],['col'=>'mid_term','max'=>20],['col'=>'final_exam','max'=>30],
        ];
    }
    public static function calcTotals(array $data): array {
        $caFields = ['ca1','ca2','ca3','ca4','ca5','ca6','ca7','ca8','ca9','ca10','conduct','handwriting','creativity'];
        $examFields = ['test1','test2','mid_term','final_exam'];
        $caRaw = 0;
        foreach ($caFields as $f) $caRaw += floatval($data[$f] ?? 0);
        $examRaw = 0;
        foreach ($examFields as $f) $examRaw += floatval($data[$f] ?? 0);
        $caTotal = round(($caRaw / 70) * 30, 2);
        $examTotal = min($examRaw, 70);
        $data['ca_total'] = $caTotal;
        $data['exam_total'] = $examTotal;
        $data['grand_total'] = round($caTotal + $examTotal, 2);
        return $data;
    }
    public function student() { return $this->belongsTo(Student::class); }
    public function subject() { return $this->belongsTo(Subject::class); }
    public function term() { return $this->belongsTo(Term::class); }
}
MODEL
);

echo "[2/4] Writing Controllers...\n";
wf('app/Http/Controllers/Admin/MarkEntryController.php', <<<'CTRL'
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
        return view('admin.mark-entries.index', compact('academicYears'));
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
        if (!$classId) return response()->json([]);
        return response()->json(Section::where('class_id',$classId)->orderBy('name','asc')->get(['id','name']));
    }
    public function apiSubjects(Request $request) {
        $classId = $request->query('class_id');
        $sectionId = $request->query('section_id');
        $ayId = $request->query('academic_year_id');
        if (!$classId || !$sectionId) return response()->json([]);
        $query = TeacherAssignment::with('subject')->where('class_id',$classId)
            ->where(function($q) use ($sectionId) { $q->where('section_id',$sectionId)->orWhereNull('section_id'); });
        if ($ayId) $query->where('academic_year_id',$ayId);
        $assignments = $query->get();
        $subjects = $assignments->map(function($a) {
            $subj = $a->subject;
            if (!$subj) return null;
            return ['id'=>$subj->id,'name'=>$subj->name,'code'=>$subj->code??'','type'=>strtolower($subj->type??''),'is_core'=>is_null($a->section_id)];
        })->filter();
        return response()->json($subjects->unique('id')->values());
    }
    public function apiLoadStudents(Request $request) {
        $ayId=$request->query('academic_year_id'); $termId=$request->query('term_id');
        $classId=$request->query('class_id'); $sectionId=$request->query('section_id'); $subjectId=$request->query('subject_id');
        if (!$ayId||!$termId||!$classId||!$sectionId||!$subjectId) return response()->json(['error'=>'All filters required'],400);
        $students = DB::table('students')->leftJoin('users','students.user_id','=','users.id')
            ->where('students.class_id',$classId)->where('students.section_id',$sectionId)->where('students.academic_year_id',$ayId)
            ->orderBy('users.name','asc')
            ->select('students.id as student_id','users.name as student_name','students.roll_number','students.gender')->get();
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
        $request->validate(['student_id'=>'required|integer','subject_id'=>'required|integer',
            'academic_year_id'=>'required|integer','term_id'=>'required|integer','class_id'=>'required|integer','section_id'=>'required|integer']);
        $data = $request->only('student_id','subject_id','academic_year_id','term_id','class_id','section_id',
            'ca1','ca2','ca3','ca4','ca5','ca6','ca7','ca8','ca9','ca10','conduct','handwriting','creativity','test1','test2','mid_term','final_exam');
        if (auth()->check()) $data['teacher_id']=auth()->id();
        $data = MarkEntry::calcTotals($data);
        $entry = MarkEntry::updateOrCreate(['student_id'=>$data['student_id'],'subject_id'=>$data['subject_id'],
            'academic_year_id'=>$data['academic_year_id'],'term_id'=>$data['term_id']], $data);
        return response()->json(['success'=>true,'ca_total'=>$entry->ca_total,'exam_total'=>$entry->exam_total,'grand_total'=>$entry->grand_total]);
    }
}
CTRL
);

wf('app/Http/Controllers/Admin/ExamController.php', <<<'CTRL'
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
CTRL
);

wf('app/Http/Controllers/Admin/SubjectController.php', <<<'CTRL'
<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
class SubjectController extends Controller
{
    public function index() { $subjects=Subject::orderBy('name','asc')->get(); return view('admin.subjects.index',compact('subjects')); }
    public function create() { return view('admin.subjects.create'); }
    public function store(Request $request) {
        $request->validate(['name'=>'required|string|max:255','code'=>'nullable|string|max:50|unique:subjects,code','type'=>'nullable|string|max:100','description'=>'nullable|string']);
        Subject::create($request->all());
        return redirect()->route('admin.subjects.index')->with('success','Subject created.');
    }
    public function edit(Subject $subject) { return view('admin.subjects.edit',compact('subject')); }
    public function update(Request $request, Subject $subject) {
        $request->validate(['name'=>'required|string|max:255','code'=>'nullable|string|max:50|unique:subjects,code,'.$subject->id,'type'=>'nullable|string|max:100','description'=>'nullable|string']);
        $subject->update($request->all());
        return redirect()->route('admin.subjects.index')->with('success','Subject updated.');
    }
    public function destroy(Subject $subject) { $subject->delete(); return redirect()->route('admin.subjects.index')->with('success','Subject deleted.'); }
}
CTRL
);

wf('app/Http/Controllers/Admin/StaffController.php', <<<'CTRL'
<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
class StaffController extends Controller
{
    public function index() { $staff=User::whereIn('role',['teacher','admin'])->orderBy('name','asc')->paginate(20); return view('admin.staff.index',compact('staff')); }
    public function create() { return view('admin.staff.create'); }
    public function store(Request $request) {
        $request->validate(['name'=>'required|string|max:255','email'=>'required|email|unique:users,email','phone'=>'nullable|string|max:20','role'=>'required|in:teacher,admin','password'=>'required|string|min:6|confirmed','gender'=>'nullable|string|max:10','qualification'=>'nullable|string|max:255','address'=>'nullable|string|max:500']);
        User::create(['name'=>$request->name,'email'=>$request->email,'phone'=>$request->phone,'role'=>$request->role,'password'=>Hash::make($request->password),'gender'=>$request->gender,'qualification'=>$request->qualification,'address'=>$request->address]);
        return redirect()->route('admin.staff.index')->with('success','Staff member added.');
    }
    public function edit(User $user) { return view('admin.staff.edit',compact('user')); }
    public function update(Request $request, User $user) {
        $request->validate(['name'=>'required|string|max:255','email'=>'required|email|unique:users,email,'.$user->id,'phone'=>'nullable|string|max:20','role'=>'required|in:teacher,admin','password'=>'nullable|string|min:6|confirmed','gender'=>'nullable|string|max:10','qualification'=>'nullable|string|max:255','address'=>'nullable|string|max:500']);
        $data=$request->except('password');
        if ($request->filled('password')) $data['password']=Hash::make($request->password);
        $user->update($data);
        return redirect()->route('admin.staff.index')->with('success','Staff member updated.');
    }
    public function destroy(User $user) {
        if ($user->role==='admin' && User::where('role','admin')->count()<=1) return redirect()->route('admin.staff.index')->with('error','Cannot delete last admin.');
        $user->delete();
        return redirect()->route('admin.staff.index')->with('success','Staff member removed.');
    }
}
CTRL
);

echo "[3/4] Writing Routes and Layout...\n";
wf('app/Http/Controllers/Admin/SubjectAssignmentController.php', <<<'CTRL'
<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\TeacherAssignment;
use App\Models\ClassRoom;
use App\Models\Section;
use App\Models\Subject;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class SubjectAssignmentController extends Controller
{
    public function index(Request $request) {
        $query=TeacherAssignment::with(['subject','classRoom','section','teacher','academicYear']);
        if ($request->filled('academic_year_id')) $query->where('academic_year_id',$request->academic_year_id);
        if ($request->filled('class_id')) $query->where('class_id',$request->class_id);
        $assignments=$query->orderBy('class_id')->orderBy('section_id')->orderBy('subject_id')->get();
        $coreAssignments=$assignments->whereNull('section_id');
        $electiveAssignments=$assignments->whereNotNull('section_id');
        $academicYears=AcademicYear::orderBy('id','desc')->get();
        $classes=ClassRoom::orderBy('name','asc')->get();
        return view('admin.subject-assignments.index',compact('assignments','coreAssignments','electiveAssignments','academicYears','classes'));
    }
    public function create() {
        $academicYears=AcademicYear::orderBy('id','desc')->get(); $classes=ClassRoom::orderBy('name','asc')->get();
        $subjects=Subject::orderBy('name','asc')->get();
        $teachers=DB::table('users')->whereIn('role',['teacher','admin'])->orderBy('name')->select('id','name')->get();
        return view('admin.subject-assignments.create',compact('academicYears','classes','subjects','teachers'));
    }
    public function store(Request $request) {
        $request->validate(['academic_year_id'=>'required|exists:academic_years,id','subject_id'=>'required|exists:subjects,id','class_ids'=>'required|array|min:1','class_ids.*'=>'exists:classes,id','teacher_id'=>'nullable|exists:users,id','assignment_type'=>'required|in:core,elective']);
        if ($request->assignment_type==='elective') $request->validate(['section_ids'=>'required|array|min:1','section_ids.*'=>'exists:sections,id']);
        $subject=Subject::find($request->subject_id); $ayId=$request->academic_year_id; $teacherId=$request->teacher_id;
        $subjectId=$request->subject_id; $type=$request->assignment_type; $created=0;
        foreach ($request->class_ids as $classId) {
            if ($type==='core') {
                if (!TeacherAssignment::where('academic_year_id',$ayId)->where('subject_id',$subjectId)->where('class_id',$classId)->whereNull('section_id')->exists()) {
                    TeacherAssignment::create(['academic_year_id'=>$ayId,'subject_id'=>$subjectId,'class_id'=>$classId,'section_id'=>null,'teacher_id'=>$teacherId]); $created++;
                }
            } else {
                foreach ($request->section_ids as $sectionId) {
                    if (!TeacherAssignment::where('academic_year_id',$ayId)->where('subject_id',$subjectId)->where('class_id',$classId)->where('section_id',$sectionId)->exists()) {
                        TeacherAssignment::create(['academic_year_id'=>$ayId,'subject_id'=>$subjectId,'class_id'=>$classId,'section_id'=>$sectionId,'teacher_id'=>$teacherId]); $created++;
                    }
                }
            }
        }
        $msg=$created>0?"Assigned \"$subject->name\" ($type) to $created combination(s).":"No new assignments (duplicates).";
        return redirect()->route('admin.subject-assignments.index')->with('success',$msg);
    }
    public function edit(TeacherAssignment $teacherAssignment) {
        $academicYears=AcademicYear::orderBy('id','desc')->get(); $classes=ClassRoom::orderBy('name','asc')->get();
        $subjects=Subject::orderBy('name','asc')->get();
        $sections=Section::where('class_id',$teacherAssignment->class_id)->orderBy('name','asc')->get();
        $teachers=DB::table('users')->whereIn('role',['teacher','admin'])->orderBy('name')->select('id','name')->get();
        $assignment=$teacherAssignment;
        return view('admin.subject-assignments.edit',compact('academicYears','classes','subjects','sections','teachers','assignment'));
    }
    public function update(Request $request, TeacherAssignment $teacherAssignment) {
        $request->validate(['academic_year_id'=>'required|exists:academic_years,id','subject_id'=>'required|exists:subjects,id','class_id'=>'required|exists:classes,id','section_id'=>'nullable|exists:sections,id','teacher_id'=>'nullable|exists:users,id']);
        $teacherAssignment->update($request->all());
        return redirect()->route('admin.subject-assignments.index')->with('success','Assignment updated.');
    }
    public function destroy(TeacherAssignment $teacherAssignment) { $teacherAssignment->delete(); return redirect()->route('admin.subject-assignments.index')->with('success','Assignment removed.'); }
    public function bulkDelete(Request $request) {
        $ids=$request->input('ids',[]);
        if (!empty($ids)) { TeacherAssignment::whereIn('id',$ids)->delete(); return redirect()->route('admin.subject-assignments.index')->with('success',count($ids).' deleted.'); }
        return redirect()->route('admin.subject-assignments.index');
    }
    public function apiClasses() { return response()->json(ClassRoom::orderBy('name','asc')->get(['id','name'])); }
    public function apiSections(Request $request) {
        $classId=$request->query('class_id'); if (!$classId) return response()->json([]);
        return response()->json(Section::where('class_id',$classId)->orderBy('name','asc')->get(['id','name']));
    }
}
CTRL
);

wf('routes/web.php', <<<'ROUTE'
<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\TeamMemberController;
use App\Http\Controllers\Admin\GalleryImageController;
use App\Http\Controllers\Admin\GalleryVideoController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\TermController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\MarkEntryController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\SubjectAssignmentController;
use App\Http\Controllers\Admin\StaffController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [AboutController::class, 'index'])->name('about');
Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'sendMessage'])->name('contact.send');
Route::get('/team', [TeamController::class, 'index'])->name('team');
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('academic-years', AcademicYearController::class);
    Route::resource('terms', TermController::class);
    Route::resource('exams', ExamController::class);
    Route::resource('subjects', SubjectController::class);
    Route::get('subject-assignments', [SubjectAssignmentController::class, 'index'])->name('subject-assignments.index');
    Route::get('subject-assignments/create', [SubjectAssignmentController::class, 'create'])->name('subject-assignments.create');
    Route::post('subject-assignments', [SubjectAssignmentController::class, 'store'])->name('subject-assignments.store');
    Route::get('subject-assignments/{teacherAssignment}/edit', [SubjectAssignmentController::class, 'edit'])->name('subject-assignments.edit');
    Route::put('subject-assignments/{teacherAssignment}', [SubjectAssignmentController::class, 'update'])->name('subject-assignments.update');
    Route::delete('subject-assignments/{teacherAssignment}', [SubjectAssignmentController::class, 'destroy'])->name('subject-assignments.destroy');
    Route::delete('subject-assignments/bulk-delete', [SubjectAssignmentController::class, 'bulkDelete'])->name('subject-assignments.bulk-delete');
    Route::get('subject-assignments/api/classes', [SubjectAssignmentController::class, 'apiClasses'])->name('subject-assignments.api.classes');
    Route::get('subject-assignments/api/sections', [SubjectAssignmentController::class, 'apiSections'])->name('subject-assignments.api.sections');
    Route::redirect('teacher-assignments', 'subject-assignments', 301);
    Route::redirect('teacher-assignments/create', 'subject-assignments/create', 301);
    Route::get('mark-entries', [MarkEntryController::class, 'index'])->name('mark-entries.index');
    Route::get('mark-entries/api/classes', [MarkEntryController::class, 'apiClasses'])->name('mark-entries.api.classes');
    Route::get('mark-entries/api/terms', [MarkEntryController::class, 'apiTerms'])->name('mark-entries.api.terms');
    Route::get('mark-entries/api/sections', [MarkEntryController::class, 'apiSections'])->name('mark-entries.api.sections');
    Route::get('mark-entries/api/subjects', [MarkEntryController::class, 'apiSubjects'])->name('mark-entries.api.subjects');
    Route::get('mark-entries/api/load-students', [MarkEntryController::class, 'apiLoadStudents'])->name('mark-entries.api.load-students');
    Route::post('mark-entries/api/save', [MarkEntryController::class, 'apiSave'])->name('mark-entries.api.save');
    Route::get('staff', [StaffController::class, 'index'])->name('staff.index');
    Route::get('staff/create', [StaffController::class, 'create'])->name('staff.create');
    Route::post('staff', [StaffController::class, 'store'])->name('staff.store');
    Route::get('staff/{user}/edit', [StaffController::class, 'edit'])->name('staff.edit');
    Route::put('staff/{user}', [StaffController::class, 'update'])->name('staff.update');
    Route::delete('staff/{user}', [StaffController::class, 'destroy'])->name('staff.destroy');
    Route::resource('sliders', SliderController::class);
    Route::resource('team', TeamMemberController::class);
    Route::get('/gallery', [GalleryImageController::class, 'index'])->name('gallery.index');
    Route::get('/gallery/images/create', [GalleryImageController::class, 'create'])->name('gallery.images.create');
    Route::post('/gallery/images', [GalleryImageController::class, 'store'])->name('gallery.images.store');
    Route::delete('/gallery/images/{galleryImage}', [GalleryImageController::class, 'destroy'])->name('gallery.images.destroy');
    Route::get('/gallery/videos/create', [GalleryVideoController::class, 'create'])->name('gallery.videos.create');
    Route::post('/gallery/videos', [GalleryVideoController::class, 'store'])->name('gallery.videos.store');
    Route::delete('/gallery/videos/{video}', [GalleryVideoController::class, 'destroy'])->name('gallery.videos.destroy');
    Route::resource('branches', BranchController::class);
    Route::resource('students', \App\Http\Controllers\Admin\StudentController::class);
    Route::resource('messages', \App\Http\Controllers\Admin\MessageController::class)->only(['index','show','destroy']);
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
});
ROUTE
);

echo "[4/4] Writing Views...\n";
wf('resources/views/layouts/admin.blade.php', <<<'VIEW'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') | School of Redemption</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
    <div class="admin-wrapper">
        <nav class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
                    <span class="sidebar-brand-pre">School of</span>
                    <span class="sidebar-brand-name">REDEMPTION</span>
                </a>
            </div>
            <ul class="sidebar-menu">
                <li class="menu-header">MAIN</li>
                <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><a href="{{ route('admin.dashboard') }}"><i class="fas fa-th-large"></i><span>Dashboard</span></a></li>
                <li class="menu-header">ACADEMIC</li>
                <li class="{{ request()->routeIs('admin.academic-years.*') ? 'active' : '' }}"><a href="{{ route('admin.academic-years.index') }}"><i class="bi bi-calendar-range"></i><span>Academic Years</span></a></li>
                <li class="{{ request()->routeIs('admin.terms.*') ? 'active' : '' }}"><a href="{{ route('admin.terms.index') }}"><i class="bi bi-bookmark"></i><span>Terms</span></a></li>
                <li class="{{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}"><a href="{{ route('admin.subjects.index') }}"><i class="bi bi-book"></i><span>Subjects</span></a></li>
                <li class="{{ request()->routeIs('admin.subject-assignments.*') ? 'active' : '' }}"><a href="{{ route('admin.subject-assignments.index') }}"><i class="bi bi-link-45deg"></i><span>Assign Subjects</span></a></li>
                <li class="{{ request()->routeIs('admin.exams.*') ? 'active' : '' }}"><a href="{{ route('admin.exams.index') }}"><i class="bi bi-journal-text"></i><span>Exams</span></a></li>
                <li class="{{ request()->routeIs('admin.mark-entries.*') ? 'active' : '' }}"><a href="{{ route('admin.mark-entries.index') }}"><i class="bi bi-pencil-square"></i><span>Mark Entry</span></a></li>
                <li class="menu-header">PEOPLE</li>
                <li class="{{ request()->routeIs('admin.students.*') ? 'active' : '' }}"><a href="{{ route('admin.students.index') }}"><i class="fas fa-user-graduate"></i><span>Students</span></a></li>
                <li class="{{ request()->routeIs('admin.staff.*') ? 'active' : '' }}"><a href="{{ route('admin.staff.index') }}"><i class="bi bi-person-badge"></i><span>Staff &amp; Teachers</span></a></li>
                <li class="{{ request()->routeIs('admin.team.*') ? 'active' : '' }}"><a href="{{ route('admin.team.index') }}"><i class="fas fa-users"></i><span>Team Members</span></a></li>
                <li class="menu-header">WEBSITE</li>
                <li class="{{ request()->routeIs('admin.sliders.*') ? 'active' : '' }}"><a href="{{ route('admin.sliders.index') }}"><i class="fas fa-images"></i><span>Sliders</span></a></li>
                <li class="{{ str_starts_with(request()->route()->getName() ?? '', 'admin.gallery.') ? 'active' : '' }}">
                    <a href="#gallerySubmenu" data-bs-toggle="collapse"><i class="fas fa-photo-video"></i><span>Gallery</span><i class="fas fa-chevron-down ms-auto"></i></a>
                    <ul class="collapse {{ str_starts_with(request()->route()->getName() ?? '', 'admin.gallery.') ? 'show' : '' }}" id="gallerySubmenu">
                        <li><a href="{{ route('admin.gallery.images.index') }}"><i class="fas fa-image"></i> Images</a></li>
                        <li><a href="{{ route('admin.gallery.videos.index') }}"><i class="fas fa-video"></i> Videos</a></li>
                    </ul>
                </li>
                <li class="{{ request()->routeIs('admin.branches.*') ? 'active' : '' }}"><a href="{{ route('admin.branches.index') }}"><i class="fas fa-map-marker-alt"></i><span>Branches</span></a></li>
                <li class="{{ request()->routeIs('admin.messages.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.messages.index') }}"><i class="fas fa-envelope"></i><span>Messages</span>
                    @php $unread = \App\Models\ContactMessage::where('is_read', false)->count(); @endphp
                    @if($unread > 0)<span class="badge bg-danger ms-auto">{{ $unread }}</span>@endif
                    </a>
                </li>
                <li class="menu-header">SETTINGS</li>
                <li class="{{ request()->routeIs('admin.settings') ? 'active' : '' }}"><a href="{{ route('admin.settings.index') }}"><i class="fas fa-cog"></i><span>Settings</span></a></li>
            </ul>
        </nav>
        <div class="admin-main">
            <nav class="admin-topbar">
                <button class="btn btn-link sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
                <div class="topbar-right">
                    <span class="text-muted">Welcome, <strong>{{ Auth::user()->name }}</strong></span>
                    <div class="dropdown">
                        <button class="btn btn-link dropdown-toggle" data-bs-toggle="dropdown"><i class="fas fa-user-circle fa-lg"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ url('/') }}"><i class="fas fa-external-link-alt me-2"></i>View Website</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><form method="POST" action="{{ route('logout') }}">@csrf<button class="dropdown-item text-danger"><i class="fas fa-sign-out-alt me-2"></i>Logout</button></form></li>
                        </ul>
                    </div>
                </div>
            </nav>
            <div class="admin-content p-4">
                @if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
                @if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
                @yield('content')
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>document.getElementById('sidebarToggle')?.addEventListener('click', () => { document.getElementById('adminSidebar').classList.toggle('show'); });</script>
    @stack('scripts')
    @yield('scripts')
</body>
</html>
VIEW
);

echo "  OK: layouts/admin.blade.php\n";
// Mark Entry View (the critical one with cascading dropdowns)
wf('resources/views/admin/mark-entries/index.blade.php', <<<'MEVIEW'
@extends('layouts.admin')
@section('title', 'Mark Entry')
@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="mb-1 fw-bold">Mark Entry</h4><p class="text-muted mb-0">Enter and manage student marks</p></div>
    </div>
    <div class="card mb-3"><div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-2"><label class="form-label fw-semibold small">Academic Year</label>
                <select id="filterAy" class="form-select form-select-sm"><option value="">-- Select --</option>@foreach($academicYears as $ay)<option value="{{ $ay->id }}">{{ $ay->name }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label fw-semibold small">Term</label>
                <select id="filterTerm" class="form-select form-select-sm" disabled><option value="">-- Select AY first --</option></select></div>
            <div class="col-md-2"><label class="form-label fw-semibold small">Class</label>
                <select id="filterClass" class="form-select form-select-sm"><option value="">-- Select --</option></select></div>
            <div class="col-md-2"><label class="form-label fw-semibold small">Section</label>
                <select id="filterSection" class="form-select form-select-sm" disabled><option value="">-- Select Class --</option></select></div>
            <div class="col-md-2"><label class="form-label fw-semibold small">Subject</label>
                <select id="filterSubject" class="form-select form-select-sm" disabled><option value="">-- Select Section --</option></select></div>
            <div class="col-md-2"><button id="btnLoad" class="btn btn-primary btn-sm w-100" disabled><i class="bi bi-arrow-down-circle me-1"></i> Load Students</button></div>
        </div>
    </div></div>
    <div id="infoBanner" class="alert alert-info d-none mb-3 py-2"><strong id="bannerText"></strong></div>
    <div id="markGrid" class="d-none"><div class="card"><div class="card-body p-0">
        <div class="table-responsive" style="max-height:70vh;overflow:auto;">
            <table class="table table-bordered table-sm mb-0" id="marksTable" style="min-width:2200px;">
                <thead class="table-dark sticky-top">
                    <tr>
                        <th class="text-center" style="width:40px;position:sticky;left:0;z-index:3;background:#212529;">#</th>
                        <th style="width:180px;min-width:180px;position:sticky;left:40px;z-index:3;background:#212529;">Student Name</th>
                        <th style="width:80px;min-width:80px;position:sticky;left:220px;z-index:3;background:#212529;">Adm No</th>
                        <th style="width:60px;min-width:60px;position:sticky;left:300px;z-index:3;background:#212529;">Gender</th>
                        <th colspan="13" class="text-center" style="background:#0d6efd;color:white;">Continuous Assessment (Raw to Scaled 30)</th>
                        <th colspan="4" class="text-center" style="background:#198754;color:white;">Examination (70)</th>
                        <th colspan="3" class="text-center" style="background:#6f42c1;color:white;">Totals</th>
                    </tr>
                    <tr>
                        <th class="text-center" style="position:sticky;left:0;z-index:2;background:#343a40;"></th>
                        <th style="position:sticky;left:40px;z-index:2;background:#343a40;"></th>
                        <th style="position:sticky;left:220px;z-index:2;background:#343a40;"></th>
                        <th style="position:sticky;left:300px;z-index:2;background:#343a40;"></th>
                        <th class="text-center" style="background:#0d6efd;color:white;">CA1<br><small>/5</small></th>
                        <th class="text-center" style="background:#0d6efd;color:white;">CA2<br><small>/5</small></th>
                        <th class="text-center" style="background:#0d6efd;color:white;">CA3<br><small>/5</small></th>
                        <th class="text-center" style="background:#0d6efd;color:white;">CA4<br><small>/5</small></th>
                        <th class="text-center" style="background:#0d6efd;color:white;">CA5<br><small>/5</small></th>
                        <th class="text-center" style="background:#0d6efd;color:white;">CA6<br><small>/5</small></th>
                        <th class="text-center" style="background:#0d6efd;color:white;">CA7<br><small>/5</small></th>
                        <th class="text-center" style="background:#0d6efd;color:white;">CA8<br><small>/5</small></th>
                        <th class="text-center" style="background:#0d6efd;color:white;">CA9<br><small>/5</small></th>
                        <th class="text-center" style="background:#0d6efd;color:white;">CA10<br><small>/5</small></th>
                        <th class="text-center" style="background:#0d6efd;color:white;">Conduct<br><small>/5</small></th>
                        <th class="text-center" style="background:#0d6efd;color:white;">Handw.<br><small>/5</small></th>
                        <th class="text-center" style="background:#0d6efd;color:white;">Creative<br><small>/10</small></th>
                        <th class="text-center" style="background:#198754;color:white;">Test 1<br><small>/10</small></th>
                        <th class="text-center" style="background:#198754;color:white;">Test 2<br><small>/10</small></th>
                        <th class="text-center" style="background:#198754;color:white;">Mid-Term<br><small>/20</small></th>
                        <th class="text-center" style="background:#198754;color:white;">Final<br><small>/30</small></th>
                        <th class="text-center" style="background:#6f42c1;color:white;">CA Total<br><small>/30</small></th>
                        <th class="text-center" style="background:#6f42c1;color:white;">Exam Total<br><small>/70</small></th>
                        <th class="text-center fw-bold" style="background:#6f42c1;color:white;">Grand<br><small>/100</small></th>
                    </tr>
                </thead>
                <tbody id="marksBody"></tbody>
            </table>
        </div>
    </div></div></div>
    <div id="noStudentsMsg" class="alert alert-warning d-none mt-3"><i class="bi bi-exclamation-triangle me-2"></i>No students found for the selected filters.</div>
    <div id="saveIndicator" class="position-fixed bottom-0 end-0 p-3 d-none" style="z-index:9999;"><div class="toast align-items-center text-bg-success border-0 show"><div class="d-flex"><div class="toast-body"><i class="bi bi-check-circle me-1"></i> Saved!</div></div></div></div>
</div>
@endsection

@push('scripts')
<script>
const API_BASE='{{ url("/admin/mark-entries/api") }}';
const filterAy=document.getElementById('filterAy'),filterTerm=document.getElementById('filterTerm'),filterClass=document.getElementById('filterClass'),filterSection=document.getElementById('filterSection'),filterSubject=document.getElementById('filterSubject'),btnLoad=document.getElementById('btnLoad'),markGrid=document.getElementById('markGrid'),marksBody=document.getElementById('marksBody'),infoBanner=document.getElementById('infoBanner'),bannerText=document.getElementById('bannerText'),noStudentsMsg=document.getElementById('noStudentsMsg'),saveIndicator=document.getElementById('saveIndicator');
const markFields=[{col:'ca1',max:5},{col:'ca2',max:5},{col:'ca3',max:5},{col:'ca4',max:5},{col:'ca5',max:5},{col:'ca6',max:5},{col:'ca7',max:5},{col:'ca8',max:5},{col:'ca9',max:5},{col:'ca10',max:5},{col:'conduct',max:5},{col:'handwriting',max:5},{col:'creativity',max:10},{col:'test1',max:10},{col:'test2',max:10},{col:'mid_term',max:20},{col:'final_exam',max:30}];

filterAy.addEventListener('change',function(){
    const ayId=this.value;
    filterTerm.innerHTML='<option value="">-- Loading... --</option>';filterTerm.disabled=true;
    filterSection.innerHTML='<option value="">-- Select Class --</option>';filterSection.disabled=true;
    filterSubject.innerHTML='<option value="">-- Select Section --</option>';filterSubject.disabled=true;btnLoad.disabled=true;
    if(!ayId){filterTerm.innerHTML='<option value="">-- Select AY first --</option>';return;}
    fetch(API_BASE+'/terms?academic_year_id='+ayId).then(r=>{if(!r.ok)throw new Error('HTTP '+r.status);return r.json();})
    .then(terms=>{let html='<option value="">-- Select Term --</option>';terms.forEach(t=>{html+='<option value="'+t.id+'">'+t.name+'</option>';});
    if(terms.length===0)html='<option value="">-- No terms found --</option>';filterTerm.innerHTML=html;filterTerm.disabled=false;console.log('Terms loaded:',terms.length,'for AY:',ayId);})
    .catch(err=>{console.error('Error loading terms:',err);filterTerm.innerHTML='<option value="">-- Error --</option>';filterTerm.disabled=false;});
});

filterClass.addEventListener('change',function(){
    const classId=this.value;filterSection.innerHTML='<option value="">-- Loading... --</option>';filterSection.disabled=true;
    filterSubject.innerHTML='<option value="">-- Select Section --</option>';filterSubject.disabled=true;btnLoad.disabled=true;
    if(!classId){filterSection.innerHTML='<option value="">-- Select Class --</option>';return;}
    fetch(API_BASE+'/sections?class_id='+classId).then(r=>{if(!r.ok)throw new Error('HTTP '+r.status);return r.json();})
    .then(sections=>{let html='<option value="">-- Select Section --</option>';sections.forEach(s=>{html+='<option value="'+s.id+'">'+s.name+'</option>';});
    filterSection.innerHTML=html;filterSection.disabled=false;}).catch(err=>{filterSection.innerHTML='<option value="">-- Error --</option>';filterSection.disabled=false;});
});

filterSection.addEventListener('change',function(){
    const sectionId=this.value,classId=filterClass.value,ayId=filterAy.value;
    filterSubject.innerHTML='<option value="">-- Loading... --</option>';filterSubject.disabled=true;btnLoad.disabled=true;
    if(!sectionId||!classId){filterSubject.innerHTML='<option value="">-- Select Section --</option>';return;}
    fetch(API_BASE+'/subjects?class_id='+classId+'&section_id='+sectionId+'&academic_year_id='+ayId)
    .then(r=>{if(!r.ok)throw new Error('HTTP '+r.status);return r.json();})
    .then(subjects=>{let html='<option value="">-- Select Subject --</option>';
    let core=subjects.filter(s=>s.is_core),elec=subjects.filter(s=>!s.is_core);
    if(core.length>0){html+='<optgroup label="Core Subjects">';core.forEach(s=>{html+='<option value="'+s.id+'">'+s.name+(s.code?' ('+s.code+')':'')+'</option>';});html+='</optgroup>';}
    if(elec.length>0){html+='<optgroup label="Elective">';elec.forEach(s=>{html+='<option value="'+s.id+'">'+s.name+(s.code?' ('+s.code+')':'')+'</option>';});html+='</optgroup>';}
    if(subjects.length===0)html='<option value="">-- No subjects assigned --</option>';
    filterSubject.innerHTML=html;filterSubject.disabled=false;}).catch(err=>{filterSubject.innerHTML='<option value="">-- Error --</option>';filterSubject.disabled=false;});
});

filterTerm.addEventListener('change',checkCanLoad);filterSubject.addEventListener('change',checkCanLoad);
function checkCanLoad(){btnLoad.disabled=!(filterAy.value&&filterTerm.value&&filterClass.value&&filterSection.value&&filterSubject.value);}

btnLoad.addEventListener('click',loadStudents);
function loadStudents(){
    const params=new URLSearchParams({academic_year_id:filterAy.value,term_id:filterTerm.value,class_id:filterClass.value,section_id:filterSection.value,subject_id:filterSubject.value});
    btnLoad.disabled=true;btnLoad.innerHTML='<span class="spinner-border spinner-border-sm me-1"></span> Loading...';
    fetch(API_BASE+'/load-students?'+params).then(r=>{if(!r.ok)throw new Error('HTTP '+r.status);return r.json();})
    .then(data=>{btnLoad.disabled=false;btnLoad.innerHTML='<i class="bi bi-arrow-down-circle me-1"></i> Load Students';
    if(data.students&&data.students.length>0){renderGrid(data);markGrid.classList.remove('d-none');noStudentsMsg.classList.add('d-none');infoBanner.classList.remove('d-none');
    bannerText.textContent=data.class+' - '+data.section+' | '+data.term+' | Subject: '+data.subject+' | '+data.students.length+' Students';}
    else{markGrid.classList.add('d-none');noStudentsMsg.classList.remove('d-none');infoBanner.classList.add('d-none');}
    }).catch(err=>{btnLoad.disabled=false;btnLoad.innerHTML='<i class="bi bi-arrow-down-circle me-1"></i> Load Students';alert('Error: '+err.message);});
}

function renderGrid(data){let html='';data.students.forEach((s,idx)=>{
    html+='<tr data-student-id="'+s.student_id+'">';
    html+='<td class="text-center" style="position:sticky;left:0;z-index:1;background:white;">'+(idx+1)+'</td>';
    html+='<td style="position:sticky;left:40px;z-index:1;background:white;font-weight:500;">'+escapeHtml(s.student_name)+'</td>';
    html+='<td style="position:sticky;left:220px;z-index:1;background:white;">'+escapeHtml(s.roll_number||'-')+'</td>';
    html+='<td style="position:sticky;left:300px;z-index:1;background:white;">'+escapeHtml(s.gender||'-')+'</td>';
    markFields.forEach((f,fi)=>{const bg=fi<13?'#e7f1ff':'#e8f5e9';const val=(s[f.col]!==null&&s[f.col]!==undefined)?s[f.col]:'';
    html+='<td class="text-center" style="background:'+bg+';"><input type="number" class="form-control form-control-sm text-center mark-input" data-field="'+f.col+'" data-max="'+f.max+'" min="0" max="'+f.max+'" step="0.5" value="'+val+'" style="width:55px;display:inline-block;"></td>';});
    html+='<td class="text-center fw-semibold" style="background:#f3e8ff;" data-total="ca_total">'+(s.ca_total||0)+'</td>';
    html+='<td class="text-center fw-semibold" style="background:#f3e8ff;" data-total="exam_total">'+(s.exam_total||0)+'</td>';
    html+='<td class="text-center fw-bold" style="background:#f3e8ff;font-size:1.05em;" data-total="grand_total">'+(s.grand_total||0)+'</td></tr>';});
    marksBody.innerHTML=html;attachMarkHandlers();}

function attachMarkHandlers(){document.querySelectorAll('.mark-input').forEach(input=>{
    input.addEventListener('blur',function(){const field=this.dataset.field,max=parseFloat(this.dataset.max);let val=parseFloat(this.value);
    if(isNaN(val)||this.value===''){this.value='';val=0;}else{if(val<0)val=0;if(val>max)val=max;this.value=val;}saveMark(this);});
    input.addEventListener('keydown',function(e){if(e.key==='Enter'){e.preventDefault();const all=Array.from(document.querySelectorAll('.mark-input'));const idx=all.indexOf(this);if(idx<all.length-1){all[idx+1].focus();all[idx+1].select();}}});});}

function saveMark(input){const row=input.closest('tr');const studentId=row.dataset.studentId;
const payload={student_id:studentId,subject_id:filterSubject.value,academic_year_id:filterAy.value,term_id:filterTerm.value,class_id:filterClass.value,section_id:filterSection.value};
row.querySelectorAll('.mark-input').forEach(inp=>{payload[inp.dataset.field]=inp.value===''?0:parseFloat(inp.value);});
input.style.borderColor='#ffc107';
fetch(API_BASE+'/save',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')||'{{ csrf_token() }}','Accept':'application/json'},body:JSON.stringify(payload)})
.then(r=>r.json()).then(data=>{input.style.borderColor='#198754';setTimeout(()=>{input.style.borderColor='';},1500);
if(data.success){const totals=row.querySelectorAll('[data-total]');if(totals[0])totals[0].textContent=data.ca_total;if(totals[1])totals[1].textContent=data.exam_total;if(totals[2])totals[2].textContent=data.grand_total;
saveIndicator.classList.remove('d-none');setTimeout(()=>saveIndicator.classList.add('d-none'),1200);}})
.catch(err=>{input.style.borderColor='#dc3545';setTimeout(()=>{input.style.borderColor='';},2000);});}

function escapeHtml(text){if(!text)return '';const div=document.createElement('div');div.textContent=text;return div.innerHTML;}

document.addEventListener('DOMContentLoaded',function(){
    fetch('{{ url("/admin/mark-entries/api/classes") }}').then(r=>r.json()).then(classes=>{let html='<option value="">-- Select Class --</option>';classes.forEach(c=>{html+='<option value="'+c.id+'">'+c.name+'</option>';});filterClass.innerHTML=html;}).catch(err=>console.error('Error loading classes:',err));
});
</script>
@endpush
MEVIEW
);
echo "  OK: mark-entries/index.blade.php\n";

// Exam views
wf('resources/views/admin/exams/index.blade.php', <<<'EXVIEW'
@extends('layouts.admin')
@section('title', 'Exams')
@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="mb-1 fw-bold">Exams</h4><p class="text-muted mb-0">Manage school examinations</p></div>
        <a href="{{ route('admin.exams.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Create Exam</a>
    </div>
    @if($exams->count() > 0)
    <div class="card"><div class="card-body p-0"><div class="table-responsive">
        <table class="table table-hover align-middle mb-0"><thead class="table-dark"><tr><th>#</th><th>Exam Name</th><th>Academic Year</th><th>Term</th><th>Type</th><th>Start Date</th><th>End Date</th><th>Time</th><th>Total</th><th>Pass</th><th>Actions</th></tr></thead>
        <tbody>@foreach($exams as $exam)<tr><td>{{ $loop->iteration }}</td><td class="fw-semibold">{{ $exam->name }}</td><td>{{ $exam->academicYear->name ?? '-' }}</td><td>{{ $exam->term->name ?? '-' }}</td><td>{{ $exam->type ?? '-' }}</td><td>{{ $exam->start_date ? $exam->start_date->format('M d, Y') : '-' }}</td><td>{{ $exam->end_date ? $exam->end_date->format('M d, Y') : '-' }}</td><td>@if($exam->start_time){{ $exam->start_time }}{{ $exam->end_time ? ' - '.$exam->end_time : '' }}@else -@endif</td><td>{{ $exam->total_marks ?? '-' }}</td><td>{{ $exam->passing_marks ?? '-' }}</td>
        <td><div class="btn-group btn-group-sm"><a href="{{ route('admin.exams.edit', $exam) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i></a><form method="POST" action="{{ route('admin.exams.destroy', $exam) }}" onsubmit="return confirm('Delete this exam?')">@csrf @method('DELETE')<button class="btn btn-outline-danger"><i class="bi bi-trash"></i></button></form></div></td></tr>@endforeach</tbody></table>
    </div></div></div>
    @else
    <div class="card"><div class="card-body text-center py-5"><i class="bi bi-journal-text display-1 text-muted"></i><h5 class="mt-3 text-muted">No Exams Yet</h5><a href="{{ route('admin.exams.create') }}" class="btn btn-primary mt-2"><i class="bi bi-plus-circle me-1"></i> Create First Exam</a></div></div>
    @endif
</div>
@endsection
EXVIEW
);
echo "  OK: exams/index.blade.php\n";

wf('resources/views/admin/exams/create.blade.php', <<<'EXVIEW'
@extends('layouts.admin')
@section('title', 'Create Exam')
@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4"><div><h4 class="mb-1 fw-bold">Create Exam</h4></div><a href="{{ route('admin.exams.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a></div>
    <div class="card"><div class="card-body">
        <form method="POST" action="{{ route('admin.exams.store') }}">@csrf
            <div class="row g-3 mb-3">
                <div class="col-md-6"><label class="form-label fw-semibold">Exam Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required></div>
                <div class="col-md-3"><label class="form-label fw-semibold">Type</label><input type="text" name="type" class="form-control" placeholder="Mid-Term, Final"></div>
                <div class="col-md-3"><label class="form-label fw-semibold">Academic Year <span class="text-danger">*</span></label><select name="academic_year_id" id="examAy" class="form-select" required><option value="">-- Select --</option>@foreach($academicYears as $ay)<option value="{{ $ay->id }}">{{ $ay->name }}</option>@endforeach</select></div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-4"><label class="form-label fw-semibold">Term <span class="text-danger">*</span></label><select name="term_id" id="examTerm" class="form-select" required><option value="">-- Select AY first --</option></select></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Total Marks</label><input type="number" name="total_marks" class="form-control" min="0"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Passing Marks</label><input type="number" name="passing_marks" class="form-control" min="0"></div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-3"><label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label><input type="date" name="start_date" class="form-control" required></div>
                <div class="col-md-3"><label class="form-label fw-semibold">End Date <span class="text-danger">*</span></label><input type="date" name="end_date" class="form-control" required></div>
                <div class="col-md-3"><label class="form-label fw-semibold">Start Time</label><input type="time" name="start_time" class="form-control"></div>
                <div class="col-md-3"><label class="form-label fw-semibold">End Time</label><input type="time" name="end_time" class="form-control"></div>
            </div>
            <div class="row g-3 mb-4"><div class="col-12"><label class="form-label fw-semibold">Description</label><textarea name="description" class="form-control" rows="3"></textarea></div></div>
            <div class="d-flex gap-2"><button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Create Exam</button><a href="{{ route('admin.exams.index') }}" class="btn btn-outline-secondary">Cancel</a></div>
        </form>
    </div></div>
</div>
@endsection
@push('scripts')
<script>
var allTerms={{ $allTerms->toJson() }};
var examAySelect=document.getElementById('examAy'),examTermSelect=document.getElementById('examTerm');
examAySelect.addEventListener('change',function(){var ayId=this.value;examTermSelect.innerHTML='';
if(!ayId){examTermSelect.innerHTML='<option value="">-- Select AY first --</option>';return;}
var filtered=allTerms.filter(function(t){return t.academic_year_id==ayId;});
if(filtered.length===0){examTermSelect.innerHTML='<option value="">-- No terms --</option>';}
else{examTermSelect.innerHTML='<option value="">-- Select Term --</option>';filtered.forEach(function(t){var opt=document.createElement('option');opt.value=t.id;opt.textContent=t.name;examTermSelect.appendChild(opt);});}});
</script>
@endpush
EXVIEW
);
echo "  OK: exams/create.blade.php\n";

wf('resources/views/admin/exams/edit.blade.php', <<<'EXVIEW'
@extends('layouts.admin')
@section('title', 'Edit Exam')
@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4"><div><h4 class="mb-1 fw-bold">Edit Exam</h4></div><a href="{{ route('admin.exams.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a></div>
    <div class="card"><div class="card-body">
        <form method="POST" action="{{ route('admin.exams.update', $exam) }}">@csrf @method('PUT')
            <div class="row g-3 mb-3">
                <div class="col-md-6"><label class="form-label fw-semibold">Exam Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="{{ old('name', $exam->name) }}" required></div>
                <div class="col-md-3"><label class="form-label fw-semibold">Type</label><input type="text" name="type" class="form-control" value="{{ old('type', $exam->type) }}"></div>
                <div class="col-md-3"><label class="form-label fw-semibold">Academic Year <span class="text-danger">*</span></label><select name="academic_year_id" id="examAy" class="form-select" required><option value="">-- Select --</option>@foreach($academicYears as $ay)<option value="{{ $ay->id }}" {{ $exam->academic_year_id == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>@endforeach</select></div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-4"><label class="form-label fw-semibold">Term <span class="text-danger">*</span></label><select name="term_id" id="examTerm" class="form-select" required></select></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Total Marks</label><input type="number" name="total_marks" class="form-control" value="{{ old('total_marks', $exam->total_marks) }}" min="0"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Passing Marks</label><input type="number" name="passing_marks" class="form-control" value="{{ old('passing_marks', $exam->passing_marks) }}" min="0"></div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-3"><label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label><input type="date" name="start_date" class="form-control" value="{{ old('start_date', $exam->start_date ? $exam->start_date->format('Y-m-d') : '') }}" required></div>
                <div class="col-md-3"><label class="form-label fw-semibold">End Date <span class="text-danger">*</span></label><input type="date" name="end_date" class="form-control" value="{{ old('end_date', $exam->end_date ? $exam->end_date->format('Y-m-d') : '') }}" required></div>
                <div class="col-md-3"><label class="form-label fw-semibold">Start Time</label><input type="time" name="start_time" class="form-control" value="{{ old('start_time', $exam->start_time) }}"></div>
                <div class="col-md-3"><label class="form-label fw-semibold">End Time</label><input type="time" name="end_time" class="form-control" value="{{ old('end_time', $exam->end_time) }}"></div>
            </div>
            <div class="row g-3 mb-4"><div class="col-12"><label class="form-label fw-semibold">Description</label><textarea name="description" class="form-control" rows="3">{{ old('description', $exam->description) }}</textarea></div></div>
            <div class="d-flex gap-2"><button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Update</button><a href="{{ route('admin.exams.index') }}" class="btn btn-outline-secondary">Cancel</a></div>
        </form>
    </div></div>
</div>
@endsection
@push('scripts')
<script>
var allTerms={{ $allTerms->toJson() }};var examAySelect=document.getElementById('examAy'),examTermSelect=document.getElementById('examTerm');var currentTermId={{ $exam->term_id }};
function populateTerms(ayId){examTermSelect.innerHTML='';if(!ayId){examTermSelect.innerHTML='<option value="">-- Select AY first --</option>';return;}
var filtered=allTerms.filter(function(t){return t.academic_year_id==ayId;});
if(filtered.length===0){examTermSelect.innerHTML='<option value="">-- No terms --</option>';}
else{examTermSelect.innerHTML='<option value="">-- Select Term --</option>';filtered.forEach(function(t){var opt=document.createElement('option');opt.value=t.id;opt.textContent=t.name;if(t.id==currentTermId)opt.selected=true;examTermSelect.appendChild(opt);});}}
examAySelect.addEventListener('change',function(){populateTerms(this.value);});
populateTerms(examAySelect.value);
</script>
@endpush
EXVIEW
);
echo "  OK: exams/edit.blade.php\n";

// Subjects views
wf('resources/views/admin/subjects/index.blade.php', '@extends(\'layouts.admin\')
@section(\'title\', \'Subjects\')
@section(\'content\')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="mb-1 fw-bold">Subjects</h4><p class="text-muted mb-0">Manage school subjects</p></div>
        <a href="{{ route(\'admin.subjects.create\') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Add Subject</a>
    </div>
    @if($subjects->count() > 0)
    <div class="card"><div class="card-body p-0"><div class="table-responsive">
        <table class="table table-hover align-middle mb-0"><thead class="table-dark"><tr><th>#</th><th>Name</th><th>Code</th><th>Type</th><th>Description</th><th>Actions</th></tr></thead>
        <tbody>@foreach($subjects as $subject)<tr><td>{{ $loop->iteration }}</td><td class="fw-semibold">{{ $subject->name }}</td><td><code>{{ $subject->code ?? \'-\' }}</code></td><td>{{ $subject->type ?? \'-\' }}</td><td class="text-truncate" style="max-width:300px;">{{ $subject->description ?? \'-\' }}</td>
        <td><div class="btn-group btn-group-sm"><a href="{{ route(\'admin.subjects.edit\', $subject) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i></a><form method="POST" action="{{ route(\'admin.subjects.destroy\', $subject) }}" onsubmit="return confirm(\'Delete?\')">@csrf @method(\'DELETE\')<button class="btn btn-outline-danger"><i class="bi bi-trash"></i></button></form></div></td></tr>@endforeach</tbody></table>
    </div></div></div>
    @else
    <div class="card"><div class="card-body text-center py-5"><i class="bi bi-book display-1 text-muted"></i><h5 class="mt-3 text-muted">No Subjects Yet</h5><a href="{{ route(\'admin.subjects.create\') }}" class="btn btn-primary mt-2">Add First Subject</a></div></div>
    @endif
</div>
@endsection');

wf('resources/views/admin/subjects/create.blade.php', '@extends(\'layouts.admin\')
@section(\'title\', \'Create Subject\')
@section(\'content\')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4"><div><h4 class="mb-1 fw-bold">Add Subject</h4></div><a href="{{ route(\'admin.subjects.index\') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a></div>
    <div class="card"><div class="card-body">
        <form method="POST" action="{{ route(\'admin.subjects.store\') }}">@csrf
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label fw-semibold">Subject Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required></div>
                <div class="col-md-3"><label class="form-label fw-semibold">Code</label><input type="text" name="code" class="form-control" placeholder="e.g. MATH101"></div>
                <div class="col-md-3"><label class="form-label fw-semibold">Type</label><input type="text" name="type" class="form-control" placeholder="Core, Elective"></div>
                <div class="col-12"><label class="form-label fw-semibold">Description</label><textarea name="description" class="form-control" rows="3"></textarea></div>
            </div>
            <div class="mt-3"><button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Save</button><a href="{{ route(\'admin.subjects.index\') }}" class="btn btn-outline-secondary">Cancel</a></div>
        </form>
    </div></div>
</div>
@endsection');

wf('resources/views/admin/subjects/edit.blade.php', '@extends(\'layouts.admin\')
@section(\'title\', \'Edit Subject\')
@section(\'content\')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4"><div><h4 class="mb-1 fw-bold">Edit Subject</h4></div><a href="{{ route(\'admin.subjects.index\') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a></div>
    <div class="card"><div class="card-body">
        <form method="POST" action="{{ route(\'admin.subjects.update\', $subject) }}">@csrf @method(\'PUT\')
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label fw-semibold">Subject Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="{{ old(\'name\', $subject->name) }}" required></div>
                <div class="col-md-3"><label class="form-label fw-semibold">Code</label><input type="text" name="code" class="form-control" value="{{ old(\'code\', $subject->code) }}"></div>
                <div class="col-md-3"><label class="form-label fw-semibold">Type</label><input type="text" name="type" class="form-control" value="{{ old(\'type\', $subject->type) }}"></div>
                <div class="col-12"><label class="form-label fw-semibold">Description</label><textarea name="description" class="form-control" rows="3">{{ old(\'description\', $subject->description) }}</textarea></div>
            </div>
            <div class="mt-3"><button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Update</button><a href="{{ route(\'admin.subjects.index\') }}" class="btn btn-outline-secondary">Cancel</a></div>
        </form>
    </div></div>
</div>
@endsection');

echo "  OK: subjects views\n";

// Staff views
wf('resources/views/admin/staff/index.blade.php', '@extends(\'layouts.admin\')
@section(\'title\', \'Staff / Teachers\')
@push(\'styles\')
<style>.role-badge-teacher{background:#0d6efd;color:white;padding:3px 10px;border-radius:12px;font-size:.75rem;font-weight:600}.role-badge-admin{background:#dc3545;color:white;padding:3px 10px;border-radius:12px;font-size:.75rem;font-weight:600}</style>
@endpush
@section(\'content\')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="mb-1 fw-bold">Staff &amp; Teachers</h4><p class="text-muted mb-0">Manage teachers and staff</p></div>
        <a href="{{ route(\'admin.staff.create\') }}" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i> Add Staff</a>
    </div>
    @if($staff->count() > 0)
    <div class="card"><div class="card-body p-0"><div class="table-responsive">
        <table class="table table-hover align-middle mb-0"><thead class="table-dark"><tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Gender</th><th>Qualification</th><th>Actions</th></tr></thead>
        <tbody>@foreach($staff as $s)<tr><td>{{ $staff->firstItem() + $loop->index }}</td><td class="fw-semibold">{{ $s->name }}</td><td>{{ $s->email }}</td><td>{{ $s->phone ?? \'-\' }}</td><td><span class="role-badge-{{ $s->role }}">{{ ucfirst($s->role) }}</span></td><td>{{ $s->gender ?? \'-\' }}</td><td>{{ $s->qualification ?? \'-\' }}</td>
        <td><div class="btn-group btn-group-sm"><a href="{{ route(\'admin.staff.edit\', $s) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i></a>
        @if($s->email !== auth()->user()->email)<form method="POST" action="{{ route(\'admin.staff.destroy\', $s) }}" onsubmit="return confirm(\'Remove this staff member?\')">@csrf @method(\'DELETE\')<button class="btn btn-outline-danger"><i class="bi bi-trash"></i></button></form>@endif
        </div></td></tr>@endforeach</tbody></table>
    </div></div>
    @if($staff->hasPages())<div class="card-footer d-flex justify-content-center">{{ $staff->links() }}</div>@endif
    </div>
    @else
    <div class="card"><div class="card-body text-center py-5"><i class="bi bi-people display-1 text-muted"></i><h5 class="mt-3 text-muted">No Staff Members Yet</h5><p class="text-muted">Add teachers and staff.</p><a href="{{ route(\'admin.staff.create\') }}" class="btn btn-primary mt-2"><i class="bi bi-person-plus me-1"></i> Add First Staff</a></div></div>
    @endif
</div>
@endsection');

wf('resources/views/admin/staff/create.blade.php', '@extends(\'layouts.admin\')
@section(\'title\', \'Add Staff\')
@section(\'content\')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4"><div><h4 class="mb-1 fw-bold">Add Staff / Teacher</h4></div><a href="{{ route(\'admin.staff.index\') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a></div>
    <div class="row justify-content-center"><div class="col-lg-8"><div class="card"><div class="card-header bg-light fw-semibold"><i class="bi bi-person-plus me-1"></i> Staff Information</div><div class="card-body">
        <form method="POST" action="{{ route(\'admin.staff.store\') }}">@csrf
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required autofocus></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Email <span class="text-danger">*</span></label><input type="email" name="email" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Phone</label><input type="text" name="phone" class="form-control" placeholder="+251-XXX-XXXXXX"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Role <span class="text-danger">*</span></label><select name="role" class="form-select" required><option value="teacher">Teacher</option><option value="admin">Admin</option></select></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Gender</label><select name="gender" class="form-select"><option value="">-- Select --</option><option value="Male">Male</option><option value="Female">Female</option></select></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Qualification</label><input type="text" name="qualification" class="form-control" placeholder="BA, BSc, MA, PhD"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Password <span class="text-danger">*</span></label><input type="password" name="password" class="form-control" required minlength="6"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label><input type="password" name="password_confirmation" class="form-control" required></div>
                <div class="col-12"><label class="form-label fw-semibold">Address</label><textarea name="address" class="form-control" rows="2"></textarea></div>
            </div>
            <div class="mt-4 d-flex gap-2"><button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Save Staff</button><a href="{{ route(\'admin.staff.index\') }}" class="btn btn-outline-secondary">Cancel</a></div>
        </form>
    </div></div></div></div>
</div>
@endsection');

wf('resources/views/admin/staff/edit.blade.php', '@extends(\'layouts.admin\')
@section(\'title\', \'Edit Staff\')
@section(\'content\')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4"><div><h4 class="mb-1 fw-bold">Edit Staff Member</h4></div><a href="{{ route(\'admin.staff.index\') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a></div>
    <div class="row justify-content-center"><div class="col-lg-8"><div class="card"><div class="card-header bg-light fw-semibold"><i class="bi bi-pencil me-1"></i> Edit: {{ $user->name }}</div><div class="card-body">
        <form method="POST" action="{{ route(\'admin.staff.update\', $user) }}">@csrf @method(\'PUT\')
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="{{ old(\'name\', $user->name) }}" required></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Email <span class="text-danger">*</span></label><input type="email" name="email" class="form-control" value="{{ old(\'email\', $user->email) }}" required></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Phone</label><input type="text" name="phone" class="form-control" value="{{ old(\'phone\', $user->phone) }}"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Role <span class="text-danger">*</span></label><select name="role" class="form-select" required><option value="teacher" {{ old(\'role\', $user->role) === \'teacher\' ? \'selected\' : \'\' }}>Teacher</option><option value="admin" {{ old(\'role\', $user->role) === \'admin\' ? \'selected\' : \'\' }}>Admin</option></select></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Gender</label><select name="gender" class="form-select"><option value="">-- Select --</option><option value="Male" {{ old(\'gender\', $user->gender ?? \'\') === \'Male\' ? \'selected\' : \'\' }}>Male</option><option value="Female" {{ old(\'gender\', $user->gender ?? \'\') === \'Female\' ? \'selected\' : \'\' }}>Female</option></select></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Qualification</label><input type="text" name="qualification" class="form-control" value="{{ old(\'qualification\', $user->qualification ?? \'\') }}"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">New Password</label><input type="password" name="password" class="form-control" placeholder="Leave blank to keep current"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Confirm Password</label><input type="password" name="password_confirmation" class="form-control" placeholder="Leave blank to keep current"></div>
                <div class="col-12"><label class="form-label fw-semibold">Address</label><textarea name="address" class="form-control" rows="2">{{ old(\'address\', $user->address ?? \'\') }}</textarea></div>
            </div>
            <div class="mt-4 d-flex gap-2"><button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Update</button><a href="{{ route(\'admin.staff.index\') }}" class="btn btn-outline-secondary">Cancel</a></div>
        </form>
    </div></div></div></div>
</div>
@endsection');

echo "  OK: staff views\n";

// Subject-Assignments views
wf('resources/views/admin/subject-assignments/index.blade.php', '@extends(\'layouts.admin\')
@section(\'title\', \'Subject Assignments\')
@push(\'styles\')
<style>.type-badge-core{background:#0d6efd;color:#fff;padding:3px 10px;border-radius:12px;font-size:.75rem;font-weight:600}.type-badge-elective{background:#fd7e14;color:#fff;padding:3px 10px;border-radius:12px;font-size:.75rem;font-weight:600}.section-all{background:#e7f1ff;color:#0d6efd;padding:2px 8px;border-radius:8px;font-size:.75rem;font-weight:600}.section-specific{background:#fff3e0;color:#e65100;padding:2px 8px;border-radius:8px;font-size:.75rem;font-weight:600}.row-core{border-left:4px solid #0d6efd!important}.row-elective{border-left:4px solid #fd7e14!important}</style>
@endpush
@section(\'content\')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="mb-1 fw-bold">Subject Assignments</h4><p class="text-muted mb-0">Core = all sections, Elective = specific sections.</p></div>
        <a href="{{ route(\'admin.subject-assignments.create\') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Assign Subject</a>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card border-0 bg-primary bg-opacity-10"><div class="card-body text-center py-3"><div class="fs-3 fw-bold text-primary">{{ $coreAssignments->count() }}</div><div class="text-muted small">Core (All Sections)</div></div></div></div>
        <div class="col-md-4"><div class="card border-0 bg-warning bg-opacity-10"><div class="card-body text-center py-3"><div class="fs-3 fw-bold text-warning">{{ $electiveAssignments->count() }}</div><div class="text-muted small">Elective (Specific)</div></div></div></div>
        <div class="col-md-4"><div class="card border-0 bg-success bg-opacity-10"><div class="card-body text-center py-3"><div class="fs-3 fw-bold text-success">{{ $assignments->count() }}</div><div class="text-muted small">Total</div></div></div></div>
    </div>
    <form method="GET" action="{{ route(\'admin.subject-assignments.index\') }}"><div class="card mb-3"><div class="card-body py-3"><div class="row g-2 align-items-end">
        <div class="col-md-4"><label class="form-label fw-semibold small">Academic Year</label><select name="academic_year_id" class="form-select form-select-sm"><option value="">All Years</option>@foreach($academicYears as $ay)<option value="{{ $ay->id }}" {{ request(\'academic_year_id\') == $ay->id ? \'selected\' : \'\' }}>{{ $ay->name }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label fw-semibold small">Class</label><select name="class_id" class="form-select form-select-sm"><option value="">All Classes</option>@foreach($classes as $c)<option value="{{ $c->id }}" {{ request(\'class_id\') == $c->id ? \'selected\' : \'\' }}>{{ $c->name }}</option>@endforeach</select></div>
        <div class="col-md-4 d-flex gap-2"><button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="bi bi-funnel me-1"></i> Filter</button><a href="{{ route(\'admin.subject-assignments.index\') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle"></i></a></div>
    </div></div></div></form>
    <form method="POST" action="{{ route(\'admin.subject-assignments.bulk-delete\') }}" id="bulkForm">@csrf @method(\'DELETE\')
    @if($assignments->count() > 0)
    <div class="card"><div class="card-body p-0"><div class="table-responsive">
        <table class="table table-hover align-middle mb-0"><thead class="table-dark"><tr><th style="width:40px"><input type="checkbox" class="form-check-input" id="selectAll"></th><th>#</th><th>Subject</th><th>Class</th><th>Section</th><th>Type</th><th>Teacher</th><th>AY</th><th>Actions</th></tr></thead>
        <tbody>@foreach($assignments as $a)@php $isCore=is_null($a->section_id); @endphp
        <tr class="{{ $isCore ? \'row-core\' : \'row-elective\' }}">
            <td><input type="checkbox" class="form-check-input bulk-check" name="ids[]" value="{{ $a->id }}"></td>
            <td>{{ $loop->iteration }}</td>
            <td><span class="fw-semibold">{{ $a->subject->name ?? \'N/A\' }}</span></td>
            <td>{{ $a->classRoom->name ?? \'N/A\' }}</td>
            <td>@if($isCore)<span class="section-all"><i class="bi bi-collection me-1"></i>All</span>@else<span class="section-specific">{{ $a->section->name ?? \'N/A\' }}</span>@endif</td>
            <td><span class="{{ $isCore ? \'type-badge-core\' : \'type-badge-elective\' }}">{{ $isCore ? \'Core\' : \'Elective\' }}</span></td>
            <td>{{ $a->teacher->name ?? \'N/A\' }}</td>
            <td class="small">{{ $a->academicYear->name ?? \'N/A\' }}</td>
            <td><div class="btn-group btn-group-sm"><a href="{{ route(\'admin.subject-assignments.edit\', $a) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i></a>
            <button type="button" class="btn btn-outline-danger btn-delete-single" data-id="{{ $a->id }}"><i class="bi bi-trash"></i></button></div></td>
        </tr>@endforeach</tbody></table>
    </div></div></div>
    @else
    <div class="card"><div class="card-body text-center py-5"><i class="bi bi-link-45deg display-1 text-muted"></i><h5 class="mt-3 text-muted">No Subject Assignments Yet</h5><a href="{{ route(\'admin.subject-assignments.create\') }}" class="btn btn-primary mt-2">Assign First Subject</a></div></div>
    @endif
    </form>
</div>
@endsection
@push(\'scripts\')
<script>
document.getElementById(\'selectAll\')?.addEventListener(\'change\',function(){document.querySelectorAll(\'.bulk-check\').forEach(cb=>cb.checked=this.checked);updUI();});
document.querySelectorAll(\'.bulk-check\').forEach(cb=>{cb.addEventListener(\'change\',function(){const a=document.querySelectorAll(\'.bulk-check\'),c=document.querySelectorAll(\'.bulk-check:checked\');document.getElementById(\'selectAll\').checked=(a.length===c.length&&a.length>0);updUI();});});
function updUI(){const c=document.querySelectorAll(\'.bulk-check:checked\');document.getElementById(\'selectedCount\').textContent=c.length+\' selected\';document.getElementById(\'bulkDeleteBtn\').disabled=(c.length===0);}
document.querySelectorAll(\'.btn-delete-single\').forEach(btn=>{btn.addEventListener(\'click\',function(){if(confirm(\'Remove?\')){const id=this.dataset.id;const f=document.createElement(\'form\');f.method=\'POST\';f.action=\'/admin/subject-assignments/\'+id;const t=document.createElement(\'input\');t.type=\'hidden\';t.name=\'_token\';t.value=document.querySelector(\'meta[name="csrf-token"]\')?.getAttribute(\'content\');f.appendChild(t);const m=document.createElement(\'input\');m.type=\'hidden\';m.name=\'_method\';m.value=\'DELETE\';f.appendChild(m);document.body.appendChild(f);f.submit();}});});
</script>
@endpush');

echo "  OK: subject-assignments/index.blade.php\n";

wf('resources/views/admin/subject-assignments/create.blade.php', '@extends(\'layouts.admin\')
@section(\'title\', \'Assign Subject\')
@push(\'styles\')
<style>.assignment-type-card{cursor:pointer;transition:all .2s;border:2px solid transparent}.assignment-type-card:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,.1)}.assignment-type-card.active-core{border-color:#0d6efd!important;background:rgba(13,110,253,.08)}.assignment-type-card.active-elective{border-color:#fd7e14!important;background:rgba(253,126,20,.08)}.class-check-item{padding:8px 12px;border:1px solid #dee2e6;border-radius:8px;cursor:pointer}.class-check-item:hover{background:#f0f8ff;border-color:#0d6efd}.class-check-item.selected{background:#e7f1ff;border-color:#0d6efd}.section-check-item{padding:6px 12px;border:1px solid #dee2e6;border-radius:8px;cursor:pointer}.section-check-item:hover{background:#fff3e0;border-color:#fd7e14}</style>
@endpush
@section(\'content\')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="mb-1 fw-bold">Assign Subject to Classes</h4><p class="text-muted mb-0">Core = all sections. Elective = specific sections.</p></div>
        <a href="{{ route(\'admin.subject-assignments.index\') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a>
    </div>
    <form method="POST" action="{{ route(\'admin.subject-assignments.store\') }}">@csrf
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card mb-3"><div class="card-header bg-light fw-semibold py-2"><span class="text-primary me-2"><i class="bi bi-1-circle-fill"></i></span> Academic Year</div><div class="card-body">
                <select name="academic_year_id" id="academicYear" class="form-select" required><option value="">-- Select --</option>@foreach($academicYears as $ay)<option value="{{ $ay->id }}">{{ $ay->name }}</option>@endforeach</select>
            </div></div>
            <div class="card mb-3"><div class="card-header bg-light fw-semibold py-2"><span class="text-primary me-2"><i class="bi bi-2-circle-fill"></i></span> Subject</div><div class="card-body">
                <select name="subject_id" id="subjectSelect" class="form-select" required><option value="">-- Select --</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}" data-type="{{ strtolower($subject->type ?? \'\') }}">{{ $subject->name }} @if($subject->code)({{ $subject->code }})@endif @if($subject->type)- {{ $subject->type }}@endif</option>@endforeach</select>
            </div></div>
            <div class="card mb-3"><div class="card-header bg-light fw-semibold py-2"><span class="text-primary me-2"><i class="bi bi-3-circle-fill"></i></span> Assignment Type</div><div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><div class="assignment-type-card card text-center p-3 active-core" id="typeCore" data-type="core"><i class="bi bi-star-fill text-primary fs-2"></i><div class="fw-semibold mt-1">Core Subject</div><div class="text-muted small mt-1">All sections of selected classes</div></div></div>
                    <div class="col-md-6"><div class="assignment-type-card card text-center p-3" id="typeElective" data-type="elective"><i class="bi bi-star-half text-warning fs-2"></i><div class="fw-semibold mt-1">Elective / Other</div><div class="text-muted small mt-1">Specific sections only</div></div></div>
                </div>
                <input type="hidden" name="assignment_type" id="assignmentType" value="core">
            </div></div>
            <div class="card mb-3"><div class="card-header bg-light fw-semibold py-2"><span class="text-primary me-2"><i class="bi bi-4-circle-fill"></i></span> Select Classes <button type="button" class="btn btn-sm btn-outline-primary float-end" id="toggleAllClasses">Select All</button></div><div class="card-body">
                <div class="row g-2" id="classList">@foreach($classes as $class)<div class="col-md-4 col-sm-6"><div class="class-check-item" data-class-id="{{ $class->id }}"><input type="checkbox" class="form-check-input class-check" name="class_ids[]" value="{{ $class->id }}" id="class_{{ $class->id }}"><label for="class_{{ $class->id }}" class="ms-2 user-select-none cursor-pointer"><span class="fw-medium">{{ $class->name }}</span></label></div></div>@endforeach</div>
            </div></div>
            <div class="card mb-3" id="sectionCard" style="display:none;"><div class="card-header bg-light fw-semibold py-2"><span class="text-warning me-2"><i class="bi bi-5-circle-fill"></i></span> Select Sections <span class="badge bg-warning text-dark ms-2">Elective Only</span> <button type="button" class="btn btn-sm btn-outline-warning float-end" id="toggleAllSections">Select All</button></div><div class="card-body">
                <div id="sectionList"><p class="text-muted mb-0"><i class="bi bi-info-circle me-1"></i>Select classes above first.</p></div>
            </div></div>
            <div class="card mb-3"><div class="card-header bg-light fw-semibold py-2"><span class="text-secondary me-2"><i class="bi bi-person-check-fill"></i></span> Teacher (Optional)</div><div class="card-body">
                <select name="teacher_id" class="form-select"><option value="">-- No Teacher --</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}">{{ $teacher->name }}</option>@endforeach</select>
            </div></div>
        </div>
        <div class="col-lg-5">
            <div class="card border-primary mb-3"><div class="card-header bg-primary text-white py-2"><i class="bi bi-clipboard-check me-1"></i> Summary</div><div class="card-body" id="summaryBody"><div class="text-center text-muted py-4"><i class="bi bi-arrow-left-circle fs-1"></i><p class="mt-2">Select a subject and classes.</p></div></div></div>
            <div class="d-grid gap-2"><button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-circle me-1"></i> Save Assignment(s)</button><a href="{{ route(\'admin.subject-assignments.index\') }}" class="btn btn-outline-secondary">Cancel</a></div>
        </div>
    </div>
    </form>
</div>
@endsection
@push(\'scripts\')
<script>
const API_BASE=\'{{ url("/admin/subject-assignments/api") }}\';let currentType=\'core\';let selectedClasses=new Set();let allSectionsCache={};
document.getElementById(\'typeCore\').addEventListener(\'click\',function(){currentType=\'core\';document.getElementById(\'assignmentType\').value=\'core\';this.classList.add(\'active-core\');document.getElementById(\'typeElective\').classList.remove(\'active-elective\');document.getElementById(\'sectionCard\').style.display=\'none\';updateSummary();});
document.getElementById(\'typeElective\').addEventListener(\'click\',function(){currentType=\'elective\';document.getElementById(\'assignmentType\').value=\'elective\';this.classList.add(\'active-elective\');document.getElementById(\'typeCore\').classList.remove(\'active-core\');document.getElementById(\'sectionCard\').style.display=\'block\';updateSummary();});
document.querySelectorAll(\'.class-check\').forEach(cb=>{cb.addEventListener(\'change\',function(){const cid=this.value;const item=this.closest(\'.class-check-item\');if(this.checked){selectedClasses.add(cid);item.classList.add(\'selected\');loadSectionsForClass(cid);}else{selectedClasses.delete(cid);item.classList.remove(\'selected\');}updateSummary();});});
document.querySelectorAll(\'.class-check-item\').forEach(item=>{item.addEventListener(\'click\',function(e){if(e.target.tagName===\'INPUT\')return;const cb=this.querySelector(\'.class-check\');cb.checked=!cb.checked;cb.dispatchEvent(new Event(\'change\'));});});
document.getElementById(\'toggleAllClasses\').addEventListener(\'click\',function(){const all=document.querySelectorAll(\'.class-check\');const allChecked=Array.from(all).every(cb=>cb.checked);all.forEach(cb=>{cb.checked=!allChecked;cb.dispatchEvent(new Event(\'change\'));});this.textContent=allChecked?\'Select All\':\'Deselect All\';});
function loadSectionsForClass(classId){if(allSectionsCache[classId]){renderSections();return;}fetch(API_BASE+\'/sections?class_id=\'+classId).then(r=>r.json()).then(s=>{allSectionsCache[classId]=s;renderSections();}).catch(()=>{allSectionsCache[classId]=[];});}
function renderSections(){const container=document.getElementById(\'sectionList\');if(selectedClasses.size===0){container.innerHTML=\'<p class="text-muted mb-0">Select classes above first.</p>\';return;}let html=\'\';selectedClasses.forEach(cid=>{const cn=document.querySelector(\'.class-check-item[data-class-id="\'+cid+\'"]\')?.querySelector(\'label span\')?.textContent||\'Class \'+cid;const secs=allSectionsCache[cid]||[];html+=\'<div class="mb-3"><div class="fw-semibold mb-2 text-primary"><i class="bi bi-collection me-1"></i>\'+cn+\'</div><div class="d-flex flex-wrap gap-2">\';secs.forEach(sec=>{html+=\'<div class="section-check-item"><input type="checkbox" class="form-check-input section-check" name="section_ids[]" value="\'+sec.id+\'" id="sec_\'+cid+\'_\'+sec.id+\'" \'+(currentType===\'elective\'?\'checked\':\'\')+\'><label for="sec_\'+cid+\'_\'+sec.id+\'" class="ms-1 user-select-none cursor-pointer small">\'+sec.name+\'</label></div>\';});if(secs.length===0)html+=\'<small class="text-muted">No sections</small>\';html+=\'</div></div>\';});container.innerHTML=html;document.querySelectorAll(\'.section-check\').forEach(cb=>{cb.addEventListener(\'change\',updateSummary);});}
document.getElementById(\'subjectSelect\').addEventListener(\'change\',function(){const t=this.options[this.selectedIndex].getAttribute(\'data-type\')||\'\';if(t===\'core\')document.getElementById(\'typeCore\').click();else if(t===\'elective\')document.getElementById(\'typeElective\').click();updateSummary();});
function updateSummary(){const sel=document.getElementById(\'subjectSelect\'),ay=document.getElementById(\'academicYear\');const sn=sel.options[sel.selectedIndex]?.text||\'Not selected\';const an=ay.options[ay.selectedIndex]?.text||\'Not selected\';const cc=selectedClasses.size;const sb=document.getElementById(\'summaryBody\');if(cc===0){sb.innerHTML=\'<div class="text-center text-muted py-4"><p>Select a subject and classes.</p></div>\';return;}let si=currentType===\'core\'?\'<span class="text-primary">All Sections</span>\':\'<span class="text-warning">\'+document.querySelectorAll(\'.section-check:checked\').length+\' section(s)</span>\';let tc=currentType===\'core\'?cc:document.querySelectorAll(\'.section-check:checked\').length;sb.innerHTML=\'<table class="table table-sm mb-0"><tr><td class="text-muted">Subject</td><td class="fw-semibold">\'+sn+\'</td></tr><tr><td class="text-muted">AY</td><td>\'+an+\'</td></tr><tr><td class="text-muted">Type</td><td><span class="badge \'+(currentType===\'core\'?\'bg-primary\':\'bg-warning text-dark\')+\'">\'+(currentType===\'core\'?\'Core\':\'Elective\')+\'</span></td></tr><tr><td class="text-muted">Classes</td><td>\'+cc+\'</td></tr><tr><td class="text-muted">Sections</td><td>\'+si+\'</td></tr><tr class="border-top"><td class="text-muted fw-semibold">Records</td><td class="fw-bold fs-5 text-primary">\'+tc+\'</td></tr></table>\';}
updateSummary();
</script>
@endpush');

echo "  OK: subject-assignments/create.blade.php\n";

wf('resources/views/admin/subject-assignments/edit.blade.php', '@extends(\'layouts.admin\')
@section(\'title\', \'Edit Assignment\')
@section(\'content\')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4"><div><h4 class="mb-1 fw-bold">Edit Assignment</h4><p class="text-muted mb-0">@if(is_null($assignment->section_id))Core - all sections @else Elective - section-specific @endif</p></div><a href="{{ route(\'admin.subject-assignments.index\') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a></div>
    <div class="row justify-content-center"><div class="col-lg-8"><div class="card"><div class="card-body">
        <form method="POST" action="{{ route(\'admin.subject-assignments.update\', $assignment) }}">@csrf @method(\'PUT\')
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label fw-semibold">Academic Year</label><select name="academic_year_id" class="form-select" required><option value="">-- Select --</option>@foreach($academicYears as $ay)<option value="{{ $ay->id }}" {{ $assignment->academic_year_id == $ay->id ? \'selected\' : \'\' }}>{{ $ay->name }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Subject</label><select name="subject_id" class="form-select" required><option value="">-- Select --</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}" {{ $assignment->subject_id == $subject->id ? \'selected\' : \'\' }}>{{ $subject->name }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Class</label><select name="class_id" class="form-select" required><option value="">-- Select --</option>@foreach($classes as $class)<option value="{{ $class->id }}" {{ $assignment->class_id == $class->id ? \'selected\' : \'\' }}>{{ $class->name }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Section @if(is_null($assignment->section_id))<span class="badge bg-primary ms-1">Core = All</span>@endif</label><select name="section_id" class="form-select"><option value="">-- All Sections (Core) --</option>@foreach($sections as $sec)<option value="{{ $sec->id }}" {{ $assignment->section_id == $sec->id ? \'selected\' : \'\' }}>{{ $sec->name }}</option>@endforeach</select><div class="form-text"><i class="bi bi-info-circle me-1"></i>Empty = Core (all). Selected = Elective (specific).</div></div>
                <div class="col-12"><label class="form-label fw-semibold">Teacher</label><select name="teacher_id" class="form-select"><option value="">-- No Teacher --</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}" {{ $assignment->teacher_id == $teacher->id ? \'selected\' : \'\' }}>{{ $teacher->name }}</option>@endforeach</select></div>
            </div>
            <div class="d-flex gap-2 mt-4"><button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Update</button><a href="{{ route(\'admin.subject-assignments.index\') }}" class="btn btn-outline-secondary">Cancel</a></div>
        </form>
    </div></div></div></div>
</div>
@endsection');

echo "  OK: subject-assignments views\n";

// ===== DATABASE + CACHE CLEAR =====
echo "\n[DB] Ensuring tables exist...\n";
try {
    $dbConfig = require $base . '/config/database.php';
    $conn = $dbConfig['connections'][$dbConfig['default']];
    $mysqli = new mysqli($conn['host'] ?? '127.0.0.1', $conn['username'] ?? 'root', $conn['password'] ?? '', $conn['database'] ?? 'school_of_redemption', $conn['port'] ?? 3306);
    if ($mysqli->connect_errno) { echo "  [WARN] DB: " . $mysqli->connect_error . "\n"; }
    else {
        $mysqli->multi_query("CREATE TABLE IF NOT EXISTS mark_entries (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, student_id BIGINT UNSIGNED NOT NULL, subject_id BIGINT UNSIGNED NOT NULL, academic_year_id BIGINT UNSIGNED NOT NULL, term_id BIGINT UNSIGNED NOT NULL, class_id BIGINT UNSIGNED NOT NULL, section_id BIGINT UNSIGNED NOT NULL, teacher_id BIGINT UNSIGNED NULL, ca1 DECIMAL(5,2) DEFAULT 0, ca2 DECIMAL(5,2) DEFAULT 0, ca3 DECIMAL(5,2) DEFAULT 0, ca4 DECIMAL(5,2) DEFAULT 0, ca5 DECIMAL(5,2) DEFAULT 0, ca6 DECIMAL(5,2) DEFAULT 0, ca7 DECIMAL(5,2) DEFAULT 0, ca8 DECIMAL(5,2) DEFAULT 0, ca9 DECIMAL(5,2) DEFAULT 0, ca10 DECIMAL(5,2) DEFAULT 0, conduct DECIMAL(5,2) DEFAULT 0, handwriting DECIMAL(5,2) DEFAULT 0, creativity DECIMAL(5,2) DEFAULT 0, test1 DECIMAL(5,2) DEFAULT 0, test2 DECIMAL(5,2) DEFAULT 0, mid_term DECIMAL(5,2) DEFAULT 0, final_exam DECIMAL(5,2) DEFAULT 0, ca_total DECIMAL(6,2) DEFAULT 0, exam_total DECIMAL(6,2) DEFAULT 0, grand_total DECIMAL(6,2) DEFAULT 0, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uniq_mark (student_id, subject_id, academic_year_id, term_id), INDEX idx_cs (class_id, section_id, academic_year_id, term_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        CREATE TABLE IF NOT EXISTS exams (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, academic_year_id BIGINT UNSIGNED NOT NULL, term_id BIGINT UNSIGNED NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(100) NULL, start_date DATE NULL, end_date DATE NULL, start_time TIME NULL, end_time TIME NULL, total_marks INT NULL, passing_marks INT NULL, description TEXT NULL, class_id BIGINT UNSIGNED NULL, subject_id BIGINT UNSIGNED NULL, exam_date DATE NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        while ($mysqli->more_results() && $mysqli->next_result()) {}
        echo "  [OK] Tables ensured.\n";
        $mysqli->close();
    }
} catch (Exception $e) { echo "  [WARN] " . $e->getMessage() . "\n"; }

echo "\n[Cache] Clearing...\n";
foreach (['route:clear','config:clear','view:clear'] as $c) { $o=shell_exec("php artisan $c 2>&1"); if($o) echo "  ".trim($o)."\n"; }

echo "\n=== ALL DONE! ===\n";
echo "Test these pages:\n";
echo "  Mark Entry:       http://localhost/school-of-redemption/public/admin/mark-entries\n";
echo "  Staff:            http://localhost/school-of-redemption/public/admin/staff\n";
echo "  Assign Subjects:  http://localhost/school-of-redemption/public/admin/subject-assignments\n";
echo "  Exams:            http://localhost/school-of-redemption/public/admin/exams\n";
echo "  Subjects:         http://localhost/school-of-redemption/public/admin/subjects\n";
