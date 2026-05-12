<?php
/**
 * fix_all.php - Complete fix for School of Redemption ERP
 * Fixes sidebar, settings, progress reports, models, cache, and all module views
 */
error_reporting(E_ALL);
 $base = dirname(__FILE__);
 $ok = function($m){echo "  [OK] $m\n";};
 $wr = function($m){echo "  [WRITE] $m\n";};
 $warn = function($m){echo "  [WARN] $m\n";};

echo "=== School of Redemption - Complete Fix ===\n\n";

// ── 1. FIX CACHE ──────────────────────────────────────
echo "[1/8] Cache fix...\n";
 $env = $base.'/.env';
if(file_exists($env)){
    $e = file_get_contents($env);
    if(preg_match('/^CACHE_DRIVER\s*=\s*database/mi',$e)){
        try{
            $pdo = new PDO('mysql:host=127.0.0.1;dbname=school_of_redemption','root','');
            $pdo->exec("CREATE TABLE IF NOT EXISTS cache(`key` varchar(255) NOT NULL PRIMARY KEY,`value` mediumtext NOT NULL,`expiration` int NOT NULL)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $ok('cache table created');
        }catch(Exception $x){
            $e=preg_replace('/^CACHE_DRIVER\s*=\s*database/mi','CACHE_DRIVER=file',$e);
            file_put_contents($env,$e);$ok('CACHE_DRIVER set to file');
        }
    }else{$ok('cache ok');}
}

// ── 2. FIX SETTINGS ROUTE ─────────────────────────────
echo "\n[2/8] Settings route fix...\n";
 $rfile = $base.'/routes/web.php';
 $r = file_get_contents($rfile);
// Remove resource route for settings
 $r = preg_replace("/\s*Route::resource\(\s*['\"]settings['\"],\s*[^\)]+\);\s*\n/", "\n", $r);
// Add custom routes before closing of admin group
if(strpos($r,"admin/settings.index")===false){
    $r = preg_replace("/(\n\}\);)\s*$/","
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');\n$1",$r);
    $ok('custom settings routes added');
}
// Fix use import
if(strpos($r,'SettingController')!==false){
    $r = preg_replace("/use App\\\\Http\\\\Controllers\\\\[A-Za-z]+\\\\SettingController;/","use App\\Http\\Controllers\\Setting\\SettingController;",$r);
    if(strpos($r,'use App\\Http\\Controllers\\Setting\\SettingController;')===false){
        $r=preg_replace("/(use App\\\\Http\\\\Controllers\\\\Admin\\\\SettingController;)/","",$r);
        $r=preg_replace("/(use App\\\\Http\\\\Controllers\\\\[^\n]+;\n)/","$1use App\\Http\\Controllers\\Setting\\SettingController;\n",$r,1);
    }
}
file_put_contents($rfile,$r);$ok('routes updated');

// ── 3. SETTINGS CONTROLLER ─────────────────────────────
echo "\n[3/8] Settings controller...\n";
 $cdir = $base.'/app/Http/Controllers/Setting';
if(!is_dir($cdir))mkdir($cdir,0755,true);
file_put_contents($cdir.'/SettingController.php','<?php
namespace App\Http\Controllers\Setting;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
class SettingController extends Controller{
    public function index(){
        $settings=Setting::all()->groupBy("group");
        $groupLabels=["general"=>"General Settings","academic"=>"Academic Settings","contact"=>"Contact Information","social"=>"Social Media Links","about"=>"About Page Content","appearance"=>"Appearance","email"=>"Email Settings","fees"=>"Fee Settings"];
        return view("admin.settings",compact("settings","groupLabels"));
    }
    public function update(Request $request){
        $d=$request->validate(["settings"=>"required|array","settings.*"=>"nullable|string"]);
        foreach($d["settings"] as $k=>$v){
            $p=explode("__",$k,2);
            Setting::updateOrCreate(["key"=>$p[1]??$k],["value"=>$v??"","group"=>$p[0]??"general"]);
        }
        return redirect()->back()->with("success","Settings updated successfully.");
    }
}');
 $ok('SettingController written');

// ── 4. SETTINGS VIEW ──────────────────────────────────
echo "\n[4/8] Settings view...\n";
 $vdir = $base.'/resources/views/admin';
file_put_contents($vdir.'/settings.blade.php','@extends("layouts.admin")
@section("page-title","Settings")
@section("content")
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div><h4 class="fw-bold mb-1"><i class="bi bi-gear me-2"></i>Settings</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route("admin.dashboard") }}" class="text-decoration-none text-muted">Dashboard</a></li>
            <li class="breadcrumb-item active text-gold">Settings</li>
        </ol></nav></div>
    </div>
    @if(session("success"))
    <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i>{{ session("success") }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    <form method="POST" action="{{ route("admin.settings.update") }}">@csrf @method("PUT")
    @foreach($settings as $group => $items)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3" style="border-top:3px solid #c9a84c;"><h5 class="mb-0 fw-semibold"><i class="bi bi-sliders me-2"></i>{{ $groupLabels[$group] ?? ucfirst($group) }}</h5></div>
        <div class="card-body"><div class="row g-3">
            @foreach($items as $s)
            <div class="col-md-6">
                <label class="form-label fw-medium">{{ ucfirst(str_replace("_"," ",$s->key)) }}</label>
                <input type="text" name="settings[{{ $group }}__{{ $s->key }}]" class="form-control form-control-sm" value="{{ old($group."__".$s->key, $s->value) }}">
            </div>
            @endforeach
        </div></div>
    </div>
    @endforeach
    @if($settings->isEmpty())
    <div class="card border-0 shadow-sm mb-4"><div class="card-body text-center py-5">
        <i class="bi bi-inbox text-muted" style="font-size:3rem;"></i>
        <h5 class="mt-3 text-muted">No Settings Found</h5>
        <p class="text-muted">Add settings to the database to configure them here.</p>
    </div></div>
    @endif
    <button type="submit" class="btn btn-gold mb-4"><i class="bi bi-check-lg me-1"></i>Save All Settings</button>
    </form>
</div>
@endsection');
 $ok('settings view written');

// ── 5. PROGRESS REPORT MODULE ──────────────────────────
echo "\n[5/8] Progress Report module...\n";
try{
 $pdo=new PDO('mysql:host=127.0.0.1;dbname=school_of_redemption','root','');
 $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
 $t=$pdo->query("SHOW TABLES LIKE 'progress_reports'")->fetchAll();
if(empty($t)){$pdo->exec("CREATE TABLE progress_reports(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,student_id BIGINT UNSIGNED NOT NULL,academic_year_id BIGINT UNSIGNED NULL,term_id BIGINT UNSIGNED NULL,classroom_id BIGINT UNSIGNED NULL,overall_grade VARCHAR(20) NULL,total_marks DECIMAL(8,2) DEFAULT 0,max_marks DECIMAL(8,2) DEFAULT 100,class_rank INT NULL,remarks TEXT NULL,teacher_comment TEXT NULL,status ENUM('draft','published','archived') DEFAULT 'draft',created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(student_id)REFERENCES students(id)ON DELETE CASCADE)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");$ok('progress_reports table');}
 $t2=$pdo->query("SHOW TABLES LIKE 'progress_report_subjects'")->fetchAll();
if(empty($t2)){$pdo->exec("CREATE TABLE progress_report_subjects(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,progress_report_id BIGINT UNSIGNED NOT NULL,subject_name VARCHAR(255) NOT NULL,marks_obtained DECIMAL(8,2) DEFAULT 0,max_marks DECIMAL(8,2) DEFAULT 100,grade VARCHAR(10) NULL,remarks TEXT NULL,created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(progress_report_id)REFERENCES progress_reports(id)ON DELETE CASCADE)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");$ok('progress_report_subjects table');}
}catch(Exception $x){$warn('DB: '.$x->getMessage());}

 $mdir=$base.'/app/Models';
file_put_contents($mdir.'/ProgressReport.php','<?php namespace App\Models;use Illuminate\Database\Eloquent\Factories\HasFactory;use Illuminate\Database\Eloquent\Model;class ProgressReport extends Model{use HasFactory;protected $table="progress_reports";protected $fillable=["student_id","academic_year_id","term_id","classroom_id","overall_grade","total_marks","max_marks","class_rank","remarks","teacher_comment","status"];protected $casts=["total_marks"=>"decimal:2","max_marks"=>"decimal:2"];public function student(){return $this->belongsTo(Student::class);}public function academicYear(){return $this->belongsTo(AcademicYear::class);}public function term(){return $this->belongsTo(Term::class);}public function classroom(){return $this->belongsTo(Classroom::class);}public function subjects(){return $this->hasMany(ProgressReportSubject::class,"progress_report_id");}}');
file_put_contents($mdir.'/ProgressReportSubject.php','<?php namespace App\Models;use Illuminate\Database\Eloquent\Factories\HasFactory;use Illuminate\Database\Eloquent\Model;class ProgressReportSubject extends Model{use HasFactory;protected $table="progress_report_subjects";protected $fillable=["progress_report_id","subject_name","marks_obtained","max_marks","grade","remarks"];protected $casts=["marks_obtained"=>"decimal:2","max_marks"=>"decimal:2"];public function progressReport(){return $this->belongsTo(ProgressReport::class);}}');
 $ok('models written');

 $pcdir=$base.'/app/Http/Controllers/ProgressReport';
if(!is_dir($pcdir))mkdir($pcdir,0755,true);
file_put_contents($pcdir.'/ProgressReportController.php','<?php
namespace App\Http\Controllers\ProgressReport;use App\Http\Controllers\Controller;use App\Models\ProgressReport;use App\Models\ProgressReportSubject;use App\Models\Student;use App\Models\AcademicYear;use App\Models\Term;use App\Models\Classroom;use Illuminate\Http\Request;
class ProgressReportController extends Controller{
public function index(Request $r){$q=ProgressReport::with(["student","academicYear","term","classroom"]);if($r->filled("search")){$s=$r->search;$q->whereHas("student",function($x)use($s){$x->where("first_name","LIKE","%$s%")->orWhere("last_name","LIKE","%$s%");});}if($r->filled("academic_year_id"))$q->where("academic_year_id",$r->academic_year_id);if($r->filled("status"))$q->where("status",$r->status);$reports=$q->latest()->paginate(15);return view("admin.ProgressReport.index",compact("reports","totalReports",ProgressReport::count()));}
public function create(){$s=Student::where("status","active")->orderBy("first_name")->get();$ay=AcademicYear::orderBy("name")->get();$t=Term::orderBy("name")->get();$c=Classroom::orderBy("name")->get();return view("admin.ProgressReport.create",compact("s","ay","t","c"));}
public function store(Request $r){$v=$r->validate(["student_id"=>"required|exists:students,id","academic_year_id"=>"nullable","term_id"=>"nullable","classroom_id"=>"nullable","overall_grade"=>"nullable","total_marks"=>"nullable|numeric","max_marks"=>"nullable|numeric","class_rank"=>"nullable|integer","remarks"=>"nullable","teacher_comment"=>"nullable","status"=>"nullable|in:draft,published","subject_names"=>"nullable|array","subject_marks"=>"nullable|array","subject_max"=>"nullable|array","subject_grades"=>"nullable|array"]);$rep=ProgressReport::create(["student_id"=>$v["student_id"],"academic_year_id"=>$v["academic_year_id"]??null,"term_id"=>$v["term_id"]??null,"classroom_id"=>$v["classroom_id"]??null,"overall_grade"=>$v["overall_grade"]??null,"total_marks"=>$v["total_marks"]??0,"max_marks"=>$v["max_marks"]??100,"class_rank"=>$v["class_rank"]??null,"remarks"=>$v["remarks"]??null,"teacher_comment"=>$v["teacher_comment"]??null,"status"=>$v["status"]??"draft"]);if(!empty($v["subject_names"]))foreach($v["subject_names"] as $i=>$n){if(empty($n))continue;ProgressReportSubject::create(["progress_report_id"=>$rep->id,"subject_name"=>$n,"marks_obtained"=>$v["subject_marks"][$i]??0,"max_marks"=>$v["subject_max"][$i]??100,"grade"=>$v["subject_grades"][$i]??null]);}return redirect()->route("admin.progress-reports.index")->with("success","Report created.");}
public function edit(ProgressReport $p){$p->load("subjects");$s=Student::where("status","active")->orderBy("first_name")->get();$ay=AcademicYear::orderBy("name")->get();$t=Term::orderBy("name")->get();$c=Classroom::orderBy("name")->get();return view("admin.ProgressReport.edit",compact("p","s","ay","t","c"));}
public function update(Request $r,ProgressReport $p){$v=$r->validate(["student_id"=>"required|exists:students,id","academic_year_id"=>"nullable","term_id"=>"nullable","classroom_id"=>"nullable","overall_grade"=>"nullable","total_marks"=>"nullable|numeric","max_marks"=>"nullable|numeric","class_rank"=>"nullable|integer","remarks"=>"nullable","teacher_comment"=>"nullable","status"=>"nullable|in:draft,published,archived","subject_names"=>"nullable|array","subject_marks"=>"nullable|array","subject_max"=>"nullable|array","subject_grades"=>"nullable|array"]);$p->update(["student_id"=>$v["student_id"],"academic_year_id"=>$v["academic_year_id"]??null,"term_id"=>$v["term_id"]??null,"classroom_id"=>$v["classroom_id"]??null,"overall_grade"=>$v["overall_grade"]??null,"total_marks"=>$v["total_marks"]??0,"max_marks"=>$v["max_marks"]??100,"class_rank"=>$v["class_rank"]??null,"remarks"=>$v["remarks"]??null,"teacher_comment"=>$v["teacher_comment"]??null,"status"=>$v["status"]??"draft"]);$p->subjects()->delete();if(!empty($v["subject_names"]))foreach($v["subject_names"] as $i=>$n){if(empty($n))continue;ProgressReportSubject::create(["progress_report_id"=>$p->id,"subject_name"=>$n,"marks_obtained"=>$v["subject_marks"][$i]??0,"max_marks"=>$v["subject_max"][$i]??100,"grade"=>$v["subject_grades"][$i]??null]);}return redirect()->route("admin.progress-reports.index")->with("success","Report updated.");}
public function destroy(ProgressReport $p){$p->subjects()->delete();$p->delete();return redirect()->route("admin.progress-reports.index")->with("success","Report deleted.");}
}');
 $ok('controller written');

// ── 6. MODELS RELATIONSHIPS ────────────────────────────
echo "\n[6/8] Model relationships...\n";
 $mdefs=[
'Fee.php'=>['table'=>'fees','fill'=>'name,fee_type,category,classroom_id,academic_year_id,amount,due_date,description,is_active','rels'=>'public function classroom(){return $this->belongsTo(Classroom::class);}public function academicYear(){return $this->belongsTo(AcademicYear::class);}'],
'FeePayment.php'=>['table'=>'fee_payments','fill'=>'fee_id,student_id,amount_paid,payment_method,transaction_id,receipt_number,payment_date,status,notes','rels'=>'public function fee(){return $this->belongsTo(Fee::class);}public function student(){return $this->belongsTo(Student::class);}'],
'Section.php'=>['table'=>'sections','fill'=>'name,classroom_id,teacher_id,capacity,is_active','rels'=>'public function classroom(){return $this->belongsTo(Classroom::class);}public function teacher(){return $this->belongsTo(Teacher::class);}'],
];
foreach($mdefs as $fn=>$d){
    $fp=$mdir.'/'.$fn;
    $ex=file_exists($fp)?file_get_contents($fp):'';
    if(strpos($ex,'function classroom()')!==false||strpos($ex,'function fee()')!==false){$ok("$fn has relationships");continue;}
    $cls=str_replace('.php','',$fn);
    file_put_contents($fp,"<?php\nnamespace App\Models;use Illuminate\Database\Eloquent\Factories\HasFactory;use Illuminate\Database\Eloquent\Model;class $cls extends Model{use HasFactory;protected \$table=\"{$d['table']}\";protected \$fillable=[\"{$d['fill']}\"];{$d['rels']}\n}");
    $wr("$fn written");
}

// ── 7. MODULE VIEWS ────────────────────────────────────
echo "\n[7/8] Module views...\n";
 $mods=[
'Exam'=>['icon'=>'clipboard-check','cols'=>'name,type,start_date,end_date,status','fields'=>'name:text:1,type:select:midterm|Mid Term|final|Final|quiz|Quiz,start_date:date:1,end_date:date:1,total_marks:number:2,status:select:upcoming|Upcoming|ongoing|Ongoing|completed|Completed'],
'Leave'=>['icon'=>'calendar-x','cols'=>'type,start_date,end_date,total_days,status','fields'=>'employee_id:text:1,type:select:sick|Sick|annual|Annual|emergency|Emergency,start_date:date:1,end_date:date:1,reason:textarea:2,status:select:pending|Pending|approved|Approved|rejected|Rejected'],
'Payroll'=>['icon'=>'cash-coin','cols'=>'month,year,basic_salary,net_salary,status','fields'=>'employee_id:text:1,month:select:1|Jan|2|Feb|3|Mar|4|Apr|5|May|6|Jun|7|Jul|8|Aug|9|Sep|10|Oct|11|Nov|12|Dec,year:number:1,basic_salary:number:1,allowances:number:2,deductions:number:2,status:select:pending|Pending|paid|Paid'],
'MarkEntry'=>['icon'=>'pencil-square','cols'=>'student_id,exam_id,subject_id,marks_obtained,max_marks','fields'=>'student_id:text:1,exam_id:text:1,subject_id:text:1,marks_obtained:number:1,max_marks:number:2,remarks:textarea:2'],
'TeacherAssignment'=>['icon'=>'person-check','cols'=>'teacher_id,classroom_id,subject_id,academic_year_id','fields'=>'teacher_id:text:1,classroom_id:text:1,subject_id:text:1,academic_year_id:text:2'],
'Certificate'=>['icon'=>'award','cols'=>'student_id,type,certificate_number,issue_date,status','fields'=>'student_id:text:1,type:select:academic|Academic|character|Character|transfer|Transfer,certificate_number:text:1,issue_date:date:1,description:textarea:2,status:select:draft|Draft|issued|Issued'],
'IdCard'=>['icon'=>'badge-3d','cols'=>'student_id,card_number,type,issue_date,status','fields'=>'student_id:text:1,card_number:text:1,type:select:student|Student|staff|Staff,issue_date:date:1,expiry_date:date:2,status:select:active|Active|expired|Expired'],
];
foreach($mods as $m=>$d){
    $md=$vdir.'/'.$m;if(!is_dir($md))mkdir($md,0755,true);
    $rp='admin.'.strtolower($m).'s';
    $th='<tr>';foreach(explode(',',$d['cols']) as $c)$th.='<th>'.ucfirst(str_replace('_',' ',$c)).'</th>';$th.='<th class="text-center">Actions</th></tr>';
    $td='';foreach(explode(',',$d['cols']) as $c)$td.='<td>{{$item->'.trim($c).' ?? "—"}}</td>';
    // index
    file_put_contents($md.'/index.blade.php',"@extends(\"layouts.admin\")\n@section(\"page-title\",\"$m\")\n@section(\"content\")\n<div class=\"container-fluid py-4\"><div class=\"d-flex flex-wrap justify-content-between align-items-center mb-4\"><div><h4 class=\"fw-bold mb-1\"><i class=\"bi bi-{$d['icon']} me-2\"></i>".ucfirst($m)."s</h4><nav aria-label=\"breadcrumb\"><ol class=\"breadcrumb mb-0\"><li class=\"breadcrumb-item\"><a href=\"{{route('admin.dashboard')}}\" class=\"text-decoration-none text-muted\">Dashboard</a></li><li class=\"breadcrumb-item active text-gold\">".ucfirst($m)."s</li></ol></nav></div><a href=\"{{route('$rp.create')}}\" class=\"btn btn-gold\"><i class=\"bi bi-plus-lg me-1\"></i>Add</a></div>\n@if(session('success'))<div class=\"alert alert-success alert-dismissible fade show\"><i class=\"bi bi-check-circle me-2\"></i>{{session('success')}}<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button></div>@endif\n<div class=\"card border-0 shadow-sm\"><div class=\"card-body p-0\">@if(isset(\$data)&&\$data->count())<div class=\"table-responsive\"><table class=\"table table-hover align-middle mb-0\"><thead class=\"table-light\">$th</thead><tbody>@foreach(\$data as \$item)<tr><td class=\"ps-3 text-muted\">{{\$loop->iteration}}</td>$td<td class=\"text-center\"><a href=\"{{route('$rp.edit',\$item->id)}}\" class=\"btn btn-sm btn-link text-primary p-0 me-2\"><i class=\"bi bi-pencil\"></i></a><form method=\"POST\" action=\"{{route('$rp.destroy',\$item->id)}}\" class=\"d-inline\" onsubmit=\"return confirm('Delete?')\">@csrf @method('DELETE')<button class=\"btn btn-sm btn-link text-danger p-0\"><i class=\"bi bi-trash\"></i></button></form></td></tr>@endforeach</tbody></table></div><div class=\"px-3 py-2\">{{\$data->links()}}</div>@else<div class=\"text-center py-5\"><i class=\"bi bi-inbox text-muted\" style=\"font-size:3rem;\"></i><h5 class=\"mt-3 text-muted\">No Records</h5></div>@endif</div></div></div>\n@endsection");
    // form rows
    $frm='';foreach(explode(',',$d['fields']) as $f){
        $p=explode(':',$f,3);$nm=$p[0];$tp=$p[1]??'text';$w=$p[2]??'1';
        $col=$w=='1'?'col-md-6':($w=='2'?'col-md-12':'col-md-4');
        $frm.="<div class=\"$col\"><label class=\"form-label fw-medium\">".ucfirst(str_replace('_',' ',$nm))."</label>";
        if($tp==='select'){$opts=explode('|',$p[2]??'');$frm.="<select name=\"$nm\" class=\"form-select\"><option value=\"\">Select...</option>";for($i=0;$i<count($opts);$i+=2)$frm.="<option value=\"{$opts[$i]}\">{$opts[$i+1]}</option>";$frm.="</select>";}
        elseif($tp==='textarea')$frm.="<textarea name=\"$nm\" class=\"form-control\" rows=\"3\"></textarea>";
        elseif($tp==='date')$frm.="<input type=\"date\" name=\"$nm\" class=\"form-control\">";
        elseif($tp==='number')$frm.="<input type=\"number\" name=\"$nm\" class=\"form-control\" step=\"any\">";
        else $frm.="<input type=\"text\" name=\"$nm\" class=\"form-control\">";
        $frm.="@error('$nm')<div class=\"text-danger small mt-1\">{{\$message}}</div>@enderror</div>\n";
    }
    // create
    file_put_contents($md.'/create.blade.php',"@extends(\"layouts.admin\")\n@section(\"page-title\",\"New $m\")\n@section(\"content\")\n<div class=\"container-fluid py-4\"><div class=\"d-flex flex-wrap justify-content-between align-items-center mb-4\"><div><h4 class=\"fw-bold mb-1\"><i class=\"bi bi-plus-circle me-2\"></i>New ".ucfirst($m)."</h4><nav aria-label=\"breadcrumb\"><ol class=\"breadcrumb mb-0\"><li class=\"breadcrumb-item\"><a href=\"{{route('admin.dashboard')}}\" class=\"text-decoration-none text-muted\">Dashboard</a></li><li class=\"breadcrumb-item\"><a href=\"{{route('$rp.index')}}\" class=\"text-decoration-none text-muted\">".ucfirst($m)."s</a></li><li class=\"breadcrumb-item active text-gold\">New</li></ol></nav></div></div>\n<form method=\"POST\" action=\"{{route('$rp.store')}}\">@csrf\n<div class=\"card border-0 shadow-sm\"><div class=\"card-header bg-white py-3\" style=\"border-top:3px solid #c9a84c;\"><h5 class=\"mb-0 fw-semibold\">Details</h5></div><div class=\"card-body\"><div class=\"row g-3\">$frm</div></div></div>\n<div class=\"my-4\"><button type=\"submit\" class=\"btn btn-gold me-2\"><i class=\"bi bi-check-lg me-1\"></i>Save</button><a href=\"{{route('$rp.index')}}\" class=\"btn btn-outline-secondary\">Cancel</a></div></form></div>\n@endsection");
    // edit
    $frmE='';foreach(explode(',',$d['fields']) as $f){
        $p=explode(':',$f,3);$nm=$p[0];$tp=$p[1]??'text';$w=$p[2]??'1';
        $col=$w=='1'?'col-md-6':($w=='2'?'col-md-12':'col-md-4');
        $frmE.="<div class=\"$col\"><label class=\"form-label fw-medium\">".ucfirst(str_replace('_',' ',$nm))."</label>";
        if($tp==='select'){$opts=explode('|',$p[2]??'');$frmE.="<select name=\"$nm\" class=\"form-select\"><option value=\"\">Select...</option>";for($i=0;$i<count($opts);$i+=2)$frmE.="<option value=\"{$opts[$i]}\" {{\$item->$nm=='{$opts[$i]}'?'selected':''}}>{$opts[$i+1]}</option>";$frmE.="</select>";}
        elseif($tp==='textarea')$frmE.="<textarea name=\"$nm\" class=\"form-control\" rows=\"3\">{{\$item->$nm}}</textarea>";
        elseif($tp==='date')$frmE.="<input type=\"date\" name=\"$nm\" class=\"form-control\" value=\"{{\$item->$nm}}\">";
        elseif($tp==='number')$frmE.="<input type=\"number\" name=\"$nm\" class=\"form-control\" step=\"any\" value=\"{{\$item->$nm}}\">";
        else $frmE.="<input type=\"text\" name=\"$nm\" class=\"form-control\" value=\"{{\$item->$nm}}\">";
        $frmE.="@error('$nm')<div class=\"text-danger small mt-1\">{{\$message}}</div>@enderror</div>\n";
    }
    file_put_contents($md.'/edit.blade.php',"@extends(\"layouts.admin\")\n@section(\"page-title\",\"Edit $m\")\n@section(\"content\")\n<div class=\"container-fluid py-4\"><div class=\"d-flex flex-wrap justify-content-between align-items-center mb-4\"><div><h4 class=\"fw-bold mb-1\"><i class=\"bi bi-pencil-square me-2\"></i>Edit ".ucfirst($m)."</h4><nav aria-label=\"breadcrumb\"><ol class=\"breadcrumb mb-0\"><li class=\"breadcrumb-item\"><a href=\"{{route('admin.dashboard')}}\" class=\"text-decoration-none text-muted\">Dashboard</a></li><li class=\"breadcrumb-item\"><a href=\"{{route('$rp.index')}}\" class=\"text-decoration-none text-muted\">".ucfirst($m)."s</a></li><li class=\"breadcrumb-item active text-gold\">Edit</li></ol></nav></div></div>\n<form method=\"POST\" action=\"{{route('$rp.update',\$item->id)}}\">@csrf @method('PUT')\n<div class=\"card border-0 shadow-sm\"><div class=\"card-header bg-white py-3\" style=\"border-top:3px solid #c9a84c;\"><h5 class=\"mb-0 fw-semibold\">Details</h5></div><div class=\"card-body\"><div class=\"row g-3\">$frmE</div></div></div>\n<div class=\"my-4\"><button type=\"submit\" class=\"btn btn-gold me-2\"><i class=\"bi bi-check-lg me-1\"></i>Update</button><a href=\"{{route('$rp.index')}}\" class=\"btn btn-outline-secondary\">Cancel</a></div></form></div>\n@endsection");
    $ok("$m views written");
}

// ── 8. SIDEBAR + LAYOUT ────────────────────────────────
echo "\n[8/8] Sidebar & Layout...\n";
 $lfile=$base.'/resources/views/layouts/admin.blade.php';
copy($lfile,$lfile.'.bak');$ok('layout backed up');

// Build the menu items
 $menuItems = [];
 $menuItems[] = ['type'=>'header','label'=>'MAIN'];
 $menuItems[] = ['type'=>'link','route'=>'admin.dashboard','icon'=>'grid-1x2-fill','label'=>'Dashboard','active'=>'admin.dashboard'];
 $menuItems[] = ['type'=>'header','label'=>'ACADEMIC'];
 $menuItems[] = ['type'=>'link','route'=>'admin.academic-years.index','icon'=>'calendar-range','label'=>'Academic Years','active'=>'admin.academic-years'];
 $menuItems[] = ['type'=>'link','route'=>'admin.terms.index','icon'=>'book-half','label'=>'Terms','active'=>'admin.terms'];
 $menuItems[] = ['type'=>'link','route'=>'admin.classrooms.index','icon'=>'door-open','label'=>'Classrooms','active'=>'admin.classrooms'];
 $menuItems[] = ['type'=>'link','route'=>'admin.sections.index','icon'=>'layers','label'=>'Sections','active'=>'admin.sections'];
 $menuItems[] = ['type'=>'link','route'=>'admin.subjects.index','icon'=>'journal-bookmark','label'=>'Subjects','active'=>'admin.subjects'];
 $menuItems[] = ['type'=>'link','route'=>'admin.exams.index','icon'=>'clipboard-check','label'=>'Exams','active'=>'admin.exams'];
 $menuItems[] = ['type'=>'link','route'=>'admin.mark-entries.index','icon'=>'pencil-square','label'=>'Mark Entries','active'=>'admin.mark-entries'];
 $menuItems[] = ['type'=>'link','route'=>'admin.teacher-assignments.index','icon'=>'person-check','label'=>'Teacher Assignments','active'=>'admin.teacher-assignments'];
 $menuItems[] = ['type'=>'header','label'=>'PEOPLE'];
 $menuItems[] = ['type'=>'link','route'=>'admin.students.index','icon'=>'mortarboard','label'=>'Students','active'=>'admin.students'];
 $menuItems[] = ['type'=>'link','route'=>'admin.teachers.index','icon'=>'person-workspace','label'=>'Staff / Teachers','active'=>'admin.teachers'];
 $menuItems[] = ['type'=>'link','route'=>'admin.parents.index','icon'=>'people','label'=>'Parents','active'=>'admin.parents'];
 $menuItems[] = ['type'=>'header','label'=>'FINANCE'];
 $menuItems[] = ['type'=>'link','route'=>'admin.fees.index','icon'=>'receipt','label'=>'Fee Structure','active'=>'admin.fees'];
 $menuItems[] = ['type'=>'link','route'=>'admin.fee-payments.index','icon'=>'cash-stack','label'=>'Fee Payments','active'=>'admin.fee-payments'];
 $menuItems[] = ['type'=>'header','label'=>'REPORTS'];
 $menuItems[] = ['type'=>'link','route'=>'admin.progress-reports.index','icon'=>'journal-text','label'=>'Progress Reports','active'=>'admin.progress-reports'];
 $menuItems[] = ['type'=>'link','route'=>'admin.performance-reports.index','icon'=>'graph-up','label'=>'Performance Reports','active'=>'admin.performance-reports'];
 $menuItems[] = ['type'=>'header','label'=>'ASSETS'];
 $menuItems[] = ['type'=>'link','route'=>'admin.class-assets.index','icon'=>'box-seam','label'=>'Class Assets','active'=>'admin.class-assets'];
 $menuItems[] = ['type'=>'header','label'=>'MANAGE WEBSITE'];
 $menuItems[] = ['type'=>'link','route'=>'admin.sliders.index','icon'=>'images','label'=>'Sliders','active'=>'admin.sliders'];
 $menuItems[] = ['type'=>'link','route'=>'admin.team-members.index','icon'=>'person-badge','label'=>'Team Members','active'=>'admin.team-members'];
 $menuItems[] = ['type'=>'link','route'=>'admin.gallery-images.index','icon'=>'image','label'=>'Gallery Images','active'=>'admin.gallery-images'];
 $menuItems[] = ['type'=>'link','route'=>'admin.gallery-videos.index','icon'=>'camera-video','label'=>'Gallery Videos','active'=>'admin.gallery-videos'];
 $menuItems[] = ['type'=>'link','route'=>'admin.branches.index','icon'=>'geo-alt','label'=>'Branches','active'=>'admin.branches'];
 $menuItems[] = ['type'=>'link','route'=>'admin.contact-messages.index','icon'=>'envelope','label'=>'Messages','active'=>'admin.contact-messages','badge'=>'unread'];
 $menuItems[] = ['type'=>'header','label'=>'SYSTEM'];
 $menuItems[] = ['type'=>'link','route'=>'admin.settings.index','icon'=>'gear','label'=>'Settings','active'=>'admin.settings'];

// Render sidebar HTML
 $sbHtml = '';
foreach($menuItems as $mi){
    if($mi['type']==='header'){
        $sbHtml .= '                <li class="menu-header">'.strtoupper($mi['label'])."</li>\n";
    } else {
        $active = 'request()->routeIs(\''.str_replace('*','',$mi['active']).'.*\') ? \'active\' : \'\'';
        $badge = '';
        if(isset($mi['badge'])){
            $badge = "@php \${$mi['badge']} = \\App\\Models\\ContactMessage::where('is_read', false)->count(); @endphp\n                        @if(\${$mi['badge']} > 0)\n                            <span class=\"badge bg-danger ms-auto\">{{\${$mi['badge']}}}</span>\n                        @endif";
        }
        $sbHtml .= '                <li class="{{ '.$active.' }}">'."\n";
        $sbHtml .= '                    <a href="{{ route(\''.$mi['route'].'\') }}"><i class="bi bi-'.$mi['icon'].'"></i><span>'.$mi['label']."</span>".$badge."</a>\n";
        $sbHtml .= "                </li>\n";
    }
}

file_put_contents($lfile, '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield(\'page-title\', \'Dashboard\') | School of Redemption</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset(\'css/admin.css\') }}" rel="stylesheet">
    <style>
        .admin-sidebar{width:260px;min-height:100vh;background:#1a1a2e;color:#fff;position:fixed;top:0;left:0;z-index:1040;transition:transform .3s;overflow-y:auto;}
        .admin-sidebar.show{transform:translateX(0);}
        .sidebar-header{padding:20px 16px;border-bottom:1px solid rgba(255,255,255,.1);text-align:center;}
        .sidebar-brand{text-decoration:none;font-family:\'Playfair Display\',serif;}
        .sidebar-brand-pre{display:block;font-size:11px;letter-spacing:3px;color:#c9a84c;text-transform:uppercase;}
        .sidebar-brand-name{display:block;font-size:22px;font-weight:700;color:#fff;letter-spacing:2px;}
        .sidebar-menu{list-style:none;padding:0;margin:0;}
        .menu-header{padding:12px 20px 4px;font-size:10px;font-weight:700;letter-spacing:2px;color:rgba(255,255,255,.35);text-transform:uppercase;}
        .sidebar-menu>li>a{display:flex;align-items:center;padding:9px 20px;color:rgba(255,255,255,.7);text-decoration:none;font-size:13px;transition:all .2s;border-left:3px solid transparent;}
        .sidebar-menu>li>a:hover{background:rgba(255,255,255,.06);color:#fff;}
        .sidebar-menu>li.active>a{background:rgba(201,168,76,.12);color:#c9a84c;border-left-color:#c9a84c;}
        .sidebar-menu>li>a i{font-size:16px;width:28px;text-align:center;margin-right:10px;flex-shrink:0;}
        .sidebar-menu>li>a .badge{font-size:10px;padding:2px 7px;}
        .admin-main{margin-left:260px;min-height:100vh;background:#f4f6f9;transition:margin-left .3s;}
        .admin-topbar{display:flex;align-items:center;justify-content:space-between;padding:12px 24px;background:#fff;border-bottom:1px solid #e0e0e0;}
        .sidebar-toggle{font-size:20px;color:#333;text-decoration:none;border:none;}
        .topbar-right{display:flex;align-items:center;gap:16px;}
        .topbar-right .dropdown-toggle::after{display:none;}
        .admin-content{padding:24px;}
        .btn-gold{background:#c9a84c;color:#fff;border:none;font-weight:500;}
        .btn-gold:hover{background:#b8963f;color:#fff;}
        .text-gold{color:#c9a84c !important;}
        @media(max-width:991px){.admin-sidebar{transform:translateX(-100%);}.admin-sidebar.show{transform:translateX(0);}.admin-main{margin-left:0;}}
    </style>
    @stack(\'styles\')
</head>
<body>
    <div class="admin-wrapper">
        <nav class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <a href="{{ route(\'admin.dashboard\') }}" class="sidebar-brand">
                    <span class="sidebar-brand-pre">School of</span>
                    <span class="sidebar-brand-name">REDEMPTION</span>
                </a>
            </div>
            <ul class="sidebar-menu">
'.$sbHtml.'
            </ul>
        </nav>
        <div class="admin-main">
            <nav class="admin-topbar">
                <button class="btn btn-link sidebar-toggle" id="sidebarToggle">
                    <i class="bi bi-list" style="font-size:24px;color:#333;"></i>
                </button>
                <div class="topbar-right">
                    <span class="text-muted" style="font-size:13px;">Welcome, <strong>{{ Auth::user()->name ?? \'Admin\' }}</strong></span>
                    <div class="dropdown">
                        <button class="btn btn-link dropdown-toggle" data-bs-toggle="dropdown" style="text-decoration:none;">
                            <i class="bi bi-person-circle" style="font-size:28px;color:#555;"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" style="min-width:200px;">
                            <li><a class="dropdown-item" href="{{ route(\'home\') }}" target="_blank"><i class="bi bi-globe me-2"></i>Visit Website</a></li>
                            <li><a class="dropdown-item" href="{{ route(\'admin.dashboard\') }}"><i class="bi bi-house me-2"></i>Manage Website</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><form method="POST" action="{{ route(\'logout\') }}">@csrf<button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</button></form></li>
                        </ul>
                    </div>
                </div>
            </nav>
            <div class="admin-content">
                @if(session(\'success\'))
                <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i>{{ session(\'success\') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                @endif
                @if(session(\'error\'))
                <div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-circle me-2"></i>{{ session(\'error\') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                @endif
                @yield(\'content\')
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById(\'sidebarToggle\')?.addEventListener(\'click\',function(){document.getElementById(\'adminSidebar\').classList.toggle(\'show\');});
        document.addEventListener(\'click\',function(e){var s=document.getElementById(\'adminSidebar\'),t=document.getElementById(\'sidebarToggle\');if(window.innerWidth<992&&s&&s.classList.contains(\'show\')&&!s.contains(e.target)&&!t.contains(e.target))s.classList.remove(\'show\');});
    </script>
    @stack(\'scripts\')
</body>
</html>');
 $ok('layout rewritten');

// ── CLEAR CACHES ───────────────────────────────────────
echo "\nClearing caches...\n";
foreach(['route:clear','config:clear','view:clear','cache:clear'] as $c){
    $o=shell_exec('cd '.escapeshellarg($base)." && php artisan $c 2>&1");
    $ok(explode("\n",trim($o))[0]);
}

echo "\n=== ALL DONE ===\n";
echo "Run: php artisan route:list --path=admin | head -5\n";
