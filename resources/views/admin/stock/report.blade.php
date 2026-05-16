@extends('layouts.admin')
@section('title', __('Stock Report'))
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fas fa-chart-bar me-2"></i>{{ __('Asset & Stock Report') }}</h4>
    <a href="{{ route('admin.stock.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>{{ __('Back') }}</a>
</div>

{{-- Filter Bar --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('admin.stock.report') }}" class="row g-2 align-items-end">
            <div class="col-md-2">
                <select name="report_type" class="form-select form-select-sm">
                    <option value="summary" {{ $reportType == 'summary' ? 'selected' : '' }}>{{ __('Summary') }}</option>
                    <option value="low_stock" {{ $reportType == 'low_stock' ? 'selected' : '' }}>{{ __('Low Stock') }}</option>
                    <option value="out_of_stock" {{ $reportType == 'out_of_stock' ? 'selected' : '' }}>{{ __('Out of Stock') }}</option>
                    <option value="employee_issues" {{ $reportType == 'employee_issues' ? 'selected' : '' }}>{{ __('Employee Issues') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="category" class="form-select form-select-sm">
                    <option value="">{{ __('All Categories') }}</option>
                    @foreach($categories as $key => $label)
                    <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="branch_id" class="form-select form-select-sm">
                    <option value="">{{ __('All Branches') }}</option>
                    @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm" placeholder="{{ __('From') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm" placeholder="{{ __('To') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm me-1"><i class="fas fa-filter me-1"></i>{{ __('Generate') }}</button>
                <button type="button" onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="fas fa-print me-1"></i>{{ __('Print') }}</button>
            </div>
        </form>
    </div>
</div>

{{-- Summary Stats --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-boxes fa-2x text-primary mb-2"></i>
                <h3 class="mb-0">{{ $totalItems }}</h3>
                <small class="text-muted">{{ __('Total Items') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-layer-group fa-2x text-info mb-2"></i>
                <h3 class="mb-0">{{ number_format($totalQuantity) }}</h3>
                <small class="text-muted">{{ __('Total Quantity') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-coins fa-2x text-success mb-2"></i>
                <h3 class="mb-0">{{ number_format($totalValue, 2) }}</h3>
                <small class="text-muted">{{ __('Total Value (ETB)') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                <h3 class="mb-0">{{ $lowStockItems->count() + $outOfStockItems->count() }}</h3>
                <small class="text-muted">{{ __('Needs Attention') }}</small>
            </div>
        </div>
    </div>
</div>

{{-- Category Breakdown --}}
<div class="row g-3 mb-4">
    <div class="col-md-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-chart-pie me-1"></i>{{ __('Category Breakdown') }}</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>{{ __('Category') }}</th><th class="text-center">{{ __('Items') }}</th><th class="text-center">{{ __('Qty') }}</th><th class="text-end">{{ __('Value') }}</th></tr>
                    </thead>
                    <tbody>
                        @foreach($categoryBreakdown as $cat => $data)
                        <tr>
                            <td>{{ $categories[$cat] ?? $cat }}</td>
                            <td class="text-center">{{ $data['count'] }}</td>
                            <td class="text-center">{{ $data['quantity'] }}</td>
                            <td class="text-end">{{ number_format($data['value'], 2) }}</td>
                        </tr>
                        @endforeach
                        @if($categoryBreakdown->isEmpty())
                        <tr><td colspan="4" class="text-center py-3 text-muted">{{ __('No data') }}</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-exclamation-circle me-1"></i>{{ __('Low & Out of Stock Items') }}</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>{{ __('Item') }}</th><th>{{ __('Category') }}</th><th class="text-center">{{ __('In Stock') }}</th><th class="text-center">{{ __('Min.') }}</th><th>{{ __('Status') }}</th></tr>
                    </thead>
                    <tbody>
                        @foreach($outOfStockItems as $item)
                        <tr class="table-danger">
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->category_label }}</td>
                            <td class="text-center fw-bold">{{ $item->quantity }}</td>
                            <td class="text-center">{{ $item->minimum_stock }}</td>
                            <td><span class="badge bg-danger">{{ __('Out of Stock') }}</span></td>
                        </tr>
                        @endforeach
                        @foreach($lowStockItems as $item)
                        <tr class="table-warning">
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->category_label }}</td>
                            <td class="text-center fw-bold">{{ $item->quantity }}</td>
                            <td class="text-center">{{ $item->minimum_stock }}</td>
                            <td><span class="badge bg-warning text-dark">{{ __('Low Stock') }}</span></td>
                        </tr>
                        @endforeach
                        @if($outOfStockItems->isEmpty() && $lowStockItems->isEmpty())
                        <tr><td colspan="5" class="text-center py-3 text-muted">{{ __('All items are well stocked') }}</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Full Stock Inventory Table --}}
@if($reportType === 'summary' || $reportType === 'low_stock' || $reportType === 'out_of_stock')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-light">
        <h6 class="mb-0"><i class="fas fa-list me-1"></i>{{ __('Stock Inventory') }}</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Code') }}</th>
                        <th>{{ __('Item Name') }}</th>
                        <th>{{ __('Category') }}</th>
                        <th>{{ __('Unit') }}</th>
                        <th class="text-center">{{ __('In Stock') }}</th>
                        <th class="text-center">{{ __('Min. Stock') }}</th>
                        <th>{{ __('Unit Price') }}</th>
                        <th>{{ __('Total Value') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Location') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $i => $item)
                    <tr class="{{ $item->isOutOfStock() ? 'table-danger' : ($item->isLowStock() ? 'table-warning' : '') }}">
                        <td>{{ $i + 1 }}</td>
                        <td><code>{{ $item->code ?? '-' }}</code></td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->category_label }}</td>
                        <td>{{ $item->unit }}</td>
                        <td class="text-center fw-bold">{{ $item->quantity }}</td>
                        <td class="text-center">{{ $item->minimum_stock }}</td>
                        <td>{{ number_format($item->unit_price, 2) }}</td>
                        <td>{{ number_format($item->total_value, 2) }}</td>
                        <td>
                            @if($item->isOutOfStock())
                            <span class="badge bg-danger">{{ __('Out') }}</span>
                            @elseif($item->isLowStock())
                            <span class="badge bg-warning text-dark">{{ __('Low') }}</span>
                            @else
                            <span class="badge bg-success">{{ __('OK') }}</span>
                            @endif
                        </td>
                        <td>{{ $item->location ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="11" class="text-center py-4 text-muted">{{ __('No items found.') }}</td></tr>
                    @endforelse
                </tbody>
                @if($items->count() > 0)
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="5">{{ __('Total') }}</td>
                        <td class="text-center">{{ $items->sum('quantity') }}</td>
                        <td></td>
                        <td></td>
                        <td>{{ number_format($items->sum('total_value'), 2) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endif

{{-- Employee Issues Detail --}}
@if($reportType === 'employee_issues' && $employeeIssues)
<div class="card border-0 shadow-sm">
    <div class="card-header bg-light">
        <h6 class="mb-0"><i class="fas fa-user-tag me-1"></i>{{ __('Items Issued to Employees') }}</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Item') }}</th>
                        <th>{{ __('Category') }}</th>
                        <th class="text-center">{{ __('Quantity') }}</th>
                        <th>{{ __('Employee') }}</th>
                        <th>{{ __('Value') }}</th>
                        <th>{{ __('Notes') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employeeIssues as $i => $txn)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $txn->transaction_date->format('M d, Y') }}</td>
                        <td>{{ $txn->stockItem->name ?? '-' }}</td>
                        <td>{{ $categories[$txn->stockItem->category ?? ''] ?? '-' }}</td>
                        <td class="text-center fw-bold">{{ $txn->quantity }}</td>
                        <td>{{ $txn->recipient->name ?? '-' }}</td>
                        <td>{{ number_format($txn->total_price, 2) }}</td>
                        <td>{{ $txn->notes ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-4 text-muted">{{ __('No employee issues found.') }}</td></tr>
                    @endforelse
                </tbody>
                @if($employeeIssues->count() > 0)
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="4">{{ __('Total') }}</td>
                        <td class="text-center">{{ $employeeIssues->sum('quantity') }}</td>
                        <td></td>
                        <td>{{ number_format($employeeIssues->sum('total_price'), 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endif
@endsection
