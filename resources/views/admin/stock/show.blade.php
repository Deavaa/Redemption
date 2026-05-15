@extends('layouts.admin')
@section('title', __('Stock Item Details'))
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fas fa-box me-2"></i>{{ __('Stock Item Details') }}</h4>
    <a href="{{ route('admin.stock.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>{{ __('Back') }}</a>
</div>

<div class="row g-3">
    {{-- Item Info --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-info-circle me-1"></i>{{ __('Item Information') }}</h6>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-sm mb-0">
                    <tr><th width="140" class="table-light">{{ __('Name') }}</th><td>{{ $stock->name }}</td></tr>
                    <tr><th class="table-light">{{ __('Code') }}</th><td><code>{{ $stock->code ?? '-' }}</code></td></tr>
                    <tr><th class="table-light">{{ __('Category') }}</th><td><span class="badge bg-secondary">{{ $stock->category_label }}</span></td></tr>
                    <tr><th class="table-light">{{ __('Unit') }}</th><td>{{ $stock->unit }}</td></tr>
                    <tr><th class="table-light">{{ __('Location') }}</th><td>{{ $stock->location ?? '-' }}</td></tr>
                    <tr><th class="table-light">{{ __('Branch') }}</th><td>{{ $stock->branch->name ?? __('All Branches') }}</td></tr>
                    <tr><th class="table-light">{{ __('Description') }}</th><td>{{ $stock->description ?? '-' }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Stock Status --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-chart-pie me-1"></i>{{ __('Stock Status') }}</h6>
            </div>
            <div class="card-body text-center">
                <h1 class="display-4 fw-bold {{ $stock->isOutOfStock() ? 'text-danger' : ($stock->isLowStock() ? 'text-warning' : 'text-success') }}">{{ $stock->quantity }}</h1>
                <p class="text-muted">{{ $stock->unit }} {{ __('in stock') }}</p>

                @if($stock->isOutOfStock())
                <span class="badge bg-danger fs-6">{{ __('OUT OF STOCK') }}</span>
                @elseif($stock->isLowStock())
                <span class="badge bg-warning text-dark fs-6">{{ __('LOW STOCK') }}</span>
                @else
                <span class="badge bg-success fs-6">{{ __('AVAILABLE') }}</span>
                @endif

                <table class="table table-bordered table-sm mt-4 mb-0">
                    <tr><th class="table-light">{{ __('Minimum Stock') }}</th><td>{{ $stock->minimum_stock }}</td></tr>
                    <tr><th class="table-light">{{ __('Unit Price') }}</th><td>{{ number_format($stock->unit_price, 2) }} ETB</td></tr>
                    <tr><th class="table-light">{{ __('Total Value') }}</th><td class="fw-bold">{{ number_format($stock->total_value, 2) }} ETB</td></tr>
                </table>

                <div class="mt-3">
                    <a href="{{ route('admin.stock.edit', $stock) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit me-1"></i>{{ __('Edit') }}</a>
                    <a href="{{ route('admin.stock.stock-in') }}" class="btn btn-success btn-sm"><i class="fas fa-plus me-1"></i>{{ __('Stock In') }}</a>
                    <a href="{{ route('admin.stock.stock-out') }}" class="btn btn-danger btn-sm"><i class="fas fa-minus me-1"></i>{{ __('Stock Out') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Transaction History --}}
<div class="card border-0 shadow-sm mt-3">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-history me-1"></i>{{ __('Transaction History') }}</h6>
        <a href="{{ route('admin.stock.transactions', ['stock_item_id' => $stock->id]) }}" class="btn btn-outline-secondary btn-xs">{{ __('View All') }}</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Reason') }}</th>
                        <th>{{ __('Qty') }}</th>
                        <th>{{ __('Recipient') }}</th>
                        <th>{{ __('Ref No') }}</th>
                        <th>{{ __('Notes') }}</th>
                        <th>{{ __('By') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stock->transactions->take(20) as $txn)
                    <tr>
                        <td>{{ $txn->transaction_date->format('M d, Y') }}</td>
                        <td>
                            @if($txn->type === 'in')
                            <span class="badge bg-success"><i class="fas fa-arrow-down me-1"></i>{{ __('IN') }}</span>
                            @else
                            <span class="badge bg-danger"><i class="fas fa-arrow-up me-1"></i>{{ __('OUT') }}</span>
                            @endif
                        </td>
                        <td>{{ $txn->reason_label }}</td>
                        <td class="fw-bold">{{ $txn->quantity }}</td>
                        <td>{{ $txn->recipient ? ($txn->recipient->name ?? '-') : '-' }}</td>
                        <td><code>{{ $txn->reference_no ?? '-' }}</code></td>
                        <td>{{ $txn->notes ?? '-' }}</td>
                        <td>{{ $txn->createdBy->name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-3 text-muted">{{ __('No transactions yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
