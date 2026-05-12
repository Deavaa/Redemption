<?php
echo "=== Fix ClassAsset & Section ===\n\n";
 $b = __DIR__;

// ─────────── 1. ClassAsset Controller ───────────
echo "[1/6] Fixing ClassAssetController...\n";
 $ctrl = '<?php
namespace App\Http\Controllers\ClassAsset;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClassAsset;
use App\Models\Classroom;
class ClassAssetController extends Controller
{
    public function index() {
        $data = ClassAsset::with("classroom")->latest()->paginate(20);
        $totalAssets = ClassAsset::count();
        $totalValue = ClassAsset::sum("purchase_price");
        $classrooms = Classroom::orderBy("name")->get();
        return view("admin.ClassAsset.index", compact("data", "totalAssets", "totalValue", "classrooms"));
    }
    public function create() {
        $classrooms = Classroom::orderBy("name")->get();
        return view("admin.ClassAsset.create", compact("classrooms"));
    }
    public function store(Request $r) {
        $r->validate([
            "name" => "required|string|max:255",
            "class_id" => "required|exists:classrooms,id",
            "quantity" => "required|integer|min:1",
            "condition" => "required|in:new,good,fair,poor,damaged",
            "purchase_date" => "nullable|date",
            "purchase_price" => "nullable|numeric|min:0",
            "description" => "nullable|string|max:500",
        ]);
        ClassAsset::create($r->all());
        return redirect()->route("admin.class-assets.index")->with("success","Asset registered successfully");
    }
    public function show(ClassAsset $item) { return view("admin.ClassAsset.show", compact("item")); }
    public function edit(ClassAsset $item) {
        $classrooms = Classroom::orderBy("name")->get();
        return view("admin.ClassAsset.edit", compact("item", "classrooms"));
    }
    public function update(Request $r, ClassAsset $item) {
        $r->validate([
            "name" => "required|string|max:255",
            "class_id" => "required|exists:classrooms,id",
            "quantity" => "required|integer|min:1",
            "condition" => "required|in:new,good,fair,poor,damaged",
            "purchase_date" => "nullable|date",
            "purchase_price" => "nullable|numeric|min:0",
            "description" => "nullable|string|max:500",
        ]);
        $item->update($r->all());
        return redirect()->route("admin.class-assets.index")->with("success","Asset updated successfully");
    }
    public function destroy(ClassAsset $item) { $item->delete(); return back()->with("success","Asset deleted successfully"); }
}';
file_put_contents($b . '/app/Http/Controllers/ClassAsset/ClassAssetController.php', $ctrl);
echo "  [OK]\n";

// ─────────── 2. ClassAsset Index View ───────────
echo "[2/6] Writing ClassAsset index...\n";
 $idx = '@extends("layouts.admin")
@section("page-title", "Class Assets")
@section("content")
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-box-seam me-2"></i>Class Assets</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route(\'admin.dashboard\') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                <li class="breadcrumb-item active text-gold">Class Assets</li>
            </ol></nav>
        </div>
        <a href="{{ route(\'admin.class-assets.create\') }}" class="btn btn-gold">
            <i class="bi bi-plus-lg me-1"></i>Register Asset
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #c9a84c !important">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-primary mb-0">{{ $totalAssets }}</h3>
                    <small class="text-muted">Total Assets</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #28a745 !important">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-success mb-0">{{ number_format($totalValue, 2) }}</h3>
                    <small class="text-muted">Total Value (ETB)</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #0dcaf0 !important">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-info mb-0">{{ $classrooms->count() }}</h3>
                    <small class="text-muted">Classrooms</small>
                </div>
            </div>
        </div>
    </div>

    @if(session("success"))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>{{ session("success") }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($data->count() > 0)
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="fw-semibold mb-0"><i class="bi bi-list-ul me-2"></i>All Assets</h6>
                <span class="badge bg-light text-dark">{{ $data->count() }} asset(s)</span>
            </div>
        </div>
        <div class="card-body p0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width:40px">#</th>
                            <th>Asset Name</th>
                            <th>Classroom</th>
                            <th>Quantity</th>
                            <th>Condition</th>
                            <th>Purchase Date</th>
                            <th>Price</th>
                            <th>Description</th>
                            <th style="width:100px" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $item)
                        <tr>
                            <td class="ps-3 text-muted">{{ $loop->iteration }}</td>
                            <td><span class="fw-semibold">{{ $item->name }}</span></td>
                            <td class="text-muted">{{ $item->classroom->name ?? "-" }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>
                                @php
                                    $condColor = match($item->condition) {
                                        "new" => "success",
                                        "good" => "info",
                                        "fair" => "warning",
                                        "poor" => "danger",
                                        "damaged" => "secondary",
                                        default => "secondary"
                                    };
                                @endphp
                                <span class="badge bg-{{ $condColor }} bg-opacity-10 text-{{ $condColor }}">{{ ucfirst($item->condition) }}</span>
                            </td>
                            <td class="text-muted">{{ $item->purchase_date ?? "-" }}</td>
                            <td class="text-muted">{{ $item->purchase_price ? number_format($item->purchase_price, 2) : "-" }}</td>
                            <td class="text-muted small">{{ Str::limit($item->description ?? "-", 40) }}</td>
                            <td class="text-center">
                                <a href="{{ route(\'admin.class-assets.edit\', $item->id) }}" class="btn btn-sm btn-link text-primary p-0" title="Edit">
                                    <i class="bi bi-pencil-square fs-5"></i>
                                </a>
                                <form method="POST" action="{{ route(\'admin.class-assets.destroy\', $item->id) }}" style="display:inline" onsubmit="return confirm(\'Delete this asset?\')">
                                    @csrf @method(\'DELETE\')
                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Delete">
                                        <i class="bi bi-trash fs-5"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top">
            {{ $data->appends(request()->query())->links() }}
        </div>
    </div>
    @else
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-box-seam fs-1 text-muted"></i>
            <h5 class="mt-3 text-muted">No Assets Found</h5>
            <p class="text-muted">Start by registering your first class asset.</p>
            <a href="{{ route(\'admin.class-assets.create\') }}" class="btn btn-gold">
                <i class="bi bi-plus-lg me-1"></i>Register Asset
            </a>
        </div>
    </div>
    @endif
</div>
@endsection';
file_put_contents($b . '/resources/views/admin/ClassAsset/index.blade.php', $idx);
echo "  [OK]\n";

// ─────────── 3. ClassAsset Create View ───────────
echo "[3/6] Writing ClassAsset create...\n";
 $cre = '@extends("layouts.admin")
@section("page-title", "Register Asset")
@section("content")
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-box-seam me-2"></i>Register Asset</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route(\'admin.dashboard\') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route(\'admin.class-assets.index\') }}" class="text-decoration-none text-muted">Class Assets</a></li>
                <li class="breadcrumb-item active text-gold">Register</li>
            </ol></nav>
        </div>
        <a href="{{ route(\'admin.class-assets.index\') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    @if(session("success"))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>{{ session("success") }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-semibold mb-0"><i class="bi bi-plus-circle me-2"></i>Asset Information</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route(\'admin.class-assets.store\') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Asset Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error(\'name\') is-invalid @enderror" value="{{ old(\'name\') }}" placeholder="e.g. Projector, Whiteboard, Chairs" required>
                        @error(\'name\')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Classroom <span class="text-danger">*</span></label>
                        <select name="class_id" class="form-select @error(\'class_id\') is-invalid @enderror" required>
                            <option value="">-- Select Classroom --</option>
                            @foreach($classrooms as $cls)
                            <option value="{{ $cls->id }}" {{ old(\'class_id\') == $cls->id ? \'selected\' : \'\' }}>{{ $cls->name }}</option>
                            @endforeach
                        </select>
                        @error(\'class_id\')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" class="form-control @error(\'quantity\') is-invalid @enderror" value="{{ old(\'quantity\', 1) }}" min="1" required>
                        @error(\'quantity\')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Condition <span class="text-danger">*</span></label>
                        <select name="condition" class="form-select @error(\'condition\') is-invalid @enderror" required>
                            <option value="">-- Select --</option>
                            <option value="new" {{ old(\'condition\') === \'new\' ? \'selected\' : \'\' }}>New</option>
                            <option value="good" {{ old(\'condition\') === \'good\' ? \'selected\' : \'\' }}>Good</option>
                            <option value="fair" {{ old(\'condition\') === \'fair\' ? \'selected\' : \'\' }}>Fair</option>
                            <option value="poor" {{ old(\'condition\') === \'poor\' ? \'selected\' : \'\' }}>Poor</option>
                            <option value="damaged" {{ old(\'condition\') === \'damaged\' ? \'selected\' : \'\' }}>Damaged</option>
                        </select>
                        @error(\'condition\')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Purchase Date</label>
                        <input type="date" name="purchase_date" class="form-control @error(\'purchase_date\') is-invalid @enderror" value="{{ old(\'purchase_date\') }}">
                        @error(\'purchase_date\')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Purchase Price (ETB)</label>
                        <input type="number" step="0.01" min="0" name="purchase_price" class="form-control @error(\'purchase_price\') is-invalid @enderror" value="{{ old(\'purchase_price\') }}" placeholder="0.00">
                        @error(\'purchase_price\')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control @error(\'description\') is-invalid @enderror" rows="3" placeholder="Optional description...">{{ old(\'description\') }}</textarea>
                        @error(\'description\')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-gold">
                            <i class="bi bi-check-lg me-1"></i>Register Asset
                        </button>
                        <a href="{{ route(\'admin.class-assets.index\') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection';
file_put_contents($b . '/resources/views/admin/ClassAsset/create.blade.php', $cre);
echo "  [OK]\n";

// ─────────── 4. ClassAsset Edit View ───────────
echo "[4/6] Writing ClassAsset edit...\n";
 $edt = '@extends("layouts.admin")
@section("page-title", "Edit Asset")
@section("content")
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-box-seam me-2"></i>Edit Asset</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route(\'admin.dashboard\') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route(\'admin.class-assets.index\') }}" class="text-decoration-none text-muted">Class Assets</a></li>
                <li class="breadcrumb-item active text-gold">Edit</li>
            </ol></nav>
        </div>
        <a href="{{ route(\'admin.class-assets.index\') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-semibold mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Asset: {{ $item->name }}</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route(\'admin.class-assets.update\', $item->id) }}">
                @csrf @method(\'PUT\')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Asset Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error(\'name\') is-invalid @enderror" value="{{ old(\'name\', $item->name) }}" required>
                        @error(\'name\')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Classroom <span class="text-danger">*</span></label>
                        <select name="class_id" class="form-select @error(\'class_id\') is-invalid @enderror" required>
                            <option value="">-- Select Classroom --</option>
                            @foreach($classrooms as $cls)
                            <option value="{{ $cls->id }}" {{ old(\'class_id\', $item->class_id) == $cls->id ? \'selected\' : \'\' }}>{{ $cls->name }}</option>
                            @endforeach
                        </select>
                        @error(\'class_id\')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" class="form-control @error(\'quantity\') is-invalid @enderror" value="{{ old(\'quantity\', $item->quantity) }}" min="1" required>
                        @error(\'quantity\')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Condition <span class="text-danger">*</span></label>
                        <select name="condition" class="form-select @error(\'condition\') is-invalid @enderror" required>
                            <option value="">-- Select --</option>
                            <option value="new" {{ old(\'condition\', $item->condition) === \'new\' ? \'selected\' : \'\' }}>New</option>
                            <option value="good" {{ old(\'condition\', $item->condition) === \'good\' ? \'selected\' : \'\' }}>Good</option>
                            <option value="fair" {{ old(\'condition\', $item->condition) === \'fair\' ? \'selected\' : \'\' }}>Fair</option>
                            <option value="poor" {{ old(\'condition\', $item->condition) === \'poor\' ? \'selected\' : \'\' }}>Poor</option>
                            <option value="damaged" {{ old(\'condition\', $item->condition) === \'damaged\' ? \'selected\' : \'\' }}>Damaged</option>
                        </select>
                        @error(\'condition\')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Purchase Date</label>
                        <input type="date" name="purchase_date" class="form-control @error(\'purchase_date\') is-invalid @enderror" value="{{ old(\'purchase_date\', $item->purchase_date) }}">
                        @error(\'purchase_date\')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Purchase Price (ETB)</label>
                        <input type="number" step="0.01" min="0" name="purchase_price" class="form-control @error(\'purchase_price\') is-invalid @enderror" value="{{ old(\'purchase_price\', $item->purchase_price) }}">
                        @error(\'purchase_price\')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control @error(\'description\') is-invalid @enderror" rows="3">{{ old(\'description\', $item->description) }}</textarea>
                        @error(\'description\')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-gold">
                            <i class="bi bi-check-lg me-1"></i>Update Asset
                        </button>
                        <a href="{{ route(\'admin.class-assets.index\') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection';
file_put_contents($b . '/resources/views/admin/ClassAsset/edit.blade.php', $edt);
echo "  [OK]\n";

// ─────────── 5. Section Controller ───────────
echo "[5/6] Fixing SectionController...\n";
 $sc = '<?php
namespace App\Http\Controllers\Section;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Section;
use App\Models\Classroom;
use App\Models\Teacher;
class SectionController extends Controller {
    public function index(){
        $data = Section::with(["classroom", "teacher"])->latest()->paginate(20);
        $totalSections = Section::count();
        $classrooms = Classroom::orderBy("name")->get();
        return view("admin.Section.index", compact("data", "totalSections", "classrooms"));
    }
    public function create(){
        $classrooms = Classroom::orderBy("name")->get();
        $teachers = Teacher::orderBy("first_name")->get();
        return view("admin.Section.create", compact("classrooms", "teachers"));
    }
    public function store(Request $r){
        $r->validate([
            "name" => "required|string|max:255",
            "class_id" => "required|exists:classrooms,id",
            "teacher_id" => "nullable|exists:teachers,id",
            "capacity" => "nullable|integer|min:1",
            "max_students" => "nullable|integer|min:1",
        ]);
        Section::create($r->all());
        return redirect()->back()->with("section_success", "Section created successfully");
    }
    public function show(Section $item){ return view("admin.Section.show", compact("item")); }
    public function edit(Section $item){
        $classrooms = Classroom::orderBy("name")->get();
        $teachers = Teacher::orderBy("first_name")->get();
        return view("admin.Section.edit", compact("item", "classrooms", "teachers"));
    }
    public function update(Request $r, Section $item){
        $item->update($r->all());
        if($r->ajax() || $r->wantsJson()){ return response()->json(["success"=>true, "name"=>$item->name, "capacity"=>$item->capacity]); }
        return redirect()->back()->with("section_success", "Section updated");
    }
    public function destroy(Section $item){ $item->delete(); return back()->with("section_success", "Section deleted"); }
}';
file_put_contents($b . '/app/Http/Controllers/Section/SectionController.php', $sc);
echo "  [OK]\n";

// ─────────── 6. Section Index View ───────────
echo "[6/6] Writing Section index...\n";
 $si = '@extends("layouts.admin")
@section("page-title", "Sections")
@section("content")
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-columns-gap me-2"></i>Sections</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route(\'admin.dashboard\') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                <li class="breadcrumb-item active text-gold">Sections</li>
            </ol></nav>
        </div>
        <a href="{{ route(\'admin.sections.create\') }}" class="btn btn-gold">
            <i class="bi bi-plus-lg me-1"></i>Add Section
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #c9a84c !important">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-primary mb-0">{{ $totalSections }}</h3>
                    <small class="text-muted">Total Sections</small>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #0dcaf0 !important">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-info mb-0">{{ $classrooms->count() }}</h3>
                    <small class="text-muted">Classrooms</small>
                </div>
            </div>
        </div>
    </div>

    @if(session("section_success"))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>{{ session("section_success") }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($data->count() > 0)
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="fw-semibold mb-0"><i class="bi bi-list-ul me-2"></i>All Sections</h6>
                <span class="badge bg-light text-dark">{{ $data->count() }} section(s)</span>
            </div>
        </div>
        <div class="card-body p0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width:40px">#</th>
                            <th>Section Name</th>
                            <th>Classroom</th>
                            <th>Teacher</th>
                            <th>Capacity</th>
                            <th>Max Students</th>
                            <th style="width:100px" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $item)
                        <tr>
                            <td class="ps-3 text-muted">{{ $loop->iteration }}</td>
                            <td><span class="fw-semibold">{{ $item->name }}</span></td>
                            <td class="text-muted">{{ $item->classroom->name ?? "-" }}</td>
                            <td class="text-muted">{{ $item->teacher->first_name ?? "-" }}</td>
                            <td>{{ $item->capacity ?? "-" }}</td>
                            <td>{{ $item->max_students ?? "-" }}</td>
                            <td class="text-center">
                                <a href="{{ route(\'admin.sections.edit\', $item->id) }}" class="btn btn-sm btn-link text-primary p-0" title="Edit">
                                    <i class="bi bi-pencil-square fs-5"></i>
                                </a>
                                <form method="POST" action="{{ route(\'admin.sections.destroy\', $item->id) }}" style="display:inline" onsubmit="return confirm(\'Delete this section?\')">
                                    @csrf @method(\'DELETE\')
                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Delete">
                                        <i class="bi bi-trash fs-5"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top">
            {{ $data->appends(request()->query())->links() }}
        </div>
    </div>
    @else
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-columns-gap fs-1 text-muted"></i>
            <h5 class="mt-3 text-muted">No Sections Found</h5>
            <p class="text-muted">Start by adding your first section.</p>
            <a href="{{ route(\'admin.sections.create\') }}" class="btn btn-gold">
                <i class="bi bi-plus-lg me-1"></i>Add Section
            </a>
        </div>
    </div>
    @endif
</div>
@endsection';
file_put_contents($b . '/resources/views/admin/Section/index.blade.php', $si);
echo "  [OK]\n";

// ── Check ClassAsset model has classroom relationship ──
echo "\n[BONUS] Checking ClassAsset model...\n";
 $modelFile = $b . '/app/Models/ClassAsset.php';
if (file_exists($modelFile)) {
    $mc = file_get_contents($modelFile);
    if (strpos($mc, 'function classroom') === false) {
        $mc = str_replace('}', '    public function classroom(){ return $this->belongsTo(Classroom::class, "class_id"); }'."\n}", $mc);
        if (strpos($mc, 'use App\Models\Classroom') === false) {
            $mc = str_replace('namespace App\Models;', "namespace App\Models;\nuse App\Models\Classroom;", $mc);
        }
        file_put_contents($modelFile, $mc);
        echo "  [OK] Added classroom relationship to ClassAsset model\n";
    } else {
        echo "  [OK] classroom relationship already exists\n";
    }
}

// ── Check Section model has classroom & teacher relationships ──
echo "\n[BONUS] Checking Section model...\n";
 $secModel = $b . '/app/Models/Section.php';
if (file_exists($secModel)) {
    $sm = file_get_contents($secModel);
    $changed = false;
    if (strpos($sm, 'function classroom') === false) {
        if (strpos($sm, 'use App\Models\Classroom') === false) {
            $sm = str_replace('namespace App\Models;', "namespace App\Models;\nuse App\Models\Classroom;\nuse App\Models\Teacher;", $sm);
        }
        $sm = str_replace('}', '    public function classroom(){ return $this->belongsTo(Classroom::class, "class_id"); }'."\n    public function teacher(){ return $this->belongsTo(Teacher::class, "teacher_id"); }'."\n}", $sm);
        $changed = true;
    }
    if ($changed) {
        file_put_contents($secModel, $sm);
        echo "  [OK] Added relationships to Section model\n";
    } else {
        echo "  [OK] Relationships already exist\n";
    }
}

// Clear caches
echo "\nClearing caches...\n";
foreach(['view:clear','config:clear','cache:clear','route:clear'] as $cmd){
    $o=shell_exec('php artisan '.$cmd.' 2>&1'); echo "  ".trim($o)."\n";
}
echo "\n=== All done! Check Class Assets and Sections pages. ===\n";
