<?php
echo "=== Part 2B: Fee + FeePayment + Settings + ProgressReport ===\n\n";
 $b = __DIR__;

// FEE CONTROLLER
file_put_contents($b.'/app/Http/Controllers/Fee/FeeController.php', '<?php
namespace App\Http\Controllers\Fee;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Fee;
use App\Models\Classroom;
use App\Models\AcademicYear;
class FeeController extends Controller
{
    public function index(){
        $data = Fee::with("classroom","academicYear")->latest()->paginate(20);
        $totalFees = Fee::count();
        $totalAmount = Fee::sum("amount");
        return view("admin.Fee.index", compact("data","totalFees","totalAmount"));
    }
    public function create(){
        $classrooms = Classroom::orderBy("name")->get();
        $academicYears = AcademicYear::orderBy("name")->get();
        return view("admin.Fee.create", compact("classrooms","academicYears"));
    }
    public function store(Request $r){
        $r->validate(["fee_type"=>"required","amount"=>"required|numeric|min:0","class_id"=>"required|exists:classrooms,id","academic_year_id"=>"required|exists:academic_years,id","type"=>"required|in:tuition,lab,library,transport,sports,other"]);
        Fee::create($r->all());
        return redirect()->route("admin.fees.index")->with("success","Fee created");
    }
    public function show(Fee $item){ return view("admin.Fee.show", compact("item")); }
    public function edit(Fee $item){
        $classrooms = Classroom::orderBy("name")->get();
        $academicYears = AcademicYear::orderBy("name")->get();
        return view("admin.Fee.edit", compact("item","classrooms","academicYears"));
    }
    public function update(Request $r, Fee $item){
        $r->validate(["fee_type"=>"required","amount"=>"required|numeric|min:0","class_id"=>"required|exists:classrooms,id","academic_year_id"=>"required|exists:academic_years,id","type"=>"required|in:tuition,lab,library,transport,sports,other"]);
        $item->update($r->all());
        return redirect()->route("admin.fees.index")->with("success","Updated");
    }
    public function destroy(Fee $item){ $item->delete(); return back()->with("success","Deleted"); }
}');
echo "[OK] Fee controller\n";

// FEE INDEX
file_put_contents($b.'/resources/views/admin/Fee/index.blade.php', '@extends("layouts.admin")
@section("page-title","Fee Structure")
@section("content")
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div><h4 class="fw-bold mb-1"><i class="bi bi-receipt me-2"></i>Fee Structure</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route(\'admin.dashboard\') }}" class="text-decoration-none text-muted">Dashboard</a></li>
            <li class="breadcrumb-item active text-gold">Fee Structure</li>
        </ol></nav></div>
        <a href="{{ route(\'admin.fees.create\') }}" class="btn btn-gold"><i class="bi bi-plus-lg me-1"></i>Add Fee</a>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card border-0 shadow-sm h-100" style="border-top:3px solid #c9a84c !important"><div class="card-body text-center"><h3 class="fw-bold text-primary mb-0">{{ $totalFees }}</h3><small class="text-muted">Total Fees</small></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm h-100" style="border-top:3px solid #28a745 !important"><div class="card-body text-center"><h3 class="fw-bold text-success mb-0">{{ number_format($totalAmount,2) }}</h3><small class="text-muted">Total Amount (ETB)</small></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm h-100" style="border-top:3px solid #0dcaf0 !important"><div class="card-body text-center"><h3 class="fw-bold text-info mb-0">{{ $data->count() }}</h3><small class="text-muted">This Page</small></div></div></div>
    </div>
    @if(session("success"))<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i>{{ session("success") }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if($data->count() > 0)
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3"><div class="d-flex justify-content-between align-items-center"><h6 class="fw-semibold mb-0"><i class="bi bi-list-ul me-2"></i>All Fees</h6><span class="badge bg-light text-dark">{{ $totalFees }}</span></div></div>
        <div class="card-body p0"><div class="table-responsive">
        <table class="table table-hover align-middle mb-0"><thead class="table-light"><tr>
            <th class="ps-3" style="width:40px">#</th><th>Fee Type</th><th>Category</th><th>Classroom</th><th>Year</th><th>Amount (ETB)</th><th>Due Date</th><th>Status</th><th style="width:100px" class="text-center">Actions</th>
        </tr></thead><tbody>
        @foreach($data as $item)
        <tr><td class="ps-3 text-muted">{{ $loop->iteration }}</td>
            <td><span class="fw-semibold">{{ $item->fee_type }}</span></td>
            <td><span class="badge bg-info bg-opacity-10 text-info">{{ ucfirst($item->type ?? "-") }}</span></td>
            <td class="text-muted">{{ $item->classroom->name ?? "-" }}</td>
            <td class="text-muted">{{ $item->academicYear->name ?? "-" }}</td>
            <td><strong>{{ number_format($item->amount,2) }}</strong></td>
            <td class="text-muted">{{ $item->due_date ?? "-" }}</td>
            <td>@if($item->is_active)<span class="badge bg-success bg-opacity-10 text-success">Active</span>@else<span class="badge bg-secondary bg-opacity-10 text-secondary">Inactive</span>@endif</td>
            <td class="text-center">
                <a href="{{ route(\'admin.fees.edit\',$item->id) }}" class="btn btn-sm btn-link text-primary p-0" title="Edit"><i class="bi bi-pencil-square fs-5"></i></a>
                <form method="POST" action="{{ route(\'admin.fees.destroy\',$item->id) }}" style="display:inline" onsubmit="return confirm(\'Delete?\')">@csrf @method(\'DELETE\')<button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Delete"><i class="bi bi-trash fs-5"></i></button></form>
            </td></tr>
        @endforeach</tbody></table></div></div>
        <div class="card-footer bg-white border-top">{{ $data->links() }}</div>
    </div>
    @else
    <div class="card border-0 shadow-sm"><div class="card-body text-center py-5"><i class="bi bi-receipt fs-1 text-muted"></i><h5 class="mt-3 text-muted">No Fees Found</h5><a href="{{ route(\'admin.fees.create\') }}" class="btn btn-gold"><i class="bi bi-plus-lg me-1"></i>Add Fee</a></div></div>
    @endif
</div>
@endsection');
echo "[OK] Fee index\n";

// FEE CREATE
file_put_contents($b.'/resources/views/admin/Fee/create.blade.php', '@extends("layouts.admin")
@section("page-title","Add Fee")
@section("content")
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div><h4 class="fw-bold mb-1"><i class="bi bi-receipt me-2"></i>Add Fee</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route(\'admin.dashboard\') }}" class="text-decoration-none text-muted">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route(\'admin.fees.index\') }}" class="text-decoration-none text-muted">Fees</a></li>
            <li class="breadcrumb-item active text-gold">Add</li>
        </ol></nav></div>
        <a href="{{ route(\'admin.fees.index\') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
    <div class="card border-0 shadow-sm"><div class="card-header bg-white border-bottom py-3"><h6 class="fw-semibold mb-0"><i class="bi bi-plus-circle me-2"></i>Fee Details</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route(\'admin.fees.store\') }}">@csrf
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label fw-semibold">Fee Type *</label><input type="text" name="fee_type" class="form-control" value="{{ old(\'fee_type\') }}" required></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Category *</label><select name="type" class="form-select" required><option value="">-- Select --</option><option value="tuition">Tuition</option><option value="lab">Lab</option><option value="library">Library</option><option value="transport">Transport</option><option value="sports">Sports</option><option value="other">Other</option></select></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Amount (ETB) *</label><input type="number" step="0.01" min="0" name="amount" class="form-control" value="{{ old(\'amount\') }}" required></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Classroom *</label><select name="class_id" class="form-select" required><option value="">-- Select --</option>@foreach($classrooms as $cls)<option value="{{ $cls->id }}">{{ $cls->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Academic Year *</label><select name="academic_year_id" class="form-select" required><option value="">-- Select --</option>@foreach($academicYears as $ay)<option value="{{ $ay->id }}">{{ $ay->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Due Date</label><input type="date" name="due_date" class="form-control" value="{{ old(\'due_date\') }}"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Description</label><textarea name="description" class="form-control" rows="2">{{ old(\'description\') }}</textarea></div>
            <div class="col-md-3 d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked><label class="form-check-label fw-semibold">Active</label></div></div>
            <div class="col-12 mt-4"><button type="submit" class="btn btn-gold"><i class="bi bi-check-lg me-1"></i>Save Fee</button><a href="{{ route(\'admin.fees.index\') }}" class="btn btn-outline-secondary ms-2">Cancel</a></div>
        </div></form>
    </div></div>
</div>
@endsection');
echo "[OK] Fee create\n";

// FEE EDIT
file_put_contents($b.'/resources/views/admin/Fee/edit.blade.php', '@extends("layouts.admin")
@section("page-title","Edit Fee")
@section("content")
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div><h4 class="fw-bold mb-1"><i class="bi bi-receipt me-2"></i>Edit Fee</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route(\'admin.dashboard\') }}" class="text-decoration-none text-muted">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route(\'admin.fees.index\') }}" class="text-decoration-none text-muted">Fees</a></li>
            <li class="breadcrumb-item active text-gold">Edit</li>
        </ol></nav></div>
        <a href="{{ route(\'admin.fees.index\') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
    <div class="card border-0 shadow-sm"><div class="card-header bg-white border-bottom py-3"><h6 class="fw-semibold mb-0"><i class="bi bi-pencil-square me-2"></i>Edit: {{ $item->fee_type }}</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route(\'admin.fees.update\',$item->id) }}">@csrf @method(\'PUT\')
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label fw-semibold">Fee Type *</label><input type="text" name="fee_type" class="form-control" value="{{ $item->fee_type }}" required></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Category *</label><select name="type" class="form-select" required><option value="">-- Select --</option><option value="tuition" {{ $item->type==="tuition"?"selected":"" }}>Tuition</option><option value="lab" {{ $item->type==="lab"?"selected":"" }}>Lab</option><option value="library" {{ $item->type==="library"?"selected":"" }}>Library</option><option value="transport" {{ $item->type==="transport"?"selected":"" }}>Transport</option><option value="sports" {{ $item->type==="sports"?"selected":"" }}>Sports</option><option value="other" {{ $item->type==="other"?"selected":"" }}>Other</option></select></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Amount (ETB) *</label><input type="number" step="0.01" min="0" name="amount" class="form-control" value="{{ $item->amount }}" required></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Classroom *</label><select name="class_id" class="form-select" required><option value="">-- Select --</option>@foreach($classrooms as $cls)<option value="{{ $cls->id }}" {{ $item->class_id==$cls->id?"selected":"" }}>{{ $cls->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Academic Year *</label><select name="academic_year_id" class="form-select" required><option value="">-- Select --</option>@foreach($academicYears as $ay)<option value="{{ $ay->id }}" {{ $item->academic_year_id==$ay->id?"selected":"" }}>{{ $ay->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Due Date</label><input type="date" name="due_date" class="form-control" value="{{ $item->due_date }}"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Description</label><textarea name="description" class="form-control" rows="2">{{ $item->description }}</textarea></div>
            <div class="col-md-3 d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $item->is_active?"checked":"" }}><label class="form-check-label fw-semibold">Active</label></div></div>
            <div class="col-12 mt-4"><button type="submit" class="btn btn-gold"><i class="bi bi-check-lg me-1"></i>Update</button><a href="{{ route(\'admin.fees.index\') }}" class="btn btn-outline-secondary ms-2">Cancel</a></div>
        </div></form>
    </div></div>
</div>
@endsection');
echo "[OK] Fee edit\n";
echo "[OK] Fee module done\n";

// FEE PAYMENT CONTROLLER
file_put_contents($b.'/app/Http/Controllers/FeePayment/FeePaymentController.php', '<?php
namespace App\Http\Controllers\FeePayment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FeePayment;
use App\Models\Fee;
use App\Models\Student;
class FeePaymentController extends Controller
{
    public function index(){
        $data = FeePayment::with("fee","student")->latest()->paginate(20);
        $totalPayments = FeePayment::count();
        $totalCollected = FeePayment::sum("amount_paid");
        return view("admin.FeePayment.index", compact("data","totalPayments","totalCollected"));
    }
    public function create(){
        $fees = Fee::with("classroom","academicYear")->where("is_active",1)->get();
        $students = Student::orderBy("first_name")->get();
        return view("admin.FeePayment.create", compact("fees","students"));
    }
    public function store(Request $r){
        $r->validate(["fee_id"=>"required|exists:fees,id","student_id"=>"required|exists:students,id","amount_paid"=>"required|numeric|min:0","payment_date"=>"required|date","payment_method"=>"required|in:cash,bank,mobile,cheque,online","status"=>"required|in:paid,partial,pending,overdue"]);
        FeePayment::create($r->all());
        return redirect()->route("admin.fee-payments.index")->with("success","Payment recorded");
    }
    public function show(FeePayment $item){ return view("admin.FeePayment.show", compact("item")); }
    public function edit(FeePayment $item){
        $fees = Fee::with("classroom","academicYear")->where("is_active",1)->get();
        $students = Student::orderBy("first_name")->get();
        return view("admin.FeePayment.edit", compact("item","fees","students"));
    }
    public function update(Request $r, FeePayment $item){
        $r->validate(["fee_id"=>"required|exists:fees,id","student_id"=>"required|exists:students,id","amount_paid"=>"required|numeric|min:0","payment_date"=>"required|date","payment_method"=>"required|in:cash,bank,mobile,cheque,online","status"=>"required|in:paid,partial,pending,overdue"]);
        $item->update($r->all());
        return redirect()->route("admin.fee-payments.index")->with("success","Updated");
    }
    public function destroy(FeePayment $item){ $item->delete(); return back()->with("success","Deleted"); }
}');
echo "[OK] FeePayment controller\n";

// FEE PAYMENT INDEX
file_put_contents($b.'/resources/views/admin/FeePayment/index.blade.php', '@extends("layouts.admin")
@section("page-title","Fee Payments")
@section("content")
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div><h4 class="fw-bold mb-1"><i class="bi bi-credit-card me-2"></i>Fee Payments</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route(\'admin.dashboard\') }}" class="text-decoration-none text-muted">Dashboard</a></li>
            <li class="breadcrumb-item active text-gold">Fee Payments</li>
        </ol></nav></div>
        <a href="{{ route(\'admin.fee-payments.create\') }}" class="btn btn-gold"><i class="bi bi-plus-lg me-1"></i>Record Payment</a>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card border-0 shadow-sm h-100" style="border-top:3px solid #c9a84c !important"><div class="card-body text-center"><h3 class="fw-bold text-primary mb-0">{{ $totalPayments }}</h3><small class="text-muted">Total Payments</small></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm h-100" style="border-top:3px solid #28a745 !important"><div class="card-body text-center"><h3 class="fw-bold text-success mb-0">{{ number_format($totalCollected,2) }}</h3><small class="text-muted">Collected (ETB)</small></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm h-100" style="border-top:3px solid #0dcaf0 !important"><div class="card-body text-center"><h3 class="fw-bold text-info mb-0">{{ $data->count() }}</h3><small class="text-muted">This Page</small></div></div></div>
    </div>
    @if(session("success"))<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i>{{ session("success") }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if($data->count() > 0)
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3"><div class="d-flex justify-content-between align-items-center"><h6 class="fw-semibold mb-0"><i class="bi bi-list-ul me-2"></i>All Payments</h6><span class="badge bg-light text-dark">{{ $totalPayments }}</span></div></div>
        <div class="card-body p0"><div class="table-responsive">
        <table class="table table-hover align-middle mb-0"><thead class="table-light"><tr>
            <th class="ps-3" style="width:40px">#</th><th>Student</th><th>Fee Type</th><th>Amount (ETB)</th><th>Method</th><th>Date</th><th>Status</th><th style="width:100px" class="text-center">Actions</th>
        </tr></thead><tbody>
        @foreach($data as $item)
        <tr><td class="ps-3 text-muted">{{ $loop->iteration }}</td>
            <td><span class="fw-semibold">{{ $item->student->first_name ?? "-" }} {{ $item->student->last_name ?? "" }}</span></td>
            <td class="text-muted">{{ $item->fee->fee_type ?? "-" }}</td>
            <td><strong>{{ number_format($item->amount_paid,2) }}</strong></td>
            <td><span class="badge bg-info bg-opacity-10 text-info">{{ ucfirst($item->payment_method) }}</span></td>
            <td class="text-muted">{{ $item->payment_date }}</td>
            <td><span class="badge bg-{{ match($item->status){ "paid"=>"success","partial"=>"warning","pending"=>"secondary","overdue"=>"danger",default=>"secondary"} }} bg-opacity-10 text-{{ match($item->status){ "paid"=>"success","partial"=>"warning","pending"=>"secondary","overdue"=>"danger",default=>"secondary"} }}">{{ ucfirst($item->status) }}</span></td>
            <td class="text-center">
                <a href="{{ route(\'admin.fee-payments.edit\',$item->id) }}" class="btn btn-sm btn-link text-primary p-0" title="Edit"><i class="bi bi-pencil-square fs-5"></i></a>
                <form method="POST" action="{{ route(\'admin.fee-payments.destroy\',$item->id) }}" style="display:inline" onsubmit="return confirm(\'Delete?\')">@csrf @method(\'DELETE\')<button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Delete"><i class="bi bi-trash fs-5"></i></button></form>
            </td></tr>
        @endforeach</tbody></table></div></div>
        <div class="card-footer bg-white border-top">{{ $data->links() }}</div>
    </div>
    @else
    <div class="card border-0 shadow-sm"><div class="card-body text-center py-5"><i class="bi bi-credit-card fs-1 text-muted"></i><h5 class="mt-3 text-muted">No Payments Found</h5><a href="{{ route(\'admin.fee-payments.create\') }}" class="btn btn-gold"><i class="bi bi-plus-lg me-1"></i>Record Payment</a></div></div>
    @endif
</div>
@endsection');
echo "[OK] FeePayment index\n";

// FEE PAYMENT CREATE
file_put_contents($b.'/resources/views/admin/FeePayment/create.blade.php', '@extends("layouts.admin")
@section("page-title","Record Payment")
@section("content")
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div><h4 class="fw-bold mb-1"><i class="bi bi-credit-card me-2"></i>Record Payment</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route(\'admin.dashboard\') }}" class="text-decoration-none text-muted">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route(\'admin.fee-payments.index\') }}" class="text-decoration-none text-muted">Payments</a></li>
            <li class="breadcrumb-item active text-gold">New</li>
        </ol></nav></div>
        <a href="{{ route(\'admin.fee-payments.index\') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
    <div class="card border-0 shadow-sm"><div class="card-header bg-white border-bottom py-3"><h6 class="fw-semibold mb-0"><i class="bi bi-plus-circle me-2"></i>Payment Details</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route(\'admin.fee-payments.store\') }}">@csrf
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label fw-semibold">Student *</label><select name="student_id" class="form-select" required><option value="">-- Select --</option>@foreach($students as $s)<option value="{{ $s->id }}">{{ $s->first_name }} {{ $s->last_name }} ({{ $s->roll_number ?? "" }})</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Fee *</label><select name="fee_id" class="form-select" required><option value="">-- Select --</option>@foreach($fees as $f)<option value="{{ $f->id }}">{{ $f->fee_type }} - {{ $f->classroom->name ?? "" }} ({{ number_format($f->amount,2) }} ETB)</option>@endforeach</select></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Amount Paid *</label><input type="number" step="0.01" min="0" name="amount_paid" class="form-control" required></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Payment Date *</label><input type="date" name="payment_date" class="form-control" value="{{ date("Y-m-d") }}" required></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Method *</label><select name="payment_method" class="form-select" required><option value="">-- Select --</option><option value="cash">Cash</option><option value="bank">Bank</option><option value="mobile">Mobile</option><option value="cheque">Cheque</option><option value="online">Online</option></select></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Status *</label><select name="status" class="form-select" required><option value="">-- Select --</option><option value="paid">Paid</option><option value="partial">Partial</option><option value="pending">Pending</option><option value="overdue">Overdue</option></select></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Transaction ID</label><input type="text" name="transaction_id" class="form-control"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Receipt Number</label><input type="text" name="receipt_number" class="form-control"></div>
            <div class="col-12 mt-4"><button type="submit" class="btn btn-gold"><i class="bi bi-check-lg me-1"></i>Record Payment</button><a href="{{ route(\'admin.fee-payments.index\') }}" class="btn btn-outline-secondary ms-2">Cancel</a></div>
        </div></form>
    </div></div>
</div>
@endsection');
echo "[OK] FeePayment create\n";

// FEE PAYMENT EDIT
file_put_contents($b.'/resources/views/admin/FeePayment/edit.blade.php', '@extends("layouts.admin")
@section("page-title","Edit Payment")
@section("content")
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div><h4 class="fw-bold mb-1"><i class="bi bi-credit-card me-2"></i>Edit Payment</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route(\'admin.dashboard\') }}" class="text-decoration-none text-muted">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route(\'admin.fee-payments.index\') }}" class="text-decoration-none text-muted">Payments</a></li>
            <li class="breadcrumb-item active text-gold">Edit</li>
        </ol></nav></div>
        <a href="{{ route(\'admin.fee-payments.index\') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
    <div class="card border-0 shadow-sm"><div class="card-header bg-white border-bottom py-3"><h6 class="fw-semibold mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Payment #{{ $item->id }}</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route(\'admin.fee-payments.update\',$item->id) }}">@csrf @method(\'PUT\')
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label fw-semibold">Student *</label><select name="student_id" class="form-select" required><option value="">-- Select --</option>@foreach($students as $s)<option value="{{ $s->id }}" {{ $item->student_id==$s->id?"selected":"" }}>{{ $s->first_name }} {{ $s->last_name }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Fee *</label><select name="fee_id" class="form-select" required><option value="">-- Select --</option>@foreach($fees as $f)<option value="{{ $f->id }}" {{ $item->fee_id==$f->id?"selected":"" }}>{{ $f->fee_type }}</option>@endforeach</select></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Amount Paid *</label><input type="number" step="0.01" min="0" name="amount_paid" class="form-control" value="{{ $item->amount_paid }}" required></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Payment Date *</label><input type="date" name="payment_date" class="form-control" value="{{ $item->payment_date }}" required></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Method *</label><select name="payment_method" class="form-select" required><option value="">-- Select --</option><option value="cash" {{ $item->payment_method==="cash"?"selected":"" }}>Cash</option><option value="bank" {{ $item->payment_method==="bank"?"selected":"" }}>Bank</option><option value="mobile" {{ $item->payment_method==="mobile"?"selected":"" }}>Mobile</option><option value="cheque" {{ $item->payment_method==="cheque"?"selected":"" }}>Cheque</option><option value="online" {{ $item->payment_method==="online"?"selected":"" }}>Online</option></select></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Status *</label><select name="status" class="form-select" required><option value="">-- Select --</option><option value="paid" {{ $item->status==="paid"?"selected":"" }}>Paid</option><option value="partial" {{ $item->status==="partial"?"selected":"" }}>Partial</option><option value="pending" {{ $item->status==="pending"?"selected":"" }}>Pending</option><option value="overdue" {{ $item->status==="overdue"?"selected":"" }}>Overdue</option></select></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Transaction ID</label><input type="text" name="transaction_id" class="form-control" value="{{ $item->transaction_id }}"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Receipt Number</label><input type="text" name="receipt_number" class="form-control" value="{{ $item->receipt_number }}"></div>
            <div class="col-12 mt-4"><button type="submit" class="btn btn-gold"><i class="bi bi-check-lg me-1"></i>Update</button><a href="{{ route(\'admin.fee-payments.index\') }}" class="btn btn-outline-secondary ms-2">Cancel</a></div>
        </div></form>
    </div></div>
</div>
@endsection');
echo "[OK] FeePayment edit\n";
echo "[OK] FeePayment module done\n";

// Clear
foreach(['route:clear','config:clear','view:clear','cache:clear'] as $cmd){
    $o=shell_exec('php artisan '.$cmd.' 2>&1'); echo trim($o)."\n";
}
echo "\n=== Part 2B done. ===\n";
