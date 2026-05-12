@extends("layouts.admin")
@section("page-title","Record Payment")
@section("content")
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div><h4 class="fw-bold mb-1"><i class="bi bi-credit-card me-2"></i>Record Payment</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.fee-payments.index') }}" class="text-decoration-none text-muted">Payments</a></li>
            <li class="breadcrumb-item active text-gold">New</li>
        </ol></nav></div>
        <a href="{{ route('admin.fee-payments.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
    <div class="card border-0 shadow-sm"><div class="card-header bg-white border-bottom py-3"><h6 class="fw-semibold mb-0"><i class="bi bi-plus-circle me-2"></i>Payment Details</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.fee-payments.store') }}">@csrf
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label fw-semibold">Student *</label><select name="student_id" class="form-select" required><option value="">-- Select --</option>@foreach($students as $s)<option value="{{ $s->id }}">{{ $s->first_name }} {{ $s->last_name }} ({{ $s->roll_number ?? "" }})</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Fee *</label><select name="fee_id" class="form-select" required><option value="">-- Select --</option>@foreach($fees as $f)<option value="{{ $f->id }}">{{ $f->fee_type }} - {{ $f->classroom->name ?? "" }} ({{ number_format($f->amount,2) }} ETB)</option>@endforeach</select></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Amount Paid *</label><input type="number" step="0.01" min="0" name="amount_paid" class="form-control" required></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Payment Date *</label><input type="date" name="payment_date" class="form-control" value="{{ date("Y-m-d") }}" required></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Method *</label><select name="payment_method" class="form-select" required><option value="">-- Select --</option><option value="cash">Cash</option><option value="bank">Bank</option><option value="mobile">Mobile</option><option value="cheque">Cheque</option><option value="online">Online</option></select></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Status *</label><select name="status" class="form-select" required><option value="">-- Select --</option><option value="paid">Paid</option><option value="partial">Partial</option><option value="pending">Pending</option><option value="overdue">Overdue</option></select></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Transaction ID</label><input type="text" name="transaction_id" class="form-control"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Receipt Number</label><input type="text" name="receipt_number" class="form-control"></div>
            <div class="col-12 mt-4"><button type="submit" class="btn btn-gold"><i class="bi bi-check-lg me-1"></i>Record Payment</button><a href="{{ route('admin.fee-payments.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a></div>
        </div></form>
    </div></div>
</div>
@endsection