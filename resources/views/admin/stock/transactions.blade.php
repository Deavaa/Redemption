@extends('layouts.admin')
@section('title', __('Stock Transactions'))
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fas fa-history me-2"></i>{{ __('Stock Transactions') }}</h4>
    <div class="btn-group">
        <a href="{{ route('admin.stock.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-warehouse me-1"></i>{{ __('Stock Items') }}</a>
        <a href="{{ route('admin.stock.report') }}" class="btn btn-info btn-sm"><i class="fas fa-chart-bar me-1"></i>{{ __('Report') }}</a>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('admin.stock.transactions') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <select name="stock_item_id" class="form-select form-select-sm">
                    <option value="">{{ __('All Items') }}</option>
                    @foreach($items as $item)
                    <option value="{{ $item->id }}" {{ request('stock_item_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select form-select-sm">
                    <option value="">{{ __('All Types') }}</option>
                    @foreach($types as $key => $label)
                    <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="reason" class="form-select form-select-sm">
                    <option value="">{{ __('All Reasons') }}</option>
                    @foreach($reasons as $key => $label)
                    <option value="{{ $key }}" {{ request('reason') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm" placeholder="{{ __('From') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm" placeholder="{{ __('To') }}">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-filter"></i></button>
            </div>
        </form>
    </div>
</div>

{{-- Transactions Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Item') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Reason') }}</th>
                        <th class="text-center">{{ __('Quantity') }}</th>
                        <th>{{ __('Unit Price') }}</th>
                        <th>{{ __('Total') }}</th>
                        <th>{{ __('Recipient') }}</th>
                        <th>{{ __('Ref No') }}</th>
                        <th>{{ __('By') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $i => $txn)
                    <tr>
                        <td>{{ $transactions->firstItem() + $i }}</td>
                        <td>{{ $txn->transaction_date->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('admin.stock.show', $txn->stock_item_id) }}">{{ $txn->stockItem->name ?? '-' }}</a>
                        </td>
                        <td>
                            @if($txn->type === 'in')
                            <span class="badge bg-success"><i class="fas fa-arrow-down me-1"></i>{{ __('IN') }}</span>
                            @else
                            <span class="badge bg-danger"><i class="fas fa-arrow-up me-1"></i>{{ __('OUT') }}</span>
                            @endif
                        </td>
                        <td>{{ $txn->reason_label }}</td>
                        <td class="text-center fw-bold">{{ $txn->quantity }}</td>
                        <td>{{ number_format($txn->unit_price, 2) }}</td>
                        <td>{{ number_format($txn->total_price, 2) }}</td>
                        <td>{{ $txn->recipient ? ($txn->recipient->name ?? '-') : '-' }}</td>
                        <td><code>{{ $txn->reference_no ?? '-' }}</code></td>
                        <td>{{ $txn->createdBy->name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="11" class="text-center py-4 text-muted">{{ __('No transactions found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $transactions->withQueryString()->links() }}
</div>
@endsection
