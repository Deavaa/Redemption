@extends("layouts.admin")
@section("page-title","Fee Payments")
@section("content")
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div><h4 class="fw-bold mb-1"><i class="bi bi-credit-card me-2"></i>Fee Payments</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
            <li class="breadcrumb-item active text-gold">Fee Payments</li>
        </ol></nav></div>
        <a href="{{ route('admin.fee-payments.create') }}" class="btn btn-gold"><i class="bi bi-plus-lg me-1"></i>Record Payment</a>
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
                <a href="{{ route('admin.fee-payments.edit',$item->id) }}" class="btn btn-sm btn-link text-primary p-0" title="Edit"><i class="bi bi-pencil-square fs-5"></i></a>
                <form method="POST" action="{{ route('admin.fee-payments.destroy',$item->id) }}" style="display:inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Delete"><i class="bi bi-trash fs-5"></i></button></form>
            </td></tr>
        @endforeach</tbody></table></div></div>
        <div class="card-footer bg-white border-top">{{ $data->links() }}</div>
    </div>
    @else
    <div class="card border-0 shadow-sm"><div class="card-body text-center py-5"><i class="bi bi-credit-card fs-1 text-muted"></i><h5 class="mt-3 text-muted">No Payments Found</h5><a href="{{ route('admin.fee-payments.create') }}" class="btn btn-gold"><i class="bi bi-plus-lg me-1"></i>Record Payment</a></div></div>
    @endif
</div>
@endsection