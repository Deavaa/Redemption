@extends("layouts.admin")
@section("page-title","Edit Payment")
@section("content")
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div><h4 class="fw-bold mb-1"><i class="bi bi-credit-card me-2"></i>Edit Payment</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.fee-payments.index') }}" class="text-decoration-none text-muted">Payments</a></li>
            <li class="breadcrumb-item active text-gold">Edit</li>
        </ol></nav></div>
        <a href="{{ route('admin.fee-payments.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
    <div class="card border-0 shadow-sm"><div class="card-header bg-white border-bottom py-3"><h6 class="fw-semibold mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Payment #{{ $item->id }}</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.fee-payments.update',$item->id) }}">@csrf @method('PUT')
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label fw-semibold">Student *</label><select name="student_id" class="form-select" required><option value="">-- Select --</option>@foreach($students as $s)<option value="{{ $s->id }}" {{ $item->student_id==$s->id?"selected":"" }}>{{ $s->first_name }} {{ $s->last_name }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Fee *</label><select name="fee_id" class="form-select" required><option value="">-- Select --</option>@foreach($fees as $f)<option value="{{ $f->id }}" {{ $item->fee_id==$f->id?"selected":"" }}>{{ $f->fee_type }}</option>@endforeach</select></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Amount Paid *</label><input type="number" step="0.01" min="0" name="amount_paid" class="form-control" value="{{ $item->amount_paid }}" required></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Payment Date *</label><input type="date" name="payment_date" class="form-control" value="{{ $item->payment_date }}" required></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Method *</label><select name="payment_method" class="form-select" required><option value="">-- Select --</option><option value="cash" {{ $item->payment_method==="cash"?"selected":"" }}>Cash</option><option value="bank" {{ $item->payment_method==="bank"?"selected":"" }}>Bank</option><option value="mobile" {{ $item->payment_method==="mobile"?"selected":"" }}>Mobile</option><option value="cheque" {{ $item->payment_method==="cheque"?"selected":"" }}>Cheque</option><option value="online" {{ $item->payment_method==="online"?"selected":"" }}>Online</option></select></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Status *</label><select name="status" class="form-select" required><option value="">-- Select --</option><option value="paid" {{ $item->status==="paid"?"selected":"" }}>Paid</option><option value="partial" {{ $item->status==="partial"?"selected":"" }}>Partial</option><option value="pending" {{ $item->status==="pending"?"selected":"" }}>Pending</option><option value="overdue" {{ $item->status==="overdue"?"selected":"" }}>Overdue</option></select></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Transaction ID</label><input type="text" name="transaction_id" class="form-control" value="{{ $item->transaction_id }}"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Receipt Number</label><input type="text" name="receipt_number" class="form-control" value="{{ $item->receipt_number }}"></div>
            <div class="col-12 mt-4"><button type="submit" class="btn btn-gold"><i class="bi bi-check-lg me-1"></i>Update</button><a href="{{ route('admin.fee-payments.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a></div>
        </div></form>
    </div></div>
</div>
@endsection