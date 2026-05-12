<?php
echo "=== Part 2C: Settings + ProgressReport + Models ===\n\n";
 $b = __DIR__;

// SETTINGS CONTROLLER
file_put_contents($b.'/app/Http/Controllers/Setting/SettingController.php', '<?php
namespace App\Http\Controllers\Setting;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
class SettingController extends Controller
{
    public function index(){
        $settings = Setting::all()->groupBy("group");
        return view("admin.Setting.index", compact("settings"));
    }
    public function updateAll(Request $r){
        $data = $r->except("_token");
        foreach($data as $key => $value){
            Setting::where("key",$key)->update(["value"=>$value]);
        }
        return redirect()->back()->with("success","Settings saved");
    }
    public function store(Request $r){
        $r->validate(["key"=>"required|unique:settings,key","value"=>"required","group"=>"required","type"=>"required"]);
        Setting::create($r->all());
        return back()->with("success","Setting added");
    }
    public function destroy(Setting $item){ $item->delete(); return back()->with("success","Deleted"); }
}');
echo "[OK] Settings controller\n";

// SETTINGS INDEX
file_put_contents($b.'/resources/views/admin/Setting/index.blade.php', '@extends("layouts.admin")
@section("page-title","Settings")
@section("content")
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div><h4 class="fw-bold mb-1"><i class="bi bi-gear me-2"></i>School Settings</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route(\'admin.dashboard\') }}" class="text-decoration-none text-muted">Dashboard</a></li>
            <li class="breadcrumb-item active text-gold">Settings</li>
        </ol></nav></div>
    </div>
    @if(session("success"))<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i>{{ session("success") }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    <form method="POST" action="{{ route(\'admin.settings.updateAll\') }}">@csrf
    @foreach($settings as $group => $items)
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-semibold mb-0"><i class="bi bi-folder2-open me-2"></i>{{ ucfirst($group) }} Settings</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
            @foreach($items as $item)
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ ucfirst(str_replace("_"," ",$item->key)) }}</label>
                    @if($item->type === "boolean")
                        <div class="form-check form-switch mt-1">
                            <input class="form-check-input" type="checkbox" name="{{ $item->key }}" value="1" {{ $item->value ? "checked" : "" }}>
                        </div>
                    @elseif($item->type === "number")
                        <input type="number" name="{{ $item->key }}" class="form-control" value="{{ $item->value }}">
                    @else
                        <input type="text" name="{{ $item->key }}" class="form-control" value="{{ $item->value }}">
                    @endif
                    @if($item->description)<small class="text-muted d-block">{{ $item->description }}</small>@endif
                </div>
            @endforeach
            </div>
        </div>
    </div>
    @endforeach
    <button type="submit" class="btn btn-gold"><i class="bi bi-check-lg me-1"></i>Save All Settings</button>
    </form>
</div>
@endsection');
echo "[OK] Settings index\n";
echo "[OK] Settings module done\n";

// PROGRESS REPORT CONTROLLER
file_put_contents($b.'/app/Http/Controllers/ProgressReport/ProgressReportController.php', '<?php
namespace App\Http\Controllers\ProgressReport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProgressReport;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\Classroom;
class ProgressReportController extends Controller
{
    public function index(){
        $data = ProgressReport::with("student","academicYear","term","classroom")->latest()->paginate(20);
        $totalReports = ProgressReport::count();
        return view("admin.ProgressReport.index", compact("data","totalReports"));
    }
    public function create(){
        $students = Student::orderBy("first_name")->get();
        $academicYears = AcademicYear::orderBy("name")->get();
        $terms = Term::orderBy("name")->get();
        $classrooms = Classroom::orderBy("name")->get();
        return view("admin.ProgressReport.create", compact("students","academicYears","terms","classrooms"));
    }
    public function store(Request $r){
        $r->validate(["student_id"=>"required|exists:students,id","academic_year_id"=>"required|exists:academic_years,id","term_id"=>"required|exists:terms,id","class_id"=>"required|exists:classrooms,id","total_marks"=>"required|numeric","percentage"=>"required|numeric","grade"=>"required"]);
        ProgressReport::create($r->all());
        return redirect()->route("admin.progress-reports.index")->with("success","Report created");
    }
    public function show(ProgressReport $item){ return view("admin.ProgressReport.show", compact("item")); }
    public function edit(ProgressReport $item){
        $students = Student::orderBy("first_name")->get();
        $academicYears = AcademicYear::orderBy("name")->get();
        $terms = Term::orderBy("name")->get();
        $classrooms = Classroom::orderBy("name")->get();
        return view("admin.ProgressReport.edit", compact("item","students","academicYears","terms","classrooms"));
    }
    public function update(Request $r, ProgressReport $item){
        $r->validate(["student_id"=>"required|exists:students,id","academic_year_id"=>"required|exists:academic_years,id","term_id"=>"required|exists:terms,id","class_id"=>"required|exists:classrooms,id","total_marks"=>"required|numeric","percentage"=>"required|numeric","grade"=>"required"]);
        $item->update($r->all());
        return redirect()->route("admin.progress-reports.index")->with("success","Updated");
    }
    public function destroy(ProgressReport $item){ $item->delete(); return back()->with("success","Deleted"); }
}');
echo "[OK] ProgressReport controller\n";

// PROGRESS REPORT INDEX
file_put_contents($b.'/resources/views/admin/ProgressReport/index.blade.php', '@extends("layouts.admin")
@section("page-title","Progress Reports")
@section("content")
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div><h4 class="fw-bold mb-1"><i class="bi bi-file-earmark-bar-graph me-2"></i>Progress Reports</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route(\'admin.dashboard\') }}" class="text-decoration-none text-muted">Dashboard</a></li>
            <li class="breadcrumb-item active text-gold">Progress Reports</li>
        </ol></nav></div>
        <a href="{{ route(\'admin.progress-reports.create\') }}" class="btn btn-gold"><i class="bi bi-plus-lg me-1"></i>Create Report</a>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-6"><div class="card border-0 shadow-sm h-100" style="border-top:3px solid #c9a84c !important"><div class="card-body text-center"><h3 class="fw-bold text-primary mb-0">{{ $totalReports }}</h3><small class="text-muted">Total Reports</small></div></div></div>
        <div class="col-md-6"><div class="card border-0 shadow-sm h-100" style="border-top:3px solid #0dcaf0 !important"><div class="card-body text-center"><h3 class="fw-bold text-info mb-0">{{ $data->count() }}</h3><small class="text-muted">This Page</small></div></div></div>
    </div>
    @if(session("success"))<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i>{{ session("success") }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if($data->count() > 0)
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3"><div class="d-flex justify-content-between align-items-center"><h6 class="fw-semibold mb-0"><i class="bi bi-list-ul me-2"></i>All Reports</h6><span class="badge bg-light text-dark">{{ $totalReports }}</span></div></div>
        <div class="card-body p0"><div class="table-responsive">
        <table class="table table-hover align-middle mb-0"><thead class="table-light"><tr>
            <th class="ps-3" style="width:40px">#</th><th>Student</th><th>Year</th><th>Term</th><th>Class</th><th>Marks</th><th>%</th><th>Grade</th><th>Rank</th><th style="width:100px" class="text-center">Actions</th>
        </tr></thead><tbody>
        @foreach($data as $item)
        <tr><td class="ps-3 text-muted">{{ $loop->iteration }}</td>
            <td><span class="fw-semibold">{{ $item->student->first_name ?? "-" }} {{ $item->student->last_name ?? "" }}</span></td>
            <td class="text-muted">{{ $item->academicYear->name ?? "-" }}</td>
            <td class="text-muted">{{ $item->term->name ?? "-" }}</td>
            <td class="text-muted">{{ $item->classroom->name ?? "-" }}</td>
            <td>{{ $item->total_marks }}</td>
            <td><strong>{{ $item->percentage }}%</strong></td>
            <td><span class="badge bg-{{ in_array($item->grade,["A","A+"]) ? "success" : (in_array($item->grade,["B","B+"]) ? "info" : ($item->grade==="C" ? "warning" : "danger")) }} bg-opacity-10 text-{{ in_array($item->grade,["A","A+"]) ? "success" : (in_array($item->grade,["B","B+"]) ? "info" : ($item->grade==="C" ? "warning" : "danger")) }}">{{ $item->grade }}</span></td>
            <td>{{ $item->rank ?? "-" }}</td>
            <td class="text-center">
                <a href="{{ route(\'admin.progress-reports.edit\',$item->id) }}" class="btn btn-sm btn-link text-primary p-0" title="Edit"><i class="bi bi-pencil-square fs-5"></i></a>
                <form method="POST" action="{{ route(\'admin.progress-reports.destroy\',$item->id) }}" style="display:inline" onsubmit="return confirm(\'Delete?\')">@csrf @method(\'DELETE\')<button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Delete"><i class="bi bi-trash fs-5"></i></button></form>
            </td></tr>
        @endforeach</tbody></table></div></div>
        <div class="card-footer bg-white border-top">{{ $data->links() }}</div>
    </div>
    @else
    <div class="card border-0 shadow-sm"><div class="card-body text-center py-5"><i class="bi bi-file-earmark-bar-graph fs-1 text-muted"></i><h5 class="mt-3 text-muted">No Reports Found</h5><a href="{{ route(\'admin.progress-reports.create\') }}" class="btn btn-gold"><i class="bi bi-plus-lg me-1"></i>Create Report</a></div></div>
    @endif
</div>
@endsection');
echo "[OK] ProgressReport index\n";

// PROGRESS REPORT CREATE
file_put_contents($b.'/resources/views/admin/ProgressReport/create.blade.php', '@extends("layouts.admin")
@section("page-title","Create Report")
@section("content")
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div><h4 class="fw-bold mb-1"><i class="bi bi-file-earmark-bar-graph me-2"></i>Create Report</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route(\'admin.dashboard\') }}" class="text-decoration-none text-muted">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route(\'admin.progress-reports.index\') }}" class="text-decoration-none text-muted">Reports</a></li>
            <li class="breadcrumb-item active text-gold">Create</li>
        </ol></nav></div>
        <a href="{{ route(\'admin.progress-reports.index\') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
    <div class="card border-0 shadow-sm"><div class="card-header bg-white border-bottom py-3"><h6 class="fw-semibold mb-0"><i class="bi bi-plus-circle me-2"></i>Report Details</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route(\'admin.progress-reports.store\') }}">@csrf
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label fw-semibold">Student *</label><select name="student_id" class="form-select" required><option value="">-- Select --</option>@foreach($students as $s)<option value="{{ $s->id }}">{{ $s->first_name }} {{ $s->last_name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Academic Year *</label><select name="academic_year_id" class="form-select" required><option value="">-- Select --</option>@foreach($academicYears as $ay)<option value="{{ $ay->id }}">{{ $ay->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Term *</label><select name="term_id" class="form-select" required><option value="">-- Select --</option>@foreach($terms as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Class *</label><select name="class_id" class="form-select" required><option value="">-- Select --</option>@foreach($classrooms as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Total Marks *</label><input type="number" step="0.01" name="total_marks" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Percentage *</label><input type="number" step="0.01" name="percentage" class="form-control" required></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Grade *</label><select name="grade" class="form-select" required><option value="">-- Select --</option><option value="A+">A+</option><option value="A">A</option><option value="B+">B+</option><option value="B">B</option><option value="C">C</option><option value="D">D</option><option value="F">F</option></select></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Rank</label><input type="number" name="rank" class="form-control" min="1"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Remarks</label><textarea name="remarks" class="form-control" rows="2"></textarea></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Teacher Comment</label><textarea name="teacher_comment" class="form-control" rows="2"></textarea></div>
            <div class="col-12 mt-4"><button type="submit" class="btn btn-gold"><i class="bi bi-check-lg me-1"></i>Create Report</button><a href="{{ route(\'admin.progress-reports.index\') }}" class="btn btn-outline-secondary ms-2">Cancel</a></div>
        </div></form>
    </div></div>
</div>
@endsection');
echo "[OK] ProgressReport create\n";

// PROGRESS REPORT EDIT
file_put_contents($b.'/resources/views/admin/ProgressReport/edit.blade.php', '@extends("layouts.admin")
@section("page-title","Edit Report")
@section("content")
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div><h4 class="fw-bold mb-1"><i class="bi bi-file-earmark-bar-graph me-2"></i>Edit Report</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route(\'admin.dashboard\') }}" class="text-decoration-none text-muted">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route(\'admin.progress-reports.index\') }}" class="text-decoration-none text-muted">Reports</a></li>
            <li class="breadcrumb-item active text-gold">Edit</li>
        </ol></nav></div>
        <a href="{{ route(\'admin.progress-reports.index\') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
    <div class="card border-0 shadow-sm"><div class="card-header bg-white border-bottom py-3"><h6 class="fw-semibold mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Report</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route(\'admin.progress-reports.update\',$item->id) }}">@csrf @method(\'PUT\')
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label fw-semibold">Student *</label><select name="student_id" class="form-select" required><option value="">-- Select --</option>@foreach($students as $s)<option value="{{ $s->id }}" {{ $item->student_id==$s->id?"selected":"" }}>{{ $s->first_name }} {{ $s->last_name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Academic Year *</label><select name="academic_year_id" class="form-select" required><option value="">-- Select --</option>@foreach($academicYears as $ay)<option value="{{ $ay->id }}" {{ $item->academic_year_id==$ay->id?"selected":"" }}>{{ $ay->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Term *</label><select name="term_id" class="form-select" required><option value="">-- Select --</option>@foreach($terms as $t)<option value="{{ $t->id }}" {{ $item->term_id==$t->id?"selected":"" }}>{{ $t->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Class *</label><select name="class_id" class="form-select" required><option value="">-- Select --</option>@foreach($classrooms as $c)<option value="{{ $c->id }}" {{ $item->class_id==$c->id?"selected":"" }}>{{ $c->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Total Marks *</label><input type="number" step="0.01" name="total_marks" class="form-control" value="{{ $item->total_marks }}" required></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Percentage *</label><input type="number" step="0.01" name="percentage" class="form-control" value="{{ $item->percentage }}" required></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Grade *</label><select name="grade" class="form-select" required><option value="">-- Select --</option><option value="A+" {{ $item->grade==="A+"?"selected":"" }}>A+</option><option value="A" {{ $item->grade==="A"?"selected":"" }}>A</option><option value="B+" {{ $item->grade==="B+"?"selected":"" }}>B+</option><option value="B" {{ $item->grade==="B"?"selected":"" }}>B</option><option value="C" {{ $item->grade==="C"?"selected":"" }}>C</option><option value="D" {{ $item->grade==="D"?"selected":"" }}>D</option><option value="F" {{ $item->grade==="F"?"selected":"" }}>F</option></select></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Rank</label><input type="number" name="rank" class="form-control" value="{{ $item->rank }}" min="1"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Remarks</label><textarea name="remarks" class="form-control" rows="2">{{ $item->remarks }}</textarea></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Teacher Comment</label><textarea name="teacher_comment" class="form-control" rows="2">{{ $item->teacher_comment }}</textarea></div>
            <div class="col-12 mt-4"><button type="submit" class="btn btn-gold"><i class="bi bi-check-lg me-1"></i>Update</button><a href="{{ route(\'admin.progress-reports.index\') }}" class="btn btn-outline-secondary ms-2">Cancel</a></div>
        </div></form>
    </div></div>
</div>
@endsection');
echo "[OK] ProgressReport edit\n";
echo "[OK] ProgressReport module done\n";

// Clear
foreach(['route:clear','config:clear','view:clear','cache:clear'] as $cmd){
    $o=shell_exec('php artisan '.$cmd.' 2>&1'); echo trim($o)."\n";
}
echo "\n=== Part 2C done! ===\n";
