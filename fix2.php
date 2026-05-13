<?php
error_reporting(E_ALL);
 $B=getcwd();
echo "=== Mark Entry Setup ===\n";
require $B.'/vendor/autoload.php';
 $app=require $B.'/bootstrap/app.php';
 $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use Illuminate\Support\Facades\DB;

echo "[1/6] Tables... ";
DB::statement("CREATE TABLE IF NOT EXISTS subjects (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,name VARCHAR(255) NOT NULL,code VARCHAR(50) NOT NULL UNIQUE,created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
DB::statement("CREATE TABLE IF NOT EXISTS teacher_assignments (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,user_id BIGINT UNSIGNED NOT NULL,subject_id BIGINT UNSIGNED NOT NULL,class_grade VARCHAR(50) DEFAULT NULL,section VARCHAR(50) DEFAULT NULL,created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,UNIQUE KEY ta_uq(user_id,subject_id,class_grade,section)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
DB::statement("CREATE TABLE IF NOT EXISTS mark_entries (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,student_id BIGINT UNSIGNED NOT NULL,subject_id BIGINT UNSIGNED NOT NULL,academic_year_id BIGINT UNSIGNED DEFAULT NULL,term_id BIGINT UNSIGNED DEFAULT NULL,class_grade VARCHAR(50) DEFAULT NULL,section VARCHAR(50) DEFAULT NULL,ca1 DECIMAL(5,2) NULL,ca2 DECIMAL(5,2) NULL,ca3 DECIMAL(5,2) NULL,ca4 DECIMAL(5,2) NULL,ca5 DECIMAL(5,2) NULL,ca6 DECIMAL(5,2) NULL,ca7 DECIMAL(5,2) NULL,ca8 DECIMAL(5,2) NULL,ca9 DECIMAL(5,2) NULL,ca10 DECIMAL(5,2) NULL,conduct DECIMAL(5,2) NULL,handwriting DECIMAL(5,2) NULL,creativity DECIMAL(5,2) NULL,test1 DECIMAL(5,2) NULL,test2 DECIMAL(5,2) NULL,mid_term DECIMAL(5,2) NULL,final_exam DECIMAL(5,2) NULL,ca_raw_total DECIMAL(6,2) DEFAULT 0,ca_scaled DECIMAL(6,2) DEFAULT 0,term_total DECIMAL(6,2) DEFAULT 0,grade VARCHAR(10) DEFAULT NULL,teacher_id BIGINT UNSIGNED DEFAULT NULL,created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,UNIQUE KEY me_uq(student_id,subject_id,academic_year_id,term_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "OK\n";

echo "[2/6] Models... ";
file_put_contents("$B/app/Models/Subject.php",<<<'E'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Subject extends Model{
    use HasFactory;
    protected $fillable=["name","code"];
    public function teacherAssignments(){return $this->hasMany(TeacherAssignment::class,"subject_id");}
    public function markEntries(){return $this->hasMany(MarkEntry::class,"subject_id");}
}
E
);
file_put_contents("$B/app/Models/TeacherAssignment.php",<<<'E'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class TeacherAssignment extends Model{
    use HasFactory;
    protected $fillable=["user_id","subject_id","class_grade","section"];
    public function user(){return $this->belongsTo(User::class,"user_id");}
    public function subject(){return $this->belongsTo(Subject::class,"subject_id");}
}
E
);
file_put_contents("$B/app/Models/MarkEntry.php",<<<'E'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class MarkEntry extends Model{
    use HasFactory;
    protected $table="mark_entries";
    protected $fillable=["student_id","subject_id","academic_year_id","term_id","class_grade","section","ca1","ca2","ca3","ca4","ca5","ca6","ca7","ca8","ca9","ca10","conduct","handwriting","creativity","test1","test2","mid_term","final_exam","ca_raw_total","ca_scaled","term_total","grade","teacher_id"];
    protected $casts=["ca1"=>"decimal:2","ca2"=>"decimal:2","ca3"=>"decimal:2","ca4"=>"decimal:2","ca5"=>"decimal:2","ca6"=>"decimal:2","ca7"=>"decimal:2","ca8"=>"decimal:2","ca9"=>"decimal:2","ca10"=>"decimal:2","conduct"=>"decimal:2","handwriting"=>"decimal:2","creativity"=>"decimal:2","test1"=>"decimal:2","test2"=>"decimal:2","mid_term"=>"decimal:2","final_exam"=>"decimal:2","ca_raw_total"=>"decimal:2","ca_scaled"=>"decimal:2","term_total"=>"decimal:2"];
    public static function getFields(){return [["key"=>"ca1","label"=>"CA 1","max"=>5,"group"=>"ca"],["key"=>"ca2","label"=>"CA 2","max"=>5,"group"=>"ca"],["key"=>"ca3","label"=>"CA 3","max"=>5,"group"=>"ca"],["key"=>"ca4","label"=>"CA 4","max"=>5,"group"=>"ca"],["key"=>"ca5","label"=>"CA 5","max"=>5,"group"=>"ca"],["key"=>"ca6","label"=>"CA 6","max"=>5,"group"=>"ca"],["key"=>"ca7","label"=>"CA 7","max"=>5,"group"=>"ca"],["key"=>"ca8","label"=>"CA 8","max"=>5,"group"=>"ca"],["key"=>"ca9","label"=>"CA 9","max"=>5,"group"=>"ca"],["key"=>"ca10","label"=>"CA 10","max"=>5,"group"=>"ca"],["key"=>"conduct","label"=>"Conduct","max"=>5,"group"=>"ca"],["key"=>"handwriting","label"=>"Handwriting","max"=>5,"group"=>"ca"],["key"=>"creativity","label"=>"Creativity","max"=>10,"group"=>"ca"],["key"=>"test1","label"=>"Test 1","max"=>10,"group"=>"exam"],["key"=>"test2","label"=>"Test 2","max"=>10,"group"=>"exam"],["key"=>"mid_term","label"=>"Mid-Term","max"=>20,"group"=>"exam"],["key"=>"final_exam","label"=>"Final Exam","max"=>30,"group"=>"exam"]];}
    public static function calcTotals($d){$cf=["ca1","ca2","ca3","ca4","ca5","ca6","ca7","ca8","ca9","ca10","conduct","handwriting","creativity"];$cm=[5,5,5,5,5,5,5,5,5,5,5,5,10];$cr=0;foreach($cf as $i=>$f){$v=isset($d[$f])&&$d[$f]!==""&&$d[$f]!==null?floatval($d[$f]):0;$cr+=min($v,$cm[$i]);}$cs=$cr>0?round(($cr/70)*30,2):0;$et=0;foreach(["test1","test2","mid_term","final_exam"] as $f){$v=isset($d[$f])&&$d[$f]!==""&&$d[$f]!==null?floatval($d[$f]):0;$et+=$v;}$tt=round($cs+$et,2);$g="F";if($tt>=90)$g="A+";elseif($tt>=80)$g="A";elseif($tt>=75)$g="A-";elseif($tt>=70)$g="B+";elseif($tt>=65)$g="B";elseif($tt>=60)$g="B-";elseif($tt>=55)$g="C+";elseif($tt>=50)$g="C";elseif($tt>=45)$g="C-";elseif($tt>=40)$g="D";return["ca_raw_total"=>$cr,"ca_scaled"=>$cs,"term_total"=>$tt,"grade"=>$g];}
    public function student(){return $this->belongsTo(Student::class,"student_id");}
    public function subject(){return $this->belongsTo(Subject::class,"subject_id");}
    public function academicYear(){return $this->belongsTo(AcademicYear::class,"academic_year_id");}
    public function term(){return $this->belongsTo(Term::class,"term_id");}
}
E
);
echo "OK\n";

echo "[3/6] Controllers... ";
file_put_contents("$B/app/Http/Controllers/Admin/SubjectController.php",<<<'E'
<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
class SubjectController extends Controller{
    public function index(){return view("admin.subjects.index",["subjects"=>Subject::orderBy("name")->get()]);}
    public function create(){return view("admin.subjects.create");}
    public function store(Request $r){$r->validate(["name"=>"required","code"=>"required|unique:subjects,code"]);Subject::create($r->only("name","code"));return redirect()->route("admin.subjects.index")->with("success","Subject created!");}
    public function edit(Subject $s){return view("admin.subjects.edit",["subject"=>$s]);}
    public function update(Request $r,Subject $s){$r->validate(["name"=>"required","code"=>"required|unique:subjects,code,".$s->id]);$s->update($r->only("name","code"));return redirect()->route("admin.subjects.index")->with("success","Updated!");}
    public function destroy(Subject $s){$s->delete();return redirect()->route("admin.subjects.index")->with("success","Deleted!");}
}
E
);
file_put_contents("$B/app/Http/Controllers/Admin/TeacherAssignmentController.php",<<<'E'
<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\TeacherAssignment;
use App\Models\Student;
use Illuminate\Http\Request;
class TeacherAssignmentController extends Controller{
    public function index(){return view("admin.teacher-assignments.index",["assignments"=>TeacherAssignment::with(["user","subject"])->latest()->get()]);}
    public function create(){return view("admin.teacher-assignments.create",["teachers"=>\App\Models\User::orderBy("name")->get(),"subjects"=>\App\Models\Subject::orderBy("name")->get(),"classGrades"=>Student::distinct()->whereNotNull("class_grade")->orderBy("class_grade")->pluck("class_grade")]);}
    public function store(Request $r){$r->validate(["user_id"=>"required|exists:users,id","subject_id"=>"required|exists:subjects,id","class_grade"=>"nullable","section"=>"nullable"]);TeacherAssignment::create($r->only("user_id","subject_id","class_grade","section"));return redirect()->route("admin.teacher-assignments.index")->with("success","Assigned!");}
    public function destroy(\App\Models\TeacherAssignment $ta){$ta->delete();return redirect()->route("admin.teacher-assignments.index")->with("success","Removed!");}
}
E
);
file_put_contents("$B/app/Http/Controllers/Admin/MarkEntryController.php",<<<'E'
<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\MarkEntry;use App\Models\Subject;use App\Models\TeacherAssignment;use App\Models\Student;use App\Models\AcademicYear;use App\Models\Term;
use Illuminate\Http\Request;
class MarkEntryController extends Controller{
    public function index(Request $r){$q=MarkEntry::with(["student","subject","term","academicYear"]);if($r->filled("academic_year_id"))$q->where("academic_year_id",$r->academic_year_id);if($r->filled("term_id"))$q->where("term_id",$r->term_id);if($r->filled("subject_id"))$q->where("subject_id",$r->subject_id);if($r->filled("class_grade"))$q->where("class_grade",$r->class_grade);if($r->filled("section"))$q->where("section",$r->section);return view("admin.mark-entries.index",["entries"=>$q->latest()->paginate(25),"subjects"=>Subject::orderBy("name")->get(),"academicYears"=>AcademicYear::orderBy("name","desc")->get(),"terms"=>Term::orderBy("name")->get()]);}
    public function create(){$tid=auth()->id();$aids=TeacherAssignment::where("user_id",$tid)->pluck("subject_id")->toArray();$subjects=$aids?Subject::whereIn("id",$aids)->orderBy("name")->get():Subject::orderBy("name")->get();return view("admin.mark-entries.create",["subjects"=>$subjects,"academicYears"=>AcademicYear::where("is_active",true)->orderBy("name","desc")->get(),"classGrades"=>Student::distinct()->whereNotNull("class_grade")->orderBy("class_grade")->pluck("class_grade")]);}
    public function destroy(MarkEntry $me){$me->delete();return redirect()->route("admin.mark-entries.index")->with("success","Deleted.");}
    public function apiSave(Request $r){$d=$r->only(["student_id","subject_id","academic_year_id","term_id","class_grade","section","ca1","ca2","ca3","ca4","ca5","ca6","ca7","ca8","ca9","ca10","conduct","handwriting","creativity","test1","test2","mid_term","final_exam"]);$tid=auth()->id();$ok=TeacherAssignment::where("user_id",$tid)->where("subject_id",$d["subject_id"])->where(function($q)use($d){$q->whereNull("class_grade")->orWhere("class_grade",$d["class_grade"]??"");})->exists();if(!$ok&&TeacherAssignment::count()>0)return response()->json(["error"=>"Not authorized"],403);$t=MarkEntry::calcTotals($d);$d=array_merge($d,$t,["teacher_id"=>$tid]);$e=MarkEntry::updateOrCreate(["student_id"=>$d["student_id"],"subject_id"=>$d["subject_id"],"academic_year_id"=>$d["academic_year_id"]??null,"term_id"=>$d["term_id"]??null],$d);return response()->json(["success"=>true,"totals"=>$t]);}
    public function apiStudents(Request $r){$q=Student::where("status","active");if($r->filled("class_grade"))$q->where("class_grade",$r->class_grade);if($r->filled("section"))$q->where("section",$r->section);$students=$q->orderBy("last_name")->orderBy("first_name")->get();$sid=$r->get("subject_id");$ayid=$r->get("academic_year_id");$tid=$r->get("term_id");$marks=[];if($sid){$mq=MarkEntry::where("subject_id",$sid);if($ayid)$mq->where("academic_year_id",$ayid);if($tid)$mq->where("term_id",$tid);foreach($mq->get() as $m)$marks[$m->student_id]=$m;}$res=[];foreach($students as $s){$m=$marks[$s->id]??null;$res[]=["id"=>$s->id,"name"=>$s->first_name." ".$s->last_name,"first_name"=>$s->first_name,"last_name"=>$s->last_name,"roll_number"=>$s->roll_number,"class_grade"=>$s->class_grade,"section"=>$s->section,"has_marks"=>$m!==null,"marks"=>$m?["ca1"=>$m->ca1,"ca2"=>$m->ca2,"ca3"=>$m->ca3,"ca4"=>$m->ca4,"ca5"=>$m->ca5,"ca6"=>$m->ca6,"ca7"=>$m->ca7,"ca8"=>$m->ca8,"ca9"=>$m->ca9,"ca10"=>$m->ca10,"conduct"=>$m->conduct,"handwriting"=>$m->handwriting,"creativity"=>$m->creativity,"test1"=>$m->test1,"test2"=>$m->test2,"mid_term"=>$m->mid_term,"final_exam"=>$m->final_exam,"ca_raw_total"=>$m->ca_raw_total,"ca_scaled"=>$m->ca_scaled,"term_total"=>$m->term_total,"grade"=>$m->grade]:null];}return response()->json(["students"=>$res]);}
    public function apiTerms(Request $r){$t=Term::where("is_active",true);$ayid=$r->get("academic_year_id");if($ayid)$t->where("academic_year_id",$ayid);return response()->json($t->orderBy("term_number")->get());}
    public function apiSections(Request $r){$cg=$r->get("class_grade");$s=Student::whereNotNull("section")->where("section","!=","");if($cg)$s->where("class_grade",$cg);return response()->json($s->distinct()->orderBy("section")->pluck("section"));}
}
E
);
echo "OK\n";
echo "[4/6] Views... ";
@mkdir("$B/resources/views/admin/subjects",0777,true);
@mkdir("$B/resources/views/admin/teacher-assignments",0777,true);
@mkdir("$B/resources/views/admin/mark-entries",0777,true);

file_put_contents("$B/resources/views/admin/subjects/index.blade.php",<<<'E'
@extends('layouts.admin')
@section('title','Manage Subjects')
@section('content')
<div class="container-fluid py-4">
<div class="d-flex justify-content-between align-items-center mb-4"><h4 class="fw-bold mb-0"><i class="fas fa-book me-2 text-primary"></i>Manage Subjects</h4><a href="{{ route('admin.subjects.create') }}" class="btn btn-gold"><i class="fas fa-plus me-1"></i> Add Subject</a></div>
<div class="card admin-card border-0 shadow-sm"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>#</th><th>Name</th><th>Code</th><th style="width:120px" class="text-end">Actions</th></tr></thead><tbody>@forelse($subjects as $s)<tr><td>{{ $loop->iteration }}</td><td class="fw-semibold">{{ $s->name }}</td><td><span class="badge bg-primary">{{ $s->code }}</span></td><td class="text-end"><a href="{{ route('admin.subjects.edit',$s) }}" class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-edit"></i></a><button type="button" class="btn btn-sm btn-outline-danger" onclick="if(confirm('Delete?'))document.getElementById('d{{ $s->id }}').submit()"><i class="fas fa-trash"></i></button><form id="d{{ $s->id }}" action="{{ route('admin.subjects.destroy',$s) }}" method="POST" class="d-none">@csrf @method('DELETE')</form></td></tr>@empty<tr><td colspan="4" class="text-center text-muted py-5">No subjects.</td></tr>@endforelse</tbody></table></div></div></div>
</div>
@endsection
E
);
file_put_contents("$B/resources/views/admin/subjects/create.blade.php",<<<'E'
@extends('layouts.admin')
@section('title','Add Subject')
@section('content')
<div class="container-fluid py-4"><nav aria-label="breadcrumb" class="mb-3"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('admin.subjects.index') }}">Subjects</a></li><li class="breadcrumb-item active">Add</li></ol></nav><h4 class="fw-bold mb-4"><i class="fas fa-book me-2 text-primary"></i>Add Subject</h4><div class="card admin-card border-0 shadow-sm" style="max-width:600px"><div class="card-body"><form method="POST" action="{{ route('admin.subjects.store') }}">@csrf<div class="mb-3"><label class="form-label fw-semibold">Name</label><input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="mb-3"><label class="form-label fw-semibold">Code</label><input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" required placeholder="MATH">@error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="d-flex gap-2"><button type="submit" class="btn btn-gold"><i class="fas fa-save me-1"></i> Save</button><a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-secondary">Cancel</a></div></form></div></div></div>
@endsection
E
);
file_put_contents("$B/resources/views/admin/subjects/edit.blade.php",<<<'E'
@extends('layouts.admin')
@section('title','Edit Subject')
@section('content')
<div class="container-fluid py-4"><nav aria-label="breadcrumb" class="mb-3"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('admin.subjects.index') }}">Subjects</a></li><li class="breadcrumb-item active">Edit</li></ol></nav><h4 class="fw-bold mb-4"><i class="fas fa-book me-2 text-primary"></i>Edit Subject</h4><div class="card admin-card border-0 shadow-sm" style="max-width:600px"><div class="card-body"><form method="POST" action="{{ route('admin.subjects.update',$subject) }}">@csrf @method('PUT')<div class="mb-3"><label class="form-label fw-semibold">Name</label><input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name',$subject->name) }}" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="mb-3"><label class="form-label fw-semibold">Code</label><input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code',$subject->code) }}" required>@error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="d-flex gap-2"><button type="submit" class="btn btn-gold"><i class="fas fa-save me-1"></i> Update</button><a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-secondary">Cancel</a></div></form></div></div></div>
@endsection
E
);

file_put_contents("$B/resources/views/admin/teacher-assignments/index.blade.php",<<<'E'
@extends('layouts.admin')
@section('title','Teacher Assignments')
@section('content')
<div class="container-fluid py-4"><div class="d-flex justify-content-between align-items-center mb-4"><h4 class="fw-bold mb-0"><i class="fas fa-chalkboard-teacher me-2 text-primary"></i>Teacher Assignments</h4><a href="{{ route('admin.teacher-assignments.create') }}" class="btn btn-gold"><i class="fas fa-plus me-1"></i> Assign Teacher</a></div><div class="card admin-card border-0 shadow-sm"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>#</th><th>Teacher</th><th>Subject</th><th>Class</th><th>Section</th><th style="width:80px" class="text-end">Action</th></tr></thead><tbody>@forelse($assignments as $a)<tr><td>{{ $loop->iteration }}</td><td class="fw-semibold">{{ $a->user->name ?? 'User #'.$a->user_id }}</td><td><span class="badge bg-primary">{{ $a->subject->name ?? '--' }}</span></td><td>{{ $a->class_grade ? 'Class '.$a->class_grade : 'All' }}</td><td>{{ $a->section ?? 'All' }}</td><td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger" onclick="if(confirm('Remove?'))document.getElementById('d{{ $a->id }}').submit()"><i class="fas fa-trash"></i></button><form id="d{{ $a->id }}" action="{{ route('admin.teacher-assignments.destroy',$a) }}" method="POST" class="d-none">@csrf @method('DELETE')</form></td></tr>@empty<tr><td colspan="6" class="text-center text-muted py-5">No assignments.</td></tr>@endforelse</tbody></table></div></div></div></div>
@endsection
E
);
file_put_contents("$B/resources/views/admin/teacher-assignments/create.blade.php",<<<'E'
@extends('layouts.admin')
@section('title','Assign Teacher')
@section('content')
<div class="container-fluid py-4"><nav aria-label="breadcrumb" class="mb-3"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('admin.teacher-assignments.index') }}">Assignments</a></li><li class="breadcrumb-item active">Assign</li></ol></nav><h4 class="fw-bold mb-4"><i class="fas fa-chalkboard-teacher me-2 text-primary"></i>Assign Teacher</h4><div class="card admin-card border-0 shadow-sm" style="max-width:600px"><div class="card-body"><form method="POST" action="{{ route('admin.teacher-assignments.store') }}">@csrf<div class="mb-3"><label class="form-label fw-semibold">Teacher</label><select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required><option value="">Select...</option>@foreach($teachers as $t)<option value="{{ $t->id }}" {{ old('user_id')==$t->id?'selected':'' }}>{{ $t->name }} ({{ $t->email }})</option>@endforeach</select>@error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="mb-3"><label class="form-label fw-semibold">Subject</label><select name="subject_id" class="form-select @error('subject_id') is-invalid @enderror" required><option value="">Select...</option>@foreach($subjects as $s)<option value="{{ $s->id }}" {{ old('subject_id')==$s->id?'selected':'' }}>{{ $s->name }} ({{ $s->code }})</option>@endforeach</select>@error('subject_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="row"><div class="col-6 mb-3"><label class="form-label fw-semibold">Class</label><select name="class_grade" class="form-select"><option value="">All</option>@foreach($classGrades as $cg)<option value="{{ $cg }}">{{ $cg }}</option>@endforeach</select></div><div class="col-6 mb-3"><label class="form-label fw-semibold">Section</label><input type="text" name="section" class="form-control" value="{{ old('section') }}" placeholder="A"></div></div><div class="d-flex gap-2"><button type="submit" class="btn btn-gold"><i class="fas fa-save me-1"></i> Save</button><a href="{{ route('admin.teacher-assignments.index') }}" class="btn btn-outline-secondary">Cancel</a></div></form></div></div></div>
@endsection
E
);

file_put_contents("$B/resources/views/admin/mark-entries/index.blade.php",<<<'E'
@extends('layouts.admin')
@section('title','Mark Entries')
@section('content')
<div class="container-fluid py-4">
<div class="d-flex justify-content-between align-items-center mb-4"><div><nav aria-label="breadcrumb"><ol class="breadcrumb mb-1"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Mark Entries</li></ol></nav><h4 class="fw-bold mb-0"><i class="fas fa-pen-alt me-2 text-primary"></i>Mark Entries</h4></div><a href="{{ route('admin.mark-entries.create') }}" class="btn btn-gold"><i class="fas fa-plus me-1"></i> Enter Marks</a></div>
<div class="card admin-card border-0 shadow-sm mb-4"><div class="card-body"><form action="{{ route('admin.mark-entries.index') }}" method="GET" class="row g-2 align-items-end"><div class="col-md-2"><label class="form-label fw-semibold small">Year</label><select name="academic_year_id" class="form-select form-select-sm"><option value="">All</option>@foreach($academicYears as $ay)<option value="{{ $ay->id }}" {{ request('academic_year_id')==$ay->id?'selected':'' }}>{{ $ay->name }}</option>@endforeach</select></div><div class="col-md-2"><label class="form-label fw-semibold small">Term</label><select name="term_id" class="form-select form-select-sm"><option value="">All</option>@foreach($terms as $t)<option value="{{ $t->id }}" {{ request('term_id')==$t->id?'selected':'' }}>{{ $t->name }}</option>@endforeach</select></div><div class="col-md-2"><label class="form-label fw-semibold small">Subject</label><select name="subject_id" class="form-select form-select-sm"><option value="">All</option>@foreach($subjects as $s)<option value="{{ $s->id }}" {{ request('subject_id')==$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach</select></div><div class="col-md-1"><label class="form-label fw-semibold small">Class</label><input type="text" name="class_grade" class="form-control form-control-sm" value="{{ request('class_grade') }}"></div><div class="col-md-1"><label class="form-label fw-semibold small">Sec</label><input type="text" name="section" class="form-control form-control-sm" value="{{ request('section') }}"></div><div class="col-md-2 d-flex gap-1"><button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="fas fa-filter me-1"></i>Filter</button><a href="{{ route('admin.mark-entries.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-times"></i></a></div></form></div></div>
<div class="card admin-card border-0 shadow-sm"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>Student</th><th>Subject</th><th>Term</th><th>Class</th><th>CA /70</th><th>CA /30</th><th>Exam /70</th><th>Total /100</th><th>Grade</th><th style="width:60px"></th></tr></thead><tbody>@forelse($entries as $e)@php $gc=["A+"=>"success","A"=>"primary","A-"=>"info","B+"=>"secondary","B"=>"dark","B-"=>"warning","C+"=>"secondary","C"=>"secondary","C-"=>"warning","D"=>"danger","F"=>"danger"]; @endphp<tr><td class="fw-semibold">{{ $e->student->first_name ?? '' }} {{ $e->student->last_name ?? '' }}</td><td><span class="badge bg-primary">{{ $e->subject->name ?? '' }}</span></td><td>{{ $e->term->name ?? '' }}</td><td>{{ $e->class_grade ? 'Class '.$e->class_grade : '' }} {{ $e->section }}</td><td>{{ number_format($e->ca_raw_total,1) }}/70</td><td>{{ number_format($e->ca_scaled,1) }}/30</td><td>{{ number_format($e->term_total - $e->ca_scaled,1) }}/70</td><td class="fw-bold">{{ number_format($e->term_total,1) }}</td><td><span class="badge bg-{{ $gc[$e->grade] ?? 'secondary' }}">{{ $e->grade }}</span></td><td><button type="button" class="btn btn-sm btn-outline-danger" onclick="if(confirm('Delete?'))document.getElementById('d{{ $e->id }}').submit()"><i class="fas fa-trash"></i></button><form id="d{{ $e->id }}" action="{{ route('admin.mark-entries.destroy',$e) }}" method="POST" class="d-none">@csrf @method('DELETE')</form></td></tr>@empty<tr><td colspan="10" class="text-center text-muted py-5">No entries.</td></tr>@endforelse</tbody></table></div></div></div>
@if($entries->hasPages())<div class="mt-4 d-flex justify-content-center">{{ $entries->appends(request()->query())->links() }}</div>@endif
</div>
@endsection
E
);
echo "OK\n";
