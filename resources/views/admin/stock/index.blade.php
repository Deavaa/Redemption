@extends('layouts.admin')
@section('title', __('Stock Management'))
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fas fa-warehouse me-2"></i>{{ __('Stock Management') }}</h4>
    <div class="btn-group">
        <a href="{{ route('admin.stock.stock-in') }}" class="btn btn-success btn-sm"><i class="fas fa-plus-circle me-1"></i>{{ __('Stock In') }}</a>
        <a href="{{ route('admin.stock.stock-out') }}" class="btn btn-warning btn-sm"><i class="fas fa-minus-circle me-1"></i>{{ __('Stock Out') }}</a>
        <a href="{{ route('admin.stock.report') }}" class="btn btn-info btn-sm"><i class="fas fa-chart-bar me-1"></i>{{ __('Report') }}</a>
        <a href="{{ route('admin.stock.transactions') }}" class="btn btn-secondary btn-sm"><i class="fas fa-history me-1"></i>{{ __('Transactions') }}</a>
        <a href="{{ route('admin.stock.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>{{ __('New Item') }}</a>
    </div>
</div>

{{-- Stats Cards --}}
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
                <i class="fas fa-coins fa-2x text-success mb-2"></i>
                <h3 class="mb-0">{{ number_format($totalValue, 2) }}</h3>
                <small class="text-muted">{{ __('Total Value (ETB)') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm {{ $lowStockCount > 0 ? 'border-warning' : '' }}">
            <div class="card-body text-center">
                <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                <h3 class="mb-0 {{ $lowStockCount > 0 ? 'text-warning' : '' }}">{{ $lowStockCount }}</h3>
                <small class="text-muted">{{ __('Low Stock') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm {{ $outOfStockCount > 0 ? 'border-danger' : '' }}">
            <div class="card-body text-center">
                <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                <h3 class="mb-0 {{ $outOfStockCount > 0 ? 'text-danger' : '' }}">{{ $outOfStockCount }}</h3>
                <small class="text-muted">{{ __('Out of Stock') }}</small>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('admin.stock.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="{{ __('Search items...') }}">
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
                <select name="stock_status" class="form-select form-select-sm">
                    <option value="">{{ __('All Status') }}</option>
                    <option value="available" {{ request('stock_status') == 'available' ? 'selected' : '' }}>{{ __('Available') }}</option>
                    <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>{{ __('Low Stock') }}</option>
                    <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>{{ __('Out of Stock') }}</option>
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
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary btn-sm me-1"><i class="fas fa-filter me-1"></i>{{ __('Filter') }}</button>
                <a href="{{ route('admin.stock.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('Clear') }}</a>
            </div>
        </form>
    </div>
</div>

{{-- Stock Items Table --}}
<div class="card border-0 shadow-sm">
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
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $i => $item)
                    <tr class="{{ $item->isOutOfStock() ? 'table-danger' : ($item->isLowStock() ? 'table-warning' : '') }}">
                        <td>{{ $items->firstItem() + $i }}</td>
                        <td><code>{{ $item->code ?? '-' }}</code></td>
                        <td>
                            <a href="{{ route('admin.stock.show', $item) }}">{{ $item->name }}</a>
                            @if($item->location)
                            <br><small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>{{ $item->location }}</small>
                            @endif
                        </td>
                        <td><span class="badge bg-secondary">{{ $item->category_label }}</span></td>
                        <td>{{ $item->unit }}</td>
                        <td class="text-center fw-bold">{{ $item->quantity }}</td>
                        <td class="text-center">{{ $item->minimum_stock }}</td>
                        <td>{{ number_format($item->unit_price, 2) }}</td>
                        <td>{{ number_format($item->total_value, 2) }}</td>
                        <td>
                            @if($item->isOutOfStock())
                            <span class="badge bg-danger">{{ __('Out of Stock') }}</span>
                            @elseif($item->isLowStock())
                            <span class="badge bg-warning text-dark">{{ __('Low Stock') }}</span>
                            @else
                            <span class="badge bg-success">{{ __('Available') }}</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.stock.show', $item) }}" class="btn btn-outline-info btn-xs" title="{{ __('View') }}"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('admin.stock.edit', $item) }}" class="btn btn-outline-warning btn-xs" title="{{ __('Edit') }}"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('admin.stock.destroy', $item) }}" method="POST" style="display:inline" onsubmit="return confirm('Are you sure?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-xs" title="{{ __('Delete') }}"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="11" class="text-center py-4 text-muted">{{ __('No stock items found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $items->withQueryString()->links() }}
</div>
@endsection
