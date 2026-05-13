<?php
echo "=== Add Section Support to Class Assets ===\n\n";
 $b = __DIR__;

// ── 1. Add section_id column to class_assets table ──
echo "[1/5] Adding section_id to class_assets table...\n";
try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=school_of_redemption", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $cols = $pdo->query("SHOW COLUMNS FROM class_assets")->fetchAll(PDO::FETCH_ASSOC);
    $colNames = array_column($cols, 'Field');
    if (!in_array('section_id', $colNames)) {
        $pdo->exec("ALTER TABLE class_assets ADD COLUMN section_id BIGINT UNSIGNED NULL AFTER class_id");
        $pdo->exec("ALTER TABLE class_assets ADD FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL");
        echo "  [OK] Added section_id column\n";
    } else {
        echo "  [SKIP] section_id already exists\n";
    }
} catch (Exception $e) {
    echo "  [WARN] " . $e->getMessage() . "\n";
}

// ── 2. Update ClassAsset Model ──
echo "[2/5] Updating ClassAsset model...\n";
 $model = '<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class ClassAsset extends Model
{
    use HasFactory;
    protected $fillable = [
        "class_id", "section_id", "name", "quantity", "condition",
        "purchase_date", "purchase_price", "description"
    ];

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, "class_id");
    }

    public function section()
    {
        return $this->belongsTo(Section::class, "section_id");
    }
}';
file_put_contents($b . '/app/Models/ClassAsset.php', $model);
echo "  [OK]\n";

// ── 3. Update ClassAssetController ──
echo "[3/5] Updating ClassAssetController...\n";
 $ctrl = '<?php
namespace App\Http\Controllers\ClassAsset;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClassAsset;
use App\Models\Classroom;
use App\Models\Section;
class ClassAssetController extends Controller
{
    public function index()
    {
        $data = ClassAsset::with("classroom", "section")->latest()->paginate(20);
        $totalAssets = ClassAsset::count();
        $totalValue = ClassAsset::sum("purchase_price");
        $classrooms = Classroom::orderBy("name")->get();
        return view("admin.ClassAsset.index", compact("data", "totalAssets", "totalValue", "classrooms"));
    }

    public function create()
    {
        $classrooms = Classroom::with("sections")->orderBy("name")->get();
        return view("admin.ClassAsset.create", compact("classrooms"));
    }

    public function store(Request $r)
    {
        $r->validate([
            "name" => "required|string|max:255",
            "class_id" => "required|exists:classrooms,id",
            "section_id" => "nullable|exists:sections,id",
            "quantity" => "required|integer|min:1",
            "condition" => "required|in:new,good,fair,poor,damaged",
            "purchase_date" => "nullable|date",
            "purchase_price" => "nullable|numeric|min:0",
            "description" => "nullable|string|max:500",
        ]);
        ClassAsset::create($r->all());
        return redirect()->route("admin.class-assets.index")->with("success", "Asset registered successfully");
    }

    public function show(ClassAsset $item) { return view("admin.ClassAsset.show", compact("item")); }

    public function edit(ClassAsset $item)
    {
        $classrooms = Classroom::with("sections")->orderBy("name")->get();
        return view("admin.ClassAsset.edit", compact("item", "classrooms"));
    }

    public function update(Request $r, ClassAsset $item)
    {
        $r->validate([
            "name" => "required|string|max:255",
            "class_id" => "required|exists:classrooms,id",
            "section_id" => "nullable|exists:sections,id",
            "quantity" => "required|integer|min:1",
            "condition" => "required|in:new,good,fair,poor,damaged",
            "purchase_date" => "nullable|date",
            "purchase_price" => "nullable|numeric|min:0",
            "description" => "nullable|string|max:500",
        ]);
        $item->update($r->all());
        return redirect()->route("admin.class-assets.index")->with("success", "Asset updated successfully");
    }

    public function destroy(ClassAsset $item)
    {
        $item->delete();
        return back()->with("success", "Asset deleted successfully");
    }

    public function getSectionsByClass($classId)
    {
        $sections = Section::where("class_id", $classId)->orderBy("name")->get();
        return response()->json($sections);
    }
}';
file_put_contents($b . '/app/Http/Controllers/ClassAsset/ClassAssetController.php', $ctrl);
echo "  [OK]\n";

// ── 4. ClassAsset Index View ──
echo "[4/5] Writing ClassAsset index...\n";
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
                            <th>Section</th>
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
                            <td>
                                @if($item->section_id)
                                    <span class="badge bg-info bg-opacity-10 text-info">{{ $item->section->name ?? "-" }}</span>
                                @else
                                    <span class="text-muted">All Sections</span>
                                @endif
                            </td>
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

// ── 5. Create View with dynamic section dropdown ──
echo "[5/5] Writing ClassAsset create & edit...\n";
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

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-semibold mb-0"><i class="bi bi-plus-circle me-2"></i>Asset Information</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route(\'admin.class-assets.store\') }}" id="assetForm">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Asset Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error(\'name\') is-invalid @enderror" value="{{ old(\'name\') }}" placeholder="e.g. Projector, Whiteboard, Chairs" required>
                                @error(\'name\')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Classroom <span class="text-danger">*</span></label>
                                <select name="class_id" id="classSelect" class="form-select @error(\'class_id\') is-invalid @enderror" required>
                                    <option value="">-- Select --</option>
                                    @foreach($classrooms as $cls)
                                    <option value="{{ $cls->id }}" {{ old(\'class_id\') == $cls->id ? \'selected\' : \'\' }}>{{ $cls->name }}</option>
                                    @endforeach
                                </select>
                                @error(\'class_id\')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Section</label>
                                <select name="section_id" id="sectionSelect" class="form-select @error(\'section_id\') is-invalid @enderror">
                                    <option value="">-- All Sections --</option>
                                </select>
                                @error(\'section_id\')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" class="form-control @error(\'quantity\') is-invalid @enderror" value="{{ old(\'quantity\', 1) }}" min="1" required>
                                @error(\'quantity\')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
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
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Purchase Date</label>
                                <input type="date" name="purchase_date" class="form-control @error(\'purchase_date\') is-invalid @enderror" value="{{ old(\'purchase_date\') }}">
                                @error(\'purchase_date\')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Price (ETB)</label>
                                <input type="number" step="0.01" min="0" name="purchase_price" class="form-control @error(\'purchase_price\') is-invalid @enderror" value="{{ old(\'purchase_price\') }}" placeholder="0.00">
                                @error(\'purchase_price\')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control @error(\'description\') is-invalid @enderror" rows="3" placeholder="Optional description...">{{ old(\'description\') }}</textarea>
                                @error(\'description\')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 mt-4">
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

        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-semibold mb-0"><i class="bi bi-info-circle me-2"></i>Asset Registration Info</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="bi bi-1-circle text-gold me-2"></i>Select the <strong>Classroom</strong> first</li>
                        <li class="mb-2"><i class="bi bi-2-circle text-gold me-2"></i>Optionally choose a <strong>Section</strong></li>
                        <li class="mb-2"><i class="bi bi-3-circle text-gold me-2"></i>Leave section as "All" for classroom-wide assets</li>
                        <li class="mb-2"><i class="bi bi-4-circle text-gold me-2"></i>Set the <strong>condition</strong> accurately</li>
                        <li><i class="bi bi-5-circle text-gold me-2"></i>Add <strong>price</strong> for valuation</li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-semibold mb-0"><i class="bi bi-layers me-2"></i>Existing Assets Quick View</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" id="existingAssets">
                        <div class="list-group-item text-center text-muted py-3">
                            <small>Select a classroom to see its assets</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push("scripts")
<script>
document.addEventListener("DOMContentLoaded", function() {
    const classSelect = document.getElementById("classSelect");
    const sectionSelect = document.getElementById("sectionSelect");
    const allSections = @json($classrooms->flatMap(fn($c) => $c->sections->map(fn($s) => ["id" => $s->id, "name" => $s->name, "class_id" => $s->class_id])));

    function loadSections(classId) {
        sectionSelect.innerHTML = \'<option value="">-- All Sections --</option>\';
        if (!classId) return;
        const sections = allSections.filter(s => s.class_id == classId);
        if (sections.length === 0) {
            sectionSelect.innerHTML = \'<option value="">No sections</option>\';
            return;
        }
        sections.forEach(s => {
            const opt = document.createElement("option");
            opt.value = s.id;
            opt.textContent = s.name;
            sectionSelect.appendChild(opt);
        });
    }

    classSelect.addEventListener("change", function() {
        const classId = this.value;
        loadSections(classId);
        loadExistingAssets(classId);
    });

    function loadExistingAssets(classId) {
        const container = document.getElementById("existingAssets");
        if (!classId) {
            container.innerHTML = \'<div class="list-group-item text-center text-muted py-3"><small>Select a classroom to see its assets</small></div>\';
            return;
        }
        fetch("/admin/class-assets/api/by-class/" + classId)
            .then(r => r.json())
            .then(assets => {
                if (assets.length === 0) {
                    container.innerHTML = \'<div class="list-group-item text-center text-muted py-3"><small>No assets registered</small></div>\';
                    return;
                }
                container.innerHTML = assets.map(a => `
                    <div class="list-group-item px-3 py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="small">${a.name}</strong>
                                ${a.section_name ? `<span class="badge bg-info bg-opacity-10 text-info ms-1">${a.section_name}</span>` : \'<span class="badge bg-secondary bg-opacity-10 text-secondary ms-1">All</span>\'}
                            </div>
                            <small class="text-muted">Qty: ${a.quantity}</small>
                        </div>
                    </div>
                `).join("");
            })
            .catch(() => {
                container.innerHTML = \'<div class="list-group-item text-center text-muted py-3"><small>Could not load assets</small></div>\';
            });
    }
});
</script>
@endpush
@endsection';
file_put_contents($b . '/resources/views/admin/ClassAsset/create.blade.php', $cre);

// ── Edit View ──
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

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-semibold mb-0"><i class="bi bi-pencil-square me-2"></i>Edit: {{ $item->name }}</h6>
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
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Classroom <span class="text-danger">*</span></label>
                                <select name="class_id" id="classSelect" class="form-select @error(\'class_id\') is-invalid @enderror" required>
                                    <option value="">-- Select --</option>
                                    @foreach($classrooms as $cls)
                                    <option value="{{ $cls->id }}" {{ old(\'class_id\', $item->class_id) == $cls->id ? \'selected\' : \'\' }}>{{ $cls->name }}</option>
                                    @endforeach
                                </select>
                                @error(\'class_id\')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Section</label>
                                <select name="section_id" id="sectionSelect" class="form-select @error(\'section_id\') is-invalid @enderror">
                                    <option value="">-- All Sections --</option>
                                </select>
                                @error(\'section_id\')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" class="form-control @error(\'quantity\') is-invalid @enderror" value="{{ old(\'quantity\', $item->quantity) }}" min="1" required>
                                @error(\'quantity\')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
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
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Purchase Date</label>
                                <input type="date" name="purchase_date" class="form-control @error(\'purchase_date\') is-invalid @enderror" value="{{ old(\'purchase_date\', $item->purchase_date) }}">
                                @error(\'purchase_date\')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Price (ETB)</label>
                                <input type="number" step="0.01" min="0" name="purchase_price" class="form-control @error(\'purchase_price\') is-invalid @enderror" value="{{ old(\'purchase_price\', $item->purchase_price) }}">
                                @error(\'purchase_price\')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control @error(\'description\') is-invalid @enderror" rows="3">{{ old(\'description\', $item->description) }}</textarea>
                                @error(\'description\')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 mt-4">
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
    </div>
</div>

@push("scripts")
<script>
document.addEventListener("DOMContentLoaded", function() {
    const classSelect = document.getElementById("classSelect");
    const sectionSelect = document.getElementById("sectionSelect");
    const currentSectionId = {{ $item->section_id ?? "null" }};
    const allSections = @json($classrooms->flatMap(fn($c) => $c->sections->map(fn($s) => ["id" => $s->id, "name" => $s->name, "class_id" => $s->class_id])));

    function loadSections(classId) {
        sectionSelect.innerHTML = \'<option value="">-- All Sections --</option>\';
        if (!classId) return;
        const sections = allSections.filter(s => s.class_id == classId);
        if (sections.length === 0) {
            sectionSelect.innerHTML = \'<option value="">No sections</option>\';
            return;
        }
        sections.forEach(s => {
            const opt = document.createElement("option");
            opt.value = s.id;
            opt.textContent = s.name;
            if (s.id == currentSectionId) opt.selected = true;
            sectionSelect.appendChild(opt);
        });
    }

    // Load sections for the current classroom on page load
    loadSections(classSelect.value);

    classSelect.addEventListener("change", function() {
        loadSections(this.value);
    });
});
</script>
@endpush
@endsection';
file_put_contents($b . '/resources/views/admin/ClassAsset/edit.blade.php', $edt);
echo "  [OK]\n";

// ── 6. Add API route for fetching assets by classroom ──
echo "[6/6] Adding API route...\n";
 $routeFile = $b . '/routes/web.php';
 $rc = file_get_contents($routeFile);
if (strpos($rc, 'getSectionsByClass') === false && strpos($rc, 'api/by-class') === false) {
    // Find the class-assets resource route group and add custom route before it
    $newRoute = "\n    // Class Asset section filter\n    Route::get('/class-assets/api/by-class/{classId}', [App\\Http\\Controllers\\ClassAsset\\ClassAssetController::class, 'getSectionsByClass'])->name('admin.class-assets.by-class');\n";
    if (strpos($rc, 'class-assets') !== false) {
        $rc = str_replace('class-assets', 'class-assets-placeholder' . $newRoute . '    // class-assets-resource', $rc);
        $rc = str_replace('class-assets-placeholder', 'class-assets', $rc);
    }
    file_put_contents($routeFile, $rc);
    echo "  [OK] API route added\n";
} else {
    echo "  [SKIP] Route may already exist\n";
}

// ── Add the getAssetsByClass method to controller ──
echo "\n[BONUS] Adding assets-by-class API method...\n";
 $ctrlFile = $b . '/app/Http/Controllers/ClassAsset/ClassAssetController.php';
 $cc = file_get_contents($ctrlFile);
if (strpos($cc, 'getAssetsByClass') === false) {
    $cc = str_replace(
        'public function getSectionsByClass',
        'public function getAssetsByClass($classId)
    {
        $assets = ClassAsset::with("section")->where("class_id", $classId)->get();
        return response()->json($assets->map(function($a) {
            return [
                "id" => $a->id,
                "name" => $a->name,
                "quantity" => $a->quantity,
                "condition" => $a->condition,
                "section_name" => $a->section ? $a->section->name : null,
            ];
        }));
    }

    public function getSectionsByClass',
        $cc
    );
    file_put_contents($ctrlFile, $cc);
    echo "  [OK] getAssetsByClass method added\n";
}

// Update the API route to point to the new method
 $routeFile2 = $b . '/routes/web.php';
 $rc2 = file_get_contents($routeFile2);
 $rc2 = str_replace('getSectionsByClass', 'getAssetsByClass', $rc2);
file_put_contents($routeFile2, $rc2);

// Clear caches
echo "\nClearing caches...\n";
foreach(['view:clear','config:clear','cache:clear','route:clear'] as $cmd){
    $o=shell_exec('php artisan '.$cmd.' 2>&1'); echo "  ".trim($o)."\n";
}
echo "\n=== All done! ===\n";
echo "You can now:\n";
echo "  - Register assets for a specific classroom & section\n";
echo "  - Leave section as \"All Sections\" for classroom-wide assets\n";
echo "  - The section dropdown updates dynamically when you pick a classroom\n";
echo "  - Existing assets panel shows what already registered for the selected classroom\n";
